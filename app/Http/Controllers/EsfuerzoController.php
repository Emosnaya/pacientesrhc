<?php

namespace App\Http\Controllers;

use App\Http\Resources\EsfuerzoCollection;
use App\Models\Esfuerzo;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EsfuerzoController extends Controller
{
    /**
     * División segura para evitar división por cero
     * @param float $numerator
     * @param float $denominator
     * @param mixed $default Valor por defecto si el denominador es 0 (default: null)
     * @return float|null
     */
    private function safeDivide($numerator, $denominator, $default = null)
    {
        if (empty($denominator) || $denominator == 0) {
            return $default;
        }
        return $numerator / $denominator;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Todos los usuarios pueden ver los esfuerzos de su clínica
        $esfuerzos = Esfuerzo::whereHas('paciente', function($query) use ($user) {
            $query->where('clinica_id', $user->clinica_efectiva_id);
        })->with('paciente')->get();
        
        return new EsfuerzoCollection($esfuerzos);
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
        $pesfuerzo = new Esfuerzo();
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

            $nuevoPaciente->registro = $paciente['registro'];
            $nuevoPaciente->nombre = $paciente['nombre'];
            $nuevoPaciente->apellidoPat = $paciente['apellidoPat'];
            $nuevoPaciente->apellidoMat = $paciente['apellidoMat'];
            $nuevoPaciente->telefono = $paciente['telefono'];
            $nuevoPaciente->domicilio = $paciente['domicilio'];
            $nuevoPaciente->profesion = $paciente['profesion'];
            $nuevoPaciente->cintura = $paciente['cintura'];
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
            
            if ($nuevoPaciente->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
            }

            $pesfuerzo->paciente_id = $nuevoPaciente->id;
            
        }

        $prevalencia = 0.98;
        $sensibilidad = $data['confusor'] === 'true'?.64:.68;
        $especificidad = $data['especificidad'];

        $vpp = ($sensibilidad*$prevalencia)/(($sensibilidad*$prevalencia)+(1-$especificidad)*(1-$prevalencia));
        $vpn = ($especificidad*$prevalencia)/(($especificidad*$prevalencia)+(1-$sensibilidad)*(1-$prevalencia));

        $fcBasal = $data['fcBasal'];
        $tasBasal = $data['tasBasal'];
        $tadBasal = $data['tadBasal'];
        $dpBasal = $fcBasal*$tasBasal;

        $fcBorg =  $data['fcBorg12'];
        $tasBorg = $data['tasBorg12'];
        $tadBorg = $data['tadBorg12'];
        $dpBorg =$fcBorg*$tasBorg;

        $fc50 = $data['fcw50'];
        $tas50 = $data['tasw50'];
        $dp50 = $fc50*$tas50; 

        $tasMax = $data['tasMax'];
        $tAmax_tbasal = $tasMax-$tasBasal;
        $tAmax_tbasal_value = null;


        if($tAmax_tbasal >= 40){
            $tAmax_tbasal_value = 0;
        }else if(($tAmax_tbasal >= 31) & ($tAmax_tbasal <= 40)){
            $tAmax_tbasal_value = 1;
        }else if(($tAmax_tbasal >= 21) & ($tAmax_tbasal <= 30)){
            $tAmax_tbasal_value = 2;
        }else if(($tAmax_tbasal >= 11) & ($tAmax_tbasal <= 20)){
            $tAmax_tbasal_value = 3;
        }else if(($tAmax_tbasal >= 0) & ($tAmax_tbasal <= 10)){
            $tAmax_tbasal_value = 4;
        }else if($tAmax_tbasal < 0){
            $tAmax_tbasal_value = 5;
        }

        $fcMax = $data['fcMax'];
        $dpMax = $fcMax*$tasMax;

        $fc1ermin = $data['fc1erMin'];
        $tas1ermin = $data['tas1erMin'];
        $dp1ermin = $fc1ermin*$tas1ermin;


        $fc3ermin = $data['fc3erMin'];
        $tas3ermin = $data['tas3erMin'];
        $dp3ermin = $fc3ermin*$tas3ermin;

        $icc = ($data['icc'] == 'true')? 1 : 0;

        $maxInfra = $data['maxInfradesnivel'];

        $UisqBorf = $data['borgUisq'];

        $fcUisq = $data['fcUisq'];
        $tasUisq = $data['tasUisq'];
        $dpUisq = $fcUisq*$tasUisq;

        $fcMaxCalc = 220-$nuevoPaciente->edad;

        $fc85 = $fcMaxCalc*0.85;

        $fcMaxAlcanzado = $this->safeDivide($fcMax*100, $fcMaxCalc, 0);

        $vo2tM = 42.3-(0.356*$nuevoPaciente->edad);
        $metsMT = $vo2tM/3.5;

        $vo2tV= 57.8-(0.445*$nuevoPaciente->edad);
        $metsVT = $vo2tV/3.5;

        $metsTG = ($metsVT*$nuevoPaciente->genero)+($metsMT*(1-$nuevoPaciente->genero));

        $banda = ($data['banda'] === 'true')? 1 : 0;
        $ciclo =($data['ciclo'] === 'true')? 1 : 0;

        $mediconGases = ($data['medicionGases'] === 'true')? 1 : 0;

        $metsCicloB12 = $ciclo*$this->safeDivide($data['wattsCicloBorg'], 16, 0);
        $metsGasesB12 = $this->safeDivide($data['vo2BorgGases'], 3.5, 0)*$mediconGases;

        $metsCicloMax = $ciclo*$this->safeDivide($data['wattsCicloMax'], 16, 0);
        $metsGasesMax = $this->safeDivide($data['vo2picoGases'], 3.5, 0)*$mediconGases;


        $metsCicloUisq = $ciclo*$this->safeDivide($data['wattsCicloUmIsq'], 16, 0);

        $mvo2 = ($dpMax*0.14*0.01)-6.3;

        $mvo2Mets = ($mvo2/3.5)*0.1;
        $po2TM = $this->safeDivide($vo2tM*$nuevoPaciente->peso, 220-$nuevoPaciente->edad, 0);

        $po2TV = $this->safeDivide($vo2tV*$nuevoPaciente->peso, 220-$nuevoPaciente->edad, 0);

        $velBorg12 = ($data['velBorg']*1609)/60;
        $velMax =  ($data['velmax']*1609)/60;
        $velUisq =  ($data['velUmIsq']*1609)/60;
        $chBorg = ($velBorg12*0.1)+3.5;
        $chMax = ($velMax*0.1)+3.5;
        $chUisq = ($velUisq*0.1)+3.5;
        $cvBorg = ($velBorg12*1.8)*($data['inclinBorg']*0.01);
        $cvMax = ($velMax*1.8)*($data['inclMax']*0.01);
        $cvUisq = ($velUisq*1.8)*($data['inclUmIsq']*0.01);
        $vor2Max = $chMax+$cvMax;
        $metsMaxBanda = $banda*($vor2Max/3.5);
        $metsMax = $metsMaxBanda + $metsCicloMax + $metsGasesMax;
        $po2r = $this->safeDivide(($metsMax*3.5)*$nuevoPaciente->peso, $fcMax, 0);
        $porvo2Alcanzado = $this->safeDivide(($metsMax*3.5), ($metsTG*3.5), 0)*100;
        $duke = ($metsMax+1.69)-(5*$maxInfra)-(4*$data['scoreAngina']);
        $veteranos = 5*($icc)+($maxInfra)+($tAmax_tbasal_value)-$metsMax;
        $vo2Uisq = $chUisq + $cvUisq;
        $metsUisqBanda = $banda*($vo2Uisq/3.5);
        $metsUisq = $metsUisqBanda + $metsCicloUisq;
        $rfaM = $this->safeDivide((($vo2tM/3.5)-$metsMax), $metsMax, 0)*100;
        $rfaV = $this->safeDivide((($vo2tV/3.5)-$metsMax), $metsMax, 0)*100;
        $respPresora = $this->safeDivide($tasMax-$tasBasal, $metsMax, 0);
        $indiceTasEsfuerzo = $this->safeDivide($tasMax, $tasBasal, 0);
        $respCrono = $this->safeDivide($fcMax-$fcBasal, $metsMax, 0);
        $fcMax_fc1 = $fcMax-$fc1ermin;
        $fcMax_fc3 = $fcMax-$fc3ermin;
        $fcPorcentajeRecu1 = ($this->safeDivide($fc1ermin*100, $fcMax, 0)-100)*-1;
        $fcPorcentajeRecu3 = ($this->safeDivide($fc3ermin*100, $fcMax, 0)-100)*-1;
        $pbp3 = $this->safeDivide($tas3ermin, $tasMax, 0);
        $pce = $porvo2Alcanzado*$tasMax;
        $tce = $this->safeDivide($pce, $fcMax, 0);
        $iem = $this->safeDivide(($mvo2/3.5), $metsMax, 0)*10;
        $vo2rB = $chBorg+$cvBorg;
        $metsBandaBorg = $banda*(($chBorg+$cvBorg)/3.5);
        $metsBorg12 = $metsBandaBorg + $metsCicloB12 + $metsGasesB12;
        $tiempoEsfuerzo = $metsMax+1.692;




        $pesfuerzo->numPrueba = $data['numPrueba'];
        $pesfuerzo->icc =  $icc;
        $pesfuerzo->FEVI =  $data['fevi'];
        $pesfuerzo->metodo = $data['metodo'];
        $pesfuerzo->ectopia_ventricular =($data['ectopia'] == 'true') ? 1:0;
        $pesfuerzo->disfuncionDias = ($data['disfuncion'] == 'true') ? 1:0;
        $pesfuerzo->nyha = $data['nya'];
        $pesfuerzo->ccs = $data['ccs'];
        $pesfuerzo->betabloqueador = ($data['betabloqueador'] == 'true') ? 1:0;
        $pesfuerzo->iecas = ($data['iecass'] == 'true') ? 1:0;
        $pesfuerzo->nitratos = ($data['nitratos'] == 'true') ? 1:0;
        $pesfuerzo->digoxina = ($data['digoxina'] == 'true') ? 1:0;
        $pesfuerzo->calcioAntag = ($data['calcio'] == 'true') ? 1:0;
        $pesfuerzo->antirritmicos = ($data['antiarritmicos'] == 'true') ? 1:0;
        $pesfuerzo->hipolipemiantes = ($data['hipolipemiantes'] == 'true') ? 1:0;
        $pesfuerzo->diureticos = ($data['diureticos'] == 'true') ? 1:0;
        $pesfuerzo->aldactone = ($data['aldactone'] == 'true') ? 1:0;
        $pesfuerzo->antiagregante = ($data['antiagregante'] == 'true') ? 1:0;
        $pesfuerzo->otros = ( $data['otros'] == 'true') ? 1:0;
        $pesfuerzo->prevalencia =  $prevalencia;
        $pesfuerzo->confusor = ($data['confusor'] == 'true') ? 1:0;
        $pesfuerzo->sensibilidad = $sensibilidad;
        $pesfuerzo->especificidad = $data['especificidad'];
        $pesfuerzo->vpp = $vpp;
        $pesfuerzo->vpn = $vpn;
        $pesfuerzo->pruebaIngreso = ($data['pruebaIngreso']== 'true') ? 1:0;
        $pesfuerzo->pruebaFinFase2 = ( $data['pruebaFin2']== 'true') ? 1:0;
        $pesfuerzo->pruebaFinFase3 = ( $data['pruebaFin3']== 'true') ? 1:0;
        $pesfuerzo->fechaDeInicio =  $data['pruebaInicio'];
        $pesfuerzo->balke = ($data['balke'] == 'true') ? 1:0;
        $pesfuerzo->bruce = ($data['bruce'] == 'true') ? 1:0;
        $pesfuerzo->naughton = (isset($data['naughton']) && $data['naughton'] == 'true') ? 1:0;
        $pesfuerzo->tipo_esfuerzo = isset($data['tipoEsfuerzo']) ? $data['tipoEsfuerzo'] : 'cardiaco';
        $pesfuerzo->ciclo = ($data['ciclo'] == 'true') ? 1:0;
        $pesfuerzo->banda = ($data['banda'] == 'true') ? 1:0;
        $pesfuerzo->medicionGases = ($data['medicionGases'] == 'true') ? 1:0;
        $pesfuerzo->fcBasal = $fcBasal;
        $pesfuerzo->tasBasal = $tasBasal;
        $pesfuerzo->tadBasal = $tadBasal;
        $pesfuerzo->dapBasal = $dpBasal;
        $pesfuerzo->fcBorg12 = $fcBorg;
        $pesfuerzo->tasBorg12 = $tasBorg;
        $pesfuerzo->tadBorg12 = $tadBorg;
        $pesfuerzo->dpBorg12 = $dpBorg;
        $pesfuerzo->w_50 = $data['w50'];
        $pesfuerzo->fc_w_50 = $fc50;
        $pesfuerzo->tas_w_50 = $tas50;
        $pesfuerzo->tad_w_50 = $data['tad50'];
        $pesfuerzo->borg_w_50 = $data['borgw50'];
        $pesfuerzo->dp_w_50 = $dp50;
        $pesfuerzo->fcMax = $fcMax;
        $pesfuerzo->tasMax = $tasMax;
        $pesfuerzo->tadMax = $data['tadMax'];
        $pesfuerzo->borgMax = $data['borgMax'];
        $pesfuerzo->tAMax_tAbasal = $tAmax_tbasal;
        $pesfuerzo->tAMax_tAbasal_val = $tAmax_tbasal_value;
        $pesfuerzo->tiempoEsfuerzo = $tiempoEsfuerzo;
        $pesfuerzo->dpMax = $dpMax;
        $pesfuerzo->motivoSuspension = $data['motivoSusp'];
        $pesfuerzo->pba_submax = ($data['pbaSubmax'] == 'true') ? 1:0;
        $pesfuerzo->fc_mayor_50 = ($data['fcMayor85'] == 'true') ? 1:0;
        $pesfuerzo->fc_1er_min = $fc1ermin;
        $pesfuerzo->tas_1er_min = $tas1ermin;
        $pesfuerzo->tad_1er_min = $data['tad1erMin'];
        $pesfuerzo->borg_1er_min = $data['borg1erMin'];
        $pesfuerzo->dp_1er_min = $dp1ermin;
        $pesfuerzo->fc_3er_min = $fc3ermin;
        $pesfuerzo->tas_3er_min = $tas3ermin;
        $pesfuerzo->tad_3er_min =  $data['tad3erMin'];
        $pesfuerzo->borg_3er_min =  $data['borg3erMin'];
        $pesfuerzo->dp_3er_min =  $dp3ermin;
        $pesfuerzo->fc_5to_min =  $data['fc5toMin'];
        $pesfuerzo->tas_5to_min =  $data['tas5toMin'];
        $pesfuerzo->tad_5to_min =   $data['tad5toMin'];
        $pesfuerzo->fc_8vo_min =  $data['fc8voMin'];
        $pesfuerzo->tas_8vo_min =  $data['tas8voMin'];
        $pesfuerzo->tad_8vo_min =  $data['tad8voMin'];
        $pesfuerzo->fc_U_isq =  $fcUisq;
        $pesfuerzo->tas_U_isq =  $tasUisq;
        $pesfuerzo->tad_U_isq =  $data['tadUisq'];
        $pesfuerzo->borg_U_isq =  $data['borgUisq'];
        $pesfuerzo->scoreAngina =  $data['scoreAngina'];
        $pesfuerzo->arritmias =  ($data['arritmias'] == 'true') ? 1:0;
        $pesfuerzo->tipoArritmias =  $data['tipoArritmias'];
        $pesfuerzo->positiva =  ($data['positiva'] == 'true') ? 1:0;
        $pesfuerzo->tipoCambioElectrico =  $data['tipoCambioE'];
        $pesfuerzo->MaxInfra =   $data['maxInfradesnivel'];
        $pesfuerzo->veteranos =  $veteranos;
        $pesfuerzo->duke =  $duke;
        $pesfuerzo->riesgo =  $data['riesgo'];
        $pesfuerzo->u_isq_borg =  $UisqBorf;
        $pesfuerzo->u_isq_dp =  $dpUisq;
        $pesfuerzo->vel_borg_12 =  $data['velBorg'];
        $pesfuerzo->inclin_borg_12 =  $data['inclinBorg'];
        $pesfuerzo->watts_ciclo_b_12 =  $data['wattsCicloBorg'];
        $pesfuerzo->vel_max =  $data['velmax'];
        $pesfuerzo->incl_max =  $data['inclMax'];
        $pesfuerzo->watts_ciclo_max =  $data['wattsCicloMax'];
        $pesfuerzo->vel_um_isq =  $data['velUmIsq'];
        $pesfuerzo->incl_um_isq =  $data['inclUmIsq'];
        $pesfuerzo->watts_ciclo_u_isq =   $data['wattsCicloUmIsq'];
        $pesfuerzo->vo2_max_gases =  $data['vo2MaxGases'];
        $pesfuerzo->vo2_pico_gases =  $data['vo2picoGases'];
        $pesfuerzo->vo2_borg_gases =  $data['vo2BorgGases'];
        $pesfuerzo->r_qmax =  $data['rQmax'];
        $pesfuerzo->umbral_aeer_anaer = $data['umbralAer'];
        $pesfuerzo->po2_teor = $data['poTeorico'];
        $pesfuerzo->vo2_max_percent = isset($data['vo2MaxPercent']) && $data['vo2MaxPercent'] !== '' ? $data['vo2MaxPercent'] : null;
        $pesfuerzo->fc_max_calc =  $fcMaxCalc;
        $pesfuerzo->fc_85 =  $fc85;
        $pesfuerzo->fc_max_alcanzado =  $fcMaxAlcanzado;
        $pesfuerzo->vo2t_mujer =  $vo2tM;
        $pesfuerzo->mets_teorico_mujer =  $metsMT;
        $pesfuerzo->vo2t_varon =  $vo2tV;
        $pesfuerzo->mets_teorico_varon =  $metsVT;
        $pesfuerzo->mets_teorico_general =  $metsTG;
        $pesfuerzo->vo2r_borg_12 =  $vo2rB;
        $pesfuerzo->mets_banda_borg_12 =  $metsBandaBorg;
        $pesfuerzo->mets_ciclo_b12 =  $metsCicloB12;
        $pesfuerzo->mets_gases_b12 =  $metsGasesB12;
        $pesfuerzo->mets_borg_12 =  $metsBorg12;
        $pesfuerzo->vo2r_max =  $vor2Max;
        $pesfuerzo->mets_banda_max =  $metsMaxBanda;
        $pesfuerzo->mets_ciclo_max =  $metsCicloMax;
        $pesfuerzo->mets_gases_max =  $metsGasesMax;
        $pesfuerzo->mets_max =  $metsMax;
        $pesfuerzo->vo2_alcanzado =  $porvo2Alcanzado;
        $pesfuerzo->vo2r_U_isq =  $vo2Uisq;
        $pesfuerzo->mets_banda_U_isq =  $metsUisqBanda;
        $pesfuerzo->mets_ciclo_U_isq =  $metsCicloUisq;
        $pesfuerzo->mets_U_isq =  $metsUisq;
        $pesfuerzo->mvo2 =  $mvo2;
        $pesfuerzo->mvo2_mets =  $mvo2Mets;
        $pesfuerzo->iem =  $iem;
        $pesfuerzo->po2t_mujer =  $po2TM;
        $pesfuerzo->po2t_varon =  $po2TV;
        $pesfuerzo->po2tr =  $po2r;
        $pesfuerzo->rfa_mujer =  $rfaM;
        $pesfuerzo->rfa_varon =  $rfaV;
        $pesfuerzo->resp_presora =  $respPresora;
        $pesfuerzo->indice_tas =  $indiceTasEsfuerzo;
        $pesfuerzo->resp_crono =  $respCrono;
        $pesfuerzo->fcmax_fc1er =  $fcMax_fc1;
        $pesfuerzo->fcmax_fc3er =  $fcMax_fc3;
        $pesfuerzo->fc_rec_1_por =  $fcPorcentajeRecu1;
        $pesfuerzo->fc_rec_3_por =  $fcPorcentajeRecu3;
        $pesfuerzo->pbp3 =  $pbp3;
        $pesfuerzo->pce =  $pce;
        $pesfuerzo->tce =  $tce;
        $pesfuerzo->vel_borg_12_mh =  $velBorg12;
        $pesfuerzo->vel_max_mh =  $velMax;
        $pesfuerzo->vel_u_isq_mh =  $velUisq;
        $pesfuerzo->ch_borg_12 =  $chBorg;
        $pesfuerzo->ch_max =  $chMax;
        $pesfuerzo->ch_u_isq =  $chUisq;
        $pesfuerzo->cv_borg_12 =  $cvBorg;
        $pesfuerzo->cv_max =  $cvMax;
        $pesfuerzo->cv_u_isq =  $cvUisq;
        $pesfuerzo->conclusiones =  $data['comentarios'];
        $pesfuerzo->fecha =  $data['fecha'];
        $pesfuerzo->recup_tas = $this->safeDivide($tas3ermin, $tas1ermin, 0);

        // Asignar el user_id del dueño del paciente
        $pesfuerzo->user_id = $nuevoPaciente->user_id;
        $pesfuerzo->tipo_exp = 1;
        $pesfuerzo->clinica_id = $user->clinica_efectiva_id;
        $pesfuerzo->sucursal_id = $nuevoPaciente->sucursal_id;

        $pesfuerzo->paciente_id = $nuevoPaciente->id;
        $pesfuerzo->save();
        
        return response()->json($pesfuerzo);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Esfuerzo  $esfuerzo
     * @return \Illuminate\Http\Response
     */
    public function show(Esfuerzo $esfuerzo)
    {
        $user = Auth::user();
        
        // Verificar que el esfuerzo pertenece a la misma clínica
        $paciente = $esfuerzo->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente de esfuerzo'], 403);
        }

        return response()->json($esfuerzo->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Esfuerzo  $esfuerzo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Esfuerzo $esfuerzo)
    {
        $user = Auth::user();
        
        // Verificar que el esfuerzo pertenece a la misma clínica
        $paciente = $esfuerzo->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente de esfuerzo'], 403);
        }

        // Usar directamente el modelo del Route Model Binding
        $pesfuerzo = $esfuerzo;
        $nuevoPaciente = $paciente; // Ya tenemos el paciente cargado

        $data = $request->all();

        $prevalencia = 0.98;
        $sensibilidad = $data['confusor'] === 'true' ? .64 : .68;
        $especificidad = $data['especificidad'];

        $vpp = ($sensibilidad*$prevalencia)/(($sensibilidad*$prevalencia)+(1-$especificidad)*(1-$prevalencia));
        $vpn = ($especificidad*$prevalencia)/(($especificidad*$prevalencia)+(1-$sensibilidad)*(1-$prevalencia));

        $fcBasal = $data['fcBasal'];
        $tasBasal = $data['tasBasal'];
        $tadBasal = $data['tadBasal'];
        $dpBasal = $fcBasal*$tasBasal;

        $fcBorg =  $data['fcBorg12'];
        $tasBorg = $data['tasBorg12'];
        $tadBorg = $data['tadBorg12'];
        $dpBorg =$fcBorg*$tasBorg;

        $fc50 = $data['fcw50'];
        $tas50 = $data['tasw50'];
        $dp50 = $fc50*$tas50; 

        $tasMax = $data['tasMax'];
        $tAmax_tbasal = $tasMax-$tasBasal;
        $tAmax_tbasal_value = null;


        if($tAmax_tbasal >= 40){
            $tAmax_tbasal_value = 0;
        }else if(($tAmax_tbasal >= 31) & ($tAmax_tbasal <= 40)){
            $tAmax_tbasal_value = 1;
        }else if(($tAmax_tbasal >= 21) & ($tAmax_tbasal <= 30)){
            $tAmax_tbasal_value = 2;
        }else if(($tAmax_tbasal >= 11) & ($tAmax_tbasal <= 20)){
            $tAmax_tbasal_value = 3;
        }else if(($tAmax_tbasal >= 0) & ($tAmax_tbasal <= 10)){
            $tAmax_tbasal_value = 4;
        }else if($tAmax_tbasal < 0){
            $tAmax_tbasal_value = 5;
        }

        $fcMax = $data['fcMax'];
        $dpMax = $fcMax*$tasMax;

        $fc1ermin = $data['fc1erMin'];
        $tas1ermin = $data['tas1erMin'];
        $dp1ermin = $fc1ermin*$tas1ermin;


        $fc3ermin = $data['fc3erMin'];
        $tas3ermin = $data['tas3erMin'];
        $dp3ermin = $fc3ermin*$tas3ermin;

        $icc = ($data['icc'] == 'true')? 1 : 0;

        $maxInfra = $data['maxInfradesnivel'];

        $UisqBorf = $data['borgUisq'];

        $fcUisq = $data['fcUisq'];
        $tasUisq = $data['tasUisq'];
        $dpUisq = $fcUisq*$tasUisq;

        $fcMaxCalc = 220-$nuevoPaciente->edad;


        $fc85 = $fcMaxCalc*0.85;

        $fcMaxAlcanzado = $this->safeDivide($fcMax*100, $fcMaxCalc, 0);

        $vo2tM = 42.3-(0.356*$nuevoPaciente->edad);
        $metsMT = $vo2tM/3.5;

        $vo2tV= 57.8-(0.445*$nuevoPaciente->edad);
        $metsVT = $vo2tV/3.5;

        $metsTG = ($metsVT*$nuevoPaciente->genero)+($metsMT*(1-$nuevoPaciente->genero));

        $banda = ($data['banda'] === 1 || $data['banda'] === 'true') ? 1 : 0;
        $ciclo = ($data['ciclo'] === 1 || $data['ciclo'] === 'true') ? 1 : 0;

        $mediconGases = ($data['medicionGases'] === 'true' || $data['medicionGases'] === 1) ? 1 : 0;

        // Mismas claves camelCase que en store() (el front envía el body plano, no snake_case)
        $metsCicloB12 = $ciclo * $this->safeDivide($data['wattsCicloBorg'] ?? null, 16, 0);
        $metsGasesB12 = $this->safeDivide($data['vo2BorgGases'] ?? null, 3.5, 0) * $mediconGases;

        $metsCicloMax = $ciclo * $this->safeDivide($data['wattsCicloMax'] ?? null, 16, 0);
        $metsGasesMax = $this->safeDivide($data['vo2picoGases'] ?? null, 3.5, 0) * $mediconGases;

        $metsCicloUisq = $ciclo * $this->safeDivide($data['wattsCicloUmIsq'] ?? null, 16, 0);

        $mvo2 = ($dpMax*0.14*0.01)-6.3;

        $mvo2Mets = ($mvo2/3.5)*0.1;
        $po2TM = $this->safeDivide($vo2tM*$nuevoPaciente->peso, 220-$nuevoPaciente->edad, 0);

        $po2TV = $this->safeDivide($vo2tV*$nuevoPaciente->peso, 220-$nuevoPaciente->edad, 0);

        $velBorg12 = (($data['velBorg'] ?? 0) * 1609) / 60;
        $velMax = (($data['velmax'] ?? 0) * 1609) / 60;
        $velUisq = (($data['velUmIsq'] ?? 0) * 1609) / 60;
        $chBorg = ($velBorg12 * 0.1) + 3.5;
        $chMax = ($velMax * 0.1) + 3.5;
        $chUisq = ($velUisq * 0.1) + 3.5;
        $cvBorg = ($velBorg12 * 1.8) * (($data['inclinBorg'] ?? 0) * 0.01);
        $cvMax = ($velMax * 1.8) * (($data['inclMax'] ?? 0) * 0.01);
        $cvUisq = ($velUisq * 1.8) * (($data['inclUmIsq'] ?? 0) * 0.01);
        $vor2Max = $chMax + $cvMax;
        $metsMaxBanda = $banda * ($vor2Max / 3.5);
        $metsMax = $metsMaxBanda + $metsCicloMax + $metsGasesMax;
        $po2r = $this->safeDivide(($metsMax * 3.5) * $nuevoPaciente->peso, $fcMax, 0);
        $porvo2Alcanzado = $this->safeDivide(($metsMax * 3.5), ($metsTG * 3.5), 0) * 100;
        $duke = ($metsMax + 1.69) - (5 * $maxInfra) - (4 * $data['scoreAngina']);
        $veteranos = 5*($icc)+($maxInfra)+($tAmax_tbasal_value)-$metsMax;
        $vo2Uisq = $chUisq + $cvUisq;
        $metsUisqBanda = $banda*($vo2Uisq/3.5);
       
        $metsUisq = $metsUisqBanda + $metsCicloUisq;
        $rfaM = $this->safeDivide((($vo2tM/3.5)-$metsMax), $metsMax, 0)*100;
        $rfaV = $this->safeDivide((($vo2tV/3.5)-$metsMax), $metsMax, 0)*100;
        $respPresora = $this->safeDivide($tasMax-$tasBasal, $metsMax, 0);
        $indiceTasEsfuerzo = $this->safeDivide($tasMax, $tasBasal, 0);
        $respCrono = $this->safeDivide($fcMax-$fcBasal, $metsMax, 0);
        $fcMax_fc1 = $fcMax-$fc1ermin;
        $fcMax_fc3 = $fcMax-$fc3ermin;
        $fcPorcentajeRecu1 = ($this->safeDivide($fc1ermin*100, $fcMax, 0)-100)*-1;
        $fcPorcentajeRecu3 = ($this->safeDivide($fc3ermin*100, $fcMax, 0)-100)*-1;
        $pbp3 = $this->safeDivide($tas3ermin, $tasMax, 0);
        $pce = $porvo2Alcanzado*$tasMax;
        $tce = $this->safeDivide($pce, $fcMax, 0);
        $iem = $this->safeDivide(($mvo2/3.5), ($metsMax*10), 0);
        $vo2rB = $chBorg+$cvBorg;
        $metsBandaBorg = $banda*(($chBorg+$cvBorg)/3.5);
        $metsBorg12 = $metsBandaBorg + $metsCicloB12 + $metsGasesB12;
        $tiempoEsfuerzo = $metsMax+1.692;




        $pesfuerzo->numPrueba = $data['numPrueba'];
        $pesfuerzo->icc =  $icc;
        $pesfuerzo->FEVI =  $data['fevi'];
        $pesfuerzo->metodo = $data['metodo'];
        $pesfuerzo->ectopia_ventricular = ($data['ectopia'] == 'true') ? 1:0;
        $pesfuerzo->disfuncionDias = ($data['disfuncion'] == 'true' || $data['disfuncion'] === '0') ? $data['disfuncion'] : 0;
        $pesfuerzo->nyha = $data['nya'];
        $pesfuerzo->ccs = $data['ccs'];
        $pesfuerzo->betabloqueador = ($data['betabloqueador'] == 'true') ? 1:0;
        $pesfuerzo->iecas = ($data['iecass'] == 'true') ? 1:0;
        $pesfuerzo->nitratos = ($data['nitratos'] == 'true') ? 1:0;
        $pesfuerzo->digoxina = ($data['digoxina'] == 'true') ? 1:0;
        $pesfuerzo->calcioAntag = ($data['calcio'] == 'true') ? 1:0;
        $pesfuerzo->antirritmicos = ($data['antiarritmicos'] == 'true') ? 1:0;
        $pesfuerzo->hipolipemiantes = ($data['hipolipemiantes'] == 'true') ? 1:0;
        $pesfuerzo->diureticos = ($data['diureticos'] == 'true') ? 1:0;
        $pesfuerzo->aldactone = ($data['aldactone'] == 'true') ? 1:0;
        $pesfuerzo->antiagregante = ($data['antiagregante'] == 'true') ? 1:0;
        $pesfuerzo->otros = ($data['otros'] == 'true') ? 1:0;
        $pesfuerzo->prevalencia =  $prevalencia;
        $pesfuerzo->confusor = ($data['confusor'] == 'true') ? 1:0;
        $pesfuerzo->sensibilidad = $sensibilidad;
        $pesfuerzo->especificidad = $data['especificidad'];
        $pesfuerzo->vpp = $vpp;
        $pesfuerzo->vpn = $vpn;
        $pesfuerzo->pruebaIngreso = ($data['pruebaIngreso'] == 'true') ? 1:0;
        $pesfuerzo->pruebaFinFase2 = ($data['pruebaFin2'] == 'true') ? 1:0;
        $pesfuerzo->pruebaFinFase3 = ($data['pruebaFin3'] == 'true') ? 1:0;
        $pesfuerzo->fechaDeInicio =  $data['pruebaInicio'];
        $pesfuerzo->balke = ($data['balke'] == 'true') ? 1:0;
        $pesfuerzo->bruce = ($data['bruce'] == 'true') ? 1:0;
        $pesfuerzo->naughton = ($data['naughton'] == 'true') ? 1:0;
        $pesfuerzo->tipo_esfuerzo = isset($data['tipoEsfuerzo']) ? $data['tipoEsfuerzo'] : 'cardiaco';
        $pesfuerzo->ciclo = ($data['ciclo'] == 'true') ? 1:0;
        $pesfuerzo->banda = ($data['banda'] == 'true') ? 1:0;
        $pesfuerzo->medicionGases = ($data['medicionGases'] == 'true') ? 1:0;
        $pesfuerzo->fcBasal = $fcBasal;
        $pesfuerzo->tasBasal = $tasBasal;
        $pesfuerzo->tadBasal = $tadBasal;
        $pesfuerzo->dapBasal = $dpBasal;
        $pesfuerzo->fcBorg12 = $fcBorg;
        $pesfuerzo->tasBorg12 = $tasBorg;
        $pesfuerzo->tadBorg12 = $tadBorg;
        $pesfuerzo->dpBorg12 = $dpBorg;
        $pesfuerzo->w_50 = $data['w50'];
        $pesfuerzo->fc_w_50 = $fc50;
        $pesfuerzo->tas_w_50 = $tas50;
        $pesfuerzo->tad_w_50 = $data['tad50'];
        $pesfuerzo->borg_w_50 = $data['borgw50'];
        $pesfuerzo->dp_w_50 = $dp50;
        $pesfuerzo->fcMax = $fcMax;
        $pesfuerzo->tasMax = $tasMax;
        $pesfuerzo->tadMax = $data['tadMax'];
        $pesfuerzo->borgMax = $data['borgMax'];
        $pesfuerzo->tAMax_tAbasal = $tAmax_tbasal;
        $pesfuerzo->tAMax_tAbasal_val = $tAmax_tbasal_value;
        $pesfuerzo->tiempoEsfuerzo = $tiempoEsfuerzo;
        $pesfuerzo->dpMax = $dpMax;
        $pesfuerzo->motivoSuspension = $data['motivoSusp'];
        $pesfuerzo->pba_submax = ($data['pbaSubmax'] == 'true') ? 1:0;
        $pesfuerzo->fc_mayor_50 = ($data['fcMayor85'] == 'true') ? 1:0;
        $pesfuerzo->fc_1er_min = $fc1ermin;
        $pesfuerzo->tas_1er_min = $tas1ermin;
        $pesfuerzo->tad_1er_min = $data['tad1erMin'];
        $pesfuerzo->borg_1er_min = $data['borg1erMin'];
        $pesfuerzo->dp_1er_min = $dp1ermin;
        $pesfuerzo->fc_3er_min = $fc3ermin;
        $pesfuerzo->tas_3er_min = $tas3ermin;
        $pesfuerzo->tad_3er_min =  $data['tad3erMin'];
        $pesfuerzo->borg_3er_min =  $data['borg3erMin'];
        $pesfuerzo->dp_3er_min =  $dp3ermin;
        $pesfuerzo->fc_5to_min =  $data['fc5toMin'];
        $pesfuerzo->tas_5to_min =  $data['tas5toMin'];
        $pesfuerzo->tad_5to_min =   $data['tad5toMin'];
        $pesfuerzo->fc_8vo_min =  $data['fc8voMin'];
        $pesfuerzo->tas_8vo_min =  $data['tas8voMin'];
        $pesfuerzo->tad_8vo_min =  $data['tad8voMin'];
        $pesfuerzo->fc_U_isq =  $fcUisq;
        $pesfuerzo->tas_U_isq =  $tasUisq;
        $pesfuerzo->tad_U_isq =  $data['tadUisq'];
        $pesfuerzo->borg_U_isq =  $data['borgUisq'];
        $pesfuerzo->scoreAngina =  $data['scoreAngina'];
        $pesfuerzo->arritmias =  ($data['arritmias'] == 'true') ? 1:0;
        $pesfuerzo->tipoArritmias =  $data['tipoArritmias'];
        $pesfuerzo->positiva =  ($data['positiva'] == 'true') ? 1:0;
        $pesfuerzo->tipoCambioElectrico =  $data['tipoCambioE'];
        $pesfuerzo->MaxInfra =   $data['maxInfradesnivel'];
        $pesfuerzo->veteranos =  $veteranos;
        $pesfuerzo->duke =  $duke;
        $pesfuerzo->riesgo =  $data['riesgo'];
        $pesfuerzo->u_isq_borg =  $UisqBorf;
        $pesfuerzo->u_isq_dp =  $dpUisq;
        $pesfuerzo->vel_borg_12 =  $data['velBorg'];
        $pesfuerzo->inclin_borg_12 =  $data['inclinBorg'];
        $pesfuerzo->watts_ciclo_b_12 =  $data['wattsCicloBorg'];
        $pesfuerzo->vel_max =  $data['velmax'];
        $pesfuerzo->incl_max =  $data['inclMax'];
        $pesfuerzo->watts_ciclo_max =  $data['wattsCicloMax'];
        $pesfuerzo->vel_um_isq =  $data['velUmIsq'];
        $pesfuerzo->incl_um_isq =  $data['inclUmIsq'];
        $pesfuerzo->watts_ciclo_u_isq =   $data['wattsCicloUmIsq'];
        $pesfuerzo->vo2_max_gases =  $data['vo2MaxGases'];
        $pesfuerzo->vo2_pico_gases =  $data['vo2picoGases'];
        $pesfuerzo->vo2_borg_gases =  $data['vo2BorgGases'];
        $pesfuerzo->r_qmax =  $data['rQmax'];
        $pesfuerzo->umbral_aeer_anaer = $data['umbralAer'];
        $pesfuerzo->po2_teor = $data['poTeorico'];
        $pesfuerzo->vo2_max_percent = isset($data['vo2MaxPercent']) && $data['vo2MaxPercent'] !== '' ? $data['vo2MaxPercent'] : null;
        $pesfuerzo->fc_max_calc =  $fcMaxCalc;
        $pesfuerzo->fc_85 =  $fc85;
        $pesfuerzo->fc_max_alcanzado =  $fcMaxAlcanzado;
        $pesfuerzo->vo2t_mujer =  $vo2tM;
        $pesfuerzo->mets_teorico_mujer =  $metsMT;
        $pesfuerzo->vo2t_varon =  $vo2tV;
        $pesfuerzo->mets_teorico_varon =  $metsVT;
        $pesfuerzo->mets_teorico_general =  $metsTG;
        $pesfuerzo->vo2r_borg_12 =  $vo2rB;
        $pesfuerzo->mets_banda_borg_12 =  $metsBandaBorg;
        $pesfuerzo->mets_ciclo_b12 =  $metsCicloB12;
        $pesfuerzo->mets_gases_b12 =  $metsGasesB12;
        $pesfuerzo->mets_borg_12 =  $metsBorg12;
        $pesfuerzo->vo2r_max =  $vor2Max;
        $pesfuerzo->mets_banda_max =  $metsMaxBanda;
        $pesfuerzo->mets_ciclo_max =  $metsCicloMax;
        $pesfuerzo->mets_gases_max =  $metsGasesMax;
        $pesfuerzo->mets_max =  $metsMax;
        $pesfuerzo->vo2_alcanzado =  $porvo2Alcanzado;
        $pesfuerzo->vo2r_U_isq =  $vo2Uisq;
        $pesfuerzo->mets_banda_U_isq =  $metsUisqBanda;
        $pesfuerzo->mets_ciclo_U_isq =  $metsCicloUisq;
        $pesfuerzo->mets_U_isq =  $metsUisq;
        $pesfuerzo->mvo2 =  $mvo2;
        $pesfuerzo->mvo2_mets =  $mvo2Mets;
        $pesfuerzo->iem =  $iem;
        $pesfuerzo->po2t_mujer =  $po2TM;
        $pesfuerzo->po2t_varon =  $po2TV;
        $pesfuerzo->po2tr =  $po2r;
        $pesfuerzo->rfa_mujer =  $rfaM;
        $pesfuerzo->rfa_varon =  $rfaV;
        $pesfuerzo->resp_presora =  $respPresora;
        $pesfuerzo->indice_tas =  $indiceTasEsfuerzo;
        $pesfuerzo->resp_crono =  $respCrono;
        $pesfuerzo->fcmax_fc1er =  $fcMax_fc1;
        $pesfuerzo->fcmax_fc3er =  $fcMax_fc3;
        $pesfuerzo->fc_rec_1_por =  $fcPorcentajeRecu1;
        $pesfuerzo->fc_rec_3_por =  $fcPorcentajeRecu3;
        $pesfuerzo->pbp3 =  $pbp3;
        $pesfuerzo->pce =  $pce;
        $pesfuerzo->tce =  $tce;
        $pesfuerzo->vel_borg_12_mh =  $velBorg12;
        $pesfuerzo->vel_max_mh =  $velMax;
        $pesfuerzo->vel_u_isq_mh =  $velUisq;
        $pesfuerzo->ch_borg_12 =  $chBorg;
        $pesfuerzo->ch_max =  $chMax;
        $pesfuerzo->ch_u_isq =  $chUisq;
        $pesfuerzo->cv_borg_12 =  $cvBorg;
        $pesfuerzo->cv_max =  $cvMax;
        $pesfuerzo->cv_u_isq =  $cvUisq;
        $pesfuerzo->conclusiones =  $data['comentarios'];
        $pesfuerzo->fecha =  $data['fecha'];
        $pesfuerzo->recup_tas = $this->safeDivide($tas3ermin, $tas1ermin, 0);
        $pesfuerzo->tipo_exp = 1;
        $pesfuerzo->clinica_id = $user->clinica_efectiva_id;
        
        $pesfuerzo->save();

        return response()->json($pesfuerzo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Esfuerzo  $esfuerzo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Esfuerzo $esfuerzo)
    {
        $user = Auth::user();
        
        // Solo admin o superadmin pueden eliminar
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes'], 403);
        }
        
        // Verificar que el esfuerzo pertenece a la misma clínica
        $paciente = $esfuerzo->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente de esfuerzo'], 403);
        }
        
        $esfuerzo->delete();
        return response()->json(['message' => 'Expediente de esfuerzo eliminado exitosamente'], 204);
    }

}
