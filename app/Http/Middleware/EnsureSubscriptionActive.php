<?php

namespace App\Http\Middleware;

use App\Models\Clinica;
use App\Services\SubscriptionStatusService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscriptionActive
{
    protected array $exemptRoutes = [
        'api/user',
        'api/logout',
        'api/clinica-contacto-comercial',
        'api/suscripcion-consultorio',
        'api/subscription',
        'api/stripe/webhook',
        'api/paciente-portal',
        'api/consultorio/mis-clinicas',
        'api/consultorio/cambiar-workspace',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user->paciente_id && ! $user->clinica_id && ! $user->clinica_activa_id) {
            return $next($request);
        }

        $path = $request->path();
        foreach ($this->exemptRoutes as $exemptRoute) {
            if (str_starts_with($path, $exemptRoute)) {
                return $next($request);
            }
        }

        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        if (! $clinicaId) {
            return $next($request);
        }

        $clinica = Clinica::find($clinicaId);
        if (! $clinica) {
            return $next($request);
        }

        app(\App\Services\StripeSubscriptionRenewalService::class)
            ->repairClinicaStripePeriodIfNeeded($clinica);
        $clinica->refresh();

        $status = SubscriptionStatusService::getStatus($clinica, $user);

        if (! $status['active']) {
            return response()->json([
                'success' => false,
                'message' => $status['message'],
                'subscription_expired' => true,
                'subscription_info' => SubscriptionStatusService::subscriptionInfoForResponse($clinica, $status),
            ], 402);
        }

        if ($status['dias_restantes'] !== null && $status['dias_restantes'] <= 7 && $status['dias_restantes'] > 0) {
            $request->merge([
                'subscription_warning' => [
                    'dias_restantes' => $status['dias_restantes'],
                    'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
                    'es_consultorio' => $clinica->es_consultorio_privado,
                ],
            ]);
        }

        return $next($request);
    }
}
