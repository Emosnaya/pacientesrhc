<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Controlador de autenticación para el backoffice de Lynkamed
 */
class AdminAuthController extends Controller
{
    /**
     * Login de usuario admin
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $adminUser = AdminUser::where('email', $request->email)->first();

        if (!$adminUser || !Hash::check($request->password, $adminUser->password)) {
            Log::channel('soporte')->warning('Intento de login fallido en admin panel', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        if (!$adminUser->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario desactivado. Contacta al administrador.',
            ], 403);
        }

        // Actualizar último login
        $adminUser->updateLastLogin($request->ip());

        // Crear token
        $token = $adminUser->createToken('admin-panel', ['admin'])->plainTextToken;

        Log::channel('soporte')->info('Login exitoso en admin panel', [
            'admin_id' => $adminUser->id,
            'email' => $adminUser->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => $adminUser->role,
            ],
        ]);
    }

    /**
     * Logout de usuario admin
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => [
                    'can_manage_clinicas' => $user->canManageClinicas(),
                    'can_manage_suscripciones' => $user->canManageSuscripciones(),
                    'can_view_soporte' => $user->canViewSoporte(),
                    'is_superadmin' => $user->isSuperAdmin(),
                ],
            ],
        ]);
    }
}
