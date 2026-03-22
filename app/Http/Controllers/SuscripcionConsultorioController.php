<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            ]
        ]);
    }

    /**
     * GET /api/suscripcion-consultorio/planes
     * 
     * Obtener planes disponibles para consultorios privados
     */
    public function planes(): JsonResponse
    {
        $planes = [
            'basico' => [
                'nombre' => 'Básico',
                'descripcion' => 'Perfecto para comenzar tu consultorio',
                'precio_mensual' => 299,
                'precio_anual' => 2999,
                'caracteristicas' => [
                    '1 consultorio privado',
                    'Hasta 100 pacientes',
                    'Agenda y citas',
                    'Expedientes digitales',
                    'Recetas médicas',
                    'Soporte por email',
                ],
                'limites' => [
                    'max_pacientes' => 100,
                    'max_usuarios' => 2,
                    'consultorios_incluidos' => 1,
                ]
            ],
            'profesional' => [
                'nombre' => 'Profesional',
                'descripcion' => 'Para consultorios en crecimiento',
                'precio_mensual' => 599,
                'precio_anual' => 5999,
                'caracteristicas' => [
                    '1 consultorio privado',
                    'Hasta 500 pacientes',
                    'Agenda y citas',
                    'Expedientes completos',
                    'Recetas y estudios',
                    'Reportes y analíticas',
                    'Asistente médico con IA',
                    'Soporte prioritario',
                ],
                'limites' => [
                    'max_pacientes' => 500,
                    'max_usuarios' => 5,
                    'consultorios_incluidos' => 1,
                ],
                'destacado' => true,
            ],
            'premium' => [
                'nombre' => 'Premium',
                'descripcion' => 'Para profesionales establecidos',
                'precio_mensual' => 999,
                'precio_anual' => 9999,
                'caracteristicas' => [
                    '1 consultorio privado',
                    'Pacientes ilimitados',
                    'Todas las funciones profesional',
                    'Expedientes avanzados',
                    'Plantillas personalizadas',
                    'Integraciones avanzadas',
                    'API access',
                    'Soporte 24/7',
                ],
                'limites' => [
                    'max_pacientes' => 999999,
                    'max_usuarios' => 10,
                    'consultorios_incluidos' => 1,
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'planes' => $planes,
            'precio_consultorio_adicional_mensual' => 199,
            'precio_consultorio_adicional_anual' => 1999,
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
            'plan' => 'required|in:basico,profesional,premium',
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
        $user->update([
            'tiene_suscripcion_consultorio' => true,
            'plan_consultorio' => $request->plan,
            'ciclo_facturacion' => 'mensual',
            'trial_ends_at' => now()->addDays(14),
            'consultorios_adicionales_comprados' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Período de prueba activado. Tienes 14 días para probar el sistema.',
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
            'plan' => 'required|in:basico,profesional,premium',
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

        // TODO: Cancelar en Stripe

        // Marcar que no se renovará, pero mantener activa hasta fecha_fin
        $user->update([
            'stripe_subscription_id' => null, // Desvincula de Stripe
        ]);

        Log::info('Suscripción de consultorio cancelada', [
            'user_id' => $user->id,
            'fecha_vencimiento' => $user->suscripcion_fin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Suscripción cancelada. Tendrás acceso hasta ' . $user->suscripcion_fin->format('d/m/Y'),
            'fecha_vencimiento' => $user->suscripcion_fin->format('Y-m-d'),
        ]);
    }
}
