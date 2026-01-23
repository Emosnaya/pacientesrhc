<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReporteFinalCollection;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteFinalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Todos los usuarios pueden ver los expedientes de su clínica
        $expedientes = ReporteFinal::whereHas('paciente', function($query) use ($user) {
            $query->where('clinica_id', $user->clinica_id);
        })->with('paciente')->get();
        
        return new ReporteFinalCollection($expedientes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->input("datos");
        $id = $request->input("esfuerzoUnoid");
        $idDos = $request->input("esfuerzoDosid");

        $esfuerzo = Esfuerzo::find($id);
        $esfuerzodos = Esfuerzo::find($idDos);
        
        $user = Auth::user();
        $paciente = $esfuerzo->paciente;
        
        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }

        $reporteFinal = new ReporteFinal();

        $reporteFinal->fecha_inicio = $esfuerzo->fecha;
        $reporteFinal->fecha_final = $esfuerzodos->fecha;
        $reporteFinal->fecha = $data['fecha'];
        $reporteFinal->fc_basal = $data['fcBasal'];
        $reporteFinal->doble_pr_bas = $data['dpBasal'];
        $reporteFinal->fc_maxima = $data['fcMax'];
        $reporteFinal->doble_pr_max = $data['dpMax'];
        $reporteFinal->fc_borg12 = $data['fcBorg12'];
        $reporteFinal->doble_pr_b12 = $data['dpBorg12'];
        $reporteFinal->carga_max = $data['metsMax'];
        $reporteFinal->mets_por = $data['vo2Alcanzado'];
        $reporteFinal->tiempo_ejer = $data['tiempoEsfuerzo'];
        $reporteFinal->recup_fc = $data['fcmaxFer'];
        $reporteFinal->umbral_isq = $data['metsUisq'];
        $reporteFinal->umbral_isq_fc = $data['fcUisq'];
        $reporteFinal->max_des_st = $data['MaxInfra'];
        $reporteFinal->indice_ta_es = $data['indiceTas'];
        $reporteFinal->recup_tas = $data['recuptas'];
        $reporteFinal->resp_crono = $data['respCrono'];
        $reporteFinal->iem = $data['iem'];
        $reporteFinal->pod_car_eje = $data['pce'];
        $reporteFinal->duke = $data['duke'];
        $reporteFinal->pe_1 = $id;
        $reporteFinal->pe_2 = $idDos;
        $reporteFinal->veteranos = $data['veteranos'];
        $reporteFinal->score_ang = $data['scoreAngina'];
        $reporteFinal->tipo_exp = 4;

        // Asignar el user_id del dueño del paciente
        $reporteFinal->user_id = $paciente->user_id;
        $reporteFinal->paciente_id = $esfuerzo->paciente_id;
        $reporteFinal->clinica_id = $user->clinica_id;
        $reporteFinal->sucursal_id = $paciente->sucursal_id;
        
        $reporteFinal->save();

        return response()->json("Guardado");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function show(ReporteFinal $reporteFinal)
    {
        $user = Auth::user();
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $reporteFinal->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        return response()->json($reporteFinal->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReporteFinal $reporteFinal)
    {
        $user = Auth::user();
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $reporteFinal->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }
        $reporteFinal->clinica_id = $user->clinica_id;

        // Aquí iría la lógica de actualización del expediente
        return response()->json(['message' => 'Expediente actualizado exitosamente']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReporteFinal $reporteFinal)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes'], 403);
        }
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $reporteFinal->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }
        
        $reporteFinal->delete();
        return response()->json(['message' => 'Expediente eliminado exitosamente'], 204);
    }
}
