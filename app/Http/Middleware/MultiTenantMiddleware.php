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
            
            // Verificar que el usuario tenga una clínica asignada
            if (!$user->clinica_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no tiene clínica asignada'
                ], 403);
            }

            // Verificar que la clínica esté activa
            $clinica = Clinica::find($user->clinica_id);
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

            // Agregar información de la clínica al request para uso posterior
            $request->merge(['current_clinica' => $clinica]);
        }

        return $next($request);
    }
}
