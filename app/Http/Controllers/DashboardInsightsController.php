<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\ReporteFisio;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardInsightsController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Obtener insights del dashboard
     * GET /api/dashboard/insights
     */
    public function getInsights(Request $request)
    {
        try {
            Log::info('📊 Generando insights del dashboard...');

            $user = $request->user();
            $clinicaId = $user->clinica_efectiva_id;

            // Obtener pacientes de la clínica
            $pacientes = Paciente::where('clinica_id', $clinicaId)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->toArray();

            // Obtener reportes recientes (última semana)
            $reportesNutri = ReporteNutri::whereHas('expediente.paciente', function($query) use ($clinicaId) {
                $query->where('clinica_id', $clinicaId);
            })->where('created_at', '>=', now()->subWeek())->get()->toArray();

            $reportesPsico = ReportePsico::whereHas('expediente.paciente', function($query) use ($clinicaId) {
                $query->where('clinica_id', $clinicaId);
            })->where('created_at', '>=', now()->subWeek())->get()->toArray();

            $reportesFisio = ReporteFisio::whereHas('expediente.paciente', function($query) use ($clinicaId) {
                $query->where('clinica_id', $clinicaId);
            })->where('created_at', '>=', now()->subWeek())->get()->toArray();

            $reportes = array_merge($reportesNutri, $reportesPsico, $reportesFisio);

            // Generar insights con IA
            $result = $this->aiService->generateDashboardInsights($pacientes, $reportes);

            Log::info('✅ Insights generados exitosamente');

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('❌ Error al generar insights: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al generar insights del dashboard',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas rápidas del dashboard
     * GET /api/dashboard/stats
     */
    public function getStats(Request $request)
    {
        try {
            $user = $request->user();
            $clinicaId = $user->clinica_efectiva_id;

            // Pacientes totales
            $totalPacientes = Paciente::where('clinica_id', $clinicaId)->count();

            // Pacientes activos (actualizados en los últimos 30 días)
            $pacientesActivos = Paciente::where('clinica_id', $clinicaId)
                ->where('updated_at', '>=', now()->subDays(30))
                ->count();

            // Pacientes que requieren seguimiento (sin actividad en 14+ días)
            $requierenSeguimiento = Paciente::where('clinica_id', $clinicaId)
                ->where('updated_at', '<', now()->subDays(14))
                ->count();

            // Reportes de la última semana
            $reportesSemanales = ReporteNutri::whereHas('expediente.paciente', function($query) use ($clinicaId) {
                $query->where('clinica_id', $clinicaId);
            })->where('created_at', '>=', now()->subWeek())->count();

            $reportesSemanales += ReportePsico::whereHas('expediente.paciente', function($query) use ($clinicaId) {
                $query->where('clinica_id', $clinicaId);
            })->where('created_at', '>=', now()->subWeek())->count();

            $reportesSemanales += ReporteFisio::whereHas('expediente.paciente', function($query) use ($clinicaId) {
                $query->where('clinica_id', $clinicaId);
            })->where('created_at', '>=', now()->subWeek())->count();

            // Pacientes nuevos (últimos 30 días)
            $pacientesNuevos = Paciente::where('clinica_id', $clinicaId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_pacientes' => $totalPacientes,
                    'pacientes_activos' => $pacientesActivos,
                    'requieren_seguimiento' => $requierenSeguimiento,
                    'reportes_semanales' => $reportesSemanales,
                    'pacientes_nuevos' => $pacientesNuevos,
                    'tasa_actividad' => $totalPacientes > 0 ? round(($pacientesActivos / $totalPacientes) * 100, 1) : 0,
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
     * Obtener pacientes que requieren seguimiento
     * GET /api/dashboard/alerts
     */
    public function getAlerts(Request $request)
    {
        try {
            $user = $request->user();
            $clinicaId = $user->clinica_efectiva_id;

            // Pacientes sin actividad en 14+ días
            $pacientesSeguimiento = Paciente::where('clinica_id', $clinicaId)
                ->where('updated_at', '<', now()->subDays(14))
                ->orderBy('updated_at', 'asc')
                ->limit(10)
                ->get()
                ->map(function($paciente) {
                    return [
                        'id' => $paciente->id,
                        'nombre' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                        'registro' => $paciente->registro,
                        'dias_sin_actividad' => now()->diffInDays($paciente->updated_at),
                        'ultima_actualizacion' => $paciente->updated_at->format('d/m/Y'),
                        'tipo' => 'seguimiento',
                        'prioridad' => now()->diffInDays($paciente->updated_at) > 30 ? 'alta' : 'media'
                    ];
                });

            return response()->json([
                'success' => true,
                'alerts' => $pacientesSeguimiento
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener alertas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener alertas'
            ], 500);
        }
    }
}
