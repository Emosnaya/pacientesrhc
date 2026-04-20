<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinica;

/**
 * Middleware que valida suscripción activa para clínicas/consultorios.
 * 
 * Si la suscripción está vencida:
 * - Clínicas empresariales: Deben contactar a ventas
 * - Consultorios privados: Pueden renovar directamente vía Stripe
 */
class EnsureSubscriptionActive
{
    /**
     * Rutas exentas de validación de suscripción
     */
    protected array $exemptRoutes = [
        'api/user',
        'api/logout',
        'api/clinica-contacto-comercial', // Para solicitar contacto
        'api/suscripcion-consultorio',    // Para renovar
        'api/subscription',               // Para verificar/renovar
        'api/stripe/webhook',             // Webhooks de Stripe
        'api/paciente-portal',            // Portal del paciente
    ];

    public function handle(Request $request, Closure $next)
    {
        // Solo aplicar si el usuario está autenticado
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Cuentas de portal del paciente no requieren validación de suscripción
        if ($user->paciente_id && !$user->clinica_id && !$user->clinica_activa_id) {
            return $next($request);
        }

        // Verificar si la ruta está exenta
        $path = $request->path();
        foreach ($this->exemptRoutes as $exemptRoute) {
            if (str_starts_with($path, $exemptRoute)) {
                return $next($request);
            }
        }

        // Obtener la clínica/consultorio actual
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        
        if (!$clinicaId) {
            return $next($request);
        }

        $clinica = Clinica::find($clinicaId);
        
        if (!$clinica) {
            return $next($request);
        }

        // Verificar si la suscripción está activa
        $subscriptionStatus = $this->getSubscriptionStatus($clinica, $user);

        if (!$subscriptionStatus['active']) {
            return response()->json([
                'success' => false,
                'message' => $subscriptionStatus['message'],
                'subscription_expired' => true,
                'subscription_info' => [
                    'tipo' => $subscriptionStatus['tipo'],
                    'clinica_id' => $clinica->id,
                    'clinica_nombre' => $clinica->nombre,
                    'es_consultorio' => $clinica->es_consultorio_privado,
                    'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
                    'dias_vencido' => $subscriptionStatus['dias_vencido'],
                    'puede_renovar_online' => $subscriptionStatus['puede_renovar_online'],
                    'contacto_comercial_url' => $subscriptionStatus['contacto_url'],
                    'renovacion_url' => $subscriptionStatus['renovacion_url'],
                ]
            ], 402); // 402 Payment Required
        }

        // Advertencia si está por vencer (menos de 7 días)
        if ($subscriptionStatus['dias_restantes'] !== null && $subscriptionStatus['dias_restantes'] <= 7 && $subscriptionStatus['dias_restantes'] > 0) {
            $request->merge([
                'subscription_warning' => [
                    'dias_restantes' => $subscriptionStatus['dias_restantes'],
                    'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
                    'es_consultorio' => $clinica->es_consultorio_privado,
                ]
            ]);
        }

        return $next($request);
    }

    /**
     * Obtener estado detallado de la suscripción
     */
    protected function getSubscriptionStatus(Clinica $clinica, $user): array
    {
        $now = now();
        $fechaVencimiento = $clinica->fecha_vencimiento;
        $esConsultorio = $clinica->es_consultorio_privado;

        // Si no tiene fecha de vencimiento, revisar si está pagada
        if (!$fechaVencimiento && !$clinica->pagado) {
            return [
                'active' => false,
                'tipo' => $esConsultorio ? 'consultorio_sin_pago' : 'clinica_sin_pago',
                'message' => 'No se ha registrado ningún pago para esta cuenta.',
                'dias_vencido' => null,
                'dias_restantes' => null,
                'puede_renovar_online' => $esConsultorio,
                'contacto_url' => '/api/clinica-contacto-comercial',
                'renovacion_url' => $esConsultorio ? '/suscripcion' : null,
            ];
        }

        // Si no tiene fecha de vencimiento pero está pagada (plan perpetuo/legacy)
        if (!$fechaVencimiento && $clinica->pagado && $clinica->activa) {
            return [
                'active' => true,
                'tipo' => 'perpetuo',
                'message' => null,
                'dias_vencido' => null,
                'dias_restantes' => null,
                'puede_renovar_online' => false,
                'contacto_url' => null,
                'renovacion_url' => null,
            ];
        }

        // Calcular días
        $diasRestantes = $fechaVencimiento ? $now->diffInDays($fechaVencimiento, false) : null;
        
        // Suscripción vencida
        if ($fechaVencimiento && $now->greaterThan($fechaVencimiento)) {
            $diasVencido = abs($diasRestantes);
            
            return [
                'active' => false,
                'tipo' => $esConsultorio ? 'consultorio_vencido' : 'clinica_vencida',
                'message' => $esConsultorio 
                    ? "Tu suscripción venció hace {$diasVencido} días. Renueva para continuar usando el sistema."
                    : "La suscripción de tu clínica ha vencido. Contacta con nuestro equipo comercial para renovar.",
                'dias_vencido' => $diasVencido,
                'dias_restantes' => null,
                'puede_renovar_online' => $esConsultorio,
                'contacto_url' => '/api/clinica-contacto-comercial',
                'renovacion_url' => $esConsultorio ? '/suscripcion' : null,
            ];
        }

        // Para consultorios: verificar también la suscripción del usuario propietario
        if ($esConsultorio && $clinica->propietario_user_id) {
            $propietario = $clinica->propietario ?? $user;
            if ($propietario && !$propietario->tieneSuscripcionConsultorioActiva()) {
                return [
                    'active' => false,
                    'tipo' => 'consultorio_vencido',
                    'message' => 'Tu suscripción de consultorio ha vencido. Renueva para continuar.',
                    'dias_vencido' => 0,
                    'dias_restantes' => null,
                    'puede_renovar_online' => true,
                    'contacto_url' => null,
                    'renovacion_url' => '/suscripcion',
                ];
            }
        }

        // Suscripción activa
        return [
            'active' => true,
            'tipo' => $esConsultorio ? 'consultorio_activo' : 'clinica_activa',
            'message' => null,
            'dias_vencido' => null,
            'dias_restantes' => $diasRestantes,
            'puede_renovar_online' => $esConsultorio,
            'contacto_url' => null,
            'renovacion_url' => $esConsultorio ? '/suscripcion' : null,
        ];
    }
}
