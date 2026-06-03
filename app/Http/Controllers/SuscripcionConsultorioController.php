<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\User;
use App\Services\PricingService;
use App\Services\StripeSubscriptionRenewalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * SuscripcionConsultorioController
 * 
 * Gestiona las suscripciones para usuarios que quieren crear consultorios privados.
 * Diferente de las suscripciones de clínicas empresariales.
 */
class SuscripcionConsultorioController extends Controller
{
    /**
     * GET /api/suscripcion-consultorio/estado
     * 
     * Obtener estado actual de suscripción del usuario
     */
    public function estado(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'suscripcion' => [
                'tiene_suscripcion' => $user->tiene_suscripcion_consultorio,
                'plan' => $user->plan_consultorio,
                'ciclo_facturacion' => $user->ciclo_facturacion,
                'activa' => $user->tieneSuscripcionConsultorioActiva(),
                'en_trial' => $user->trial_ends_at && now()->lessThan($user->trial_ends_at),
                'fecha_inicio' => $user->suscripcion_inicio?->format('Y-m-d'),
                'fecha_vencimiento' => $user->suscripcion_fin?->format('Y-m-d'),
                'trial_ends_at' => $user->trial_ends_at?->format('Y-m-d'),
                'stripe_customer_id' => $user->stripe_customer_id,
                'stripe_subscription_id' => $user->stripe_subscription_id,
            ],
            'consultorios' => [
                'limite' => $user->limite_consultorios,
                'usados' => $user->cantidad_consultorios_privados,
                'adicionales_comprados' => $user->consultorios_adicionales_comprados ?? 0,
                'puede_crear_mas' => $user->puedeCrearConsultorioAdicional(),
            ],
            'mis_consultorios_privados' => $this->consultoriosPrivadosDelUsuario($user),
        ]);
    }

    /**
     * Consultorios privados del usuario (propietario), sin depender del workspace activo.
     */
    private function consultoriosPrivadosDelUsuario(User $user): array
    {
        return $user->clinicas()
            ->where('es_consultorio_privado', true)
            ->wherePivot('rol_en_clinica', 'propietario')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Clinica $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'tipo_clinica' => $c->tipo_clinica,
                'especialidad_label' => PricingService::labelEspecialidad($c->tipo_clinica),
                'fecha_vencimiento' => $c->fecha_vencimiento?->format('Y-m-d'),
            ])
            ->values()
            ->all();
    }

    /**
     * GET /api/suscripcion-consultorio/planes
     * 
     * Obtener planes disponibles para consultorios privados
     */
    public function planes(Request $request): JsonResponse
    {
        $catalogo = PricingService::catalogoConsultorioEspecialidades();

        $tipoSolicitado = $request->query('tipo_clinica');
        if ($tipoSolicitado && ! isset(PricingService::$BASE_LAUNCH[$tipoSolicitado])) {
            $tipoSolicitado = 'general';
        }

        $item = PricingService::catalogoItem($tipoSolicitado ?? 'general')
            ?? $catalogo[0]
            ?? null;

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Catálogo de precios no disponible'], 500);
        }

        $planes = [
            'consultorio' => [
                'nombre' => 'Consultorio Privado',
                'descripcion' => 'Todo lo que necesitas para tu consulta privada',
                'tipo_clinica' => $item['tipo_clinica'],
                'especialidad_label' => $item['especialidad_label'],
                'precio_mensual' => $item['precio_mensual'],
                'precio_anual' => $item['precio_anual'],
                'precio_normal_mensual' => $item['precio_normal_mensual'],
                'precio_normal_anual' => $item['precio_normal_anual'],
                'ahorro_anual' => $item['ahorro_anual'],
                'caracteristicas' => [
                    'Expediente clínico NOM-024',
                    'Agenda en línea con recordatorios',
                    'Portal del paciente para consultas y resultados',
                    'Recetas con firma electrónica',
                    'Transcripción por voz con IA',
                    'Caja básica y recibos digitales',
                    'Soporte por WhatsApp',
                ],
                'limites' => [
                    'max_pacientes' => 999999,
                    'max_usuarios' => 5,
                    'consultorios_incluidos' => 1,
                ],
                'destacado' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'planes' => $planes,
            'catalogo_especialidades' => $catalogo,
            'tipo_clinica' => $item['tipo_clinica'],
            'especialidad_label' => $item['especialidad_label'],
            'precio_consultorio_adicional_mensual' => $item['precio_adicional_mensual'],
            'precio_consultorio_adicional_anual' => $item['precio_adicional_anual'],
            'ahorro_consultorio_adicional' => $item['ahorro_adicional_anual'],
            'nota' => 'El precio depende de la especialidad del consultorio que vayas a crear, no del espacio de trabajo activo.',
        ]);
    }

    /**
     * POST /api/suscripcion-consultorio/iniciar-trial
     * 
     * Iniciar período de prueba gratuito (14 días)
     */
    public function iniciarTrial(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => 'required|in:consultorio',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Verificar si ya tuvo un trial
        if ($user->tiene_suscripcion_consultorio) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has usado tu período de prueba gratuito.',
            ], 400);
        }

        // Activar trial de 14 días
        $trialEndsAt = now()->addDays(30)->endOfDay();

        $user->update([
            'tiene_suscripcion_consultorio' => true,
            'plan_consultorio' => $request->plan,
            'ciclo_facturacion' => 'mensual',
            'trial_ends_at' => $trialEndsAt,
            'consultorios_adicionales_comprados' => 0,
        ]);

        \App\Models\Clinica::query()
            ->where('propietario_user_id', $user->id)
            ->where('es_consultorio_privado', true)
            ->each(fn (\App\Models\Clinica $c) => $c->update([
                'trial_ends_at' => $trialEndsAt,
                'fecha_vencimiento' => $c->fecha_vencimiento ?? $trialEndsAt->toDateString(),
                'activa' => true,
            ]));

        return response()->json([
            'success' => true,
            'message' => 'Período de prueba activado. Tienes 30 días para probar el sistema.',
            'trial_ends_at' => $user->trial_ends_at->format('Y-m-d'),
        ]);
    }

    /**
     * POST /api/suscripcion-consultorio/crear-suscripcion
     * 
     * Crear suscripción de pago (integración con Stripe)
     */
    public function crearSuscripcion(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => 'required|in:consultorio',
            'ciclo' => 'required|in:mensual,anual',
            'payment_method_id' => 'nullable|string', // Stripe Payment Method ID
        ]);

        /** @var User $user */
        $user = Auth::user();

        // TODO: Integrar con Stripe
        // Por ahora, simulamos la suscripción activa

        $duracion = $request->ciclo === 'anual' ? 365 : 30;

        $user->update([
            'tiene_suscripcion_consultorio' => true,
            'plan_consultorio' => $request->plan,
            'ciclo_facturacion' => $request->ciclo,
            'suscripcion_inicio' => now(),
            'suscripcion_fin' => now()->addDays($duracion),
            'trial_ends_at' => null, // Fin del trial si lo tenía
            'consultorios_adicionales_comprados' => $user->consultorios_adicionales_comprados ?? 0,
        ]);

        Log::info('Suscripción de consultorio creada', [
            'user_id' => $user->id,
            'plan' => $request->plan,
            'ciclo' => $request->ciclo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Suscripción activada exitosamente.',
            'suscripcion' => [
                'plan' => $user->plan_consultorio,
                'ciclo' => $user->ciclo_facturacion,
                'fecha_vencimiento' => $user->suscripcion_fin->format('Y-m-d'),
            ]
        ]);
    }

    /**
     * POST /api/suscripcion-consultorio/comprar-consultorio-adicional
     * 
     * Comprar un consultorio adicional
     */
    public function comprarConsultorioAdicional(Request $request): JsonResponse
    {
        $request->validate([
            'tipo_clinica' => 'nullable|string|max:64',
            'ciclo' => 'nullable|in:mensual,anual',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!$user->tieneSuscripcionConsultorioActiva()) {
            return response()->json([
                'success' => false,
                'message' => 'Necesitas una suscripción activa para comprar consultorios adicionales.',
            ], 403);
        }

        // TODO: Integrar con Stripe para cobro
        // Por ahora, incrementamos el contador

        $user->increment('consultorios_adicionales_comprados');

        Log::info('Consultorio adicional comprado', [
            'user_id' => $user->id,
            'total_adicionales' => $user->consultorios_adicionales_comprados,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Consultorio adicional comprado exitosamente.',
            'limite_consultorios' => $user->limite_consultorios,
        ]);
    }

    /**
     * POST /api/suscripcion-consultorio/cancelar
     * 
     * Cancelar suscripción (no elimina datos, solo evita renovación)
     */
    public function cancelar(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->tiene_suscripcion_consultorio) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una suscripción activa.',
            ], 400);
        }

        $clinica = Clinica::find($user->clinica_activa_id ?? $user->clinica_id);
        $stripeSubId = $clinica?->stripe_subscription_id ?? $user->stripe_subscription_id;
        $fechaFin = $clinica?->fecha_vencimiento ?? $user->suscripcion_fin;

        if ($stripeSubId) {
            try {
                $periodEnd = app(StripeSubscriptionRenewalService::class)
                    ->scheduleCancelAtPeriodEnd($stripeSubId);

                if ($periodEnd) {
                    $fechaFin = $periodEnd;
                    if ($clinica) {
                        $clinica->update(['fecha_vencimiento' => $periodEnd]);
                    }
                    $user->update(['suscripcion_fin' => $periodEnd]);
                }
            } catch (\Exception $e) {
                Log::error('Error cancelando suscripción consultorio en Stripe: '.$e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo cancelar la renovación automática: '.$e->getMessage(),
                ], 500);
            }
        }

        Log::info('Suscripción de consultorio: renovación automática cancelada', [
            'user_id' => $user->id,
            'fecha_vencimiento' => $fechaFin?->format('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Renovación automática cancelada. Tendrás acceso hasta '
                .($fechaFin ? $fechaFin->format('d/m/Y') : 'el fin de tu periodo actual').'.',
            'fecha_vencimiento' => $fechaFin?->format('Y-m-d'),
            'cancel_at_period_end' => (bool) $stripeSubId,
        ]);
    }
}
