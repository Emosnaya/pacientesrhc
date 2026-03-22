<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinica;

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

            // Verificar suscripción si no es el registro de clínicas
            if (!$request->is('clinicas*') && !$clinica->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suscripción vencida. Contacte al administrador.',
                    'subscription_expired' => true
                ], 403);
            }

            // Inyectar la clínica efectiva al request para que los controllers la usen
            $request->merge(['current_clinica' => $clinica]);
        }

        return $next($request);
    }
}
