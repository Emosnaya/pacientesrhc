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
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfoController extends Controller
{
    public function clinicos($id)
    {
        return new ClinicoCollection(Clinico::where('paciente_id', $id)->get());
    }

    public function esfuerzos($id)
    {
        return new EsfuerzoCollection(Esfuerzo::where('paciente_id', $id)->get());
    }

    public function estratificaciones($id)
    {
        return new EstratificacionCollection(Estratificacion::where('paciente_id', $id)->get());
    }
    public function reportes($id)
    {
        return new ReporteFinalCollection(ReporteFinal::where('paciente_id', $id)->get());
    }
    public function nutricional($id)
    {
        return new ReporteNutriCollection(ReporteNutri::where('paciente_id', $id)->get());
    }
    public function psicologico($id)
    {
        return new ReportePsicoCollection(ReportePsico::where('paciente_id', $id)->get());
    }
    public function destroyPsico($id)
    {
        $reporte = ReportePsico::find($id);

        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function destroyNutri($id)
    {
        $reporte = ReporteNutri::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function nutriIndex($id)
    {
        $reporteNutriResponse = ReporteNutri::find($id);

        if($reporteNutriResponse->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$reporteNutriResponse) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($reporteNutriResponse);
    }
    public function psicoIndex($id)
    {
        $reportePsicoResponse = ReportePsico::find($id);

        if($reportePsicoResponse->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$reportePsicoResponse) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($reportePsicoResponse);
    }
    public function destroyFinal($id)
    {
        $reporte = ReporteFinal::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function destroyEsfuer($id)
    {
        $reporte = Esfuerzo::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function destroyEstrati($id)
    {
        $reporte = Estratificacion::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function destroyClinico($id)
    {
        $reporte = Clinico::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
}
