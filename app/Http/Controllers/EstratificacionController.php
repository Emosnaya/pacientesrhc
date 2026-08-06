<?php

namespace App\Http\Controllers;

use App\Http\Resources\EstratificacionCollection;
use App\Models\Estratificacion;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Support\FormValue;

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
        $data = FormValue::sanitize((array) $request->input('datos', []), false);
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
        
        // Asignar el user_id del usuario que crea el expediente
        $estratificacion->user_id = $user->id;
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
        $data = FormValue::sanitize($request->all(), false);
        $estratificacion->primeravez_rhc = $data['rhc_1_fecha'] ?? null;
        $estratificacion->pe_fecha = ($data['pe'] ?? null);
        $estratificacion->estrati_fecha = ($data['estrati'] ?? null);
        $estratificacion->c_isquemia = ($data['cIsquemia'] ?? null);
        $estratificacion->sesiones = intval(($data['sesiones'] ?? null));
        $estratificacion->im = (($data['im'] ?? null) == 'true' || ($data['im'] ?? null) == 1) ? 1:0;
        $estratificacion->ima = (($data['ima'] ?? null) == 'true'|| ($data['ima'] ?? null) == 1) ? 1:0;
        $estratificacion->imas = (($data['imas'] ?? null) == 'true' || ($data['imas'] ?? null) == 1) ? 1:0;
        $estratificacion->imaa =(($data['imaa'] ?? null) == 'true'|| ($data['imaa'] ?? null) == 1) ? 1:0;
        $estratificacion->imal  =(($data['imal'] ?? null) == 'true'|| ($data['imal'] ?? null) == 1) ? 1:0;
        $estratificacion->imae =(($data['imae'] ?? null) == 'true'|| ($data['imae'] ?? null) == 1) ? 1:0;
        $estratificacion->iminf = (($data['imInf'] ?? null) == 'true'|| ($data['imInf'] ?? null) == 1) ? 1:0;
        $estratificacion->impi =(($data['impi'] ?? null) == 'true'|| ($data['impi'] ?? null) == 1) ? 1:0;
        $estratificacion->impi_vd =  (($data['impiVd'] ?? null) == 'true'|| ($data['impiVd'] ?? null) == 1) ? 1:0;
        $estratificacion->imlat = (($data['imLat'] ?? null) == 'true'|| ($data['imLat'] ?? null) == 1) ? 1:0;
        $estratificacion->imsesst =  (($data['imSesst'] ?? null) == 'true'|| ($data['imSesst'] ?? null) == 1) ? 1:0;
        $estratificacion->imComplicado =  (($data['imComplicado'] ?? null) == 'true'|| ($data['imComplicado'] ?? null) == 1) ? 1:0;
        $estratificacion->valvular = ($data['valvular'] ?? null);
        $estratificacion->otro = (($data['otro'] ?? null) == 'true'|| ($data['otro'] ?? null) == 1) ? 1:0;
        $estratificacion->mcd =  (($data['mcd'] ?? null) == 'true'|| ($data['mcd'] ?? null) == 1) ? 1:0;
        $estratificacion->icc = (($data['icc'] ?? null) == 'true'|| ($data['icc'] ?? null) == 1) ? 1:0;
        $estratificacion->reanimacion_cardio =  (($data['reanimacion'] ?? null) == 'true'|| ($data['reanimacion'] ?? null) == 1) ? 1:0;
        $estratificacion->falla_entrenar = (($data['fallaEntrenar'] ?? null) == 'true'|| ($data['fallaEntrenar'] ?? null) == 1) ? 1:0;
        $estratificacion->tabaquismo =  (($data['tabaquismo'] ?? null) == 'true' || ($data['tabaquismo'] ?? null) == 1) ? 1:0;
        $estratificacion->dislipidemia =  (($data['dislipidemia'] ?? null) == 'true' || ($data['dislipidemia'] ?? null) == 1) ? 1:0;
        $estratificacion->dm =  (($data['dm'] ?? null) == 'true' || ($data['dm'] ?? null) == 1) ? 1:0;
        $estratificacion->has =  (($data['has'] ?? null) == 'true' || ($data['has'] ?? null) == 1) ? 1:0;
        $estratificacion->obesidad = (($data['obesidad'] ?? null) == 'true' || ($data['obesidad'] ?? null) == 1) ? 1:0;
        $estratificacion->estres =  (($data['estres'] ?? null) == 'true' || ($data['estres'] ?? null) == 1) ? 1:0;
        $estratificacion->sedentarismo =  (($data['sedentarismo'] ?? null) == 'true' || ($data['sedentarismo'] ?? null) == 1) ? 1:0;
        $estratificacion->riesgo_otro =  ($data['otroFactor'] ?? null) ?: '';
        $estratificacion->depresion = (($data['depresion'] ?? null) == 'true' || ($data['depresion'] ?? null) == 1) ? 1:0;
        $estratificacion->ansiedad =  (($data['ansiedad'] ?? null) == 'true' || ($data['ansiedad'] ?? null) == 1) ? 1:0;
        $estratificacion->sintomatologia = ($data['sintomatologia'] ?? null);
        $estratificacion->puntuacion_atp2000 = ($data['puntuacionAtp'] ?? null);
        $estratificacion->heart_score = ($data['heartScore'] ?? null);
        $estratificacion->col_total = ($data['colTotal'] ?? null);
        $estratificacion->ldl = ($data['ldl'] ?? null);
        $estratificacion->hdl = ($data['hdl'] ?? null);
        $estratificacion->tg = ($data['tg'] ?? null);
        $estratificacion->fevi = ($data['fevi'] ?? null);
        $estratificacion->pcr = ($data['pcr'] ?? null);
        $estratificacion->enf_coronaria = ($data['enfCoronaria'] ?? null);
        $estratificacion->isquemia = ($data['isquemia'] ?? null);
        $estratificacion->isquemia_irm = ($data['isquemiaIrm'] ?? null);
        $estratificacion->eco_estres = ($data['eco'] ?? null);
        $estratificacion->holter = ($data['holter'] ?? null);
        $estratificacion->pe_capacidad =  (($data['capacidadPe'] ?? null) == 'true') ? 1:0;
        $estratificacion->fc_basal = ($data['fcBasal'] ?? null);
        $estratificacion->fc_maxima = ($data['fcMax'] ?? null);
        $estratificacion->fc_borg_12 = ($data['fcBorg12'] ?? null);
        $estratificacion->dp_borg_12 = ($data['dpBorg12'] ?? null);
        $estratificacion->mets_borg_12 = ($data['metsBorg12'] ?? null);
        $estratificacion->carga_max_bnda = ($data['carga_maxima'] ?? null);
        $estratificacion->tolerancia_max_esfuerzo = ($data['tolerancia_esfuerzo'] ?? null);
        $estratificacion->respuesta_presora = ($data['respuestaPre'] ?? null);
        $estratificacion->indice_ta_esf = ($data['indiceTa'] ?? null);
        $estratificacion->porc_fc_pre_alcanzado = ($data['porcentajeFC'] ?? null);
        $estratificacion->r_cronotr = ($data['cronotr'] ?? null);
        $estratificacion->porder_cardiaco = ($data['poderCardiaco'] ?? null);
        $estratificacion->recuperacion_tas = ($data['recuperacionTas'] ?? null);
        $estratificacion->recuperacion_fc = ($data['recuperacionFc'] ?? null);
        $estratificacion->duke = ($data['duke'] ?? null);
        $estratificacion->veteranos = ($data['veteranos'] ?? null);
        $estratificacion->ectopia_ventricular =(($data['ectopiaVen'] ?? null) == 'true') ? 1:0;
        $estratificacion->umbral_isquemico = (($data['umbralIs'] ?? null) == 'true') ? 1:0;
        $estratificacion->supranivel_st = (($data['supradesnivel'] ?? null) == 'true' || ($data['supradesnivel'] ?? null) == 1) ? 1:0;
        $estratificacion->infra_st_mayor2_135 = (($data['infra135'] ?? null) == 'true') ? 1:0;
        $estratificacion->infra_st_mayor2_5mets = (($data['infra5'] ?? null) == 'true') ? 1:0;
        $estratificacion->riesgo_global = ($data['riesgoGlobal'] ?? null);
        $estratificacion->grupo = ($data['grupo'] ?? null);
        $estratificacion->semanas = ($data['semanas'] ?? null);
        $estratificacion->borg = ($data['borg'] ?? null);
        $estratificacion->fc_diana_str = ($data['fcDiana'] ?? null);
        $estratificacion->dp_diana = ($data['dpDiana'] ?? null);

        $fcBasal = floatval(($data['fcBasal'] ?? null)) ?: 0;
        $fcMaxima = floatval(($data['fcMax'] ?? null)) ?: 0;
        $pacienteObj = $estratificacion->paciente;
        $edad = $pacienteObj ? $pacienteObj->edad : 0;

        $karvonen = (($fcMaxima - $fcBasal) * 0.7) + $fcBasal;
        $blackburn = ($fcMaxima * 0.8);
        $narita = (78.4 + ((0.76 * $fcBasal) - (0.27 * $edad)));

        if(($data['fcDiana'] ?? null) === 'K'){
            $fcDiana = $karvonen;
        }else if(($data['fcDiana'] ?? null) === 'BI'){
            $fcDiana = $blackburn;
        }else if(($data['fcDiana'] ?? null) === 'N'){
            $fcDiana = $narita;
        }else if(($data['fcDiana'] ?? null) === 'UISQ'){
            $fcDiana = floatval(($data['fcdianaNumber'] ?? null)) ?: 0;    
        }else{
            $fcDiana = floatval(($data['fcBorg12'] ?? null)) ?: 0;
        }

        $estratificacion->karvonen = $karvonen;
        $estratificacion->blackburn = $blackburn;
        $estratificacion->narita = $narita;
        $estratificacion->fc_diana = $fcDiana;
        $cargaMaxBnda = floatval(($data['carga_maxima'] ?? null)) ?: 0;
        $estratificacion->carga_inicial = ($cargaMaxBnda * 0.6) * 10;
        $estratificacion->comentarios = ($data['comentarios'] ?? null);
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
