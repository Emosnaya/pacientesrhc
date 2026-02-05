<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo auditar si hay usuario autenticado
        if (!Auth::check()) {
            return $response;
        }

        // Detectar operaciones de visualización en endpoints específicos
        if ($request->isMethod('GET') && $this->shouldAuditView($request)) {
            $this->logViewAccess($request);
        }

        return $response;
    }

    /**
     * Determina si se debe auditar una visualización
     */
    protected function shouldAuditView(Request $request): bool
    {
        // Lista de rutas que requieren auditoría de visualización
        $auditableRoutes = [
            'pacientes.show',
            'expedientes.show',
            'recetas.show',
            'odontogramas.show',
            // Agregar más rutas según necesidad
        ];

        $currentRoute = $request->route()?->getName();

        return in_array($currentRoute, $auditableRoutes);
    }

    /**
     * Registra el acceso de visualización
     */
    protected function logViewAccess(Request $request): void
    {
        $route = $request->route();
        $modelClass = $this->getModelClassFromRoute($route?->getName());
        $modelId = $route?->parameter('id') ?? $route?->parameter('paciente') ?? $route?->parameter('expediente');

        if (!$modelClass || !$modelId) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'clinica_id' => Auth::user()->clinica_id,
            'sucursal_id' => session('sucursal_id'),
            'evento' => 'viewed',
            'modelo_afectado' => $modelClass,
            'id_recurso' => $modelId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_anteriores' => null,
            'datos_nuevos' => null,
            'descripcion' => Auth::user()->nombre . " visualizó " . class_basename($modelClass) . " #{$modelId}",
        ]);
    }

    /**
     * Obtiene la clase del modelo desde el nombre de la ruta
     */
    protected function getModelClassFromRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $routeToModel = [
            'pacientes.show' => \App\Models\Paciente::class,
            'expedientes.show' => \App\Models\ExpedientePulmonar::class,
            'recetas.show' => \App\Models\Receta::class,
            'odontogramas.show' => \App\Models\Odontograma::class,
        ];

        return $routeToModel[$routeName] ?? null;
    }
}
