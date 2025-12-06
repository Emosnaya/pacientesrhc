<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicoCollection;
use App\Http\Resources\EsfuerzoCollection;
use App\Http\Resources\EstratificacionCollection;
use App\Http\Resources\ReporteFinalCollection;
use App\Http\Resources\ReporteNutriCollection;
use App\Http\Resources\ReportePsicoCollection;
use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfoController extends Controller
{
    public function clinicos($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        return new ClinicoCollection(Clinico::where('paciente_id', $id)->get());
    }

    public function esfuerzos($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        return new EsfuerzoCollection(Esfuerzo::where('paciente_id', $id)->get());
    }

    public function estratificaciones($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        return new EstratificacionCollection(Estratificacion::where('paciente_id', $id)->get());
    }

    public function reportes($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        return new ReporteFinalCollection(ReporteFinal::where('paciente_id', $id)->get());
    }

    public function nutricional($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        return new ReporteNutriCollection(ReporteNutri::where('paciente_id', $id)->get());
    }

    public function psicologico($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        return new ReportePsicoCollection(ReportePsico::where('paciente_id', $id)->get());
    }

    /**
     * Verificar permisos específicos de un expediente
     */
    public function checkExpedientePermissions($id, $expedienteType, $expedienteId)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los reportes de este paciente'], 403);
        }

        // Todos los usuarios de la misma clínica tienen permisos de ver, editar, crear y comparar
        // Solo los admins pueden eliminar
        return response()->json([
            'can_read' => true,
            'can_edit' => true,
            'can_delete' => $user->isAdmin(),
            'can_create' => true,
            'can_compare' => true
        ]);
    }

    public function destroyPsico($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes'], 403);
        }
        
        $reporte = ReportePsico::find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte'], 403);
        }
        
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function destroyNutri($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes'], 403);
        }
        
        $reporte = ReporteNutri::find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte'], 403);
        }
        
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function nutriIndex($id)
    {
        $user = Auth::user();
        $reporteNutriResponse = ReporteNutri::find($id);

        if (!$reporteNutriResponse) {
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporteNutriResponse->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        return response()->json($reporteNutriResponse);
    }

    public function psicoIndex($id)
    {
        $user = Auth::user();
        $reportePsicoResponse = ReportePsico::find($id);

        if (!$reportePsicoResponse) {
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reportePsicoResponse->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        return response()->json($reportePsicoResponse);
    }

    public function destroyFinal($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes'], 403);
        }
        
        $reporte = ReporteFinal::find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte'], 403);
        }
        
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function destroyEsfuer($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes'], 403);
        }
        
        $reporte = Esfuerzo::find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte'], 403);
        }
        
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function destroyEstrati($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes'], 403);
        }
        
        $reporte = Estratificacion::find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte'], 403);
        }
        
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function destroyClinico($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes'], 403);
        }
        
        $reporte = Clinico::find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte'], 403);
        }
        
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
}
