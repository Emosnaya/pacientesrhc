<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Paciente;
use App\Models\ReporteFinal;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission  El permiso requerido (read, write, edit, delete)
     * @param  string  $resource  El tipo de recurso (paciente, expediente)
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permission, string $resource = null)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Si no se especifica recurso, verificar permisos generales
        if (!$resource) {
            return $next($request);
        }

        // Obtener el ID del recurso desde la ruta
        $resourceId = $this->getResourceIdFromRequest($request, $resource);
        
        if (!$resourceId) {
            return response()->json(['error' => 'Recurso no encontrado'], 404);
        }

        // Obtener el modelo del recurso
        $resourceModel = $this->getResourceModel($resource, $resourceId);
        
        if (!$resourceModel) {
            return response()->json(['error' => 'Recurso no encontrado'], 404);
        }

        // Los administradores solo pueden acceder a sus propios recursos
        if ($user->isAdmin()) {
            if ($resource === 'paciente' && $resourceModel->user_id !== $user->id) {
                return response()->json(['error' => 'No tienes permisos para acceder a este recurso'], 403);
            } elseif ($resource === 'expediente' && $resourceModel->user_id !== $user->id) {
                return response()->json(['error' => 'No tienes permisos para acceder a este recurso'], 403);
            }
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso específico sobre el recurso
        if (!$user->hasPermissionOn($resourceModel, $permission)) {
            return response()->json([
                'error' => 'No tienes permisos para realizar esta acción',
                'required_permission' => $permission,
                'resource' => $resource
            ], 403);
        }

        return $next($request);
    }

    /**
     * Obtener el ID del recurso desde la request
     */
    private function getResourceIdFromRequest(Request $request, string $resource): ?int
    {
        $route = $request->route();
        
        if (!$route) {
            return null;
        }

        $parameters = $route->parameters();
        
        // Buscar el parámetro que coincida con el tipo de recurso
        if ($resource === 'paciente') {
            return $parameters['paciente'] ?? $parameters['id'] ?? null;
        } elseif ($resource === 'expediente') {
            return $parameters['expediente'] ?? $parameters['id'] ?? null;
        }

        return $parameters['id'] ?? null;
    }

    /**
     * Obtener el modelo del recurso
     */
    private function getResourceModel(string $resource, int $id)
    {
        switch ($resource) {
            case 'paciente':
                return Paciente::find($id);
            case 'expediente':
                return ReporteFinal::find($id);
            default:
                return null;
        }
    }
}
