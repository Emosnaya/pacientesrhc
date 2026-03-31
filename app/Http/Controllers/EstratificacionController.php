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
        $user = Auth::user();
        
        // Todos los usuarios pueden ver las estratificaciones de su clínica
        $estratificaciones = Estratificacion::whereHas('paciente', function($query) use ($user) {
            $query->forClinicaWorkspace((int) $user->clinica_efectiva_id);
        })->with('paciente')->get();
        
        return new EstratificacionCollection($estratificaciones);
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
            $nuevoPaciente->user_id = $user->id;
            $nuevoPaciente->clinica_id = $user->clinica_efectiva_id;
            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $nuevoPaciente->sucursal_id = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $nuevoPaciente->save();

        }else{
            $id = intval($request->input('id'));
            $nuevoPaciente = Paciente::find($id);
            
            // Verificar que el paciente pertenece a la misma clínica
            if (! $nuevoPaciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
                return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
            }
        }


        $estratificacion->primeravez_rhc = $data['rhc_1_fecha'];
        $estratificacion->pe_fecha = $data['pe'];
        $estratificacion->estrati_fecha = $data['estrati'];
        $estratificacion->c_isquemia = $data['cIsquemia'];
        $estratificacion->sesiones = intval($data['sesiones']);
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
        }else if($data['fcDiana'] === 'UISQ'){
            $fcDiana = $data['fcdianaNumber'];    
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
        
        // Asignar el user_id del dueño del paciente
        $estratificacion->user_id = $nuevoPaciente->user_id;
        $estratificacion->paciente_id = $nuevoPaciente->id;
        $estratificacion->clinica_id = $user->clinica_efectiva_id;
        $estratificacion->sucursal_id = $nuevoPaciente->sucursal_id;

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
        $user = Auth::user();
        
        // Verificar que la estratificación pertenece a la misma clínica
        $paciente = $estratificacion->paciente;
        if (!$paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No tienes acceso a este expediente de estratificación'], 403);
        }

        return response()->json($estratificacion->load('paciente'));
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
        $user = Auth::user();
        
        // Verificar que la estratificación pertenece a la misma clínica
        $paciente = $estratificacion->paciente;
        if (!$paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No tienes acceso a este expediente de estratificación'], 403);
        }

        // Usar el modelo que viene del Route Model Binding
        $estratificacion->primeravez_rhc = $request['rhc_1_fecha'];
        $estratificacion->pe_fecha = $request['pe'];
        $estratificacion->estrati_fecha = $request['estrati'];
        $estratificacion->c_isquemia = $request['cIsquemia'];
        $estratificacion->sesiones = intval($request['sesiones']);
        $estratificacion->im = ($request['im'] == 'true' || $request['im'] == 1) ? 1:0;
        $estratificacion->ima = ($request['ima'] == 'true'|| $request['ima'] == 1) ? 1:0;
        $estratificacion->imas = ($request['imas'] == 'true' || $request['imas'] == 1) ? 1:0;
        $estratificacion->imaa =($request['imaa'] == 'true'|| $request['imaa'] == 1) ? 1:0;
        $estratificacion->imal  =($request['imal'] == 'true'|| $request['imal'] == 1) ? 1:0;
        $estratificacion->imae =($request['imae'] == 'true'|| $request['imae'] == 1) ? 1:0;
        $estratificacion->iminf = ($request['imInf'] == 'true'|| $request['imInf'] == 1) ? 1:0;
        $estratificacion->impi =($request['impi'] == 'true'|| $request['impi'] == 1) ? 1:0;
        $estratificacion->impi_vd =  ($request['impiVd'] == 'true'|| $request['impiVd'] == 1) ? 1:0;
        $estratificacion->imlat = ($request['imLat'] == 'true'|| $request['imLat'] == 1) ? 1:0;
        $estratificacion->imsesst =  ($request['imSesst'] == 'true'|| $request['imSesst'] == 1) ? 1:0;
        $estratificacion->imComplicado =  ($request['imComplicado'] == 'true'|| $request['imComplicado'] == 1) ? 1:0;
        $estratificacion->valvular = $request['valvular'];
        $estratificacion->otro = ($request['otro'] == 'true'|| $request['otro'] == 1) ? 1:0;
        $estratificacion->mcd =  ($request['mcd'] == 'true'|| $request['mcd'] == 1) ? 1:0;
        $estratificacion->icc = ($request['icc'] == 'true'|| $request['icc'] == 1) ? 1:0;
        $estratificacion->reanimacion_cardio =  ($request['reanimacion'] == 'true'|| $request['reanimacion'] == 1) ? 1:0;
        $estratificacion->falla_entrenar = ($request['fallaEntrenar'] == 'true'|| $request['fallaEntrenar'] == 1) ? 1:0;
        $estratificacion->tabaquismo =  ($request['tabaquismo'] == 'true' || $request['tabaquismo'] == 1) ? 1:0;
        $estratificacion->dislipidemia =  ($request['dislipidemia'] == 'true' || $request['dislipidemia'] == 1) ? 1:0;
        $estratificacion->dm =  ($request['dm'] == 'true' || $request['dm'] == 1) ? 1:0;
        $estratificacion->has =  ($request['has'] == 'true' || $request['has'] == 1) ? 1:0;
        $estratificacion->obesidad = ($request['obesidad'] == 'true' || $request['obesidad'] == 1) ? 1:0;
        $estratificacion->estres =  ($request['estres'] == 'true' || $request['estres'] == 1) ? 1:0;
        $estratificacion->sedentarismo =  ($request['sedentarismo'] == 'true' || $request['sedentarismo'] == 1) ? 1:0;
        $estratificacion->riesgo_otro =  $request['otroFactor'] ?: '';
        $estratificacion->depresion = ($request['depresion'] == 'true' || $request['depresion'] == 1) ? 1:0;
        $estratificacion->ansiedad =  ($request['ansiedad'] == 'true' || $request['ansiedad'] == 1) ? 1:0;
        $estratificacion->sintomatologia = $request['sintomatologia'];
        $estratificacion->puntuacion_atp2000 = $request['puntuacionAtp'];
        $estratificacion->heart_score = $request['heartScore'];
        $estratificacion->col_total = $request['colTotal'];
        $estratificacion->ldl = $request['ldl'];
        $estratificacion->hdl = $request['hdl'];
        $estratificacion->tg = $request['tg'];
        $estratificacion->fevi = $request['fevi'];
        $estratificacion->pcr = $request['pcr'];
        $estratificacion->enf_coronaria = $request['enfCoronaria'];
        $estratificacion->isquemia = $request['isquemia'];
        $estratificacion->isquemia_irm = $request['isquemiaIrm'];
        $estratificacion->eco_estres = $request['eco'];
        $estratificacion->holter = $request['holter'];
        $estratificacion->pe_capacidad =  ($request['capacidadPe'] == 'true') ? 1:0;
        $estratificacion->fc_basal = $request['fcBasal'];
        $estratificacion->fc_maxima = $request['fcMax'];
        $estratificacion->fc_borg_12 = $request['fcBorg12'];
        $estratificacion->dp_borg_12 = $request['dpBorg12'];
        $estratificacion->mets_borg_12 = $request['metsBorg12'];
        $estratificacion->carga_max_bnda = $request['carga_maxima'];
        $estratificacion->tolerancia_max_esfuerzo = $request['tolerancia_esfuerzo'];
        $estratificacion->respuesta_presora = $request['respuestaPre'];
        $estratificacion->indice_ta_esf = $request['indiceTa'];
        $estratificacion->porc_fc_pre_alcanzado = $request['porcentajeFC'];
        $estratificacion->r_cronotr = $request['cronotr'];
        $estratificacion->porder_cardiaco = $request['poderCardiaco'];
        $estratificacion->recuperacion_tas = $request['recuperacionTas'];
        $estratificacion->recuperacion_fc = $request['recuperacionFc'];
        $estratificacion->duke = $request['duke'];
        $estratificacion->veteranos = $request['veteranos'];
        $estratificacion->ectopia_ventricular =($request['ectopiaVen'] == 'true') ? 1:0;
        $estratificacion->umbral_isquemico = ($request['umbralIs'] == 'true') ? 1:0;
        $estratificacion->supranivel_st = ($request['supradesnivel'] == 'true' || $request['supradesnivel'] == 1) ? 1:0;
        $estratificacion->infra_st_mayor2_135 = ($request['infra135'] == 'true') ? 1:0;
        $estratificacion->infra_st_mayor2_5mets = ($request['infra5'] == 'true') ? 1:0;
        $estratificacion->riesgo_global = $request['riesgoGlobal'];
        $estratificacion->grupo = $request['grupo'];
        $estratificacion->semanas = $request['semanas'];
        $estratificacion->borg = $request['borg'];
        $estratificacion->fc_diana_str = $request['fcDiana'];
        $estratificacion->dp_diana = $request['dpDiana'];

        $fcBasal = floatval($request['fcBasal']) ?: 0;
        $fcMaxima = floatval($request['fcMax']) ?: 0;
        $pacienteObj = $estratificacion->paciente;
        $edad = $pacienteObj ? $pacienteObj->edad : 0;

        $karvonen = (($fcMaxima - $fcBasal) * 0.7) + $fcBasal;
        $blackburn = ($fcMaxima * 0.8);
        $narita = (78.4 + ((0.76 * $fcBasal) - (0.27 * $edad)));

        if($request['fcDiana'] === 'K'){
            $fcDiana = $karvonen;
        }else if($request['fcDiana'] === 'BI'){
            $fcDiana = $blackburn;
        }else if($request['fcDiana'] === 'N'){
            $fcDiana = $narita;
        }else if($request['fcDiana'] === 'UISQ'){
            $fcDiana = floatval($request['fcdianaNumber']) ?: 0;    
        }else{
            $fcDiana = floatval($request['fcBorg12']) ?: 0;
        }

        $estratificacion->karvonen = $karvonen;
        $estratificacion->blackburn = $blackburn;
        $estratificacion->narita = $narita;
        $estratificacion->fc_diana = $fcDiana;
        $cargaMaxBnda = floatval($request['carga_maxima']) ?: 0;
        $estratificacion->carga_inicial = ($cargaMaxBnda * 0.6) * 10;
        $estratificacion->comentarios = $request['comentarios'];
        $estratificacion->tipo_exp = 2;
        $estratificacion->clinica_id = $user->clinica_efectiva_id;
        $estratificacion->save();


        return response()->json($estratificacion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Estratificacion  $estratificacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Estratificacion $estratificacion)
    {
        $user = Auth::user();
        
        // Solo admin o superadmin pueden eliminar
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes'], 403);
        }
        
        // Verificar que la estratificación pertenece a la misma clínica
        $paciente = $estratificacion->paciente;
        if (!$paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No tienes acceso a este expediente de estratificación'], 403);
        }
        
        $estratificacion->delete();
        return response()->json(['message' => 'Expediente de estratificación eliminado exitosamente'], 204);
    }
}
