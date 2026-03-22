<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\ConsultorioInvitacion;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\UserClinica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * ConsultorioController
 *
 * Gestiona el ciclo de vida del consultorio privado de un médico:
 *   - Crear consultorio privado (un médico registrado en una clínica abre su propio espacio)
 *   - Listar todos los espacios de trabajo a los que pertenece
 *   - Cambiar el espacio de trabajo activo (clinica_activa_id)
 *   - Invitar colaboradores por email
 *   - Aceptar / rechazar invitación vía token
 *   - Administrar colaboradores
 */
class ConsultorioController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════
    // SETUP — Crear consultorio privado
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/consultorio/setup
     *
     * El doctor crea su propio consultorio privado.
     * Se crea una Clinica con es_consultorio_privado = true,
     * una Sucursal principal, y se vincula al usuario en user_clinicas como propietario.
     */
    public function setup(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'tipo_clinica' => 'nullable|string|in:' . implode(',', array_keys(config('clinica_tipos.tipos'))),
            'telefono'    => 'nullable|string|max:20',
            'direccion'   => 'nullable|string|max:500',
            'email'       => 'nullable|email|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // VALIDACIÓN 1: Verificar que tenga suscripción activa
        if (!$user->tieneSuscripcionConsultorioActiva()) {
            return response()->json([
                'success' => false,
                'message' => 'Necesitas una suscripción activa para crear consultorios privados.',
                'requires_subscription' => true,
            ], 403);
        }

        // VALIDACIÓN 2: Verificar que no haya alcanzado el límite
        if (!$user->puedeCrearConsultorioAdicional()) {
            $actual = $user->cantidad_consultorios_privados;
            $limite = $user->limite_consultorios;
            
            return response()->json([
                'success' => false,
                'message' => "Has alcanzado el límite de consultorios ($limite). Compra consultorios adicionales para crear más.",
                'limit_reached' => true,
                'current' => $actual,
                'max' => $limite
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Crear la clínica del consultorio
            $consultorio = Clinica::create([
                'nombre'                     => $request->nombre,
                'tipo_clinica'               => $request->tipo_clinica ?? 'general',
                'modulos_habilitados'        => [],
                'email'                      => $request->email ?? $user->email,
                'telefono'                   => $request->telefono,
                'direccion'                  => $request->direccion,
                'plan'                       => 'profesional',
                'duration'                   => 'mensual',
                'pagado'                     => true,
                'activa'                     => true,
                'es_consultorio_privado'     => true,
                'propietario_user_id'        => $user->id,
                'permite_multiples_sucursales' => false,
                'max_sucursales'             => 1,
                'max_usuarios'               => 5,
                'max_pacientes'              => 500,
                'fecha_vencimiento'          => now()->addYear(),
            ]);

            // 2. Sucursal principal automática
            Sucursal::create([
                'clinica_id'   => $consultorio->id,
                'nombre'       => $request->nombre,
                'es_principal' => true,
                'activa'       => true,
                'direccion'    => $request->direccion,
                'telefono'     => $request->telefono,
            ]);

            // 3. Vincular al propietario en la tabla pivot
            $user->clinicas()->attach($consultorio->id, [
                'rol_en_clinica' => 'propietario',
                'activa'         => true,
            ]);

            // 4. Cambiar automáticamente al nuevo consultorio como espacio activo
            $user->update(['clinica_activa_id' => $consultorio->id]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Consultorio privado creado exitosamente.',
                'consultorio' => $consultorio->load('sucursales'),
                'user'        => $user->fresh()->load('clinica', 'clinicaActiva'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el consultorio: ' . $e->getMessage()
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // WORKSPACES — Listar y cambiar espacio de trabajo
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /api/consultorio/mis-clinicas
     *
     * Devuelve todos los espacios a los que pertenece el usuario:
     * su clínica original + todos los consultorios/clínicas de user_clinicas.
     */
    public function misClinicas(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Clinica original (asignación fija)
        $clinicaOriginal = null;
        if ($user->clinica_id) {
            $clinicaOriginal = Clinica::find($user->clinica_id);
        }

        // Clínicas por pivot
        $clinicasPivot = $user->clinicas()
            ->wherePivot('activa', true)
            ->with('sucursales')
            ->get()
            ->map(function ($c) use ($user) {
                return [
                    'id'                   => $c->id,
                    'nombre'               => $c->nombre,
                    'tipo_clinica'         => $c->tipo_clinica,
                    'logo_url'             => $c->logo_url,
                    'es_consultorio_privado' => $c->es_consultorio_privado,
                    'activa'               => $c->activa,
                    'rol_en_clinica'       => $c->pivot->rol_en_clinica,
                    'rol_sistema'          => $user->role,
                    'es_activa_ahora'      => $c->id === ($user->clinica_activa_id ?? $user->clinica_id),
                ];
            });

        // Incluir la clínica original si no está ya en el pivot
        $result = $clinicasPivot->toArray();
        if ($clinicaOriginal) {
            $yaEsta = collect($result)->pluck('id')->contains($clinicaOriginal->id);
            if (!$yaEsta) {
                array_unshift($result, [
                    'id'                   => $clinicaOriginal->id,
                    'nombre'               => $clinicaOriginal->nombre,
                    'tipo_clinica'         => $clinicaOriginal->tipo_clinica,
                    'logo_url'             => $clinicaOriginal->logo_url,
                    'es_consultorio_privado' => false,
                    'activa'               => $clinicaOriginal->activa,
                    'rol_en_clinica'       => 'miembro',
                    'rol_sistema'          => $user->role,
                    'es_activa_ahora'      => $clinicaOriginal->id === ($user->clinica_activa_id ?? $user->clinica_id),
                ]);
            }
        }

        return response()->json([
            'success'  => true,
            'clinicas' => $result,
            'clinica_activa_id' => $user->clinica_activa_id ?? $user->clinica_id,
            'consultorios_info' => [
                'consultorios_adicionales' => $clinicaOriginal ? $clinicaOriginal->consultorios_adicionales : 0,
                'consultorios_usados' => $clinicasPivot->where('es_consultorio_privado', true)->count(),
                'puede_crear_mas' => $clinicaOriginal 
                    ? ($clinicasPivot->where('es_consultorio_privado', true)->count() < $clinicaOriginal->consultorios_adicionales)
                    : false
            ],
            'suscripcion_usuario' => [
                'tiene_suscripcion' => $user->tiene_suscripcion_consultorio,
                'plan' => $user->plan_consultorio,
                'suscripcion_activa' => $user->tieneSuscripcionConsultorioActiva(),
                'en_trial' => $user->trial_ends_at && now()->lessThan($user->trial_ends_at),
                'fecha_vencimiento' => $user->suscripcion_fin?->format('Y-m-d'),
                'limite_consultorios' => $user->limite_consultorios,
                'consultorios_usados' => $user->cantidad_consultorios_privados,
                'puede_crear_consultorio' => $user->puedeCrearConsultorioAdicional(),
            ]
        ]);
    }

    /**
     * POST /api/consultorio/cambiar-workspace
     *
     * Cambia la clínica activa del usuario.
     * Body: { clinica_id: int }
     */
    public function cambiarWorkspace(Request $request): JsonResponse
    {
        $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $clinicaId = (int) $request->clinica_id;

        // Verificar que el usuario tiene acceso a esa clínica
        $tieneAcceso = $user->clinica_id === $clinicaId
            || $user->clinicas()->wherePivot('activa', true)->where('clinica_id', $clinicaId)->exists();

        if (!$tieneAcceso) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esa clínica.'
            ], 403);
        }

        // Si es la clínica original, limpiar clinica_activa_id (volver al default)
        $nuevaActiva = ($clinicaId === $user->clinica_id) ? null : $clinicaId;
        $user->update(['clinica_activa_id' => $nuevaActiva]);

        $clinica = Clinica::with('sucursales')->find($clinicaId);

        return response()->json([
            'success'      => true,
            'message'      => 'Espacio de trabajo cambiado correctamente.',
            'clinica'      => $clinica,
            'user'         => $user->fresh()->load('clinica', 'clinicaActiva', 'sucursal'),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // INVITACIONES
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/consultorio/invitar
     *
     * El propietario de un consultorio invita a un colaborador por email.
     * Body: { clinica_id, email, rol }
     */
    public function invitar(Request $request): JsonResponse
    {
        $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'email'      => 'required|email',
            'rol'        => 'required|in:colaborador,visor',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $clinicaId = (int) $request->clinica_id;

        // Verificar que el usuario es propietario o admin de esa clínica
        $esAdmin = $user->clinica_id === $clinicaId && $user->isAdmin();
        $esPropietario = $user->clinicas()
            ->wherePivot('rol_en_clinica', 'propietario')
            ->where('clinica_id', $clinicaId)
            ->exists();

        if (!$esAdmin && !$esPropietario) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el propietario puede invitar colaboradores.'
            ], 403);
        }

        // No invitar al mismo email si ya tiene acceso
        $yaEsMiembro = User::where('email', $request->email)
            ->where(function ($q) use ($clinicaId) {
                $q->where('clinica_id', $clinicaId)
                  ->orWhereHas('clinicas', fn($q2) => $q2->where('clinicas.id', $clinicaId));
            })->exists();

        if ($yaEsMiembro) {
            return response()->json([
                'success' => false,
                'message' => 'Ese usuario ya tiene acceso a esta clínica.'
            ], 409);
        }

        // Invalidar invitaciones anteriores pendientes del mismo email/clinica
        ConsultorioInvitacion::where('clinica_id', $clinicaId)
            ->where('email', $request->email)
            ->where('estado', 'pendiente')
            ->update(['estado' => 'expirada']);

        // Crear la invitación
        $invitacion = ConsultorioInvitacion::create([
            'clinica_id'   => $clinicaId,
            'email'        => $request->email,
            'rol'          => $request->rol,
            'invitado_por' => $user->id,
        ]);

        $clinica = Clinica::find($clinicaId);
        $acceptUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/invitacion/{$invitacion->token}";

        // Enviar email de invitación
        try {
            Mail::send('emails.consultorio-invitacion', [
                'invitante'  => $user,
                'clinica'    => $clinica,
                'acceptUrl'  => $acceptUrl,
                'rol'        => $request->rol,
            ], function ($message) use ($request, $clinica, $user) {
                $message->to($request->email)
                        ->subject("Invitación al consultorio {$clinica->nombre} - Dr. {$user->apellidoPat}");
            });
        } catch (\Exception $e) {
            \Log::warning('No se pudo enviar email de invitación: ' . $e->getMessage());
        }

        return response()->json([
            'success'    => true,
            'message'    => "Invitación enviada a {$request->email}.",
            'invitacion' => $invitacion,
        ], 201);
    }

    /**
     * GET /api/consultorio/invitacion/{token}
     *
     * Devuelve la info de una invitación pendiente (para mostrar preview antes de aceptar/rechazar).
     * Esta ruta NO requiere auth — el invitado puede no tener cuenta aún.
     */
    public function verInvitacion(string $token): JsonResponse
    {
        $invitacion = ConsultorioInvitacion::with(['clinica', 'invitante'])
            ->where('token', $token)
            ->first();

        if (!$invitacion) {
            return response()->json(['success' => false, 'message' => 'Invitación no encontrada.'], 404);
        }

        $invitacion->checkExpiry();

        if (!$invitacion->estaVigente()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitación ya no es válida.',
                'estado'  => $invitacion->estado,
            ], 410);
        }

        return response()->json([
            'success'    => true,
            'invitacion' => [
                'clinica'    => $invitacion->clinica->only(['id', 'nombre', 'tipo_clinica', 'logo_url']),
                'invitante'  => $invitacion->invitante->only(['nombre', 'apellidoPat', 'email']),
                'rol'        => $invitacion->rol,
                'expires_at' => $invitacion->expires_at,
            ],
        ]);
    }

    /**
     * POST /api/consultorio/invitacion/{token}/aceptar
     *
     * El usuario acepta la invitación — se vincula a la clínica en user_clinicas.
     * Requiere auth.
     */
    public function aceptarInvitacion(string $token): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $invitacion = ConsultorioInvitacion::with('clinica')
            ->where('token', $token)
            ->first();

        if (!$invitacion) {
            return response()->json(['success' => false, 'message' => 'Invitación no encontrada.'], 404);
        }

        $invitacion->checkExpiry();

        if (!$invitacion->estaVigente()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitación ya no es válida.',
                'estado'  => $invitacion->estado,
            ], 410);
        }

        if (strtolower($user->email) !== strtolower($invitacion->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitación pertenece a otro email.',
            ], 403);
        }

        // Evitar duplicados
        $yaVinculado = $user->clinicas()
            ->where('clinica_id', $invitacion->clinica_id)
            ->exists();

        if (!$yaVinculado) {
            $user->clinicas()->attach($invitacion->clinica_id, [
                'rol_en_clinica' => $invitacion->rol,
                'activa'         => true,
                'invitado_por'   => $invitacion->invitado_por,
            ]);
        }

        $invitacion->update(['estado' => 'aceptada']);

        return response()->json([
            'success'  => true,
            'message'  => "Te uniste a {$invitacion->clinica->nombre} como {$invitacion->rol}.",
            'clinica'  => $invitacion->clinica,
            'user'     => $user->fresh()->load('clinica', 'clinicaActiva'),
        ]);
    }

    /**
     * POST /api/consultorio/invitacion/{token}/rechazar
     *
     * El usuario rechaza la invitación.
     */
    public function rechazarInvitacion(string $token): JsonResponse
    {
        $invitacion = ConsultorioInvitacion::where('token', $token)->first();

        if (!$invitacion || !$invitacion->estaVigente()) {
            return response()->json(['success' => false, 'message' => 'Invitación no válida.'], 410);
        }

        $invitacion->update(['estado' => 'rechazada']);

        return response()->json(['success' => true, 'message' => 'Invitación rechazada.']);
    }

    // ═══════════════════════════════════════════════════════════════════
    // COLABORADORES
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /api/consultorio/{clinicaId}/colaboradores
     *
     * Lista todos los miembros de una clínica/consultorio.
     */
    public function listarColaboradores(int $clinicaId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $efectivoId = $user->clinica_activa_id ?? $user->clinica_id;

        if ($efectivoId !== $clinicaId) {
            return response()->json(['success' => false, 'message' => 'Sin acceso.'], 403);
        }

        $clinica = Clinica::with(['miembros' => fn($q) => $q->wherePivot('activa', true)])
            ->findOrFail($clinicaId);

        $colaboradores = $clinica->miembros->map(fn($u) => [
            'id'             => $u->id,
            'nombre'         => $u->nombre_con_titulo,
            'email'          => $u->email,
            'rol_en_clinica' => $u->pivot->rol_en_clinica,
            'imagen'         => $u->imagen,
        ]);

        return response()->json(['success' => true, 'colaboradores' => $colaboradores]);
    }

    /**
     * DELETE /api/consultorio/{clinicaId}/colaboradores/{userId}
     *
     * El propietario elimina a un colaborador del consultorio.
     */
    public function eliminarColaborador(int $clinicaId, int $userId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Verificar que quien ejecuta es propietario o admin
        $esPropietario = $user->clinicas()
            ->wherePivot('rol_en_clinica', 'propietario')
            ->where('clinica_id', $clinicaId)
            ->exists();
        $esAdmin = $user->clinica_id === $clinicaId && $user->isAdmin();

        if (!$esPropietario && !$esAdmin) {
            return response()->json(['success' => false, 'message' => 'Sin permiso.'], 403);
        }

        if ($userId === $user->id) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminarte a ti mismo.'], 422);
        }

        $target = User::findOrFail($userId);
        $target->clinicas()->detach($clinicaId);

        // Si tenía activa esa clínica, resetear
        if ($target->clinica_activa_id === $clinicaId) {
            $target->update(['clinica_activa_id' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Colaborador eliminado del consultorio.']);
    }

    /**
     * GET /api/consultorio/{clinicaId}/invitaciones
     *
     * Lista invitaciones pendientes de un consultorio (para el propietario).
     */
    public function invitacionesPendientes(int $clinicaId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $esPropietario = $user->clinicas()
            ->wherePivot('rol_en_clinica', 'propietario')
            ->where('clinica_id', $clinicaId)
            ->exists();
        $esAdmin = $user->clinica_id === $clinicaId && $user->isAdmin();

        if (!$esPropietario && !$esAdmin) {
            return response()->json(['success' => false, 'message' => 'Sin acceso.'], 403);
        }

        $invitaciones = ConsultorioInvitacion::where('clinica_id', $clinicaId)
            ->where('estado', 'pendiente')
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('invitante:id,nombre,apellidoPat')
            ->get(['id', 'email', 'rol', 'invitado_por', 'created_at', 'expires_at']);

        return response()->json(['success' => true, 'invitaciones' => $invitaciones]);
    }
}
