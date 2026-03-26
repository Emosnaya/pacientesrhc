<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientPortalFullAccount
{
    /**
     * Cuenta de paciente con contraseña ya configurada (no flujo OTP intermedio).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->paciente_id || ! $user->password_set_at) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso solo para cuentas del portal de paciente activas.',
            ], 403);
        }

        return $next($request);
    }
}
