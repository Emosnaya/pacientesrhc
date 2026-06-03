<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Controlador de autenticación para el backoffice de Lynkamed
 */
class AdminAuthController extends Controller
{
    /**
     * Login de usuario admin
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $adminUser = AdminUser::where('email', $request->email)->first();

        if (!$adminUser || !Hash::check($request->password, $adminUser->password)) {
            Log::channel('soporte')->warning('Intento de login fallido en admin panel', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        if (!$adminUser->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario desactivado. Contacta al administrador.',
            ], 403);
        }

        // Actualizar último login
        $adminUser->updateLastLogin($request->ip());

        // Crear token
        $token = $adminUser->createToken('admin-panel', ['admin'])->plainTextToken;

        Log::channel('soporte')->info('Login exitoso en admin panel', [
            'admin_id' => $adminUser->id,
            'email' => $adminUser->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => $adminUser->role,
            ],
        ]);
    }

    /**
     * Logout de usuario admin
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => [
                    'can_manage_clinicas' => $user->canManageClinicas(),
                    'can_manage_suscripciones' => $user->canManageSuscripciones(),
                    'can_view_soporte' => $user->canViewSoporte(),
                    'is_superadmin' => $user->isSuperAdmin(),
                ],
            ],
        ]);
    }

    /**
     * Enviar correo de recuperación de contraseña al admin
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $admin = AdminUser::where('email', $request->email)->where('activo', true)->first();

        // Respuesta genérica para no revelar si el email existe
        $genericMsg = 'Si el correo existe, recibirás un enlace de recuperación en los próximos minutos.';

        if (!$admin) {
            return response()->json(['success' => true, 'message' => $genericMsg]);
        }

        // Generar token y guardarlo
        $token = Str::random(64);
        DB::table('admin_password_resets')->updateOrInsert(
            ['email' => $admin->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Enviar correo
        try {
            Mail::send('emails.admin-reset-password', [
                'admin' => $admin,
                'token' => $token,
                'resetUrl' => (config('app.admin_frontend_url') ?: env('ADMIN_FRONTEND_URL', 'http://localhost:5174')) . '/reset-password?token=' . $token . '&email=' . urlencode($admin->email),
            ], function ($m) use ($admin) {
                $m->to($admin->email, $admin->name)
                  ->subject('Recuperación de contraseña — Panel Lynkamed');
            });
        } catch (\Exception $e) {
            Log::error('Error enviando correo reset admin: ' . $e->getMessage());
        }

        Log::info('Reset password solicitado para admin', ['email' => $admin->email, 'ip' => $request->ip()]);

        return response()->json(['success' => true, 'message' => $genericMsg]);
    }

    /**
     * Cambiar contraseña usando el token del correo
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('admin_password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['success' => false, 'message' => 'Token inválido o expirado.'], 422);
        }

        // El token expira en 1 hora
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('admin_password_resets')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'El enlace ha expirado. Solicita uno nuevo.'], 422);
        }

        $admin = AdminUser::where('email', $request->email)->where('activo', true)->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $admin->update(['password' => Hash::make($request->password)]);
        DB::table('admin_password_resets')->where('email', $request->email)->delete();

        Log::info('Contraseña de admin actualizada', ['email' => $admin->email]);

        return response()->json(['success' => true, 'message' => '¡Contraseña actualizada exitosamente!']);
    }

    /**
     * Actividad detallada: logins recientes de usuarios y pacientes que abrieron el portal.
     * Solo accesible para admins internos de Lynkamed — nunca expuesto a clínicas.
     */
    public function actividadDetallada(Request $request)
    {
        $limit = min((int) ($request->query('limit', 100)), 200);
        $desde = $request->query('desde', now()->subDays(30)->toDateString());
        $hasta = $request->query('hasta', now()->toDateString());
        // Incluir todo el día "hasta"
        $hastaFin = $hasta . ' 23:59:59';

        // ── Logins recientes de usuarios (staff clínicas) ──────────────────
        // LEFT JOIN para incluir incluso tokens cuyo usuario fue borrado
        $logins = DB::table('personal_access_tokens as t')
            ->where('t.tokenable_type', 'App\Models\User')
            ->leftJoin('users as u', 'u.id', '=', 't.tokenable_id')
            ->where(fn($q) => $q->whereNull('u.paciente_id')->orWhereNull('u.id'))
            ->where('t.created_at', '>=', $desde)
            ->where('t.created_at', '<=', $hastaFin)
            ->leftJoin('clinicas as c', 'c.id', '=', 'u.clinica_id')
            ->select(
                'u.id as user_id',
                DB::raw("CONCAT(COALESCE(u.nombre,'(usuario eliminado)'),' ',COALESCE(u.apellidoPat,'')) as nombre"),
                'u.email',
                'u.rol',
                'c.id as clinica_id',
                'c.nombre as clinica_nombre',
                'c.tipo_clinica',
                't.created_at as login_at',
                't.last_used_at',
                't.name as token_name'
            )
            ->orderByDesc('t.created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'tipo'           => 'login_usuario',
                'user_id'        => $r->user_id,
                'nombre'         => trim($r->nombre),
                'email'          => $r->email ?? '—',
                'rol'            => $r->rol ?? '—',
                'clinica_id'     => $r->clinica_id,
                'clinica_nombre' => $r->clinica_nombre ?? '—',
                'tipo_clinica'   => $r->tipo_clinica,
                'fecha'          => $r->login_at,
                'ultimo_uso'     => $r->last_used_at,
            ]);

        // ── Pacientes que accedieron al portal ─────────────────────────────
        $portalAccesos = DB::table('personal_access_tokens as t')
            ->where('t.tokenable_type', 'App\Models\User')
            ->leftJoin('users as u', 'u.id', '=', 't.tokenable_id')
            ->whereNotNull('u.paciente_id')
            ->where('t.created_at', '>=', $desde)
            ->where('t.created_at', '<=', $hastaFin)
            ->leftJoin('pacientes as p', 'p.id', '=', 'u.paciente_id')
            ->leftJoin('clinicas as c', 'c.id', '=', 'p.clinica_id')
            ->select(
                'u.id as user_id',
                DB::raw("CONCAT(COALESCE(p.nombre,''),' ',COALESCE(p.apellidoPat,'')) as nombre"),
                'u.email',
                'p.id as paciente_id',
                'c.id as clinica_id',
                'c.nombre as clinica_nombre',
                'c.tipo_clinica',
                't.created_at as login_at',
                't.last_used_at'
            )
            ->orderByDesc('t.created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'tipo'           => 'acceso_portal_paciente',
                'user_id'        => $r->user_id,
                'nombre'         => $r->nombre,
                'email'          => $r->email,
                'rol'            => 'paciente',
                'paciente_id'    => $r->paciente_id,
                'clinica_id'     => $r->clinica_id,
                'clinica_nombre' => $r->clinica_nombre,
                'tipo_clinica'   => $r->tipo_clinica,
                'fecha'          => $r->login_at,
                'ultimo_uso'     => $r->last_used_at,
            ]);

        // Fusionar y ordenar por fecha descendente
        $actividad = $logins->concat($portalAccesos)
            ->sortByDesc('fecha')
            ->values();

        // ── Resumen ────────────────────────────────────────────────────────
        $hoy = now()->toDateString();
        $resumen = [
            'logins_usuarios_hoy'  => $logins->filter(fn ($r) => str_starts_with($r['fecha'] ?? '', $hoy))->count(),
            'logins_usuarios_rango'=> $logins->count(),
            'accesos_portal_hoy'   => $portalAccesos->filter(fn ($r) => str_starts_with($r['fecha'] ?? '', $hoy))->count(),
            'accesos_portal_rango' => $portalAccesos->count(),
            'usuarios_unicos'      => $logins->pluck('user_id')->filter()->unique()->count(),
            'clinicas_activas_hoy' => $logins->filter(fn ($r) => str_starts_with($r['fecha'] ?? '', $hoy))->pluck('clinica_id')->filter()->unique()->count(),
        ];

        return response()->json([
            'success'   => true,
            'resumen'   => $resumen,
            'actividad' => $actividad,
            'desde'     => $desde,
            'hasta'     => $hasta,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CLÍNICAS — detalle completo + acciones de suscripción
    // ──────────────────────────────────────────────────────────────────────────

    public function clinicasDetalle(Request $request)
    {
        $estado = $request->query('estado', 'todas'); // activas | vencidas | trial | todas
        $tipo   = $request->query('tipo', 'todas');
        $search = $request->query('q', '');

        $query = \App\Models\Clinica::with(['propietario:id,nombre,apellidoPat,email,tiene_suscripcion_consultorio,suscripcion_fin,trial_ends_at'])
            ->select([
                'id','nombre','email','telefono','direccion','tipo_clinica',
                'plan_type','billing_cycle','pagado','activa',
                'fecha_vencimiento','trial_ends_at','created_at','updated_at',
                'stripe_customer_id','stripe_subscription_id','next_billing_date',
                'propietario_user_id','es_consultorio_privado',
            ]);

        // Filtro estado
        $now = now();
        match ($estado) {
            'activas'  => $query->where('pagado', true)
                                ->where(fn($q) => $q->whereNull('fecha_vencimiento')->orWhere('fecha_vencimiento', '>=', $now)),
            'vencidas' => $query->where('pagado', true)->whereNotNull('fecha_vencimiento')->where('fecha_vencimiento', '<', $now),
            'trial'    => $query->where('pagado', false)->where(function ($q) use ($now) {
                $q->where(fn ($q2) => $q2->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', $now))
                    ->orWhereHas('propietario', fn ($p) => $p->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', $now))
                    ->orWhere(fn ($q3) => $q3->where('es_consultorio_privado', true)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '>=', $now->toDateString())
                        ->whereNull('stripe_subscription_id'));
            }),
            'nunca'    => $query->where('pagado', false)->where(function ($q) use ($now) {
                $q->where(fn ($q2) => $q2->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<', $now))
                    ->whereDoesntHave('propietario', fn ($p) => $p->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', $now))
                    ->where(fn ($q3) => $q3->whereNull('fecha_vencimiento')->orWhere('fecha_vencimiento', '<', $now->toDateString()));
            }),
            default    => null,
        };

        if ($tipo !== 'todas') {
            $query->where('tipo_clinica', $tipo);
        }

        if ($search) {
            $query->where(fn($q) => $q->where('nombre', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $clinicas = $query->orderByDesc('updated_at')->get()->map(function ($c) use ($now) {
            $pagosQuery = \App\Models\Payment::where('clinica_id', $c->id)->where('status', 'completed');
            $totalPagos = (clone $pagosQuery)->count();

            $ultimoPago = (clone $pagosQuery)
                ->orderByDesc('created_at')
                ->first(['amount','currency','created_at','metadata','payment_method','stripe_invoice_id']);

            $pagosRecientes = (clone $pagosQuery)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['amount','currency','created_at','metadata','payment_method'])
                ->map(function ($p) {
                    $meta = is_array($p->metadata) ? $p->metadata : [];
                    $tipo = $meta['type'] ?? $meta['billing_cycle'] ?? null;

                    return [
                        'monto'  => $p->amount,
                        'moneda' => $p->currency,
                        'fecha'  => $p->created_at->format('Y-m-d H:i'),
                        'metodo' => $p->payment_method,
                        'tipo'   => is_scalar($tipo) ? (string) $tipo : null,
                    ];
                });

            $enTrial = $c->estaEnTrial();
            $trialEndsAt = $c->trialEndsAtEfectivo();
            $diasTrial = $enTrial && $trialEndsAt
                ? (int) $now->diffInDays($trialEndsAt, false)
                : null;

            $tipoCobro = match (true) {
                (bool) $c->stripe_subscription_id => 'stripe_recurrente',
                $totalPagos > 0                     => 'pago_registrado',
                (bool) $c->pagado                   => 'manual_prepagado',
                $enTrial                            => 'trial_gratuito',
                default                             => 'sin_pago',
            };

            $diasRestantes = $c->fecha_vencimiento
                ? (int) $now->diffInDays($c->fecha_vencimiento, false)
                : ($diasTrial ?? null);

            $statusAcceso = \App\Services\SubscriptionStatusService::getStatus($c, $c->propietario);
            $tieneAcceso = (bool) ($statusAcceso['active'] ?? false);

            $estadoLabel = match (true) {
                $c->pagado && ($c->fecha_vencimiento === null || $c->fecha_vencimiento >= $now) => 'activa',
                $c->pagado && $c->fecha_vencimiento < $now => 'vencida',
                $enTrial  => 'trial',
                $tieneAcceso => 'activa',
                default   => 'inactiva',
            };

            return [
                'id'                    => $c->id,
                'nombre'                => $c->nombre,
                'email'                 => $c->email,
                'telefono'              => $c->telefono,
                'direccion'             => $c->direccion,
                'tipo_clinica'          => $c->tipo_clinica,
                'plan_type'             => $c->plan_type,
                'billing_cycle'         => $c->billing_cycle,
                'pagado'                => (bool) $c->pagado,
                'activa'                => (bool) $c->activa,
                'estado'                => $estadoLabel,
                'fecha_registro'        => $c->created_at?->format('Y-m-d'),
                'fecha_vencimiento'     => $c->fecha_vencimiento?->format('Y-m-d'),
                'dias_restantes'        => $diasRestantes,
                'trial_ends_at'         => $trialEndsAt?->format('Y-m-d H:i'),
                'dias_trial_restantes'  => $diasTrial,
                'tiene_acceso'          => $tieneAcceso,
                'acceso_motivo'         => $statusAcceso['tipo'] ?? null,
                'stripe_customer_id'    => $c->stripe_customer_id,
                'stripe_subscription_id'=> $c->stripe_subscription_id,
                'next_billing_date'     => $c->next_billing_date?->format('Y-m-d'),
                'es_consultorio'        => (bool) $c->es_consultorio_privado,
                'tipo_cobro'            => $tipoCobro,
                'total_pagos'           => $totalPagos,
                'propietario'           => $c->propietario ? [
                    'id'         => $c->propietario->id,
                    'nombre'     => trim($c->propietario->nombre . ' ' . $c->propietario->apellidoPat),
                    'email'      => $c->propietario->email,
                    'suscripcion_fin' => $c->propietario->suscripcion_fin?->format('Y-m-d'),
                    'trial_ends_at' => $c->propietario->trial_ends_at?->format('Y-m-d'),
                ] : null,
                'ultimo_pago' => $ultimoPago ? (function () use ($ultimoPago) {
                    $meta = is_array($ultimoPago->metadata) ? $ultimoPago->metadata : [];

                    return [
                        'monto'    => $ultimoPago->amount,
                        'moneda'   => $ultimoPago->currency,
                        'fecha'    => $ultimoPago->created_at->format('Y-m-d H:i'),
                        'ciclo'    => isset($meta['billing_cycle']) && is_scalar($meta['billing_cycle'])
                            ? (string) $meta['billing_cycle'] : null,
                        'tipo'     => isset($meta['type']) && is_scalar($meta['type'])
                            ? (string) $meta['type'] : null,
                        'metodo'   => $ultimoPago->payment_method,
                    ];
                })() : null,
                'pagos_recientes' => $pagosRecientes,
            ];
        });

        return response()->json(['success' => true, 'clinicas' => $clinicas]);
    }

    public function clinicaToggle(Request $request, int $id)
    {
        $clinica = \App\Models\Clinica::findOrFail($id);
        $accion  = $request->input('accion'); // 'activar' | 'desactivar'

        if ($accion === 'activar') {
            $clinica->update(['pagado' => true, 'activa' => true]);
            $msg = "Suscripción de '{$clinica->nombre}' activada manualmente.";
        } else {
            $clinica->update(['pagado' => false, 'activa' => false]);
            $msg = "Suscripción de '{$clinica->nombre}' desactivada manualmente.";
        }

        $this->syncPropietarioSuscripcion($clinica->fresh());

        Log::info("[Admin] Clinica toggle", [
            'clinica_id' => $id, 'accion' => $accion,
            'admin' => $request->user()?->email,
        ]);

        return response()->json(['success' => true, 'message' => $msg, 'pagado' => $clinica->pagado, 'activa' => $clinica->activa]);
    }

    public function clinicaDiasExtra(Request $request, int $id)
    {
        $request->validate([
            'dias'  => 'required|integer|min:1|max:365',
            'motivo'=> 'nullable|string|max:255',
        ]);

        $clinica = \App\Models\Clinica::findOrFail($id);
        $dias    = (int) $request->input('dias');
        $motivo  = $request->input('motivo', 'Días extra otorgados por administrador');

        // Si está vencida, tomar desde hoy; si sigue activa, extender desde su fecha actual
        $base = ($clinica->fecha_vencimiento && $clinica->fecha_vencimiento > now())
            ? $clinica->fecha_vencimiento->copy()
            : now();

        $nuevaFecha = $base->addDays($dias)->endOfDay();

        $clinica->update([
            'fecha_vencimiento' => $nuevaFecha,
            'pagado' => true,
            'activa' => true,
        ]);

        $this->syncPropietarioSuscripcion($clinica);

        Log::info("[Admin] Días extra otorgados", [
            'clinica_id' => $id, 'dias' => $dias, 'motivo' => $motivo,
            'nueva_fecha' => $nuevaFecha->format('Y-m-d'),
            'admin' => $request->user()?->email,
        ]);

        return response()->json([
            'success'           => true,
            'message'           => "{$dias} días extra otorgados a '{$clinica->nombre}'.",
            'fecha_vencimiento' => $nuevaFecha->format('Y-m-d'),
        ]);
    }

    /**
     * Actualizar suscripción manualmente (fecha de vencimiento, pagado, activa).
     * Para clientes prepagados o legacy sin Stripe.
     */
    public function clinicaActualizarSuscripcion(Request $request, int $id)
    {
        $request->validate([
            'fecha_vencimiento' => 'nullable|date',
            'pagado'            => 'nullable|boolean',
            'activa'            => 'nullable|boolean',
            'motivo'            => 'nullable|string|max:500',
        ]);

        $clinica = \App\Models\Clinica::findOrFail($id);
        $motivo  = $request->input('motivo', 'Ajuste manual por administrador');

        $updates = [];

        if ($request->has('fecha_vencimiento')) {
            $updates['fecha_vencimiento'] = $request->filled('fecha_vencimiento')
                ? \Carbon\Carbon::parse($request->fecha_vencimiento)->endOfDay()
                : null;
        }

        if ($request->has('pagado')) {
            $updates['pagado'] = $request->boolean('pagado');
        }

        if ($request->has('activa')) {
            $updates['activa'] = $request->boolean('activa');
        } elseif (($updates['pagado'] ?? $clinica->pagado) && isset($updates['fecha_vencimiento'])) {
            $updates['activa'] = true;
        }

        if (empty($updates)) {
            return response()->json([
                'success' => false,
                'message' => 'No se enviaron cambios.',
            ], 422);
        }

        $antes = [
            'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
            'pagado' => $clinica->pagado,
            'activa' => $clinica->activa,
        ];

        $clinica->update($updates);
        $clinica->refresh();
        $this->syncPropietarioSuscripcion($clinica);

        Log::info('[Admin] Suscripción actualizada manualmente', [
            'clinica_id' => $id,
            'antes'      => $antes,
            'despues'    => [
                'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
                'pagado' => $clinica->pagado,
                'activa' => $clinica->activa,
            ],
            'motivo' => $motivo,
            'admin'  => $request->user()?->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Suscripción de '{$clinica->nombre}' actualizada.",
            'clinica' => [
                'id'                => $clinica->id,
                'pagado'            => (bool) $clinica->pagado,
                'activa'            => (bool) $clinica->activa,
                'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Historial de pagos de una clínica (admin).
     */
    public function clinicaPagos(int $id)
    {
        $clinica = \App\Models\Clinica::findOrFail($id);

        $pagos = \App\Models\Payment::where('clinica_id', $clinica->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($p) => [
                'id'       => $p->id,
                'monto'    => $p->amount,
                'moneda'   => $p->currency,
                'estado'   => $p->status,
                'metodo'   => $p->payment_method,
                'fecha'    => $p->created_at->format('Y-m-d H:i'),
                'tipo'     => $p->metadata['type'] ?? null,
                'ciclo'    => $p->metadata['billing_cycle'] ?? null,
                'stripe_invoice_id' => $p->stripe_invoice_id,
            ]);

        return response()->json([
            'success' => true,
            'clinica' => ['id' => $clinica->id, 'nombre' => $clinica->nombre],
            'pagos'   => $pagos,
        ]);
    }

    private function syncPropietarioSuscripcion(\App\Models\Clinica $clinica): void
    {
        if (! $clinica->propietario_user_id) {
            return;
        }

        \App\Models\User::where('id', $clinica->propietario_user_id)->update([
            'tiene_suscripcion_consultorio' => (bool) $clinica->pagado,
            'suscripcion_fin'               => $clinica->fecha_vencimiento,
            'ciclo_facturacion'             => $clinica->billing_cycle ?? 'mensual',
        ]);
    }

    public function suscripcionesStats()
    {
        $now = now();

        $basePrecios = config('clinica_tipos.base_precios', []);
        $preciosMensual = collect($basePrecios)->mapWithKeys(fn ($p, $k) => [$k => $p['mensual']])->all();
        $preciosAnual   = collect($basePrecios)->mapWithKeys(fn ($p, $k) => [$k => $p['anual']])->all();

        // Suscripciones activas (no vencidas)
        $activas = \App\Models\Clinica::where('pagado', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('fecha_vencimiento')->orWhere('fecha_vencimiento', '>=', $now);
            });

        $activasMensual = (clone $activas)->where('billing_cycle', 'mensual')->count();
        $activasAnual   = (clone $activas)->where('billing_cycle', 'anual')->count();
        $activasTotal   = (clone $activas)->count();

        // MRR: suma de todas las activas mensuales + anuales/12
        $mrr = 0;
        (clone $activas)->get(['tipo_clinica', 'billing_cycle'])->each(function ($c) use (&$mrr, $preciosMensual, $preciosAnual) {
            $tipo = $c->tipo_clinica ?? 'consultorio';
            if ($c->billing_cycle === 'anual') {
                $mrr += ($preciosAnual[$tipo] ?? 11990) / 12;
            } else {
                $mrr += $preciosMensual[$tipo] ?? 1299;
            }
        });

        // ARR
        $arr = $mrr * 12;

        // Vencidas (pagaron alguna vez pero vencieron)
        $vencidas = \App\Models\Clinica::where('pagado', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', $now)
            ->count();

        // Vencen en los próximos 7 y 30 días
        $vencenEn7  = \App\Models\Clinica::where('pagado', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$now, $now->copy()->addDays(7)])
            ->count();
        $vencenEn30 = \App\Models\Clinica::where('pagado', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$now, $now->copy()->addDays(30)])
            ->count();

        // Trial activo (consultorios con fecha vigente sin pago o trial_ends_at)
        $trialesActivos = \App\Models\Clinica::where('pagado', false)
            ->where(function ($q) use ($now) {
                $q->where(fn ($q2) => $q2->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', $now))
                    ->orWhereHas('propietario', fn ($p) => $p->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', $now))
                    ->orWhere(fn ($q3) => $q3->where('es_consultorio_privado', true)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '>=', $now->toDateString()));
            })
            ->count();

        // Por tipo de clínica
        $porTipo = \App\Models\Clinica::where('pagado', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('fecha_vencimiento')->orWhere('fecha_vencimiento', '>=', $now);
            })
            ->selectRaw('tipo_clinica, count(*) as total')
            ->groupBy('tipo_clinica')
            ->pluck('total', 'tipo_clinica');

        // Pagos recientes (últimos 20)
        $pagosRecientes = \App\Models\Payment::with(['clinica:id,nombre,tipo_clinica'])
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'clinica'      => $p->clinica?->nombre ?? 'N/A',
                'tipo_clinica' => $p->clinica?->tipo_clinica ?? 'N/A',
                'amount'       => $p->amount,
                'currency'     => $p->currency,
                'billing_cycle'=> $p->metadata['billing_cycle'] ?? null,
                'fecha'        => $p->created_at->format('Y-m-d H:i'),
                'stripe_id'    => $p->stripe_payment_id,
            ]);

        // Ingresos del mes actual
        $ingresosMes = \App\Models\Payment::where('status', 'completed')
            ->where('created_at', '>=', $now->copy()->startOfMonth())
            ->sum('amount');

        // Ingresos últimos 6 meses
        $ingresosPorMes = \App\Models\Payment::where('status', 'completed')
            ->where('created_at', '>=', $now->copy()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, SUM(amount) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        // Próximas a vencer (detalle)
        $proximasVencer = \App\Models\Clinica::where('pagado', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$now, $now->copy()->addDays(30)])
            ->select('id', 'nombre', 'tipo_clinica', 'billing_cycle', 'fecha_vencimiento', 'email')
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id'              => $c->id,
                'nombre'          => $c->nombre,
                'tipo_clinica'    => $c->tipo_clinica,
                'billing_cycle'   => $c->billing_cycle,
                'fecha_vencimiento' => $c->fecha_vencimiento->format('Y-m-d'),
                'dias_restantes'  => $now->diffInDays($c->fecha_vencimiento, false),
                'email'           => $c->email,
            ]);

        return response()->json([
            'success' => true,
            'resumen' => [
                'mrr'             => round($mrr, 2),
                'arr'             => round($arr, 2),
                'activas_total'   => $activasTotal,
                'activas_mensual' => $activasMensual,
                'activas_anual'   => $activasAnual,
                'vencidas'        => $vencidas,
                'vencen_7_dias'   => $vencenEn7,
                'vencen_30_dias'  => $vencenEn30,
                'trials_activos'  => $trialesActivos,
                'por_tipo'        => $porTipo,
                'ingresos_mes'    => round($ingresosMes, 2),
            ],
            'ingresos_por_mes'   => $ingresosPorMes,
            'pagos_recientes'    => $pagosRecientes,
            'proximas_vencer'    => $proximasVencer,
        ]);
    }
}
