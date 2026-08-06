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
        $user = Auth::user();
        
        // Todos los usuarios pueden ver los clínicos de su clínica/workspace activo
        $clinicos = Clinico::whereHas('paciente', function($query) use ($user) {
            $query->forClinicaWorkspace((int) $user->clinica_efectiva_id);
        })->with('paciente')->get();
        
        return new ClinicoCollection($clinicos);
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
            $nuevoPaciente->user_id = $user->id;
            $nuevoPaciente->clinica_id = $user->clinica_efectiva_id;
            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $nuevoPaciente->sucursal_id = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $nuevoPaciente->save();

        }else{
            $id = intval($request->input('id'));
            $nuevoPaciente = Paciente::find($id);
            
            // Verificar que el paciente pertenece a la misma clínica/workspace activo
            if (! $nuevoPaciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
                return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
            }
        }
        
        $this->fillFromFormDatos($clinico, $data);
        $clinico->tipo_exp = 3;

        // Asignar el user_id del usuario que crea el expediente
        $clinico->user_id = $user->id;
        $clinico->paciente_id = $nuevoPaciente->id;
        $clinico->clinica_id = $user->clinica_efectiva_id;
        $clinico->sucursal_id = $nuevoPaciente->sucursal_id;
        $clinico->save();

        return response()->json("Guardado correctamente");
    }

    private function fillFromFormDatos(Clinico $clinico, array $data): void
    {
        $bool = static fn ($v) => ($v === true || $v === 1 || $v === '1' || $v === 'true') ? 1 : 0;
        $nullIfEmpty = static function ($v) {
            if ($v === null) {
                return null;
            }
            if (is_string($v) && trim($v) === '') {
                return null;
            }

            return $v;
        };
        $num = static function ($v) use ($nullIfEmpty) {
            $v = $nullIfEmpty($v);
            if ($v === null) {
                return null;
            }

            return is_numeric($v) ? $v + 0 : null;
        };
        $get = static fn (string $key, $default = null) => array_key_exists($key, $data) ? $data[$key] : $default;

        $clinico->fecha = $nullIfEmpty($get('fecha'));
        $clinico->fecha_1vez = $nullIfEmpty($get('fecha_1vez'));
        $hora = $nullIfEmpty($get('hora'));
        if (is_string($hora) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            $clinico->hora = strlen($hora) === 5 ? $hora.':00' : $hora;
        } else {
            $clinico->hora = $hora;
        }
        $clinico->imComplicado = $bool($get('imComplicado'));
        $clinico->imAnterior = $nullIfEmpty($get('imAnterior'));
        $clinico->imSeptal = $nullIfEmpty($get('imSeptal'));
        $clinico->imApical = $nullIfEmpty($get('imApical'));
        $clinico->imLateral = $nullIfEmpty($get('imLateral'));
        $clinico->imInferior = $nullIfEmpty($get('imInferior'));
        $clinico->imdelVD = $nullIfEmpty($get('imdelVD', $get('imdelVd')));
        $clinico->im_post_inferior = $nullIfEmpty($get('imPostInferior'));
        $clinico->anginaInestabale = $nullIfEmpty($get('anginaInestable'));
        $clinico->anginaEstabale = $nullIfEmpty($get('anginaEstable'));
        $clinico->choque_card = $nullIfEmpty($get('choqueCard'));
        $clinico->m_subita = $nullIfEmpty($get('mSubita'));
        $clinico->clase_f_ccs = $num($get('claseCcs'));
        $clinico->falla_cardiaca = $bool($get('fallaCardiaca'));
        $clinico->sobreviviente_cpr = $bool($get('sobrevivienteCpr'));
        $clinico->incapacidad_entrenar = $bool($get('incapacidadEntrenar'));
        $clinico->cf_nyha = $num($get('cfNyha'));
        $clinico->crvc = $nullIfEmpty($get('crvc'));
        $clinico->crvc_hemoductos = $nullIfEmpty($get('crvcHemo'));
        $clinico->insuficiencia_art_per = $bool($get('insuArtPer'));
        $clinico->v_mitral = $bool($get('vMitral'));
        $clinico->v_aortica = $bool($get('vAortica'));
        $clinico->v_tricuspide = $bool($get('vTricuspide'));
        $clinico->v_pulmonar = $bool($get('vPulmonar'));
        $clinico->congenitos = $bool($get('congenitos'));
        $clinico->estratificacion = $nullIfEmpty($get('estratificacion'));
        $clinico->inicio_fase_2 = $nullIfEmpty($get('inicioFase2'));
        $clinico->fin_fase_2 = $nullIfEmpty($get('finFase2'));
        $clinico->tabaquismo = $bool($get('tabaquismo'));
        $clinico->cig_dia = $num($get('cigxDia'));
        $clinico->cig_years = $num($get('cigxYear'));
        $clinico->cig_abandono = $bool($get('abadonoCigarro'));
        $clinico->cig_años_abandono = $num($get('abandonoYear'));
        $clinico->hipertension_años = $num($get('hipertensionYears'));
        $clinico->dm_years = $num($get('dmYears'));
        $clinico->actividad_fis = $bool($get('actividadFis'));
        $clinico->tipo_actividad = $nullIfEmpty($get('tipoActividad'));
        $clinico->actividad_hrs_smn = $num($get('actividadHsm'));
        $clinico->actividad_years = $num($get('actividadYears'));
        $clinico->actividad_abadono_years = $num($get('actividadYearsAbandono'));
        $clinico->estres_years = $num($get('estresYears'));
        $clinico->ansiedad_years = $num($get('ansiedadYears'));
        $clinico->depresion_years = $num($get('depresionYears'));
        $clinico->hipercolesterolemia_y = $num($get('hipercolesterolemia'));
        $clinico->hipertrigliceridemia_y = $num($get('hipertrigliceridemia'));
        $clinico->diabetes_y = $num($get('diabetesYears'));
        $clinico->tiempo_evolucion = $nullIfEmpty($get('tiempoEv'));
        $clinico->tratamiento = $nullIfEmpty($get('tratamiento'));
        $clinico->fecha_tra = $nullIfEmpty($get('fechaTra'));
        $clinico->betabloqueador = $bool($get('betabloqueador'));
        $clinico->nitratos = $bool($get('nitratos'));
        $clinico->calcioantagonista = $bool($get('calcioanta'));
        $clinico->aspirina = $bool($get('aspirina'));
        $clinico->anticoagulacion = $bool($get('anticoagulacion'));
        $clinico->iecas = $bool($get('iecas'));
        $clinico->atii = $bool($get('atii'));
        $clinico->diureticos = $bool($get('diureticos'));
        $clinico->estatinas = $bool($get('estatinas'));
        $clinico->fibratos = $bool($get('fibratos'));
        $clinico->digoxina = $bool($get('digoxina'));
        $clinico->antiarritmicos = $bool($get('antiarritmicos'));
        $clinico->arni = $bool($get('arni'));
        $clinico->sglt2 = $bool($get('sglt2'));
        $clinico->mra = $bool($get('mra'));
        $clinico->ivabradina = $bool($get('ivabradina'));
        $clinico->pcsk0 = $bool($get('pcsk0'));
        $clinico->ranalozina = $bool($get('ranalozina'));
        $clinico->timetrazidina = $bool($get('timetrazidina'));
        $clinico->inhibidor_adp = $bool($get('inhibidor_adp'));
        $clinico->otros = $nullIfEmpty($get('otro'));
        $clinico->bh_fecha = $nullIfEmpty($get('bh'));
        $clinico->hb = $num($get('hb'));
        $clinico->leucos = $num($get('leucos'));
        $clinico->plaquetas = $num($get('plaquetas'));
        $clinico->qs = $nullIfEmpty($get('qs'));
        $clinico->glucosa = $num($get('glucosa'));
        $clinico->creatinina = $num($get('creatinina'));
        $clinico->ac_unico = $num($get('acUrico'));
        $clinico->colesterol = $num($get('colesterol'));
        $clinico->ldl = $num($get('ldl'));
        $clinico->hdl = $num($get('hdl'));
        $clinico->trigliceridos = $num($get('trigliceridos'));
        $clinico->tp = $num($get('tp'));
        $clinico->inr = $num($get('inr'));
        $clinico->tpt = $num($get('tpt'));
        $clinico->pcras = $num($get('pcras'));
        $clinico->pro_bnp = $num($get('proBnp'));
        $clinico->otro_lab = $nullIfEmpty($get('otroLab'));
        $clinico->ecg_fecha = $nullIfEmpty($get('ecgFecha'));
        $clinico->ritmo = $nullIfEmpty($get('ritmo'));
        $clinico->fc_ecog = $num($get('rrmm'));
        $clinico->aP = $num($get('aP'));
        $clinico->aQRS = $num($get('aQRS'));
        $clinico->aT = $num($get('aT'));
        $clinico->duracion_qrs = $num($get('duracionQrs'));
        $clinico->duracion_p = $num($get('duracionP'));
        $clinico->qtm = $num($get('qtm'));
        // QTc: evitar división entre cero / strings vacíos (causaba Server Error 500 al editar).
        $qtm = $clinico->qtm;
        $fcEcog = $clinico->fc_ecog;
        if ($qtm !== null && $fcEcog !== null && (float) $fcEcog > 0) {
            $clinico->qtc = $qtm / sqrt(60 / (float) $fcEcog);
        } else {
            $clinico->qtc = null;
        }
        $clinico->pr = $num($get('pr'));
        $clinico->bav = $num($get('bav'));
        $clinico->brihh = $bool($get('brihh'));
        $clinico->brdhh = $bool($get('brdhh'));
        $clinico->q_as = $bool($get('qAs'));
        $clinico->q_inf = $bool($get('qInf'));
        $clinico->q_lat = $bool($get('qLat'));
        $clinico->q_ant = $bool($get('qAnt'));
        $clinico->q_poster_inferior = $bool($get('qPosterInferior'));
        $clinico->otros_ecg = $nullIfEmpty($get('otrosEcg'));
        $clinico->eco_fecha = $nullIfEmpty($get('ecoFecha'));
        $clinico->fe_por = $num($get('fePor'));
        $clinico->dd_por = $num($get('ddPor'));
        $clinico->ds_por = $num($get('dsPor'));
        $clinico->trivi_por = $nullIfEmpty($get('triviPor'));
        $clinico->rel_e_a = $num($get('relEA'));
        $clinico->valvulopatia = $bool($get('valvulopatia'));
        $clinico->otros_eco = $nullIfEmpty($get('otrosEco'));
        $clinico->mn_fecha = $nullIfEmpty($get('mnFecha'));
        $clinico->fe_por_mn = $num($get('feporMn'));
        $clinico->ant_im = $bool($get('antIm'));
        $clinico->ant_isq = $bool($get('antIsq'));
        $clinico->ant_rr = $bool($get('antRr'));
        $clinico->sept_im = $bool($get('septIM'));
        $clinico->sept_isq = $bool($get('septIsq'));
        $clinico->sept_rr = $bool($get('septRr'));
        $clinico->lat_im = $bool($get('latIm'));
        $clinico->lat_isq = $bool($get('latIsq'));
        $clinico->lat_rr = $bool($get('latRr'));
        $clinico->inf_im = $bool($get('infIM'));
        $clinico->inf_isq = $bool($get('infIsq'));
        $clinico->inf_rr = $bool($get('infRr'));
        $clinico->vrie = $bool($get('vrie'));
        $clinico->vrie_fcha = $nullIfEmpty($get('vrieFecha'));
        $clinico->fevi_basal = $num($get('feviBasal'));
        $clinico->fevi_10_dobuta = $num($get('fevi10Dobuta'));
        $clinico->reserva_inot_absolut = $num($get('reservaInotA'));
        $clinico->reserva_inot_relat = $num($get('reservaInotR'));
        $clinico->vrie_otros = $nullIfEmpty($get('vrieOtro'));
        $clinico->vrie_riesgo = $nullIfEmpty($get('vrieRiesgo'));
        $clinico->holter = $bool($get('holter'));
        $clinico->holter_fecha = $nullIfEmpty($get('holterFecha'));
        $clinico->holter_dignostico = $nullIfEmpty($get('holterDiagnostico'));
        $clinico->holter_riesgo = $nullIfEmpty($get('holterRiesgo'));
        $clinico->cateterismo = $bool($get('cateterismo'));
        $clinico->catet_fecha = $nullIfEmpty($get('catetFecha'));
        $clinico->catet_fe = $num($get('catetFe'));
        $clinico->catet_d2vi = $num($get('catetD2vi'));
        $clinico->catet_tco = $num($get('catetTco'));
        $clinico->catet_da_prox = $nullIfEmpty($get('catetDa'));
        $clinico->catet_da_med = $nullIfEmpty($get('catetDaMed'));
        $clinico->catet_da_dist = $nullIfEmpty($get('catetDaDist'));
        $clinico->catet_1a_d = $num($get('catet1aD'));
        $clinico->catet_2a_d = $num($get('catet2aD'));
        $clinico->catet_cx_prox = $nullIfEmpty($get('catetCxP'));
        $clinico->catet_cx_dist = $num($get('catetCxD'));
        $clinico->catet_om = $num($get('catetOm'));
        $clinico->catet_pl = $num($get('catetPl'));
        $clinico->catet_cd_aprox = $nullIfEmpty($get('catetCdprox'));
        $clinico->catet_cd_med = $nullIfEmpty($get('catetCdMed'));
        $clinico->catet_cd_dist = $nullIfEmpty($get('catetCdDist'));
        $clinico->catet_r_vent_izq = $num($get('catetRVIzq'));
        $clinico->catet_dp = $num($get('catetDp'));
        $clinico->catet_otros = $nullIfEmpty($get('catetOtro'));
        $clinico->catet_movilidad = $nullIfEmpty($get('catetMovilidad'));
        $clinico->catet_riesgo = $nullIfEmpty($get('catetRiesgo'));
        $clinico->termino = $bool($get('termino'));
        $clinico->semanas = $num($get('semanas'));
        $clinico->aprendio_borg = $bool($get('aprendioBorg'));
        $clinico->muerte = $bool($get('muerte'));
        $clinico->inestabilidad_cardio = $bool($get('inestabilidadCardio'));
        $clinico->hospitalizacion = $bool($get('hospitalizacion'));
        $clinico->susp_motu_propio = $bool($get('suspMotuPropio'));
        $clinico->lesion_osteo = $bool($get('lesionOsteo'));
        $clinico->res_otros = $bool($get('resOtros'));
        $clinico->era_vez_fecha = $nullIfEmpty($get('Vez1aFecha'));
        $clinico->sintomas = $nullIfEmpty($get('sintomas'));
        $clinico->comer_vestirse = $bool($get('comerVestirse'));
        $clinico->caminar_casa = $bool($get('caminarCasa'));
        $clinico->caminar_2_cuadras = $bool($get('caminar2Cuadras'));
        $clinico->subir_piso = $bool($get('subirPiso'));
        $clinico->correr_corta = $bool($get('correrCorta'));
        $clinico->lavar_trastes = $bool($get('lavarTrastes'));
        $clinico->aspirar_casa = $bool($get('aspirarCasa'));
        $clinico->trapear = $bool($get('trapear'));
        $clinico->jardineria = $bool($get('jardineria'));
        $clinico->relaciones = $bool($get('relaciones'));
        $clinico->jugar = $bool($get('jugar'));
        $clinico->deportes_extenuantes = $bool($get('deportesExtenuantes'));
        $clinico->TA = $nullIfEmpty($get('TA'));
        $clinico->fc = $num($get('FC'));
        $clinico->exploracion_fisica = $nullIfEmpty($get('exploracionFisica'));
        $clinico->estudios = $nullIfEmpty($get('estudios'));
        $clinico->diagnostico_general = $nullIfEmpty($get('diagnosticoGeneral'));
        $clinico->plan = $nullIfEmpty($get('plan'));
        $clinico->dasi = (0.43 * (
            (2.75 * (int) $clinico->comer_vestirse)
            + (1.75 * (int) $clinico->caminar_casa)
            + (2.75 * (int) $clinico->caminar_2_cuadras)
            + (5.5 * (int) $clinico->subir_piso)
            + (8 * (int) $clinico->correr_corta)
            + (2.7 * (int) $clinico->lavar_trastes)
            + (3.5 * (int) $clinico->aspirar_casa)
            + (8 * (int) $clinico->trapear)
            + (4.5 * (int) $clinico->jardineria)
            + (5.25 * (int) $clinico->relaciones)
            + (6 * (int) $clinico->jugar)
            + (7.5 * (int) $clinico->deportes_extenuantes)
        ) + 9.6) / 3.5;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Clinico  $clinico
     * @return \Illuminate\Http\Response
     */
    public function show(Clinico $clinico)
    {
        $user = Auth::user();
        
        // Verificar que el clínico pertenece al workspace activo
        $paciente = $clinico->paciente;
        if (!$paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No tienes acceso a este expediente clínico'], 403);
        }

        return response()->json($clinico->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Clinico  $clinico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Clinico $clinico)
    {
        $user = Auth::user();
        
        // Verificar que el clínico pertenece al workspace activo
        $paciente = $clinico->paciente;
        if (!$paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['message' => 'No tienes acceso a este expediente clínico'], 403);
        }

        if (! $request->has('datos') || ! is_array($request->input('datos'))) {
            return response()->json(['message' => 'Faltan los datos del expediente clínico.'], 422);
        }

        try {
            $this->fillFromFormDatos($clinico, $request->input('datos'));
            $clinico->save();

            return response()->json(['message' => 'Actualizado correctamente']);
        } catch (\Throwable $e) {
            \Log::error('Clinico update failed', [
                'clinico_id' => $clinico->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo actualizar el expediente clínico. Revisa fechas y campos numéricos vacíos e intenta de nuevo.',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Clinico  $clinico
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clinico $clinico)
    {
        $user = Auth::user();
        
        // Solo admin o superadmin pueden eliminar
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes'], 403);
        }
        
        // Verificar que el clínico pertenece al workspace activo
        $paciente = $clinico->paciente;
        if (!$paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No tienes acceso a este expediente clínico'], 403);
        }
        
        $clinico->delete();
        return response()->json(['message' => 'Expediente clínico eliminado exitosamente'], 204);
    }
}
