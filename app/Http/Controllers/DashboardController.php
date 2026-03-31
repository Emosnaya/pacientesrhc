<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;
use App\Models\Paciente;
use App\Models\ReporteFisio;
use App\Models\ReportePsico;
use App\Models\ReporteNutri;
use App\Models\Clinico;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Obtener estadísticas rápidas del dashboard
     */
    public function getStats(Request $request)
    {
        try {
            $user = $request->user();
            $clinicaId = $user->clinica_activa_id ?? $user->clinica_efectiva_id;
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            // Total de pacientes (solo vínculo clinica_paciente; sucursal opcional en pivot)
            $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null);
            $totalPacientes = $query->count();

            // Pacientes activos (con actualización en últimos 30 días)
            $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null)
                ->where('updated_at', '>=', now()->subDays(30));
            $pacientesActivos = $query->count();

            // Tasa de actividad
            $tasaActividad = $totalPacientes > 0
                ? round(($pacientesActivos / $totalPacientes) * 100, 1)
                : 0;

            // Pacientes que requieren seguimiento (sin actividad en +14 días)
            $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null)
                ->where('updated_at', '<', now()->subDays(14));
            $requierenSeguimiento = $query->count();

            // Reportes de esta semana
            $reportesSemanales = $this->countWeeklyReports($clinicaId, $sucursalId);

            // Pacientes nuevos esta semana
            $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null)
                ->where('created_at', '>=', now()->startOfWeek());
            $pacientesNuevos = $query->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_pacientes' => $totalPacientes,
                    'pacientes_activos' => $pacientesActivos,
                    'tasa_actividad' => $tasaActividad,
                    'requieren_seguimiento' => $requierenSeguimiento,
                    'reportes_semanales' => $reportesSemanales,
                    'pacientes_nuevos' => $pacientesNuevos,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Obtener alertas de pacientes que requieren seguimiento
     */
    public function getAlerts(Request $request)
    {
        try {
            $user = $request->user();
            $clinicaId = $user->clinica_activa_id ?? $user->clinica_efectiva_id;
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            // Obtener pacientes sin actividad reciente
            $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null)
                ->where('updated_at', '<', now()->subDays(14));
            
            $pacientesSinSeguimiento = $query->select('id', 'nombre', 'apellidoPat', 'registro', 'updated_at')
                ->orderBy('updated_at', 'asc')
                ->limit(10)
                ->get();

            $alerts = $pacientesSinSeguimiento->map(function($paciente) {
                $diasSinActividad = now()->diffInDays($paciente->updated_at);
                
                return [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                    'registro' => $paciente->registro,
                    'dias_sin_actividad' => $diasSinActividad,
                    'ultima_actualizacion' => $paciente->updated_at->format('d/m/Y'),
                    'prioridad' => $diasSinActividad > 30 ? 'alta' : 'media'
                ];
            });

            return response()->json([
                'success' => true,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al obtener alertas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener alertas'
            ], 500);
        }
    }

    /**
     * Generar insights automáticos usando IA
     */
    public function generateInsights(Request $request)
    {
        try {
            Log::info('📊 Generando insights del dashboard...');

            $user = $request->user();
            $clinicaId = $user->clinica_activa_id ?? $user->clinica_efectiva_id;
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            // Obtener datos de pacientes
            $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null);
            
            $pacientes = $query->select('id', 'nombre', 'apellidoPat', 'registro', 'created_at', 'updated_at')
                ->get()
                ->toArray();

            // Obtener reportes recientes (última semana)
            $reportes = $this->getRecentReports($clinicaId, $sucursalId);

            // Generar insights con IA
            $result = $this->aiService->generateDashboardInsights($pacientes, $reportes);

            if ($result['success']) {
                Log::info('✅ Insights generados exitosamente');
                return response()->json($result);
            } else {
                Log::warning('⚠️ Error al generar insights');
                return response()->json($result, 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error crítico en insights: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al generar insights'
            ], 500);
        }
    }

    /**
     * Contar reportes de la semana actual
     */
    private function countWeeklyReports($clinicaId, $sucursalId = null)
    {
        $startOfWeek = now()->startOfWeek();
        
        $fisio = ReporteFisio::whereHas('paciente', function($query) use ($clinicaId, $sucursalId) {
            $query->forClinicaWorkspace((int) $clinicaId);
            if ($sucursalId) $query->where('sucursal_id', $sucursalId);
        })->where('created_at', '>=', $startOfWeek)->count();

        $psico = ReportePsico::whereHas('paciente', function($query) use ($clinicaId, $sucursalId) {
            $query->forClinicaWorkspace((int) $clinicaId);
            if ($sucursalId) $query->where('sucursal_id', $sucursalId);
        })->where('created_at', '>=', $startOfWeek)->count();

        $nutri = ReporteNutri::whereHas('paciente', function($query) use ($clinicaId, $sucursalId) {
            $query->forClinicaWorkspace((int) $clinicaId);
            if ($sucursalId) $query->where('sucursal_id', $sucursalId);
        })->where('created_at', '>=', $startOfWeek)->count();

        return $fisio + $psico + $nutri;
    }

    /**
     * Obtener reportes recientes para análisis
     */
    private function getRecentReports($clinicaId, $sucursalId = null)
    {
        $oneWeekAgo = now()->subDays(7);

        // Reportes de fisioterapia
        $fisio = ReporteFisio::whereHas('paciente', function($query) use ($clinicaId, $sucursalId) {
            $query->forClinicaWorkspace((int) $clinicaId);
            if ($sucursalId) $query->where('sucursal_id', $sucursalId);
        })
        ->where('created_at', '>=', $oneWeekAgo)
        ->select('id', 'created_at')
        ->get()
        ->map(function($r) {
            return ['type' => 'fisio', 'created_at' => $r->created_at];
        });

        // Reportes de psicología
        $psico = ReportePsico::whereHas('paciente', function($query) use ($clinicaId, $sucursalId) {
            $query->forClinicaWorkspace((int) $clinicaId);
            if ($sucursalId) $query->where('sucursal_id', $sucursalId);
        })
        ->where('created_at', '>=', $oneWeekAgo)
        ->select('id', 'created_at')
        ->get()
        ->map(function($r) {
            return ['type' => 'psico', 'created_at' => $r->created_at];
        });

        // Reportes de nutrición
        $nutri = ReporteNutri::whereHas('paciente', function($query) use ($clinicaId, $sucursalId) {
            $query->forClinicaWorkspace((int) $clinicaId);
            if ($sucursalId) $query->where('sucursal_id', $sucursalId);
        })
        ->where('created_at', '>=', $oneWeekAgo)
        ->select('id', 'created_at')
        ->get()
        ->map(function($r) {
            return ['type' => 'nutri', 'created_at' => $r->created_at];
        });

        return array_merge(
            $fisio->toArray(),
            $psico->toArray(),
            $nutri->toArray()
        );
    }
}
