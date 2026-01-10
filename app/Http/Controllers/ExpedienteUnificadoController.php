<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Clinico;
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\ReporteFisio;
use App\Models\ExpedientePulmonar;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaAltaFisioterapia;
use App\Models\CualidadFisica;
use App\Models\ReporteFinalPulmonar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpedienteUnificadoController extends Controller
{
    /**
     * Obtener todos los expedientes de un paciente respetando permisos
     */
    public function getExpedientesByPaciente($pacienteId)
    {
        $user = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los expedientes de este paciente'], 403);
        }

        $expedientes = collect();

        // 1. Esfuerzos
        $esfuerzos = Esfuerzo::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 1,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Prueba de Esfuerzo'
                ];
            });

        // 2. Estratificaciones
        $estratificaciones = Estratificacion::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 2,
                    'fecha' => $item->estrati_fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Estratificación'
                ];
            });

        // 3. Clínicos
        $clinicos = Clinico::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 3,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Expediente Clínico'
                ];
            });

        // 4. Reportes Finales
        $reportes = ReporteFinal::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 4,
                    'fecha' => $item->created_at->format('Y-m-d'),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Reporte Final'
                ];
            });

        // 5. Reportes Nutricionales
        $nutricionales = ReporteNutri::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 6,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota Nutricional'
                ];
            });

        // 6. Reportes Psicológicos
        $psicologicos = ReportePsico::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 5,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota Psicológica'
                ];
            });

        // 7. Reportes Fisiológicos
        $fisiologicos = ReporteFisio::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 7,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota Fisiológica'
                ];
            });

        // 8. Expedientes Pulmonares
        $pulmonares = ExpedientePulmonar::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 8,
                    'fecha' => $item->fecha_consulta,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Expediente Pulmonar'
                ];
            });

        // 11. Historia Clínica de Fisioterapia
        $historiasFisioterapia = HistoriaClinicaFisioterapia::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 11,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Historia Clínica Fisioterapia'
                ];
            });

        // 12. Notas de Evolución de Fisioterapia
        $evolucionesFisioterapia = NotaEvolucionFisioterapia::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 12,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota de Evolución Fisioterapia'
                ];
            });

        // 13. Notas de Alta de Fisioterapia
        $altasFisioterapia = NotaAltaFisioterapia::where('paciente_id', $pacienteId)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 13,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota de Alta Fisioterapia'
                ];
            });

        // 14. Cualidades Físicas No Aeróbicas
        $cualidadesFisicas = CualidadFisica::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 14,
                    'fecha' => $item->fecha_prueba_inicial,
                    'fecha_prueba_inicial' => $item->fecha_prueba_inicial,
                    'fecha_prueba_final' => $item->fecha_prueba_final,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Cualidades Físicas No Aeróbicas',
                    'is_cualidad_fisica' => true
                ];
            });

        // 15. Reporte Final Pulmonar
        $reportesFinalesPulmonares = ReporteFinalPulmonar::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 15,
                    'fecha' => $item->fecha_termino ?? $item->created_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Reporte Final Pulmonar'
                ];
            });

        // Combinar todos los expedientes
        $expedientes = $expedientes
            ->merge($esfuerzos)
            ->merge($estratificaciones)
            ->merge($clinicos)
            ->merge($reportes)
            ->merge($nutricionales)
            ->merge($psicologicos)
            ->merge($fisiologicos)
            ->merge($pulmonares)
            ->merge($historiasFisioterapia)
            ->merge($evolucionesFisioterapia)
            ->merge($altasFisioterapia)
            ->merge($cualidadesFisicas)
            ->merge($reportesFinalesPulmonares);

        // Ordenar por fecha de creación (más recientes primero)
        $expedientes = $expedientes->sortByDesc('created_at')->values();

        return response()->json([
            'data' => $expedientes,
            'total' => $expedientes->count()
        ]);
    }
}
