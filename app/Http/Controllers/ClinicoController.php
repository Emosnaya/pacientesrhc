<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicoCollection;
use App\Models\Clinico;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ClinicoCollection(Clinico::where('user_id', Auth::user()->id)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $clinico = new Clinico();
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
        $clinico->fecha = $data['fecha'];
        $clinico->fecha_1vez = $data['fecha_1vez'];
        $clinico->hora = $data['hora'];
        $clinico->imComplicado = $data['imComplicado']==="true"?1:0;
        $clinico->imAnterior = $data['imAnterior'];
        $clinico->imSeptal = $data['imSeptal'];
        $clinico->imApical = $data['imApical'];
        $clinico->imLateral = $data['imLateral'];
        $clinico->imInferior = $data['imInferior'];
        $clinico->imdelVD = $data['imdelVd'];
        $clinico->anginaInestabale = $data['anginaInestable'];
        $clinico->anginaEstabale = $data['anginaEstable'];
        $clinico->choque_card = $data['choqueCard'];
        $clinico->m_subita = $data['mSubita'];
        $clinico->clase_f_ccs = $data['claseCcs']==="true"?1:0;
        $clinico->falla_cardiaca = $data['fallaCardiaca']==="true"?1:0;
        $clinico->sobreviviente_cpr = $data['sobrevivienteCpr']==="true"?1:0;
        $clinico->incapacidad_entrenar = $data['incapacidadEntrenar']==="true"?1:0;
        $clinico->cf_nyha = $data['cfNyha'];
        $clinico->crvc = $data['crvc'];
        $clinico->crvc_hemoductos = $data['crvcHemo'];
        $clinico->insuficiencia_art_per = $data['insuArtPer']==="true"?1:0;
        $clinico->v_mitral = $data['vMitral']==="true"?1:0;
        $clinico->v_aortica = $data['vAortica']==="true"?1:0;
        $clinico->v_tricuspide = $data['vTricuspide']==="true"?1:0;
        $clinico->v_pulmonar = $data['vPulmonar']==="true"?1:0;
        $clinico->congenitos = $data['congenitos']==="true"?1:0;
        $clinico->estratificacion = $data['estratificacion'];
        $clinico->inicio_fase_2 = $data['inicioFase2'];
        $clinico->fin_fase_2 = $data['finFase2'];
        $clinico->tabaquismo = $data['tabaquismo']==="true"?1:0;
        $clinico->cig_dia = $data['cigxDia'];
        $clinico->cig_years = $data['cigxYear'];
        $clinico->cig_abandono = $data['abadonoCigarro']==="true"?1:0;
        $clinico->cig_años_abandono = $data['abandonoYear'];
        $clinico->hipertension_años = $data['hipertensionYears'];
        $clinico->dm_years = $data['dmYears'];
        $clinico->actividad_fis = $data['actividadFis']==="true"?1:0;
        $clinico->tipo_actividad = $data['tipoActividad'];
        $clinico->actividad_hrs_smn = $data['actividadHsm'];
        $clinico->actividad_years = $data['actividadYears'];
        $clinico->actividad_abadono_years = $data['actividadYearsAbandono'];
        $clinico->estres_years = $data['estresYears'];
        $clinico->ansiedad_years = $data['ansiedadYears'];
        $clinico->depresion_years = $data['depresionYears'];
        $clinico->hipercolesterolemia_y = $data['hipercolesterolemia'];
        $clinico->hipertrigliceridemia_y = $data['hipertrigliceridemia'];
        $clinico->diabetes_y = $data['diabetesYears'];
        $clinico->tiempo_evolucion = $data['tiempoEv'];
        $clinico->tratamiento = $data['tratamiento'];
        $clinico->fecha_tra = $data['fechaTra'];
        $clinico->betabloqueador = $data['betabloqueador'];
        $clinico->nitratos = $data['nitratos'];
        $clinico->calcioantagonista = $data['calcioanta'];
        $clinico->aspirina = $data['aspirina'];
        $clinico->anticoagulacion = $data['anticoagulacion'];
        $clinico->iecas = $data['iecas'];
        $clinico->atii = $data['atii'];
        $clinico->diureticos = $data['diureticos'];
        $clinico->estatinas = $data['estatinas'];
        $clinico->fibratos = $data['fibratos'];
        $clinico->digoxina = $data['digoxina'];
        $clinico->antiarritmicos = $data['antiarritmicos'];
        $clinico->otros = $data['otro'];
        $clinico->bh_fecha = $data['bh'];
        $clinico->hb = $data['hb'];
        $clinico->leucos = $data['leucos'];
        $clinico->plaquetas = $data['plaquetas'];
        $clinico->qs = $data['qs'];
        $clinico->glucosa = $data['glucosa'];
        $clinico->creatinina = $data['creatinina'];
        $clinico->ac_unico = $data['acUrico'];
        $clinico->colesterol = $data['colesterol'];
        $clinico->ldl = $data['ldl'];
        $clinico->hdl = $data['hdl'];
        $clinico->trigliceridos = $data['trigliceridos'];
        $clinico->tp = $data['tp'];
        $clinico->inr = $data['inr'];
        $clinico->tpt = $data['tpt'];
        $clinico->pcras = $data['pcras'];
        $clinico->otro_lab = $data['otroLab'];
        $clinico->ecg_fecha = $data['ecgFecha'];
        $clinico->ritmo = $data['ritmo'];
        $clinico->r_r_mm = $data['rrmm'];
        $clinico->fc_ecog = 60/($data['rrmm']*0.04);
        $clinico->aP = $data['aP'];
        $clinico->aQRS = $data['aQRS'];
        $clinico->aT = $data['aT'];
        $clinico->duracion_qrs = $data['duracionQrs'];
        $clinico->duracion_p = $data['duracionP'];
        $clinico->qtm = $data['qtm'];
        $clinico->qtc = $data['qtm']/sqrt(60/$clinico->fc_ecog);
        $clinico->pr = $data['pr'];
        $clinico->bav = $data['bav'];
        $clinico->brihh = $data['brihh']==="true"?1:0;
        $clinico->brdhh = $data['brdhh']==="true"?1:0;
        $clinico->q_as = $data['qAs']==="true"?1:0;
        $clinico->q_inf = $data['qInf']==="true"?1:0;
        $clinico->q_lat = $data['qLat']==="true"?1:0;
        $clinico->otros_ecg = $data['otrosEcg'];
        $clinico->eco_fecha = $data['ecoFecha'];
        $clinico->fe_por = $data['fePor'];
        $clinico->dd_por = $data['ddPor'];
        $clinico->ds_por = $data['dsPor'];
        $clinico->trivi_por = $data['triviPor'];
        $clinico->rel_e_a = $data['relEA'];
        $clinico->otros_eco = $data['otrosEco'];
        $clinico->mn_fecha = $data['mnFecha'];
        $clinico->fe_por_mn = $data['feporMn'];
        $clinico->ant_im = $data['antIm']==="true"?1:0;
        $clinico->ant_isq = $data['antIsq']==="true"?1:0;
        $clinico->ant_rr = $data['antRr']==="true"?1:0;
        $clinico->sept_im = $data['septIM']==="true"?1:0;
        $clinico->sept_isq = $data['septIsq']==="true"?1:0;
        $clinico->sept_rr = $data['septRr']==="true"?1:0;
        $clinico->lat_im = $data['latIm']==="true"?1:0;
        $clinico->lat_isq = $data['latIsq']==="true"?1:0;
        $clinico->lat_rr = $data['latRr']==="true"?1:0;
        $clinico->inf_im = $data['infIM']==="true"?1:0;
        $clinico->inf_isq = $data['infIsq']==="true"?1:0;
        $clinico->inf_rr = $data['infRr']==="true"?1:0;
        $clinico->vrie = $data['vrie']==="true"?1:0;
        $clinico->vrie_fcha = $data['vrieFecha'];
        $clinico->fevi_basal = $data['feviBasal'];
        $clinico->fevi_10_dobuta = $data['fevi10Dobuta'];
        $clinico->reserva_inot_absolut = $data['reservaInotA'];
        $clinico->reserva_inot_relat = $data['reservaInotR'];
        $clinico->vrie_otros = $data['vrieOtro'];
        $clinico->vrie_riesgo = $data['vrieRiesgo'];
        $clinico->holter = $data['holter']==="true"?1:0;
        $clinico->holter_fecha = $data['holterFecha'];
        $clinico->holter_dignostico = $data['holterDiagnostico'];
        $clinico->holter_riesgo = $data['holterRiesgo'];
        $clinico->cateterismo = $data['cateterismo']==="true"?1:0;
        $clinico->catet_fecha = $data['catetFecha'];
        $clinico->catet_fe = $data['catetFe'];
        $clinico->catet_d2vi = $data['catetD2vi'];
        $clinico->catet_tco = $data['catetTco'];
        $clinico->catet_da_prox = $data['catetDa'];
        $clinico->catet_da_med = $data['catetDaMed'];
        $clinico->catet_da_dist = $data['catetDaDist'];
        $clinico->catet_1a_d = $data['catet1aD'];
        $clinico->catet_2a_d = $data['catet2aD'];
        $clinico->catet_cx_prox = $data['catetCxP'];
        $clinico->catet_cx_dist = $data['catetCxD'];
        $clinico->catet_om = $data['catetOm'];
        $clinico->catet_pl = $data['catetPl'];
        $clinico->catet_cd_aprox = $data['catetCdprox'];
        $clinico->catet_cd_med = $data['catetCdMed'];
        $clinico->catet_cd_dist = $data['catetCdDist'];
        $clinico->catet_r_vent_izq = $data['catetRVIzq'];
        $clinico->catet_dp = $data['catetDp'];
        $clinico->catet_otros = $data['catetOtro'];
        $clinico->catet_movilidad = $data['catetMovilidad'];
        $clinico->catet_riesgo = $data['catetRiesgo'];
        $clinico->termino = $data['termino']==="true"?1:0;
        $clinico->semanas = $data['semanas'];
        $clinico->aprendio_borg = $data['aprendioBorg']==="true"?1:0;
        $clinico->muerte = $data['muerte']==="true"?1:0;
        $clinico->inestabilidad_cardio = $data['inestabilidadCardio']==="true"?1:0;
        $clinico->hospitalizacion = $data['hospitalizacion']==="true"?1:0;
        $clinico->susp_motu_propio = $data['suspMotuPropio']==="true"?1:0;
        $clinico->lesion_osteo = $data['lesionOsteo']==="true"?1:0;
        $clinico->res_otros = $data['resOtros']==="true"?1:0;
        $clinico->era_vez_fecha = $data['Vez1aFecha'];
        $clinico->sintomas = $data['sintomas'];
        $clinico->comer_vestirse = $data['comerVestirse']==="true"?1:0;
        $clinico->caminar_casa = $data['caminarCasa']==="true"?1:0;
        $clinico->caminar_2_cuadras = $data['caminar2Cuadras']==="true"?1:0;
        $clinico->subir_piso = $data['subirPiso']==="true"?1:0;
        $clinico->correr_corta = $data['correrCorta']==="true"?1:0;
        $clinico->lavar_trastes = $data['lavarTrastes']==="true"?1:0;
        $clinico->aspirar_casa = $data['aspirarCasa']==="true"?1:0;
        $clinico->trapear = $data['trapear']==="true"?1:0;
        $clinico->jardineria = $data['jardineria']==="true"?1:0;
        $clinico->relaciones = $data['relaciones']==="true"?1:0;
        $clinico->jugar = $data['jugar']==="true"?1:0;
        $clinico->deportes_extenuantes = $data['deportesExtenuantes']==="true"?1:0;
        $clinico->TA = $data['TA'];
        $clinico->fc = $data['FC'];
        $clinico->exploracion_fisica = $data['exploracionFisica'];
        $clinico->estudios = $data['estudios'];
        $clinico->diagnostico_general = $data['diagnosticoGeneral'];
        $clinico->plan = $data['plan'];
        $clinico->tipo_exp = 3;
        $clinico->dasi =(0.43*((2.75*$clinico->comer_vestirse)+(1.75*$clinico->caminar_casa)+(2.75*$clinico->caminar_2_cuadras)+(5.5*$clinico->subir_piso)+(8*$clinico->correr_corta)+(2.7*$clinico->lavar_trastes)+(3.5*$clinico->aspirar_casa)+(8*$clinico->trapear)+(4.5*$clinico->jardineria)+(5.25*$clinico->relaciones)+(6*$clinico->jugar)+(7.5*$clinico->deportes_extenuantes))+9.6)/3.5 ;
        $clinico->user_id = Auth::user()->id;

        if($nuevoPaciente != null){
            $nuevoPaciente->save();
        }
       
        $clinico->paciente_id = $nuevoPaciente->id;
        $clinico->save();


        return response()->json("Guardado correctamente");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Clinico  $clinico
     * @return \Illuminate\Http\Response
     */
    public function show(Clinico $clinico)
    {
        $clinicoRespose = Clinico::find($clinico->id);

        if($clinicoRespose->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$clinicoRespose) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($clinicoRespose);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Clinico  $clinico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $data, Clinico $clinico)
    {
        $clinicoFind = Clinico::find($data->id);
        $nuevoPaciente = Paciente::find($data->paciente_id);

        $clinico->fecha = $data['fecha'];
        $clinico->fecha_1vez = $data['fecha_1vez'];
        $clinico->hora = $data['hora'];
        $clinico->imComplicado = $data['imComplicado']==="true"||$data['imComplicado']===1?1:0;
        $clinico->imAnterior = $data['imAnterior'];
        $clinico->imSeptal = $data['imSeptal'];
        $clinico->imApical = $data['imApical'];
        $clinico->imLateral = $data['imLateral'];
        $clinico->imInferior = $data['imInferior'];
        $clinico->imdelVD = $data['imdelVd'];
        $clinico->anginaInestabale = $data['anginaInestable'];
        $clinico->anginaEstabale = $data['anginaEstable'];
        $clinico->choque_card = $data['choque_card'];
        $clinico->m_subita = $data['m_subita'];
        $clinico->clase_f_ccs = $data['clase_f_ccs']==="true"||$data['clase_f_ccs']===1?1:0;
        $clinico->falla_cardiaca = $data['falla_cardiaca']==="true"||$data['falla_cardiaca']===1?1:0;
        $clinico->sobreviviente_cpr = $data['sobreviviente_cpr']==="true"||$data['sobreviviente_cpr']===1?1:0;
        $clinico->incapacidad_entrenar = $data['incapacidad_entrenar']==="true"|| $data['incapacidad_entrenar']===1?1:0;
        $clinico->cf_nyha = $data['cf_nyha'];
        $clinico->crvc = $data['crvc'];
        $clinico->crvc_hemoductos = $data['crvc_hemoductos'];
        $clinico->insuficiencia_art_per = $data['insuficiencia_art_per']==="true"||$data['insuficiencia_art_per']===1?1:0;
        $clinico->v_mitral = $data['v_mitral']==="true"||$data['v_mitral']===1?1:0;
        $clinico->v_aortica = $data['v_aortica']==="true"||$data['v_aortica']===1?1:0;
        $clinico->v_tricuspide = $data['v_tricuspide']==="true"||$data['v_tricuspide']===1?1:0;
        $clinico->v_pulmonar = $data['v_pulmonar']==="true"||$data['v_pulmonar']===1?1:0;
        $clinico->congenitos = $data['congenitos']==="true"||$data['congenitos']===1?1:0;
        $clinico->estratificacion = $data['estratificacion'];
        $clinico->inicio_fase_2 = $data['inicio_fase_2'];
        $clinico->fin_fase_2 = $data['fin_fase_2'];
        $clinico->tabaquismo = $data['tabaquismo']==="true"||$data['tabaquismo']===1?1:0;
        $clinico->cig_dia = $data['cig_dia'];
        $clinico->cig_years = $data['cig_years'];
        $clinico->cig_abandono = $data['cig_abandono']==="true"||$data['cig_abandono']===1?1:0;
        $clinico->cig_años_abandono = $data['cig_años_abandono'];
        $clinico->hipertension_años = $data['hipertension_años'];
        $clinico->dm_years = $data['dm_years'];
        $clinico->actividad_fis = $data['actividad_fis']==="true"||$data['actividad_fis']===1?1:0;
        $clinico->tipo_actividad = $data['tipo_actividad'];
        $clinico->actividad_hrs_smn = $data['actividad_hrs_smn'];
        $clinico->actividad_years = $data['actividad_years'];
        $clinico->actividad_abadono_years = $data['actividad_abadono_years'];
        $clinico->estres_years = $data['estres_years'];
        $clinico->ansiedad_years = $data['ansiedad_years'];
        $clinico->depresion_years = $data['depresion_years'];
        $clinico->hipercolesterolemia_y = $data['hipercolesterolemia_y'];
        $clinico->hipertrigliceridemia_y = $data['hipertrigliceridemia_y'];
        $clinico->diabetes_y = $data['diabetes_y'];
        $clinico->tiempo_evolucion = $data['tiempo_evolucion'];
        $clinico->tratamiento = $data['tratamiento'];
        $clinico->fecha_tra = $data['fecha_tra'];
        $clinico->betabloqueador = $data['betabloqueador'];
        $clinico->nitratos = $data['nitratos'];
        $clinico->calcioantagonista = $data['calcioantagonista'];
        $clinico->aspirina = $data['aspirina'];
        $clinico->anticoagulacion = $data['anticoagulacion'];
        $clinico->iecas = $data['iecas'];
        $clinico->atii = $data['atii'];
        $clinico->diureticos = $data['diureticos'];
        $clinico->estatinas = $data['estatinas'];
        $clinico->fibratos = $data['fibratos'];
        $clinico->digoxina = $data['digoxina'];
        $clinico->antiarritmicos = $data['antiarritmicos'];
        $clinico->otros = $data['otros'];
        $clinico->bh_fecha = $data['bh_fecha'];
        $clinico->hb = $data['hb'];
        $clinico->leucos = $data['leucos'];
        $clinico->plaquetas = $data['plaquetas'];
        $clinico->qs = $data['qs'];
        $clinico->glucosa = $data['glucosa'];
        $clinico->creatinina = $data['creatinina'];
        $clinico->ac_unico = $data['ac_unico'];
        $clinico->colesterol = $data['colesterol'];
        $clinico->ldl = $data['ldl'];
        $clinico->hdl = $data['hdl'];
        $clinico->trigliceridos = $data['trigliceridos'];
        $clinico->tp = $data['tp'];
        $clinico->inr = $data['inr'];
        $clinico->tpt = $data['tpt'];
        $clinico->pcras = $data['pcras'];
        $clinico->otro_lab = $data['otro_lab'];
        $clinico->ecg_fecha = $data['ecg_fecha'];
        $clinico->ritmo = $data['ritmo'];
        $clinico->r_r_mm = $data['r_r_mm'];
        $clinico->fc_ecog = 60/($data['r_r_mm']*0.04);
        $clinico->aP = $data['aP'];
        $clinico->aQRS = $data['aQRS'];
        $clinico->aT = $data['aT'];
        $clinico->duracion_qrs = $data['duracion_qrs'];
        $clinico->duracion_p = $data['duracion_p'];
        $clinico->qtm = $data['qtm'];
        $clinico->qtc = $data['qtm']/sqrt(60/$clinico->fc_ecog);
        $clinico->pr = $data['pr'];
        $clinico->bav = $data['bav'];
        $clinico->brihh = $data['brihh']==="true"||$data['brihh']===1?1:0;
        $clinico->brdhh = $data['brdhh']==="true"||$data['brdhh']===1?1:0;
        $clinico->q_as = $data['q_as']==="true"||$data['q_as']===1?1:0;
        $clinico->q_inf = $data['q_inf']==="true"||$data['q_inf']===1?1:0;
        $clinico->q_lat = $data['q_lat']==="true"||$data['q_lat']===1?1:0;
        $clinico->otros_ecg = $data['otros_ecg'];
        $clinico->eco_fecha = $data['eco_fecha'];
        $clinico->fe_por = $data['fe_por'];
        $clinico->dd_por = $data['dd_por'];
        $clinico->ds_por = $data['ds_por'];
        $clinico->trivi_por = $data['trivi_por'];
        $clinico->rel_e_a = $data['rel_e_a'];
        $clinico->otros_eco = $data['otros_eco'];
        $clinico->mn_fecha = $data['mn_fecha'];
        $clinico->fe_por_mn = $data['fe_por_mn'];
        $clinico->ant_im = $data['ant_im']==="true"||$data['ant_im']===1?1:0;
        $clinico->ant_isq = $data['ant_isq']==="true"||$data['ant_isq']===1?1:0;
        $clinico->ant_rr = $data['ant_rr']==="true"||$data['ant_rr']===1?1:0;
        $clinico->sept_im = $data['sept_im']==="true"||$data['sept_im']===1?1:0;
        $clinico->sept_isq = $data['sept_isq']==="true"||$data['sept_isq']===1?1:0;
        $clinico->sept_rr = $data['sept_rr']==="true"||$data['sept_rr']===1?1:0;
        $clinico->lat_im = $data['lat_im']==="true"||$data['lat_im']===1?1:0;
        $clinico->lat_isq = $data['lat_isq']==="true"||$data['lat_isq']===1?1:0;
        $clinico->lat_rr = $data['lat_rr']==="true"||$data['lat_rr']===1?1:0;
        $clinico->inf_im = $data['inf_im']==="true"||$data['inf_im']===1?1:0;
        $clinico->inf_isq = $data['inf_isq']==="true"||$data['inf_isq']===1?1:0;
        $clinico->inf_rr = $data['inf_rr']==="true"||$data['inf_rr']===1?1:0;
        $clinico->vrie = $data['vrie']==="true"||$data['vrie']===1?1:0;
        $clinico->vrie_fcha = $data['vrie_fcha'];
        $clinico->fevi_basal = $data['fevi_basal'];
        $clinico->fevi_10_dobuta = $data['fevi_10_dobuta'];
        $clinico->reserva_inot_absolut = $data['reserva_inot_absolut'];
        $clinico->reserva_inot_relat = $data['reserva_inot_relat'];
        $clinico->vrie_otros = $data['vrie_otros'];
        $clinico->vrie_riesgo = $data['vrie_riesgo'];
        $clinico->holter = $data['holter']==="true"||$data['holter']===1?1:0;
        $clinico->holter_fecha = $data['holter_fecha'];
        $clinico->holter_dignostico = $data['holter_dignostico'];
        $clinico->holter_riesgo = $data['holter_riesgo'];
        $clinico->cateterismo = $data['cateterismo']==="true"||$data['cateterismo']===1?1:0;
        $clinico->catet_fecha = $data['catet_fecha'];
        $clinico->catet_fe = $data['catet_fe'];
        $clinico->catet_d2vi = $data['catet_d2vi'];
        $clinico->catet_tco = $data['catet_tco'];
        $clinico->catet_da_prox = $data['catet_da_prox'];
        $clinico->catet_da_med = $data['catet_da_med'];
        $clinico->catet_da_dist = $data['catet_da_dist'];
        $clinico->catet_1a_d = $data['catet_1a_d'];
        $clinico->catet_2a_d = $data['catet_2a_d'];
        $clinico->catet_cx_prox = $data['catet_cx_prox'];
        $clinico->catet_cx_dist = $data['catet_cx_dist'];
        $clinico->catet_om = $data['catet_om'];
        $clinico->catet_pl = $data['catet_pl'];
        $clinico->catet_cd_aprox = $data['catet_cd_aprox'];
        $clinico->catet_cd_med = $data['catet_cd_med'];
        $clinico->catet_cd_dist = $data['catet_cd_dist'];
        $clinico->catet_r_vent_izq = $data['catet_r_vent_izq'];
        $clinico->catet_dp = $data['catet_dp'];
        $clinico->catet_otros = $data['catet_otros'];
        $clinico->catet_movilidad = $data['catet_movilidad'];
        $clinico->catet_riesgo = $data['catet_riesgo'];
        $clinico->termino = $data['termino']==="true"||$data['termino']===1?1:0;
        $clinico->semanas = $data['semanas'];
        $clinico->aprendio_borg = $data['aprendio_borg']==="true"||$data['aprendio_borg']===1?1:0;
        $clinico->muerte = $data['muerte']==="true"?1:0;
        $clinico->inestabilidad_cardio = $data['inestabilidad_cardio']==="true"||$data['inestabilidad_cardio']===1?1:0;
        $clinico->hospitalizacion = $data['hospitalizacion']==="true"||$data['hospitalizacion']===1?1:0;
        $clinico->susp_motu_propio = $data['susp_motu_propio']==="true"||$data['susp_motu_propio']===1?1:0;
        $clinico->lesion_osteo = $data['lesion_osteo']==="true"||$data['lesion_osteo']===1?1:0;
        $clinico->res_otros = $data['res_otros']==="true"||$data['res_otros']===1?1:0;
        $clinico->era_vez_fecha = $data['era_vez_fecha'];
        $clinico->sintomas = $data['sintomas'];
        $clinico->comer_vestirse = $data['comer_vestirse']==="true"||$data['comer_vestirse']===1?1:0;
        $clinico->caminar_casa = $data['caminar_casa']==="true"||$data['caminar_casa']===1?1:0;
        $clinico->caminar_2_cuadras = $data['caminar_2_cuadras']==="true"||$data['caminar_2_cuadras']===1?1:0;
        $clinico->subir_piso = $data['subir_piso']==="true"||$data['subir_piso']===1?1:0;
        $clinico->correr_corta = $data['correr_corta']==="true"||$data['correr_corta']===1?1:0;
        $clinico->lavar_trastes = $data['lavar_trastes']==="true"||$data['lavar_trastes']===1?1:0;
        $clinico->aspirar_casa = $data['aspirar_casa']==="true"||$data['aspirar_casa']===1?1:0;
        $clinico->trapear = $data['trapear']==="true"||$data['trapear']===1?1:0;
        $clinico->jardineria = $data['jardineria']==="true"||$data['jardineria']===1?1:0;
        $clinico->relaciones = $data['relaciones']==="true"||$data['relaciones']===1?1:0;
        $clinico->jugar = $data['jugar']==="true"||$data['jugar']===1?1:0;
        $clinico->deportes_extenuantes = $data['deportes_extenuantes']==="true"||$data['deportes_extenuantes']===1?1:0;
        $clinico->TA = $data['TA'];
        $clinico->fc = $data['FC'];
        $clinico->exploracion_fisica = $data['exploracion_fisica'];
        $clinico->estudios = $data['estudios'];
        $clinico->diagnostico_general = $data['diagnostico_general'];
        $clinico->plan = $data['plan'];

        $clinico->save();

        return response(' ', 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Clinico  $clinico
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clinico $clinico)
    {
        $clinico->delete();
        
        return response("",204);
    }
}
