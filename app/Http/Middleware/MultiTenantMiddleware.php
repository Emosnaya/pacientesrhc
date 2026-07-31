<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinica;
use App\Services\SubscriptionStatusService;

class MultiTenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo aplicar el middleware si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Cuentas solo de portal del paciente: no tienen clínica de staff
            if ($user->paciente_id && ! $user->clinica_id && ! $user->clinica_activa_id) {
                return $next($request);
            }

            // Resolver qué clínica está activa:
            // clinica_activa_id si el usuario está usando un workspace alternativo (consultorio privado),
            // de lo contrario la clinica_id asignada originalmente.
            $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;

            if (!$clinicaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no tiene clínica asignada'
                ], 403);
            }

            // Verificar que la clínica esté activa
            $clinica = Clinica::find($clinicaId);
            if (!$clinica || !$clinica->activa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clínica inactiva o no encontrada'
                ], 403);
            }

            $path = $request->path();
            // Rutas que deben responder aunque la suscripción esté vencida (bootstrap UI + pago)
            $exemptSubscription = str_starts_with($path, 'api/subscription')
                || str_starts_with($path, 'api/clinica-contacto-comercial')
                || str_starts_with($path, 'api/suscripcion-consultorio')
                || str_starts_with($path, 'api/clinica/exports')
                || str_starts_with($path, 'api/soporte/')
                || $path === 'api/user'
                || $path === 'api/logout'
                || str_starts_with($path, 'api/consultorio/mis-clinicas')
                || str_starts_with($path, 'api/consultorio/cambiar-workspace');

            if (! $request->is('clinicas*') && ! $exemptSubscription) {
                app(\App\Services\StripeSubscriptionRenewalService::class)
                    ->repairClinicaStripePeriodIfNeeded($clinica);
                $clinica->refresh();

                $status = SubscriptionStatusService::getStatus($clinica, $user);

                if (! $status['active']) {
                    return response()->json([
                        'success' => false,
                        'message' => $status['message'] ?? 'Suscripción vencida. Renueva para continuar.',
                        'subscription_expired' => true,
                        'subscription_info' => SubscriptionStatusService::subscriptionInfoForResponse($clinica, $status),
                    ], 402);
                }
            }

            // Inyectar la clínica efectiva al request para que los controllers la usen
            $request->merge(['current_clinica' => $clinica]);
        }

        return $next($request);
    }
}
