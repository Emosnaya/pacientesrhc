<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminAuditController extends Controller
{
    // ── Audit Logs ─────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $q = AuditLog::with(['user:id,nombre,apellidoPat,email', 'clinica:id,nombre'])
            ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($sub) use ($s) {
                $sub->where('descripcion', 'LIKE', "%{$s}%")
                    ->orWhere('evento', 'LIKE', "%{$s}%")
                    ->orWhere('modelo_afectado', 'LIKE', "%{$s}%")
                    ->orWhere('ip_address', 'LIKE', "%{$s}%")
                    ->orWhereHas('user', fn($u) => $u->where('nombre', 'LIKE', "%{$s}%")
                        ->orWhere('email', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('evento'))  $q->where('evento', $request->evento);
        if ($request->filled('modelo'))  $q->where('modelo_afectado', 'LIKE', "%{$request->modelo}%");
        if ($request->filled('user_id')) $q->where('user_id', $request->user_id);
        if ($request->filled('clinica_id')) $q->where('clinica_id', $request->clinica_id);
        if ($request->filled('fecha_inicio')) $q->whereDate('created_at', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin'))    $q->whereDate('created_at', '<=', $request->fecha_fin);

        $logs = $q->paginate((int) $request->get('per_page', 50));

        // Stats rápidas
        $stats = [
            'total'       => AuditLog::count(),
            'hoy'         => AuditLog::whereDate('created_at', today())->count(),
            'esta_semana' => AuditLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'por_evento'  => AuditLog::selectRaw('evento, COUNT(*) as total')
                                ->groupBy('evento')->pluck('total', 'evento'),
        ];

        return response()->json(['success' => true, 'data' => $logs, 'stats' => $stats]);
    }

    public function show(int $id): JsonResponse
    {
        $log = AuditLog::with(['user:id,nombre,apellidoPat,email', 'clinica:id,nombre'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $log]);
    }

    public function export(Request $request)
    {
        $q = AuditLog::with(['user:id,nombre,apellidoPat,email', 'clinica:id,nombre'])->latest();

        if ($request->filled('fecha_inicio')) $q->whereDate('created_at', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin'))    $q->whereDate('created_at', '<=', $request->fecha_fin);
        if ($request->filled('evento'))       $q->where('evento', $request->evento);

        $logs = $q->limit(20000)->get();

        $csv = "\xEF\xBB\xBF"; // BOM para Excel
        $csv .= "ID,Fecha,Usuario,Email,Clínica,Evento,Modelo,ID Recurso,IP,Descripción\n";
        foreach ($logs as $l) {
            $csv .= implode(',', [
                $l->id,
                $l->created_at->format('Y-m-d H:i:s'),
                '"' . ($l->user ? $l->user->nombre . ' ' . $l->user->apellidoPat : 'N/A') . '"',
                $l->user?->email ?? 'N/A',
                '"' . ($l->clinica?->nombre ?? 'N/A') . '"',
                $l->evento,
                class_basename($l->modelo_afectado ?? ''),
                $l->id_recurso ?? '',
                $l->ip_address ?? '',
                '"' . str_replace('"', '""', $l->descripcion ?? '') . '"',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="audit_' . now()->format('Y-m-d') . '.csv"');
    }

    // ── Pacientes ──────────────────────────────────────────────────────────────

    public function pacientes(Request $request): JsonResponse
    {
        $q = Paciente::with(['clinica:id,nombre'])
            ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($sub) use ($s) {
                $sub->where('nombre', 'LIKE', "%{$s}%")
                    ->orWhere('apellidoPat', 'LIKE', "%{$s}%")
                    ->orWhere('apellidoMat', 'LIKE', "%{$s}%")
                    ->orWhere('email', 'LIKE', "%{$s}%")
                    ->orWhere('telefono', 'LIKE', "%{$s}%")
                    ->orWhere('numero_expediente', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('clinica_id')) $q->where('clinica_id', $request->clinica_id);
        if ($request->filled('fecha_inicio')) $q->whereDate('created_at', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin'))    $q->whereDate('created_at', '<=', $request->fecha_fin);

        $pacientes = $q->paginate((int) $request->get('per_page', 50));

        $stats = [
            'total'        => Paciente::count(),
            'hoy'          => Paciente::whereDate('created_at', today())->count(),
            'esta_semana'  => Paciente::where('created_at', '>=', now()->startOfWeek())->count(),
            'este_mes'     => Paciente::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return response()->json(['success' => true, 'data' => $pacientes, 'stats' => $stats]);
    }

    public function pacienteAudit(int $id): JsonResponse
    {
        $paciente = Paciente::findOrFail($id);
        $logs = AuditLog::with(['user:id,nombre,apellidoPat,email'])
            ->where(function ($q) use ($id) {
                $q->where('id_recurso', $id)
                  ->where('modelo_afectado', 'LIKE', '%Paciente%');
            })
            ->orWhere(function ($q) use ($id) {
                $q->where('descripcion', 'LIKE', "%paciente_id\":{$id}%")
                  ->orWhere('datos_nuevos', 'LIKE', "%\"paciente_id\":{$id}%");
            })
            ->latest()
            ->limit(200)
            ->get();

        return response()->json(['success' => true, 'paciente' => $paciente, 'logs' => $logs]);
    }

    // ── Usuarios ───────────────────────────────────────────────────────────────

    public function usuarios(Request $request): JsonResponse
    {
        $q = User::with(['clinica:id,nombre'])
            ->whereNull('paciente_id')  // excluir portales de pacientes
            ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($sub) use ($s) {
                $sub->where('nombre', 'LIKE', "%{$s}%")
                    ->orWhere('apellidoPat', 'LIKE', "%{$s}%")
                    ->orWhere('email', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('clinica_id')) $q->where('clinica_id', $request->clinica_id);
        if ($request->filled('rol'))        $q->where('rol', $request->rol);

        $usuarios = $q->paginate((int) $request->get('per_page', 50));

        $stats = [
            'total'       => User::whereNull('paciente_id')->count(),
            'hoy'         => User::whereNull('paciente_id')->whereDate('created_at', today())->count(),
            'este_mes'    => User::whereNull('paciente_id')->where('created_at', '>=', now()->startOfMonth())->count(),
            'admins'      => User::whereNull('paciente_id')->where('isAdmin', true)->count(),
        ];

        return response()->json(['success' => true, 'data' => $usuarios, 'stats' => $stats]);
    }

    public function usuarioAudit(int $id): JsonResponse
    {
        $usuario = User::findOrFail($id);
        $logs = AuditLog::with(['clinica:id,nombre'])
            ->where('user_id', $id)
            ->latest()
            ->limit(300)
            ->get();

        return response()->json(['success' => true, 'usuario' => $usuario, 'logs' => $logs]);
    }
}
