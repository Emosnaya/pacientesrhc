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

        $reporteFinal->user_id = Auth::user()->id;
        $reporteFinal->paciente_id = $esfuerzo->paciente_id;
        
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReporteFinal $reporteFinal)
    {
        $reporteFinal->delete();
        return response()->json('',204);
    }
}
