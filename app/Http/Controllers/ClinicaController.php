<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ClinicaController extends Controller
{
    /**
     * Obtener tipos de clínicas disponibles
     */
    public function getTipos()
    {
        return response()->json([
            'tipos' => config('clinica_tipos.tipos'),
            'modulos_seleccionables' => config('clinica_tipos.modulos_seleccionables'),
            'default' => config('clinica_tipos.default')
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clinicas = Clinica::with(['users', 'pacientes'])->get();
        return response()->json($clinicas);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clinicas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'tipo_clinica' => 'nullable|string|in:' . implode(',', array_keys(config('clinica_tipos.tipos'))),
            'modulos_habilitados' => 'nullable|array',
            'modulos_habilitados.*' => 'string|in:' . implode(',', array_keys(config('clinica_tipos.modulos_seleccionables'))),
            'email' => 'required|email|unique:clinicas,email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'plan' => 'required|in:profesional,clinica,empresarial',
            'duration' => 'required|in:mensual,anual',
            'precio_final' => 'required|numeric|min:0',
            'cupon' => 'nullable|string|max:50',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Datos del administrador
            'admin_nombre' => 'required|string|max:255',
            'admin_apellidoPat' => 'required|string|max:255',
            'admin_apellidoMat' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'admin_cedula' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Determinar límites según el plan
            $limites = $this->getPlanLimits($request->plan);
            
            // Crear la clínica
            $clinica = Clinica::create([
                'nombre' => $request->nombre,
                'tipo_clinica' => $request->tipo_clinica ?? 'rehabilitacion_cardiopulmonar',
                'modulos_habilitados' => $request->modulos_habilitados ?? [],
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'plan' => $request->plan,
                'duration' => $request->duration,
                'pagado' => true, // Pago procesado exitosamente
                'fecha_vencimiento' => $this->calculateExpirationDate($request->duration),
                'activa' => true,
                'permite_multiples_sucursales' => $limites['sucursales'] > 1,
                'max_sucursales' => $limites['sucursales'],
                'max_usuarios' => $limites['usuarios'],
                'max_pacientes' => $limites['pacientes'],
            ]);

            // Manejar logo si se subió
            if ($request->hasFile('logo')) {
                // Asegurar que la carpeta existe
                $directory = storage_path('app/public/clinicas/logos');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                $logoPath = $request->file('logo')->store('clinicas/logos', 'public');
                $clinica->update(['logo' => $logoPath]);
            }

            // Crear sucursal principal automáticamente
            $sucursal = $clinica->sucursales()->create([
                'nombre' => $request->nombre . ' - Principal',
                'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'es_principal' => true,
                'activa' => true,
            ]);

            // Crear usuario administrador de la clínica (super admin)
            $admin = User::create([
                'nombre' => $request->admin_nombre,
                'apellidoPat' => $request->admin_apellidoPat,
                'apellidoMat' => $request->admin_apellidoMat,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'cedula' => $request->admin_cedula,
                'isAdmin' => true,
                'isSuperAdmin' => true, // Super admin de la clínica
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id, // Asignar a sucursal principal
                'email_verified' => true,
            ]);

            // Log de la transacción de pago
            \Log::info('Clínica registrada con pago exitoso', [
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'plan' => $request->plan,
                'duration' => $request->duration,
                'precio_final' => $request->precio_final,
                'cupon' => $request->cupon,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'admin_email' => $request->admin_email,
                'limites' => $limites
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clínica registrada exitosamente. Puedes iniciar sesión con el email del administrador.',
                'clinica' => $clinica->load('users', 'sucursales'),
                'admin' => $admin,
                'sucursal' => $sucursal
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al crear clínica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la clínica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $clinica = Clinica::with(['users', 'pacientes'])->findOrFail($id);
        return response()->json($clinica);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $clinica = Clinica::findOrFail($id);
        return view('clinicas.edit', compact('clinica'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $clinica = Clinica::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:clinicas,email,' . $id,
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'plan' => 'required|in:mensual,trimestral,anual',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'pagado' => 'boolean',
            'activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['nombre', 'email', 'telefono', 'direccion', 'plan', 'pagado', 'activa']);

            // Manejar logo si se subió
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($clinica->logo) {
                    Storage::disk('public')->delete($clinica->logo);
                }
                $logoPath = $request->file('logo')->store('clinicas/logos', 'public');
                $data['logo'] = $logoPath;
            }

            $clinica->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Clínica actualizada exitosamente',
                'clinica' => $clinica->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la clínica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $clinica = Clinica::findOrFail($id);
            
            // Eliminar logo si existe
            if ($clinica->logo) {
                Storage::disk('public')->delete($clinica->logo);
            }

            $clinica->delete();

            return response()->json([
                'success' => true,
                'message' => 'Clínica eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la clínica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular fecha de vencimiento según duración
     */
    private function calculateExpirationDate($duration)
    {
        $now = now();
        
        if ($duration === 'anual') {
            return $now->addYear();
        } else {
            return $now->addMonth();
        }
    }

    /**
     * Verificar estado de suscripción de una clínica
     */
    public function checkSubscription($id)
    {
        $clinica = Clinica::findOrFail($id);
        
        return response()->json([
            'clinica' => $clinica,
            'is_active' => $clinica->isActive(),
            'is_expired' => $clinica->isExpired(),
            'days_until_expiry' => $clinica->fecha_vencimiento ? 
                now()->diffInDays($clinica->fecha_vencimiento, false) : null
        ]);
    }

    /**
     * Obtener la clínica del usuario autenticado
     */
    public function getCurrentClinica(Request $request)
    {
        $user = $request->user();
        
        if (!$user->clinica_id) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene clínica asignada'
            ], 404);
        }

        $clinica = Clinica::find($user->clinica_id);
        
        if (!$clinica) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica no encontrada'
            ], 404);
        }

        return response()->json($clinica);
    }

    /**
     * Renovar suscripción de una clínica
     */
    public function renewSubscription(Request $request, $id)
    {
        $clinica = Clinica::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'plan' => 'required|in:mensual,trimestral,anual',
            'pagado' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clinica->update([
                'plan' => $request->plan,
                'pagado' => $request->pagado ?? true,
                'fecha_vencimiento' => $this->calculateExpirationDate($request->plan),
                'activa' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción renovada exitosamente',
                'clinica' => $clinica->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar clínica del usuario autenticado
     */
    public function updateCurrentClinica(Request $request)
    {
        $user = $request->user();
        
        if (!$user->clinica_id) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene clínica asignada'
            ], 404);
        }

        $clinica = Clinica::find($user->clinica_id);
        
        if (!$clinica) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:clinicas,email,' . $clinica->id,
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'plan' => 'nullable|in:mensual,trimestral,anual',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['nombre', 'email', 'telefono', 'direccion']);
            
            // Solo superAdmin puede cambiar el plan
            if ($user->isSuperAdmin && $request->has('plan')) {
                $data['plan'] = $request->plan;
            }

            $clinica->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Clínica actualizada exitosamente',
                'data' => $clinica->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la clínica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir logo de la clínica
     */
    public function uploadLogo(Request $request)
    {
        $user = $request->user();
        
        if (!$user->clinica_id) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene clínica asignada'
            ], 404);
        }

        $clinica = Clinica::find($user->clinica_id);
        
        if (!$clinica) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Asegurar que la carpeta existe con permisos correctos
            $directory = storage_path('app/public/clinicas/logos');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Eliminar logo anterior si existe
            if ($clinica->logo) {
                Storage::disk('public')->delete($clinica->logo);
            }

            // Subir nuevo logo
            $logoPath = $request->file('logo')->store('clinicas/logos', 'public');
            $clinica->update(['logo' => $logoPath]);

            return response()->json([
                'success' => true,
                'message' => 'Logo actualizado exitosamente',
                'data' => $clinica->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener límites según el plan
     */
    private function getPlanLimits($plan)
    {
        $limits = [
            'profesional' => [
                'sucursales' => 1,
                'usuarios' => 3,
                'pacientes' => 200,
                'ia_mensual' => 500
            ],
            'clinica' => [
                'sucursales' => 5,
                'usuarios' => 15,
                'pacientes' => 1000,
                'ia_mensual' => 2500
            ],
            'empresarial' => [
                'sucursales' => 999, // Ilimitado
                'usuarios' => 999, // Ilimitado
                'pacientes' => 999999, // Ilimitado
                'ia_mensual' => 999999 // Ilimitado
            ]
        ];

        return $limits[$plan] ?? $limits['profesional'];
    }
}
