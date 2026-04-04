<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AdminUser;

/**
 * Middleware para verificar que el usuario es un admin de Lynkamed
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Verificar que el usuario está autenticado
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        // Verificar que es un AdminUser
        if (!($user instanceof AdminUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso no autorizado',
            ], 403);
        }

        // Verificar que está activo
        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario desactivado',
            ], 403);
        }

        // Si se especificaron roles, verificar que el usuario tiene uno de ellos
        if (!empty($roles) && !in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para esta acción',
            ], 403);
        }

        return $next($request);
    }
}
