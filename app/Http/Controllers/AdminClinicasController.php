<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para gestión de clínicas desde el backoffice
 */
class AdminClinicasController extends Controller
{
    /**
     * Listar todas las clínicas con estadísticas
     */
    public function index(Request $request)
    {
        $query = Clinica::query()
            ->withCount(['users', 'pacientes', 'sucursales'])
            ->with(['propietario:id,nombre,apellidoPat,email']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('activa')) {
            $query->where('activa', $request->boolean('activa'));
        }

        if ($request->filled('es_consultorio_privado')) {
            $query->where('es_consultorio_privado', $request->boolean('es_consultorio_privado'));
        }

        $clinicas = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'clinicas' => $clinicas,
        ]);
    }

    /**
     * Ver detalle de una clínica
     */
    public function show(Clinica $clinica)
    {
        $clinica->load([
            'users:id,nombre,apellidoPat,email,created_at',
            'propietario:id,nombre,apellidoPat,email',
            'sucursales:id,clinica_id,nombre,direccion,es_principal',
        ]);

        $clinica->loadCount(['users', 'pacientes', 'sucursales']);

        // Obtener pagos recientes
        $pagos = Payment::where('clinica_id', $clinica->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'clinica' => $clinica,
            'pagos' => $pagos,
        ]);
    }

    /**
     * Crear clínica + usuario admin (sin pago, desde backoffice)
     * Similar a ClinicaController@store pero sin requerir pago.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos de la clínica
            'nombre' => 'required|string|max:255',
            'tipo_clinica' => 'nullable|string|in:' . implode(',', array_keys(config('clinica_tipos.tipos'))),
            'modulos_habilitados' => 'nullable|array',
            'modulos_habilitados.*' => 'string|in:' . implode(',', array_keys(config('clinica_tipos.modulos_seleccionables'))),
            'email' => 'required|email|unique:clinicas,email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'plan' => 'nullable|in:profesional,clinica,empresarial',
            'duration' => 'nullable|in:mensual,anual',
            'meses_gratis' => 'nullable|integer|min:1|max:36',
            'facturacion_addon_activo' => 'nullable|boolean',
            // Datos del administrador
            'admin_nombre' => 'required|string|max:255',
            'admin_apellidoPat' => 'required|string|max:255',
            'admin_apellidoMat' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'admin_cedula' => 'nullable|string|max:20',
            'admin_rol' => 'nullable|string|in:' . config('roles.validacion_in'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $meses = $request->meses_gratis ?? 12;
            $plan = $request->plan ?? 'profesional';
            $duration = $request->duration ?? 'anual';

            // Límites del plan
            $limites = [
                'profesional' => ['sucursales' => 1, 'usuarios' => 5, 'pacientes' => 500],
                'clinica' => ['sucursales' => 3, 'usuarios' => 15, 'pacientes' => 2000],
                'empresarial' => ['sucursales' => 10, 'usuarios' => 50, 'pacientes' => 10000],
            ][$plan];

            // Crear la clínica
            $clinica = Clinica::create([
                'nombre' => $request->nombre,
                'tipo_clinica' => $request->tipo_clinica ?? 'general',
                'modulos_habilitados' => $request->modulos_habilitados ?? [],
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'plan' => $plan,
                'duration' => $duration,
                'pagado' => true,
                'fecha_vencimiento' => now()->addMonths($meses),
                'activa' => true,
                'permite_multiples_sucursales' => $limites['sucursales'] > 1,
                'max_sucursales' => $limites['sucursales'],
                'max_usuarios' => $limites['usuarios'],
                'max_pacientes' => $limites['pacientes'],
                'es_consultorio_privado' => false,
                'facturacion_addon_activo' => $request->boolean('facturacion_addon_activo'),
            ]);

            // Crear sucursal principal
            $sucursal = Sucursal::create([
                'clinica_id' => $clinica->id,
                'nombre' => $request->nombre . ' - Principal',
                'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'es_principal' => true,
                'activa' => true,
            ]);

            // Crear usuario administrador
            $admin = User::create([
                'nombre' => $request->admin_nombre,
                'apellidoPat' => $request->admin_apellidoPat,
                'apellidoMat' => $request->admin_apellidoMat,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'cedula' => $request->admin_cedula,
                'rol' => $request->admin_rol ?: null,
                'isAdmin' => true,
                'isSuperAdmin' => true,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'email_verified' => true,
            ]);

            // Vincular como propietario
            $admin->clinicas()->attach($clinica->id, User::pivotPropietarioConsultorio());

            DB::commit();

            Log::channel('soporte')->info('Clínica creada desde backoffice', [
                'clinica_id' => $clinica->id,
                'admin_email' => $admin->email,
                'plan' => $plan,
                'meses_gratis' => $meses,
                'operador_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clínica creada correctamente',
                'clinica' => $clinica->fresh()->load('propietario'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando clínica desde backoffice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la clínica: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar datos de una clínica
     */
    public function update(Request $request, Clinica $clinica)
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'telefono' => 'sometimes|string|max:20',
            'direccion' => 'sometimes|string|max:500',
            'activa' => 'sometimes|boolean',
        ]);

        $clinica->update($request->only([
            'nombre', 'email', 'telefono', 'direccion', 'activa'
        ]));

        Log::channel('soporte')->info('Clínica actualizada desde admin', [
            'clinica_id' => $clinica->id,
            'admin_id' => $request->user()->id,
            'cambios' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clínica actualizada correctamente',
            'clinica' => $clinica->fresh(),
        ]);
    }

    /**
     * Activar/Desactivar clínica
     */
    public function toggleStatus(Request $request, Clinica $clinica)
    {
        $clinica->activa = !$clinica->activa;
        $clinica->save();

        Log::channel('soporte')->info('Estado de clínica cambiado', [
            'clinica_id' => $clinica->id,
            'nuevo_estado' => $clinica->activa ? 'activa' : 'inactiva',
            'admin_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la clínica actualizado',
            'clinica' => $clinica,
        ]);
    }

    /**
     * Obtener estadísticas generales de clínicas
     */
    public function stats()
    {
        $stats = [
            'total_clinicas' => Clinica::count(),
            'clinicas_activas' => Clinica::where('activa', true)->count(),
            'clinicas_inactivas' => Clinica::where('activa', false)->count(),
            'por_tipo' => Clinica::select('tipo', DB::raw('count(*) as total'))
                ->groupBy('tipo')
                ->pluck('total', 'tipo'),
            'nuevas_este_mes' => Clinica::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
