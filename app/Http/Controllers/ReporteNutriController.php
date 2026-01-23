<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReporteNutriCollection;
use App\Models\Paciente;
use App\Models\ReporteNutri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteNutriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Todos los usuarios pueden ver los reportes nutricionales de su clínica
        $reportes = ReporteNutri::whereHas('paciente', function($query) use ($user) {
            $query->where('clinica_id', $user->clinica_id);
        })->with('paciente')->get();
        
        return new ReporteNutriCollection($reportes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $reporteNutri = new ReporteNutri();
        $data = $request->input('datos');
        $id = intval($request->input('id'));
        $paciente = Paciente::find($id);
        
        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }
        
        $reporteNutri->paciente_id = $paciente->id;

        $reporteNutri->sistolica = $data['sistolica'];
        $reporteNutri->diastolica = $data['diastolica'];
        $reporteNutri->trigliceridos = $data['trigliceridos'];
        $reporteNutri->hdl = $data['hdl'];
        $reporteNutri->ldl = $data['ldl'];
        $reporteNutri->colesterol = $data['colesterol'];
        $reporteNutri->aguaSimple = $data['aguaSimple'];
        $reporteNutri->cafe_te = $data['cafe_te'];
        $reporteNutri->bebidas = $data['bebidas'];
        $reporteNutri->light = $data['light'];
        $reporteNutri->comidas = $data['comidas'];
        $reporteNutri->actividad = $data['actividad'];
        $reporteNutri->actividadDias = $data['actividadDias'];
        $reporteNutri->minutosDia = $data['minutosDia'];
        $reporteNutri->formula = $data['formula'];
        $reporteNutri->lipidos = $data['lipidos']=== 'true' ? true : false;
        $reporteNutri->Controlglucosa = $data['Controlglucosa']=== 'true' ? true : false;
        $reporteNutri->controlPeso = $data['controlPeso']=== 'true' ? true : false;
        $reporteNutri->otro = $data['otro'];
        $reporteNutri->mmHg = null;
        $reporteNutri->estado = $data['estado'];
        $reporteNutri->glucosa = $data['glucosa'];
        $reporteNutri->recomendaciones = json_encode($data['recomendaciones']);
        $reporteNutri->diagnostico = $data['diagnostico'];
        $reporteNutri->recomendacion= $data['recomendacion'];
        $reporteNutri->observaciones= $data['observaciones'];
        $reporteNutri->controlPresion= $data['controlPresion'] === 'true' ? true : false;;
        $reporteNutri->tipo_exp = 6;
        
        // Asignar el user_id del dueño del paciente
        $reporteNutri->user_id = $paciente->user_id;
        $reporteNutri->clinica_id = $user->clinica_id;
        $reporteNutri->sucursal_id = $paciente->sucursal_id;
        $reporteNutri->nutriologo = $data['nutriologo'];
        $reporteNutri->cedula_nutriologo = $data['cedula_nutriologo'];
        $reporteNutri->save();

        return response()->json("Guardado");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function show(ReporteNutri $reporteNutri)
    {
        $user = Auth::user();
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporteNutri->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte nutricional'], 403);
        }

        return response()->json($reporteNutri->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function update(Request $data, ReporteNutri $reporte)
    {
        $user = Auth::user();
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporte->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte nutricional'], 403);
        }
        
        $reporteNutri = ReporteNutri::find($data->id);
        $reporteNutri->sistolica = $data['sistolica'];
        $reporteNutri->diastolica = $data['diastolica'];
        $reporteNutri->trigliceridos = $data['trigliceridos'];
        $reporteNutri->hdl = $data['hdl'];
        $reporteNutri->ldl = $data['ldl'];
        $reporteNutri->colesterol = $data['colesterol'];
        $reporteNutri->aguaSimple = $data['aguaSimple'];
        $reporteNutri->cafe_te = $data['cafe_te'];
        $reporteNutri->bebidas = $data['bebidas'];
        $reporteNutri->light = $data['light'];
        $reporteNutri->comidas = $data['comidas'];
        $reporteNutri->actividad = $data['actividad'];
        $reporteNutri->actividadDias = $data['actividadDias'];
        $reporteNutri->minutosDia = $data['minutosDia'];
        $reporteNutri->formula = $data['formula'];
        $reporteNutri->lipidos = $data['lipidos']=== 'true' || $data['lipidos']=== "1"  ? true : false;
        $reporteNutri->Controlglucosa = $data['Controlglucosa']=== 'true'  || $data['Controlglucosa']=== "1" ? true : false;
        $reporteNutri->controlPeso = $data['controlPeso']=== 'true'  || $data['controlPeso']=== "1" ? true : false;
        $reporteNutri->otro = $data['otro'];
        $reporteNutri->mmHg = null;
        $reporteNutri->estado = $data['estado'];
        $reporteNutri->glucosa = $data['glucosa'];
        $reporteNutri->recomendaciones = json_encode($data['recomendaciones']);
        $reporteNutri->diagnostico = $data['diagnostico'];
        $reporteNutri->recomendacion= $data['recomendacion'];
        $reporteNutri->observaciones= $data['observaciones'];
        $reporteNutri->controlPresion= $data['controlPresion'] === 'true'  || $data['controlPresion']=== "1" ? true : false;
        $reporteNutri->tipo_exp = 6;
        $reporteNutri->nutriologo = $data['nutriologo'];
        $reporteNutri->cedula_nutriologo = $data['cedula_nutriologo'];
        $reporteNutri->clinica_id = $user->clinica_id;
        $reporteNutri->save();

        return response('Actualizado',204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReporteNutri $reporteNutri)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar reportes nutricionales'], 403);
        }
        
        // Verificar que el reporte pertenece a la misma clínica
        $paciente = $reporteNutri->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este reporte nutricional'], 403);
        }
        
        $reporteNutri->delete();
        return response()->json(['message' => 'Reporte nutricional eliminado exitosamente'], 204);
    }
}
