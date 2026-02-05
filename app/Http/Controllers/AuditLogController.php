<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->with(['user', 'clinica', 'sucursal'])
            ->latest();

        // Filtrar por clínica del usuario actual
        if (!auth()->user()->hasRole('super_admin')) {
            $query->where('clinica_id', auth()->user()->clinica_id);
        }

        // Aplicar filtros de búsqueda
        if ($request->filled('modelo')) {
            $query->where('modelo_afectado', 'LIKE', "%{$request->modelo}%");
        }

        if ($request->filled('evento')) {
            $query->where('evento', $request->evento);
        }

        if ($request->filled('usuario')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('nombre', 'LIKE', "%{$request->usuario}%");
            });
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio,
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        if ($request->filled('id_recurso')) {
            $query->where('id_recurso', $request->id_recurso);
        }

        $logs = $query->paginate(50);

        return response()->json([
            'logs' => $logs,
            'stats' => $this->getAuditStats()
        ]);
    }

    /**
     * Display the specified audit log
     */
    public function show($id)
    {
        $log = AuditLog::with(['user', 'clinica', 'sucursal'])->findOrFail($id);

        // Verificar que el usuario tenga acceso a este log
        if (!auth()->user()->hasRole('super_admin') && $log->clinica_id !== auth()->user()->clinica_id) {
            abort(403, 'No tienes permiso para ver este log');
        }

        return response()->json($log);
    }

    /**
     * Get audit statistics
     */
    public function stats()
    {
        return response()->json($this->getAuditStats());
    }

    /**
     * Get audit logs for a specific resource
     */
    public function forResource(Request $request, string $modelClass, int $resourceId)
    {
        $query = AuditLog::query()
            ->where('modelo_afectado', $modelClass)
            ->where('id_recurso', $resourceId)
            ->with(['user'])
            ->latest();

        // Filtrar por clínica del usuario actual
        if (!auth()->user()->hasRole('super_admin')) {
            $query->where('clinica_id', auth()->user()->clinica_id);
        }

        $logs = $query->get();

        return response()->json($logs);
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request)
    {
        $query = AuditLog::query()
            ->with(['user', 'clinica'])
            ->latest();

        // Aplicar mismo filtro de clínica
        if (!auth()->user()->hasRole('super_admin')) {
            $query->where('clinica_id', auth()->user()->clinica_id);
        }

        // Aplicar filtros de fecha si existen
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio,
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        $logs = $query->limit(10000)->get();

        $csv = "ID,Fecha,Usuario,Clínica,Evento,Modelo,ID Recurso,IP Address,Descripción\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->nombre ?? 'N/A',
                $log->clinica?->nombre ?? 'N/A',
                $log->evento,
                class_basename($log->modelo_afectado),
                $log->id_recurso,
                $log->ip_address,
                '"' . str_replace('"', '""', $log->descripcion ?? '') . '"',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * Calculate audit statistics
     */
    protected function getAuditStats(): array
    {
        $baseQuery = AuditLog::query();

        // Filtrar por clínica si no es super admin
        if (!auth()->user()->hasRole('super_admin')) {
            $baseQuery->where('clinica_id', auth()->user()->clinica_id);
        }

        return [
            'total_logs' => (clone $baseQuery)->count(),
            'logs_today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'logs_this_week' => (clone $baseQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
            'logs_this_month' => (clone $baseQuery)->where('created_at', '>=', now()->startOfMonth())->count(),
            'by_event' => (clone $baseQuery)->selectRaw('evento, COUNT(*) as total')
                ->groupBy('evento')
                ->pluck('total', 'evento'),
            'top_users' => (clone $baseQuery)
                ->selectRaw('user_id, COUNT(*) as total')
                ->with('user:id,nombre')
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn($item) => [
                    'usuario' => $item->user?->nombre ?? 'N/A',
                    'total' => $item->total
                ]),
        ];
    }
}
