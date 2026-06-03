<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Clinica;
use App\Models\PlanFacturacion;
use App\Models\SuscripcionFacturas;
use App\Models\User;
use App\Services\FacturapiService;
use App\Services\StripeSubscriptionRenewalService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Subscription as StripeSubscription;

class SuscripcionesController extends Controller
{
    protected FacturapiService $facturapi;

    public function __construct(FacturapiService $facturapi)
    {
        $this->facturapi = $facturapi;
        $this->middleware('auth:sanctum');
    }

    /**
     * Obtener planes disponibles
     */
    public function obtenerPlanes()
    {
        $planes = PlanFacturacion::where('activo', true)
            ->orderBy('cantidad_facturas_min')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $planes
        ]);
    }

    /**
     * Obtener suscripción según ámbito: personal (cuenta del médico) o workspace (clínica activa).
     */
    public function obtenerSuscripcion(Request $request)
    {
        $user = Auth::user();
        $ambito = $this->resolveAmbito($request);

        $suscripcion = $this->suscripcionParaAmbito($user, $ambito);

        if (!$suscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene suscripción activa'
            ], 404);
        }

        // Actualizar estado de vencimiento si corresponde
        $suscripcion->actualizarEstadoVencimiento();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $suscripcion->id,
                'plan' => $suscripcion->getNombrePlan(),
                'estado' => $suscripcion->estado,
                'cantidad_facturas_limite' => $suscripcion->cantidad_facturas_limite,
                'cantidad_facturas_usadas' => $suscripcion->cantidad_facturas_usadas,
                'cantidad_facturas_restantes' => max(
                    0,
                    $suscripcion->cantidad_facturas_limite - $suscripcion->cantidad_facturas_usadas
                ),
                'porcentaje_uso' => round(
                    ($suscripcion->cantidad_facturas_usadas / $suscripcion->cantidad_facturas_limite) * 100,
                    2
                ),
                'fecha_inicio' => $suscripcion->fecha_inicio->format('Y-m-d'),
                'fecha_vencimiento' => $suscripcion->fecha_vencimiento->format('Y-m-d'),
                'precio_mensual' => $suscripcion->precio_mensual,
                'proxima_a_vencer' => $suscripcion->estaProximaAVencer(),
                'facturacion_por_usuario' => $suscripcion->esPorUsuario(),
                'ambito' => $ambito,
            ]
        ]);
    }

    /**
     * Crear sesión de pago en Stripe para un plan de facturación
     */
    public function crearCheckout(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basico,pro,enterprise',
            'ambito' => 'sometimes|string|in:personal,workspace',
        ]);

        $user = Auth::user();
        $ambito = $this->resolveAmbito($request);
        $clinicaId = $user->clinica_efectiva_id;
        $clinica = Clinica::find($clinicaId);

        if (! $clinica) {
            return response()->json(['success' => false, 'message' => 'Espacio de trabajo no encontrado'], 404);
        }

        $porUsuario = $ambito === 'personal'
            || SuscripcionFacturas::facturacionEsPorUsuario($clinica);

        if ($porUsuario) {
            if (! $this->puedeContratarPlanPersonal($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para contratar un plan personal de facturación.',
                ], 403);
            }
            $suscripcionActiva = SuscripcionFacturas::obtenerActivaPorUsuario($user->id);
        } else {
            if (! $this->puedeContratarPlanFacturacion($user, $clinica)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo administradores pueden comprar planes de facturación para la clínica.',
                ], 403);
            }
            $suscripcionActiva = SuscripcionFacturas::obtenerActivaParaContexto($user, $clinicaId);
        }

        if ($suscripcionActiva) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una suscripción activa. Usa "Cambiar Plan" para actualizarla.',
            ], 400);
        }

        $plan = PlanFacturacion::where('clave', $request->plan)->where('activo', true)->first();
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado',
            ], 404);
        }

        // Plan Enterprise requiere contacto manual
        if ($plan->clave === PlanFacturacion::CLAVE_ENTERPRISE || $plan->precio_mensual == 0) {
            return response()->json([
                'success' => false,
                'message' => 'El plan Enterprise requiere cotización. Contáctanos para más información.',
                'contacto' => true,
            ], 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
            $returnBase = $porUsuario
                ? "{$frontendUrl}/perfil/{$user->id}?tab=facturacion&ambito=personal"
                : "{$frontendUrl}/clinica?tab=facturas";

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'mxn',
                        'product_data' => [
                            'name' => "Plan Facturación {$plan->nombre}",
                            'description' => $plan->descripcion ?? "Hasta {$plan->cantidad_facturas_max} facturas/mes",
                        ],
                        'unit_amount' => (int) round($plan->precio_mensual * 100),
                        'recurring' => ['interval' => 'month'],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => "{$returnBase}&exito=1&session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "{$returnBase}&cancelado=1",
                'metadata' => [
                    'type' => 'facturacion_plan',
                    'billing_scope' => $porUsuario
                        ? SuscripcionFacturas::SCOPE_USUARIO
                        : SuscripcionFacturas::SCOPE_CLINICA,
                    'clinica_id' => (string) $clinicaId,
                    'plan_clave' => $plan->clave,
                    'plan_id' => (string) $plan->id,
                    'user_id' => (string) $user->id,
                ],
            ]);

            Log::info("Stripe checkout para plan facturación creado", [
                'clinica_id' => $clinicaId,
                'plan' => $plan->clave,
                'session_id' => $session->id,
            ]);

            return response()->json([
                'success' => true,
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creando checkout Stripe para facturación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la sesión de pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar sesión de Stripe post-checkout (fallback sin webhook)
     */
    public function verifySession(Request $request, string $sessionId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid' && $session->status !== 'complete') {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago aún no ha sido confirmado',
                ], 202);
            }

            $metadata = $session->metadata;

            $clinica = Clinica::find($clinicaId);
            $porUsuario = ($metadata->billing_scope ?? null) === SuscripcionFacturas::SCOPE_USUARIO
                || ($clinica && $clinica->es_consultorio_privado);

            if ($porUsuario) {
                if ((int) ($metadata->user_id ?? 0) !== (int) $user->id) {
                    return response()->json(['success' => false, 'message' => 'Sesión inválida para este usuario'], 403);
                }
            } elseif ((int) $metadata->clinica_id !== (int) $clinicaId) {
                return response()->json(['success' => false, 'message' => 'Sesión inválida'], 403);
            }

            // Verificar si ya fue procesada
            $existe = SuscripcionFacturas::where('stripe_checkout_session_id', $sessionId)->first();
            if ($existe) {
                return response()->json([
                    'success' => true,
                    'message' => 'Suscripción ya activada',
                    'data' => ['plan' => $existe->getNombrePlan(), 'fecha_vencimiento' => $existe->fecha_vencimiento->format('Y-m-d')],
                ]);
            }

            // Activar manualmente si el webhook no lo procesó
            $suscripcion = $this->activarSuscripcionDesdeStripe($metadata, $session);

            return response()->json([
                'success' => true,
                'message' => '¡Suscripción activada!',
                'data' => [
                    'plan' => $suscripcion->getNombrePlan(),
                    'fecha_vencimiento' => $suscripcion->fecha_vencimiento->format('Y-m-d'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error verificando sesión Stripe: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al verificar el pago'], 500);
        }
    }

    /**
     * Activar suscripción de facturación tras pago de Stripe.
     * Llamado desde webhook o desde verifySession.
     */
    public function activarSuscripcionDesdeStripe(object $metadata, object $session): SuscripcionFacturas
    {
        return DB::transaction(function () use ($metadata, $session) {
            $clinicaId = (int) $metadata->clinica_id;
            $clinica = Clinica::find($clinicaId);
            $plan = PlanFacturacion::where('clave', $metadata->plan_clave)->firstOrFail();

            $porUsuario = ($metadata->billing_scope ?? null) === SuscripcionFacturas::SCOPE_USUARIO
                || ($clinica && $clinica->es_consultorio_privado);
            $userId = $porUsuario ? (int) ($metadata->user_id ?? 0) : null;

            $fechaInicio = now();
            $fechaVencimiento = $fechaInicio->copy()->addMonth();

            $suscripcion = SuscripcionFacturas::create([
                'user_id' => $userId ?: null,
                'billing_scope' => $porUsuario
                    ? SuscripcionFacturas::SCOPE_USUARIO
                    : SuscripcionFacturas::SCOPE_CLINICA,
                'clinica_id' => $porUsuario ? $clinicaId : $clinicaId,
                'plan' => $plan->clave,
                'cantidad_facturas_limite' => $plan->cantidad_facturas_max ?? $plan->cantidad_facturas_min,
                'cantidad_facturas_usadas' => 0,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_mensual' => $plan->precio_mensual,
                'estado' => SuscripcionFacturas::ESTADO_ACTIVA,
                'stripe_checkout_session_id' => $session->id,
                'stripe_subscription_id' => $session->subscription ?? null,
            ]);

            if ($session->subscription ?? null) {
                try {
                    Stripe::setApiKey(config('services.stripe.secret'));
                    $stripeSub = StripeSubscription::retrieve($session->subscription);
                    app(StripeSubscriptionRenewalService::class)
                        ->syncFacturacionPeriodFromStripe($suscripcion, $stripeSub, false);
                    $suscripcion->refresh();
                } catch (\Exception $e) {
                    Log::warning('Sync Stripe plan facturación: '.$e->getMessage());
                }
            }

            $suscripcion->sincronizarAddonClinica(true);

            Log::info('Plan de facturación activado', [
                'plan' => $plan->clave,
                'suscripcion_id' => $suscripcion->id,
                'clinica_id' => $clinicaId,
                'user_id' => $userId,
                'billing_scope' => $suscripcion->billing_scope,
            ]);

            return $suscripcion;
        });
    }

    /**
     * Crear nueva suscripción (acceso directo para testing/admin — sin pago)
     */
    public function crearSuscripcion(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basico,pro,enterprise',
        ]);

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;
        $clinica = Clinica::findOrFail($clinicaId);

        $suscripcionActiva = SuscripcionFacturas::obtenerActivaParaContexto($user, $clinicaId);

        if ($suscripcionActiva) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tiene una suscripción activa'
            ], 400);
        }

        $planRequest = PlanFacturacion::where('clave', $request->plan)->first();
        if (!$planRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado'
            ], 404);
        }

        $porUsuario = SuscripcionFacturas::facturacionEsPorUsuario($clinica);

        $suscripcion = DB::transaction(function () use ($clinica, $planRequest, $porUsuario, $user) {
            $fechaInicio = now();
            $fechaVencimiento = $fechaInicio->copy()->addMonth();

            $suscripcion = SuscripcionFacturas::create([
                'user_id' => $porUsuario ? $user->id : null,
                'billing_scope' => $porUsuario
                    ? SuscripcionFacturas::SCOPE_USUARIO
                    : SuscripcionFacturas::SCOPE_CLINICA,
                'clinica_id' => $clinica->id,
                'plan' => $planRequest->clave,
                'cantidad_facturas_limite' => $planRequest->cantidad_facturas_max ?? $planRequest->cantidad_facturas_min,
                'cantidad_facturas_usadas' => 0,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_mensual' => $planRequest->precio_mensual,
                'estado' => SuscripcionFacturas::ESTADO_ACTIVA,
            ]);

            $suscripcion->sincronizarAddonClinica(true);

            return $suscripcion;
        });

        return response()->json([
            'success' => true,
            'message' => 'Suscripción creada exitosamente',
            'data' => [
                'id' => $suscripcion->id,
                'plan' => $suscripcion->getNombrePlan(),
                'fecha_vencimiento' => $suscripcion->fecha_vencimiento->format('Y-m-d'),
                'precio_mensual' => $suscripcion->precio_mensual,
            ]
        ]);
    }

    /**
     * Cambiar de plan (upgrade/downgrade)
     */
    public function cambiarPlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basico,pro,enterprise',
            'ambito' => 'sometimes|string|in:personal,workspace',
        ]);

        $user = Auth::user();
        $ambito = $this->resolveAmbito($request);

        $suscripcionActual = $this->suscripcionParaAmbito($user, $ambito);

        if (!$suscripcionActual) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene suscripción activa'
            ], 404);
        }

        if ($ambito === 'workspace' && $suscripcionActual->esPorUsuario()) {
            return response()->json([
                'success' => false,
                'message' => 'Este plan es personal; cámbialo desde Perfil → Facturación CFDI.',
            ], 403);
        }

        if ($ambito === 'personal' && ! $suscripcionActual->esPorUsuario()) {
            return response()->json([
                'success' => false,
                'message' => 'Este plan es de la clínica; cámbialo en Gestión de clínica → Planes de facturación.',
            ], 403);
        }

        if ($suscripcionActual->plan === $request->plan) {
            return response()->json([
                'success' => false,
                'message' => 'Ya está en este plan'
            ], 400);
        }

        // Obtener nuevo plan
        $nuevoPlan = PlanFacturacion::where('clave', $request->plan)->first();
        if (!$nuevoPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado'
            ], 404);
        }

        // Actualizar suscripción (cambio inmediato de plan)
        $suscripcionActual->update([
            'plan' => $nuevoPlan->clave,
            'cantidad_facturas_limite' => $nuevoPlan->cantidad_facturas_max ?? $nuevoPlan->cantidad_facturas_min,
            'precio_mensual' => $nuevoPlan->precio_mensual,
            'cantidad_facturas_usadas' => 0, // Reset contador
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan actualizado exitosamente',
            'data' => [
                'plan' => $suscripcionActual->getNombrePlan(),
                'precio_mensual' => $suscripcionActual->precio_mensual,
                'cantidad_facturas_limite' => $suscripcionActual->cantidad_facturas_limite,
            ]
        ]);
    }

    /**
     * Cancelar suscripción
     */
    public function cancelarSuscripcion(Request $request)
    {
        $request->validate([
            'ambito' => 'sometimes|string|in:personal,workspace',
        ]);

        $user = Auth::user();
        $ambito = $this->resolveAmbito($request);

        $suscripcion = $this->suscripcionParaAmbito($user, $ambito);

        if (!$suscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene suscripción activa'
            ], 404);
        }

        $fechaFin = $suscripcion->fecha_vencimiento;

        if ($suscripcion->stripe_subscription_id) {
            try {
                $periodEnd = app(StripeSubscriptionRenewalService::class)
                    ->scheduleCancelAtPeriodEnd($suscripcion->stripe_subscription_id);
                if ($periodEnd) {
                    $fechaFin = $periodEnd;
                    $suscripcion->update([
                        'fecha_vencimiento' => $periodEnd,
                        'notas' => $request->input('razon', 'Renovación automática cancelada; acceso hasta fin de periodo'),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error cancelando suscripción Stripe facturación: '.$e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo programar la cancelación en Stripe: '.$e->getMessage(),
                ], 500);
            }
        } else {
            $suscripcion->update([
                'estado' => SuscripcionFacturas::ESTADO_CANCELADA,
                'notas' => $request->input('razon', 'Cancelación por usuario'),
            ]);
            $suscripcion->sincronizarAddonClinica(false);
        }

        return response()->json([
            'success' => true,
            'message' => 'Renovación automática cancelada. Podrás facturar hasta '
                .$fechaFin->format('d/m/Y').'.',
            'fecha_vencimiento' => $fechaFin->format('Y-m-d'),
            'cancel_at_period_end' => (bool) $suscripcion->stripe_subscription_id,
        ]);
    }

    /**
     * Listar historial de suscripciones (para admin)
     */
    public function historial($clinicaId = null)
    {
        $user = Auth::user();
        
        // Si no especifica clínica, usar la del usuario
        if (!$clinicaId) {
            $clinicaId = $user->clinica_efectiva_id;
        }

        $clinica = Clinica::find($clinicaId);
        $query = SuscripcionFacturas::query()->orderByDesc('created_at');

        if ($clinica && SuscripcionFacturas::facturacionEsPorUsuario($clinica)) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('clinica_id', $clinicaId)
                ->where('billing_scope', SuscripcionFacturas::SCOPE_CLINICA);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    /**
     * Consultorio privado: cada doctor contrata su plan.
     * Clínica tradicional: solo admin/superadmin contrata para el workspace.
     */
    private function puedeContratarPlanFacturacion(User $user, Clinica $clinica): bool
    {
        if (SuscripcionFacturas::facturacionEsPorUsuario($clinica)) {
            return true;
        }

        return $user->isAdmin || $user->isSuperAdmin;
    }

    private function puedeContratarPlanPersonal(User $user): bool
    {
        return true;
    }

    private function resolveAmbito(Request $request): string
    {
        $ambito = $request->input('ambito', 'workspace');

        return in_array($ambito, ['personal', 'workspace'], true) ? $ambito : 'workspace';
    }

    private function suscripcionParaAmbito(User $user, string $ambito): ?SuscripcionFacturas
    {
        if ($ambito === 'personal') {
            return SuscripcionFacturas::obtenerActivaPorUsuario($user->id);
        }

        return SuscripcionFacturas::obtenerActivaParaContexto($user, $user->clinica_efectiva_id);
    }
}
