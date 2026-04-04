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
use Carbon\Carbon;

/**
 * Controlador para gestión de consultorios privados desde el backoffice
 * 
 * NOTA: Los consultorios privados son registros en la tabla 'clinicas'
 * con es_consultorio_privado = true
 */
class AdminConsultoriosController extends Controller
{
    /**
     * Listar todos los consultorios privados con estadísticas
     */
    public function index(Request $request)
    {
        $query = Clinica::query()
            ->where('es_consultorio_privado', true)
            ->withCount(['users', 'pacientes'])
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

        if ($request->filled('activa')) {
            $query->where('activa', $request->boolean('activa'));
        }

        if ($request->filled('pagado')) {
            $query->where('pagado', $request->boolean('pagado'));
        }

        if ($request->filled('vencido')) {
            if ($request->boolean('vencido')) {
                $query->where('fecha_vencimiento', '<', now());
            } else {
                $query->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                      ->orWhere('fecha_vencimiento', '>=', now());
                });
            }
        }

        $consultorios = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'consultorios' => $consultorios,
        ]);
    }

    /**
     * Ver detalle de un consultorio privado
     */
    public function show(Clinica $consultorio)
    {
        // Verificar que sea un consultorio privado
        if (!$consultorio->es_consultorio_privado) {
            return response()->json([
                'success' => false,
                'message' => 'Este registro no es un consultorio privado',
            ], 400);
        }

        $consultorio->load([
            'users:id,nombre,apellidoPat,email,created_at',
            'propietario:id,nombre,apellidoPat,email',
        ]);

        $consultorio->loadCount(['users', 'pacientes']);

        // Obtener pagos recientes
        $pagos = Payment::where('clinica_id', $consultorio->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'consultorio' => $consultorio,
            'pagos' => $pagos,
        ]);
    }

    /**
     * Provisionar consultorio privado (backoffice).
     * Dos modos:
     * 1. Usuario existente: owner_email
     * 2. Usuario nuevo: admin_nombre, admin_apellidoPat, admin_email, admin_password, etc.
     */
    public function provisionar(Request $request)
    {
        // Determinar si es usuario nuevo o existente
        $esUsuarioNuevo = $request->filled('admin_email');

        // Reglas de validación base
        $rules = [
            'nombre' => 'required|string|max:255',
            'tipo_clinica' => 'nullable|string|in:' . implode(',', array_keys(config('clinica_tipos.tipos'))),
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'co_owner_email' => 'nullable|email|exists:users,email',
        ];

        if ($esUsuarioNuevo) {
            // Usuario nuevo
            $rules['admin_nombre'] = 'required|string|max:255';
            $rules['admin_apellidoPat'] = 'required|string|max:255';
            $rules['admin_apellidoMat'] = 'required|string|max:255';
            $rules['admin_email'] = 'required|email|unique:users,email';
            $rules['admin_password'] = 'required|string|min:8';
            $rules['admin_cedula'] = 'nullable|string|max:20';
            $rules['admin_rol'] = 'nullable|string|in:' . config('roles.validacion_in');
        } else {
            // Usuario existente
            $rules['owner_email'] = 'required|email|exists:users,email';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Obtener o crear el propietario
            if ($esUsuarioNuevo) {
                $owner = User::create([
                    'nombre' => $request->admin_nombre,
                    'apellidoPat' => $request->admin_apellidoPat,
                    'apellidoMat' => $request->admin_apellidoMat,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'cedula' => $request->admin_cedula,
                    'rol' => $request->admin_rol ?: null,
                    'isAdmin' => true,
                    'isSuperAdmin' => true,
                    'email_verified' => true,
                ]);
            } else {
                $owner = User::where('email', $request->owner_email)->firstOrFail();
            }

            $consultorio = Clinica::create([
                'nombre' => $request->nombre,
                'tipo_clinica' => $request->tipo_clinica ?? 'general',
                'modulos_habilitados' => [],
                'email' => $request->email ?? $owner->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'plan' => 'profesional',
                'duration' => 'mensual',
                'pagado' => true,
                'activa' => true,
                'es_consultorio_privado' => true,
                'propietario_user_id' => $owner->id,
                'permite_multiples_sucursales' => false,
                'max_sucursales' => 1,
                'max_usuarios' => 10,
                'max_pacientes' => 500,
                'fecha_vencimiento' => now()->addYear(),
            ]);

            $sucursal = Sucursal::create([
                'clinica_id' => $consultorio->id,
                'nombre' => $request->nombre,
                'es_principal' => true,
                'activa' => true,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
            ]);

            // Asignar clinica_id y sucursal_id al usuario
            $owner->update([
                'clinica_id' => $consultorio->id,
                'sucursal_id' => $sucursal->id,
            ]);

            // Vincular propietario
            if (!$owner->clinicas()->where('clinica_id', $consultorio->id)->exists()) {
                $owner->clinicas()->attach($consultorio->id, User::pivotPropietarioConsultorio());
            }

            // Vincular co-propietario si existe
            if ($request->filled('co_owner_email') && strcasecmp($request->co_owner_email, $owner->email) !== 0) {
                /** @var User $co */
                $co = User::where('email', $request->co_owner_email)->firstOrFail();
                if (!$co->clinicas()->where('clinica_id', $consultorio->id)->exists()) {
                    $co->clinicas()->attach($consultorio->id, User::pivotPropietarioConsultorio());
                }
            }

            DB::commit();

            Log::channel('soporte')->info('Consultorio provisionado desde backoffice', [
                'consultorio_id' => $consultorio->id,
                'owner_email' => $request->owner_email,
                'admin_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consultorio creado correctamente',
                'consultorio' => $consultorio->fresh()->load('propietario'),
                'sucursal_principal_id' => $sucursal->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al provisionar consultorio desde backoffice', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear consultorio: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Extender suscripción de un consultorio privado
     */
    public function extenderSuscripcion(Request $request, Clinica $consultorio)
    {
        $request->validate([
            'meses' => 'required|integer|min:1|max:24',
            'motivo' => 'required|string|max:500',
        ]);

        // Verificar que sea un consultorio privado
        if (!$consultorio->es_consultorio_privado) {
            return response()->json([
                'success' => false,
                'message' => 'Este registro no es un consultorio privado',
            ], 400);
        }

        // Calcular nueva fecha de vencimiento
        $fechaBase = $consultorio->fecha_vencimiento && $consultorio->fecha_vencimiento > now()
            ? $consultorio->fecha_vencimiento
            : now();
        
        $nuevaFechaFin = Carbon::parse($fechaBase)->addMonths($request->meses);

        $consultorio->update([
            'fecha_vencimiento' => $nuevaFechaFin,
            'pagado' => true,
            'activa' => true,
        ]);

        // Registrar como pago
        Payment::create([
            'clinica_id' => $consultorio->id,
            'user_id' => $consultorio->propietario_user_id,
            'amount' => 0,
            'currency' => 'mxn',
            'status' => 'completed',
            'payment_method' => 'admin_extension',
            'stripe_payment_id' => 'admin_extension_' . uniqid(),
            'metadata' => [
                'tipo' => 'extension_manual',
                'meses' => $request->meses,
                'motivo' => $request->motivo,
                'fecha_anterior' => $fechaBase,
                'nueva_fecha' => $nuevaFechaFin,
                'admin_id' => $request->user()->id ?? null,
            ],
        ]);

        Log::channel('soporte')->info('Suscripción de consultorio extendida', [
            'consultorio_id' => $consultorio->id,
            'meses_agregados' => $request->meses,
            'nueva_fecha_fin' => $nuevaFechaFin,
            'admin_id' => $request->user()->id ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Suscripción extendida hasta {$nuevaFechaFin->format('d/m/Y')}",
            'consultorio' => $consultorio->fresh(),
        ]);
    }

    /**
     * Activar/Desactivar consultorio
     */
    public function toggleStatus(Request $request, Clinica $consultorio)
    {
        // Verificar que sea un consultorio privado
        if (!$consultorio->es_consultorio_privado) {
            return response()->json([
                'success' => false,
                'message' => 'Este registro no es un consultorio privado',
            ], 400);
        }

        $consultorio->activa = !$consultorio->activa;
        $consultorio->save();

        Log::channel('soporte')->info('Estado de consultorio cambiado', [
            'consultorio_id' => $consultorio->id,
            'nuevo_estado' => $consultorio->activa ? 'activo' : 'inactivo',
            'admin_id' => $request->user()->id ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del consultorio actualizado',
            'consultorio' => $consultorio,
        ]);
    }

    /**
     * Estadísticas de consultorios privados
     */
    public function stats()
    {
        $baseQuery = Clinica::where('es_consultorio_privado', true);

        $stats = [
            'total_consultorios' => (clone $baseQuery)->count(),
            'activos' => (clone $baseQuery)->where('activa', true)->count(),
            'inactivos' => (clone $baseQuery)->where('activa', false)->count(),
            'con_suscripcion_activa' => (clone $baseQuery)
                ->where('pagado', true)
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                      ->orWhere('fecha_vencimiento', '>=', now());
                })
                ->count(),
            'vencidos' => (clone $baseQuery)
                ->where('fecha_vencimiento', '<', now())
                ->count(),
            'nuevos_este_mes' => (clone $baseQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'ingresos_mes' => Payment::whereHas('clinica', function ($q) {
                    $q->where('es_consultorio_privado', true);
                })
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
