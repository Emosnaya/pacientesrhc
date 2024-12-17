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
        return new ReporteNutriCollection(ReporteNutri::where('user_id', Auth::user()->id)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $reporteNutri = new ReporteNutri();
        $data = $request->input('datos');
        $id = intval($request->input('id'));
        $paciente = Paciente::find($id);
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
        $reporteNutri->mmHg = $data['mmHg'];
        $reporteNutri->estado = $data['estado'];
        $reporteNutri->glucosa = $data['glucosa'];
        $reporteNutri->recomendaciones = json_encode($data['recomendaciones']);
        $reporteNutri->diagnostico = $data['diagnostico'];
        $reporteNutri->tipo_exp = 6;
        $reporteNutri->user_id = Auth::user()->id;
        $reporteNutri->nutriologo = $data['nutriologo'];
        $reporteNutri->cedula_nutriologo = $data['cedula_nutriologo'];
        $reporteNutri->save();





    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function show(ReporteNutri $reporteNutri)
    {
        $reporteNutriResponse = ReporteNutri::find($reporteNutri->id);

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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function update(Request $data, ReporteNutri $reporte)
    {
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
        $reporteNutri->Controlglucosa = $data['Controlglucosa']=== 'true'  || $data['equilibrio_trabajo']=== "1" ? true : false;
        $reporteNutri->controlPeso = $data['controlPeso']=== 'true'  || $data['equilibrio_trabajo']=== "1" ? true : false;
        $reporteNutri->otro = $data['otro'];
        $reporteNutri->mmHg = $data['mmHg'];
        $reporteNutri->estado = $data['estado'];
        $reporteNutri->glucosa = $data['glucosa'];
        $reporteNutri->recomendaciones = json_encode($data['recomendaciones']);
        $reporteNutri->diagnostico = $data['diagnostico'];
        $reporteNutri->tipo_exp = 6;
        $reporteNutri->nutriologo = $data['nutriologo'];
        $reporteNutri->cedula_nutriologo = $data['cedula_nutriologo'];
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
        if($reporteNutri->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporteNutri->delete();
        return response()->json('',204);
    }
}
