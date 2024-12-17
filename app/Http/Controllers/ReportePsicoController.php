<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportePsicoCollection;
use App\Models\Paciente;
use App\Models\ReportePsico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportePsicoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ReportePsicoCollection(ReportePsico::where('user_id', Auth::user()->id)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $reportePsico = new ReportePsico();
        $data = $request->input('datos');
        $id = intval($request->input('id'));
        $paciente = Paciente::find($id);
        $reportePsico->paciente_id = $paciente->id;

        $reportePsico->motivo_consulta = $data['motivo_consulta'];
        $reportePsico->antecedentes_medicos = $data['antecedentes_medicos'];
        $reportePsico->cirugias_previas = $data['cirugias_previas'];
        $reportePsico->antecedentes_familiares = $data['antecedentes_familiares'];
        $reportePsico->tratamiento_actual = $data['tratamiento_actual'];
        $reportePsico->aspectos_sociales = $data['aspectos_sociales'];
        $reportePsico->escalas_utilizadas = $data['escalas_utilizadas'];
        $reportePsico->sintomas_actuales = $data['sintomas_actuales'];
        $reportePsico->plan_tratamiento = $data['plan_tratamiento'];
        $reportePsico->seguimiento = $data['seguimiento'];
        $reportePsico->calif_salud = $data['calif_salud'];
        $reportePsico->realizas_ejercicio = $data['realizas_ejercicio']=== 'true' ? true : false;
        $reportePsico->ejercicio_frecuencia = $data['ejercicio_frecuencia'];
        $reportePsico->condicion_medica = $data['condicion_medica']=== 'true' ? true : false;
        $reportePsico->dieta_diaria = $data['dieta_diaria'];
        $reportePsico->frutas_verduras = $data['frutas_verduras']=== 'true' ? true : false;
        $reportePsico->frecuencia_comida = $data['frecuencia_comida'];
        $reportePsico->feliz = $data['feliz']=== 'true' ? true : false;
        $reportePsico->apoyo_emocional = $data['apoyo_emocional']=== 'true' ? true : false;
        $reportePsico->estres_nivel = $data['estres_nivel'];
        $reportePsico->frecuencia_reuniones = $data['frecuencia_reuniones'];
        $reportePsico->actividades_comunitarias = $data['actividades_comunitarias']=== 'true' ? true : false;
        $reportePsico->comunidad = $data['comunidad']=== 'true' ? true : false;
        $reportePsico->situa_financiera = $data['situa_financiera'];
        $reportePsico->seguro_economico = $data['seguro_economico']=== 'true' ? true : false;
        $reportePsico->ingresos_suficientes = $data['ingresos_suficientes']=== 'true' ? true : false;
        $reportePsico->trabajo_actual = $data['trabajo_actual']=== 'true' ? true : false;
        $reportePsico->reconocimiento = $data['reconocimiento']=== 'true' ? true : false;
        $reportePsico->equilibrio_trabajo = $data['equilibrio_trabajo']=== 'true' ? true : false;
        $reportePsico->alchol_consumo = $data['alchol_consumo'];
        $reportePsico->drogas_recreativas = $data['drogas_recreativas'];
        $reportePsico->tabaco_consumo = $data['tabaco_consumo']=== 'true' ? true : false;
        $reportePsico->tipo_exp = 5;
        $reportePsico->user_id = Auth::user()->id;
        $reportePsico->save();
        $reportePsico->psicologo = $data['psicologo'];
        $reportePsico->cedula_psicologo = $data['cedula_psicologo'];

        return response()->json("Guardado");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReportePsico  $reportePsico
     * @return \Illuminate\Http\Response
     */
    public function show(ReportePsico $reportePsico)
    {
        $reportePsicoResponse = ReportePsico::find($reportePsico->id);

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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReportePsico  $reportePsico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $data, ReportePsico $reportePsico)
    {
        $psicoFind = ReportePsico::find($data->id);
        $psicoFind->motivo_consulta = $data['motivo_consulta']?$data['motivo_consulta']:null;
        $psicoFind->antecedentes_medicos = $data['antecedentes_medicos']?$data['antecedentes_medicos']:null;
        $psicoFind->cirugias_previas = $data['cirugias_previas']?$data['cirugias_previas']:null;
        $psicoFind->antecedentes_familiares = $data['antecedentes_familiares']?$data['antecedentes_familiares']:null;
        $psicoFind->tratamiento_actual = $data['tratamiento_actual']?$data['tratamiento_actual']:null;
        $psicoFind->aspectos_sociales = $data['aspectos_sociales']?$data['aspectos_sociales']:null;
        $psicoFind->escalas_utilizadas = $data['escalas_utilizadas']?$data['escalas_utilizadas']:null;
        $psicoFind->sintomas_actuales = $data['sintomas_actuales']?$data['sintomas_actuales']:null;
        $psicoFind->plan_tratamiento = $data['plan_tratamiento']?$data['plan_tratamiento']:null;
        $psicoFind->seguimiento = $data['seguimiento']?$data['seguimiento']:null;
        $psicoFind->calif_salud = $data['calif_salud']?$data['calif_salud']:null;
        $psicoFind->realizas_ejercicio = $data['realizas_ejercicio']=== 'true' || $data['realizas_ejercicio']=== 1 ? true : false;
        $psicoFind->ejercicio_frecuencia = $data['ejercicio_frecuencia'];
        $psicoFind->condicion_medica = $data['condicion_medica']=== 'true' || $data['condicion_medica']=== 1? true : false;
        $psicoFind->dieta_diaria = $data['dieta_diaria'];
        $psicoFind->frutas_verduras = $data['frutas_verduras']=== 'true' || $data['frutas_verduras']=== 1 ? true : false;
        $psicoFind->frecuencia_comida = $data['frecuencia_comida'];
        $psicoFind->feliz = $data['feliz']=== 'true' || $data['feliz']=== 1 ? true : false;
        $psicoFind->apoyo_emocional = $data['apoyo_emocional']=== 'true' || $data['apoyo_emocional']=== 1 ? true : false;
        $psicoFind->estres_nivel = $data['estres_nivel'];
        $psicoFind->frecuencia_reuniones = $data['frecuencia_reuniones'];
        $psicoFind->actividades_comunitarias = $data['actividades_comunitarias']=== 'true' || $data['actividades_comunitarias']=== 1 ? true : false;
        $psicoFind->comunidad = $data['comunidad']=== 'true' || $data['comunidad']=== 1 ? true : false;
        $psicoFind->situa_financiera = $data['situa_financiera'];
        $psicoFind->seguro_economico = $data['seguro_economico']=== 'true' || $data['seguro_economico']=== 1 ? true : false;
        $psicoFind->ingresos_suficientes = $data['ingresos_suficientes']=== 'true' || $data['ingresos_suficientes']=== 1 ? true : false;
        $psicoFind->trabajo_actual = $data['trabajo_actual']=== 'true' || $data['trabajo_actual']=== 1 ? true : false;
        $psicoFind->reconocimiento = $data['reconocimiento']=== 'true' || $data['reconocimiento']=== 1 ? true : false;
        $psicoFind->equilibrio_trabajo = $data['equilibrio_trabajo']=== 'true' || $data['equilibrio_trabajo']=== 1 ? true : false;
        $psicoFind->alchol_consumo = $data['alchol_consumo'];
        $psicoFind->drogas_recreativas = $data['drogas_recreativas'];
        $psicoFind->tabaco_consumo = $data['tabaco_consumo']=== 'true' || $data['tabaco_consumo']=== 1 ? true : false;
        $psicoFind->tipo_exp = 5;
        $reportePsico->psicologo = $data['psicologo'];
        $reportePsico->cedula_psicologo = $data['cedula_psicologo'];


        $psicoFind->save();

        return response('Actualizado',204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReportePsico  $reportePsico
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportePsico $reportePsico)
    {
        if($reportePsico->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reportePsico->delete();
        return response()->json('',204);
    }
}
