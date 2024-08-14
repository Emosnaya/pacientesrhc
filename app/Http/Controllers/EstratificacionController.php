<?php

namespace App\Http\Controllers;

use App\Http\Resources\EstratificacionCollection;
use App\Models\Estratificacion;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstratificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new EstratificacionCollection(Estratificacion::where('user_id', Auth::user()->id)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $estratificacion = new Estratificacion();
        $data = $request->input('datos');
        $nuevoPaciente = null;


        if($request->input('paciente')){

            $paciente = $request->input('paciente');
            $nuevoPaciente = new Paciente();

            $peso = $paciente['peso'];
            $talla = $paciente['talla'];
            $imc = ($peso)/($talla*$talla);
            $fechaNacimiento = $paciente['fechaNacimiento'];
            $edad = Carbon::parse($fechaNacimiento)->age;
            $genero = $paciente['genero'];

            if ($genero === 'masculino') {
                $nuevoPaciente->genero = 1;
            } else {
                $nuevoPaciente->genero = 0;
            }

            $nuevoPaciente->registro =$paciente['registro'];
            $nuevoPaciente->nombre = $paciente['nombre'];
            $nuevoPaciente->apellidoPat = $paciente['apellidoPat'];
            $nuevoPaciente->apellidoMat = $paciente['apellidoMat'];
            $nuevoPaciente->telefono = $paciente['telefono'];
            $nuevoPaciente->domicilio = $paciente['domicilio'];
            $nuevoPaciente->profesion = $paciente['profesion'];
            $nuevoPaciente->cintura =$paciente['cintura'];
            $nuevoPaciente->estadoCivil = $paciente['estadoCivil'];
            $nuevoPaciente->diagnostico = $paciente['diagnostico'];
            $nuevoPaciente->medicamentos = $paciente['medicamentos'];
            $nuevoPaciente->talla = $talla;
            $nuevoPaciente->peso = $peso;
            $nuevoPaciente->fechaNacimiento = $fechaNacimiento;
            $nuevoPaciente->edad = $edad;
            $nuevoPaciente->imc = $imc;

            $nuevoPaciente->user_id = Auth::user()->id;


        }else{
            $id = intval($request->input('id'));
            $nuevoPaciente = Paciente::find($id);

        }


        $estratificacion->primeravez_rhc = $data['rhc_1_fecha'];
        $estratificacion->pe_fecha = $data['pe'];
        $estratificacion->estrati_fecha = $data['estrati'];
        $estratificacion->c_isquemia = $data['cIsquemia'];
        $estratificacion->sesiones =$data['sesiones'];
        $estratificacion->im = ($data['im'] == 'true') ? 1:0;
        $estratificacion->ima = ($data['ima'] == 'true') ? 1:0;
        $estratificacion->imas = ($data['imas'] == 'true') ? 1:0;
        $estratificacion->imaa =($data['imaa'] == 'true') ? 1:0;
        $estratificacion->imal  =($data['imal'] == 'true') ? 1:0;
        $estratificacion->imae =($data['imae'] == 'true') ? 1:0;
        $estratificacion->iminf = ($data['imInf'] == 'true') ? 1:0;
        $estratificacion->impi =($data['impi'] == 'true') ? 1:0;
        $estratificacion->impi_vd =  ($data['impiVd'] == 'true') ? 1:0;
        $estratificacion->imlat = ($data['imLat'] == 'true') ? 1:0;
        $estratificacion->imsesst =  ($data['imSesst'] == 'true') ? 1:0;
        $estratificacion->imComplicado =  ($data['imComplicado'] == 'true') ? 1:0;
        $estratificacion->valvular = $data['valvular'];
        $estratificacion->otro = ($data['otro'] == 'true') ? 1:0;
        $estratificacion->mcd =  ($data['mcd'] == 'true') ? 1:0;
        $estratificacion->icc = ($data['icc'] == 'true') ? 1:0;
        $estratificacion->reanimacion_cardio =  ($data['reanimacion'] == 'true') ? 1:0;
        $estratificacion->falla_entrenar = ($data['fallaEntrenar'] == 'true') ? 1:0;
        $estratificacion->tabaquismo =  ($data['tabaquismo'] == 'true') ? 1:0;
        $estratificacion->dislipidemia =  ($data['dislipidemia'] == 'true') ? 1:0;
        $estratificacion->dm =  ($data['dm'] == 'true') ? 1:0;
        $estratificacion->has =  ($data['has'] == 'true') ? 1:0;
        $estratificacion->obesidad = ($data['obesidad'] == 'true') ? 1:0;
        $estratificacion->estres =  ($data['estres'] == 'true') ? 1:0;
        $estratificacion->sedentarismo =  ($data['sedentarismo'] == 'true') ? 1:0;
        $estratificacion->riesgo_otro =  ($data['otroFactor'] == 'true') ? 1:0;
        $estratificacion->depresion = ($data['depresion'] == 'true') ? 1:0;
        $estratificacion->ansiedad =  ($data['ansiedad'] == 'true') ? 1:0;
        $estratificacion->sintomatologia = $data['sintomatologia'];
        $estratificacion->puntuacion_atp2000 = $data['puntuacionAtp'];
        $estratificacion->heart_score = $data['heartScore'];
        $estratificacion->col_total = $data['colTotal'];
        $estratificacion->ldl = $data['ldl'];
        $estratificacion->hdl = $data['hdl'];
        $estratificacion->tg = $data['tg'];
        $estratificacion->fevi = $data['fevi'];
        $estratificacion->pcr = $data['pcr'];
        $estratificacion->enf_coronaria = $data['enfCoronaria'];
        $estratificacion->isquemia = $data['isquemia'];
        $estratificacion->isquemia_irm = $data['isquemiaIrm'];
        $estratificacion->eco_estres = $data['eco'];
        $estratificacion->holter = $data['holter'];
        $estratificacion->pe_capacidad =  ($data['capacidadPe'] == 'true') ? 1:0;
        $estratificacion->fc_basal = $data['fcBasal'];
        $estratificacion->fc_maxima = $data['fcMax'];
        $estratificacion->fc_borg_12 = $data['fcBorg12'];
        $estratificacion->dp_borg_12 = $data['dpBorg12'];
        $estratificacion->mets_borg_12 = $data['metsBorg12'];
        $estratificacion->carga_max_bnda = $data['carga_maxima'];
        $estratificacion->tolerancia_max_esfuerzo = $data['tolerancia_esfuerzo'];
        $estratificacion->respuesta_presora = $data['respuestaPre'];
        $estratificacion->indice_ta_esf = $data['indiceTa'];
        $estratificacion->porc_fc_pre_alcanzado = $data['porcentajeFC'];
        $estratificacion->r_cronotr = $data['cronotr'];
        $estratificacion->porder_cardiaco = $data['poderCardiaco'];
        $estratificacion->recuperacion_tas = $data['recuperacionTas'];
        $estratificacion->recuperacion_fc = $data['recuperacionFc'];
        $estratificacion->duke = $data['duke'];
        $estratificacion->veteranos = $data['veteranos'];
        $estratificacion->ectopia_ventricular =($data['ectopiaVen'] == 'true') ? 1:0;
        $estratificacion->umbral_isquemico = $data['umbralIs'];
        $estratificacion->supranivel_st = ($data['supradesnivel'] == 'true') ? 1:0;
        $estratificacion->infra_st_mayor2_135 = $data['infra135'];
        $estratificacion->infra_st_mayor2_5mets = $data['infra5'];
        $estratificacion->riesgo_global = $data['riesgoGlobal'];
        $estratificacion->grupo = $data['grupo'];
        $estratificacion->semanas = $data['semanas'];
        $estratificacion->borg = $data['borg'];
        $estratificacion->fc_diana_str = $data['fcDiana'];
        $estratificacion->dp_diana = $data['dpDiana'];

        $karvonen = (($data['fcMax']-$data['fcBasal'])*0.7)+$data['fcBasal'];
        $blackburn = ($data['fcMax']*0.8);
        $narita = (78.4+((0.76*$data['fcBasal'])-(0.27*$nuevoPaciente->edad)));

        if($data['fcDiana'] === 'K'){
            $fcDiana = $karvonen;
        }else if($data['fcDiana'] === 'BI'){
            $fcDiana = $blackburn;
        }else if($data['fcDiana'] === 'N'){
            $fcDiana = $narita;
        }else{
            $fcDiana = $data['fcBorg12'];
        }

        $estratificacion->karvonen = $karvonen;
        $estratificacion->blackburn = $blackburn;
        $estratificacion->narita = $narita;
        $estratificacion->fc_diana = $fcDiana;
        $estratificacion->carga_inicial = ($data['carga_maxima']*0.6)*10;
        $estratificacion->comentarios = $data['comentarios'];
        $estratificacion->tipo_exp = 2;
        $estratificacion->user_id = Auth::user()->id;

        if($nuevoPaciente != null){
            $nuevoPaciente->save();
        }
       
        $estratificacion->paciente_id = $nuevoPaciente->id;

        $estratificacion->save();

        return response()->json("Guardado correctamente");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Estratificacion  $estratificacion
     * @return \Illuminate\Http\Response
     */
    public function show(Estratificacion $estratificacion)
    {
        $pexpedienteRespose = Estratificacion::find($estratificacion->id);

        if($pexpedienteRespose->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$pexpedienteRespose) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($pexpedienteRespose);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Estratificacion  $estratificacion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Estratificacion $estratificacion)
    {
        $expedienteFind = Estratificacion::find($request->id);

        $paciente = Paciente::find($request->paciente_id);



        $expedienteFind->primeravez_rhc = $request['primeravez_rhc'];
        $expedienteFind->pe_fecha = $request['pe_fecha'];
        $expedienteFind->estrati_fecha = $request['estrati_fecha'];
        $expedienteFind->c_isquemia = $request['c_isquemia'];
        $estratificacion->sesiones =$request['sesiones'];
        $expedienteFind->im = ($request['im'] == 'true' || $request['im'] == 1) ? 1:0;
        $expedienteFind->ima = ($request['ima'] == 'true'|| $request['ima'] == 1) ? 1:0;
        $expedienteFind->imas = ($request['imas'] == 'true' || $request['imas'] == 1) ? 1:0;
        $expedienteFind->imaa =($request['imaa'] == 'true'|| $request['imaa'] == 1) ? 1:0;
        $expedienteFind->imal  =($request['imal'] == 'true'|| $request['imal'] == 1) ? 1:0;
        $expedienteFind->imae =($request['imae'] == 'true'|| $request['imae'] == 1) ? 1:0;
        $expedienteFind->iminf = ($request['iminf'] == 'true'|| $request['iminf'] == 1) ? 1:0;
        $expedienteFind->impi =($request['impi'] == 'true'|| $request['impi'] == 1) ? 1:0;
        $expedienteFind->impi_vd =  ($request['impi_vd'] == 'true'|| $request['impi_vd'] == 1) ? 1:0;
        $expedienteFind->imlat = ($request['imlat'] == 'true'|| $request['imlat'] == 1) ? 1:0;
        $expedienteFind->imsesst =  ($request['imsesst'] == 'true'|| $request['imsesst'] == 1) ? 1:0;
        $expedienteFind->imComplicado =  ($request['imComplicado'] == 'true'|| $request['imComplicado'] == 1) ? 1:0;
        $expedienteFind->valvular = $request['valvular'];
        $expedienteFind->otro = ($request['otro'] == 'true'|| $request['otro'] == 1) ? 1:0;
        $expedienteFind->mcd =  ($request['mcd'] == 'true'|| $request['mcd'] == 1) ? 1:0;
        $expedienteFind->icc = ($request['icc'] == 'true'|| $request['icc'] == 1) ? 1:0;
        $expedienteFind->reanimacion_cardio =  ($request['reanimacion_cardio'] == 'true'|| $request['reanimacion_cardio'] == 1) ? 1:0;
        $expedienteFind->falla_entrenar = ($request['falla_entrenar'] == 'true'|| $request['falla_entrenar'] == 1) ? 1:0;
        $expedienteFind->tabaquismo =  ($request['tabaquismo'] == 'true' || $request['tabaquismo'] == 1) ? 1:0;
        $expedienteFind->dislipidemia =  ($request['dislipidemia'] == 'true' || $request['dislipidemia'] == 1) ? 1:0;
        $expedienteFind->dm =  ($request['dm'] == 'true' || $request['dm'] == 1) ? 1:0;
        $expedienteFind->has =  ($request['has'] == 'true' || $request['has'] == 1) ? 1:0;
        $expedienteFind->obesidad = ($request['obesidad'] == 'true' || $request['obesidad'] == 1) ? 1:0;
        $expedienteFind->estres =  ($request['estres'] == 'true' || $request['estres'] == 1) ? 1:0;
        $expedienteFind->sedentarismo =  ($request['sedentarismo'] == 'true' || $request['sedentarismo'] == 1) ? 1:0;
        $expedienteFind->riesgo_otro =  ($request['riesgo_otro'] == 'true' || $request['riesgo_otro'] == 1) ? 1:0;
        $expedienteFind->depresion = ($request['depresion'] == 'true' || $request['depresion'] == 1) ? 1:0;
        $expedienteFind->ansiedad =  ($request['ansiedad'] == 'true' || $request['ansiedad'] == 1) ? 1:0;
        $expedienteFind->sintomatologia = $request['sintomatologia'];
        $expedienteFind->puntuacion_atp2000 = $request['puntuacion_atp2000'];
        $expedienteFind->heart_score = $request['heart_score'];
        $expedienteFind->col_total = $request['col_total'];
        $expedienteFind->ldl = $request['ldl'];
        $expedienteFind->hdl = $request['hdl'];
        $expedienteFind->tg = $request['tg'];
        $expedienteFind->fevi = $request['fevi'];
        $expedienteFind->pcr = $request['pcr'];
        $expedienteFind->enf_coronaria = $request['enf_coronaria'];
        $expedienteFind->isquemia = $request['isquemia'];
        $estratificacion->isquemia_irm = $request['isquemia_irm'];
        $estratificacion->eco_estres = $request['eco_estres'];
        $expedienteFind->holter = $request['holter'];
        $expedienteFind->pe_capacidad =  ($request['pe_capacidad'] == 'true') ? 1:0;
        $expedienteFind->fc_basal = $request['fc_basal'];
        $expedienteFind->fc_maxima = $request['fc_maxima'];
        $expedienteFind->fc_borg_12 = $request['fc_borg_12'];
        $expedienteFind->dp_borg_12 = $request['dp_borg_12'];
        $expedienteFind->mets_borg_12 = $request['mets_borg_12'];
        $expedienteFind->carga_max_bnda = $request['carga_max_bnda'];
        $expedienteFind->tolerancia_max_esfuerzo = $request['tolerancia_max_esfuerzo'];
        $expedienteFind->respuesta_presora = $request['respuesta_presora'];
        $expedienteFind->indice_ta_esf = $request['indice_ta_esf'];
        $expedienteFind->porc_fc_pre_alcanzado = $request['porc_fc_pre_alcanzado'];
        $expedienteFind->r_cronotr = $request['r_cronotr'];
        $expedienteFind->porder_cardiaco = $request['porder_cardiaco'];
        $expedienteFind->recuperacion_tas = $request['recuperacion_tas'];
        $expedienteFind->recuperacion_fc = $request['recuperacion_fc'];
        $expedienteFind->duke = $request['duke'];
        $expedienteFind->veteranos = $request['veteranos'];
        $expedienteFind->ectopia_ventricular =($request['ectopia_ventricular'] == 'true') ? 1:0;
        $expedienteFind->umbral_isquemico = $request['umbral_isquemico'];
        $expedienteFind->supranivel_st = ($request['supranivel_st'] == 'true' || $request['supranivel_st'] == 1) ? 1:0;
        $expedienteFind->infra_st_mayor2_135 = $request['infra_st_mayor2_135'];
        $expedienteFind->infra_st_mayor2_5mets = $request['infra_st_mayor2_5mets'];
        $expedienteFind->riesgo_global = $request['riesgo_global'];
        $expedienteFind->grupo = $request['grupo'];
        $expedienteFind->semanas = $request['semanas'];
        $expedienteFind->borg = $request['borg'];
        $expedienteFind->fc_diana_str = $request['fc_diana_str'];
        $expedienteFind->dp_diana = $request['dp_diana'];

        $karvonen = (($request['fc_maxima']-$request['fc_basal'])*0.7)+$request['fc_basal'];
        $blackburn = ($request['fc_maxima']*0.8);
        $narita = (78.4+((0.76*$request['fc_basal'])-(0.27*$paciente->edad)));

        if($request['fc_diana_str'] === 'K'){
            $fcDiana = $karvonen;
        }else if($request['fc_diana_str'] === 'BI'){
            $fcDiana = $blackburn;
        }else if($request['fc_diana_str'] === 'N'){
            $fcDiana = $narita;
        }else{
            $fcDiana = $request['fc_borg_12'];
        }

        $expedienteFind->karvonen = $karvonen;
        $expedienteFind->blackburn = $blackburn;
        $expedienteFind->narita = $narita;
        $expedienteFind->fc_diana = $fcDiana;
        $expedienteFind->carga_inicial = ($request['carga_max_bnda']*0.6)*10;
        $expedienteFind->comentarios = $request['comentarios'];
        $expedienteFind->tipo_exp = 2;

        $expedienteFind->save();


        return response()->json($expedienteFind);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Estratificacion  $estratificacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Estratificacion $estratificacion)
    {
        $estratificacion->delete();
        return response("",204);
    }
}
