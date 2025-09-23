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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // Los administradores solo ven sus propios esfuerzos
            $esfuerzos = Esfuerzo::where('user_id', $user->id)->with('paciente')->get();
        } else {
            // Los usuarios no admin solo ven los esfuerzos a los que tienen acceso
            $accessibleEsfuerzos = $user->getAccessibleResources('esfuerzos');
            $esfuerzoIds = $accessibleEsfuerzos->pluck('permissionable_id')->toArray();
            $esfuerzos = Esfuerzo::whereIn('id', $esfuerzoIds)->with('paciente')->get();
        }
        
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

        // Verificar permisos de escritura para usuarios no admin
        $permission = null;
        if (!$user->isAdmin()) {
            $permission = $user->permissions()->where('can_write', true)->first();
            if (!$permission) {
                return response()->json(['error' => 'No tienes permisos para crear expedientes de esfuerzo'], 403);
            }
        }


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

            // Usar la misma lógica para el paciente
            if (!$user->isAdmin()) {
                $nuevoPaciente->user_id = $permission->granted_by;
            } else {
                $nuevoPaciente->user_id = $user->id;
            }


        }else{
            $id = intval($request->input('id'));
            $nuevoPaciente = Paciente::find($id);

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

        $fcMaxAlcanzado = ($fcMax*100)/$fcMaxCalc;

        $vo2tM = 42.3-(0.356*$nuevoPaciente->edad);
        $metsMT = $vo2tM/3.5;

        $vo2tV= 57.8-(0.445*$nuevoPaciente->edad);
        $metsVT = $vo2tV/3.5;

        $metsTG = ($metsVT*$nuevoPaciente->genero)+($metsMT*(1-$nuevoPaciente->genero));

        $banda = ($data['banda'] === 'true')? 1 : 0;
        $ciclo =($data['ciclo'] === 'true')? 1 : 0;

        $mediconGases = ($data['medicionGases'] === 'true')? 1 : 0;

        $metsCicloB12 = $ciclo*($data['wattsCicloBorg']/16);
        $metsGasesB12 = ($data['vo2BorgGases']/3.5)*$mediconGases;

        $metsCicloMax = $ciclo*($data['wattsCicloMax']/16);
        $metsGasesMax = ($data['vo2picoGases']/3.5)*$mediconGases;


        $metsCicloUisq = $ciclo*($data['wattsCicloUmIsq']/16);

        $mvo2 = ($dpMax*0.14*0.01)-6.3;

        $mvo2Mets = ($mvo2/3.5)*0.1;
        $po2TM = ($vo2tM*$nuevoPaciente->peso)/(220-$nuevoPaciente->edad);

        $po2TV = ($vo2tV*$nuevoPaciente->peso)/(220-$nuevoPaciente->edad);

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
        $po2r = (($metsMax*3.5)*$nuevoPaciente->peso)/$fcMax;
        $porvo2Alcanzado = (($metsMax*3.5)/($metsTG*3.5))*100;
        $duke = ($metsMax+1.69)-(5*$maxInfra)-(4*$data['scoreAngina']);
        $veteranos = 5*($icc)+($maxInfra)+($tAmax_tbasal_value)-$metsMax;
        $vo2Uisq = $chUisq + $cvUisq;
        $metsUisqBanda = $banda*($vo2Uisq/3.5);
        $metsUisq = $metsUisqBanda + $metsCicloUisq;
        $rfaM = ((($vo2tM/3.5)-$metsMax)/$metsMax)*100;
        $rfaV = ((($vo2tV/3.5)-$metsMax)/$metsMax)*100;
        $respPresora = ($tasMax-$tasBasal)/$metsMax;
        $indiceTasEsfuerzo = $tasMax/$tasBasal;
        $respCrono = ($fcMax-$fcBasal)/$metsMax;
        $fcMax_fc1 = $fcMax-$fc1ermin;
        $fcMax_fc3 = $fcMax-$fc3ermin;
        $fcPorcentajeRecu1 = ((($fc1ermin*100)/$fcMax)-100)*-1;
        $fcPorcentajeRecu3 = ((($fc3ermin*100)/$fcMax)-100)*-1;
        $pbp3 = $tas3ermin/$tasMax;
        $pce = $porvo2Alcanzado*$tasMax;
        $tce = $pce/$fcMax;
        $iem = (($mvo2/3.5)/($metsMax)*10);
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
        $pesfuerzo->recup_tas = $tas3ermin/$tas1ermin;

        // Asignar user_id según el tipo de usuario
        if (!$user->isAdmin()) {
            // Usar el user_id del admin que otorgó los permisos
            $pesfuerzo->user_id = $permission->granted_by;
        } else {
            // Si es admin, usar su propio user_id
            $pesfuerzo->user_id = $user->id;
        }
        $pesfuerzo->tipo_exp = 1;


        if($nuevoPaciente != null){
            $nuevoPaciente->save();
        }

        
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
        
        // Los administradores solo pueden ver sus propios esfuerzos
        if ($user->isAdmin() && $esfuerzo->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para ver este expediente de esfuerzo'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($esfuerzo, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver este expediente de esfuerzo'], 403);
        }

        // Verificar si se encontró el esfuerzo
        if (!$esfuerzo) {
            return response()->json(['error' => 'Expediente de esfuerzo no encontrado'], 404);
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
        
        // Los administradores solo pueden editar sus propios esfuerzos
        if ($user->isAdmin() && $esfuerzo->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para editar este expediente de esfuerzo'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($esfuerzo, 'can_edit')) {
            return response()->json(['error' => 'No tienes permisos para editar este expediente de esfuerzo'], 403);
        }

        $esfuerzoFind = Esfuerzo::find($request->id);
        $nuevoPaciente = Paciente::find($request->paciente_id);

        $prevalencia = 0.98;
        $sensibilidad = $request['confusor'] === 'true'?.64:.68;
        $especificidad = $request['especificidad'];

        $vpp = ($sensibilidad*$prevalencia)/(($sensibilidad*$prevalencia)+(1-$especificidad)*(1-$prevalencia));
        $vpn = ($especificidad*$prevalencia)/(($especificidad*$prevalencia)+(1-$sensibilidad)*(1-$prevalencia));

        $fcBasal = $request['fcBasal'];
        $tasBasal = $request['tasBasal'];
        $tadBasal = $request['tadBasal'];
        $dpBasal = $fcBasal*$tasBasal;

        $fcBorg =  $request['fcBorg12'];
        $tasBorg = $request['tasBorg12'];
        $tadBorg = $request['tadBorg12'];
        $dpBorg =$fcBorg*$tasBorg;

        $fc50 = $request['fc_w_50'];
        $tas50 = $request['tas_w_50'];
        $dp50 = $fc50*$tas50; 

        $tasMax = $request['tasMax'];
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

        $fcMax = $request['fcMax'];
        $dpMax = $fcMax*$tasMax;

        $fc1ermin = $request['fc_1er_min'];
        $tas1ermin = $request['tas_1er_min'];
        $dp1ermin = $fc1ermin*$tas1ermin;


        $fc3ermin = $request['fc_3er_min'];
        $tas3ermin = $request['tas_3er_min'];
        $dp3ermin = $fc3ermin*$tas3ermin;

        $icc = ($request['icc'] == 'true')? 1 : 0;

        $maxInfra = $request['MaxInfra'];

        $UisqBorf = $request['u_isq_borg'];

        $fcUisq = $request['fc_U_isq'];
        $tasUisq = $request['tas_U_isq'];
        $dpUisq = $fcUisq*$tasUisq;

        $fcMaxCalc = 220-$nuevoPaciente->edad;


        $fc85 = $fcMaxCalc*0.85;

        $fcMaxAlcanzado = ($fcMax*100)/$fcMaxCalc;

        $vo2tM = 42.3-(0.356*$nuevoPaciente->edad);
        $metsMT = $vo2tM/3.5;

        $vo2tV= 57.8-(0.445*$nuevoPaciente->edad);
        $metsVT = $vo2tV/3.5;

        $metsTG = ($metsVT*$nuevoPaciente->genero)+($metsMT*(1-$nuevoPaciente->genero));

        $banda = ($request['banda'] === 1 ||$request['banda'] === 'true' )? 1 : 0;
        $ciclo =($request['ciclo'] === 1 ||$request['ciclo'] === 'true' )? 1 : 0;

        $mediconGases = ($request['medicionGases'] === 'true'|| $request['medicionGases'] === 1)? 1 : 0;

        $metsCicloB12 = $ciclo*($request['watts_ciclo_b_12']/16);
        $metsGasesB12 = ($request['vo2_borg_gases']/3.5)*$mediconGases;

        $metsCicloMax = $ciclo*($request['watts_ciclo_max']/16);
        $metsGasesMax = ($request['vo2_pico_gases']/3.5)*$mediconGases;


        $metsCicloUisq = $ciclo*($request['watts_ciclo_b_12']/16);

        $mvo2 = ($dpMax*0.14*0.01)-6.3;

        $mvo2Mets = ($mvo2/3.5)*0.1;
        $po2TM = ($vo2tM*$nuevoPaciente->peso)/(220-$nuevoPaciente->edad);

        $po2TV = ($vo2tV*$nuevoPaciente->peso)/(220-$nuevoPaciente->edad);

        $velBorg12 = ($request['vel_borg_12']*1609)/60;
      $velMax =  ($request['vel_max']*1609)/60;
       $velUisq =  ($request['vel_um_isq']*1609)/60;
        $chBorg = ($velBorg12*0.1)+3.5;
        $chMax = ($velMax*0.1)+3.5;
        $chUisq = ($velUisq*0.1)+3.5;
        $cvBorg = ($velBorg12*1.8)*($request['inclin_borg_12']*0.01);
        $cvMax = ($velMax*1.8)*($request['incl_max']*0.01);
        $cvUisq = ($velUisq*1.8)*($request['incl_um_isq']*0.01);        
        $vor2Max = $chMax+$cvMax;
        $metsMaxBanda = $banda*($vor2Max/3.5);
         $metsMax = $metsMaxBanda + $metsCicloMax + $metsGasesMax;
         $po2r = (($metsMax*3.5)*$nuevoPaciente->peso)/$fcMax;
        $porvo2Alcanzado = (($metsMax*3.5)/($metsTG*3.5))*100;
        $duke = ($metsMax+1.69)-(5*$maxInfra)-(4*$request['scoreAngina']);
        $veteranos = 5*($icc)+($maxInfra)+($tAmax_tbasal_value)-$metsMax;
        $vo2Uisq = $chUisq + $cvUisq;
        $metsUisqBanda = $banda*($vo2Uisq/3.5);
       
        $metsUisq = $metsUisqBanda + $metsCicloUisq;
        $rfaM = ((($vo2tM/3.5)-$metsMax)/$metsMax)*100;
        $rfaV = ((($vo2tV/3.5)-$metsMax)/$metsMax)*100;
        $respPresora = ($tasMax-$tasBasal)/$metsMax;
        $indiceTasEsfuerzo = $tasMax/$tasBasal;
        $respCrono = ($fcMax-$fcBasal)/$metsMax;
        $fcMax_fc1 = $fcMax-$fc1ermin;
        $fcMax_fc3 = $fcMax-$fc3ermin;
        $fcPorcentajeRecu1 = ((($fc1ermin*100)/$fcMax)-100)*-1;
        $fcPorcentajeRecu3 = ((($fc3ermin*100)/$fcMax)-100)*-1;
        $pbp3 = $tas3ermin/$tasMax;
        $pce = $porvo2Alcanzado*$tasMax;
        $tce = $pce/$fcMax;
        $iem = (($mvo2/3.5)/($metsMax*10));
        $vo2rB = $chBorg+$cvBorg;
        $metsBandaBorg = $banda*(($chBorg+$cvBorg)/3.5);
        $metsBorg12 = $metsBandaBorg + $metsCicloB12 + $metsGasesB12;
        $tiempoEsfuerzo = $metsMax+1.692;




        $esfuerzoFind->numPrueba = $request['numPrueba'];
        $esfuerzoFind->icc =  $icc;
        $esfuerzoFind->FEVI =  $request['FEVI'];
        $esfuerzoFind->metodo = $request['metodo'];
        $esfuerzoFind->disfuncionDias = ($request['disfuncionDias'] == 'true'|| $request['disfuncionDias'] === 1) ? 1:0;
        $esfuerzoFind->nyha = $request['nyha'];
        $esfuerzoFind->ccs = $request['ccs'];
        $esfuerzoFind->betabloqueador = ($request['betabloqueador'] == 'true'||$request['betabloqueador'] === 1) ? 1:0;
        $esfuerzoFind->iecas = ($request['iecas'] == 'true'|| $request['iecas'] === 1) ? 1:0;
        $esfuerzoFind->nitratos = ($request['nitratos'] == 'true'||$request['nitratos'] === 1) ? 1:0;
        $esfuerzoFind->digoxina = ($request['digoxina'] == 'true'||$request['digoxina'] === 1) ? 1:0;
        $esfuerzoFind->calcioAntag = ($request['calcioAntag'] == 'true' ||$request['calcioAntag'] === 1) ? 1:0;
        $esfuerzoFind->antirritmicos = ($request['antirritmicos'] == 'true'||$request['antirritmicos'] === 1) ? 1:0;
        $esfuerzoFind->hipolipemiantes = ($request['hipolipemiantes'] == 'true' ||$request['hipolipemiantes'] === 1) ? 1:0;
        $esfuerzoFind->diureticos = ($request['diureticos'] == 'true' ||$request['diureticos'] === 1) ? 1:0;
        $esfuerzoFind->aldactone = ($request['aldactone'] == 'true' ||$request['aldactone'] === 1) ? 1:0;
        $esfuerzoFind->antiagregante = ($request['antiagregante'] == 'true' ||$request['antiagregante'] === 1) ? 1:0;
        $esfuerzoFind->otros = ( $request['otros'] == 'true' ||$request['otros'] === 1) ? 1:0;
        $esfuerzoFind->prevalencia =  $prevalencia;
        $esfuerzoFind->confusor = ($request['confusor'] == 'true' ||$request['confusor'] === 1) ? 1:0;
        $esfuerzoFind->sensibilidad = $sensibilidad;
        $esfuerzoFind->especificidad = $request['especificidad'];
        $esfuerzoFind->vpp = $vpp;
        $esfuerzoFind->vpn = $vpn;
        $esfuerzoFind->pruebaIngreso = ($request['pruebaIngreso']== 'true' ||$request['pruebaIngreso'] === 1) ? 1:0;
        $esfuerzoFind->pruebaFinFase2 = ( $request['pruebaFinFase2']== 'true' ||$request['pruebaFinFase2'] === 1) ? 1:0;
        $esfuerzoFind->pruebaFinFase3 = ( $request['pruebaFinFase3']== 'true' ||$request['pruebaFinFase3'] === 1) ? 1:0;
        $esfuerzoFind->fechaDeInicio =  $request['fechaDeInicio'];
        $esfuerzoFind->balke = ($request['balke'] == 'true' ||$request['balke'] === 1) ? 1:0;
        $esfuerzoFind->bruce = ($request['bruce'] == 'true' ||$request['bruce'] === 1) ? 1:0;
        $esfuerzoFind->ciclo = ($request['ciclo'] == 'true' ||$request['ciclo'] === 1) ? 1:0;
        $esfuerzoFind->banda = ($request['banda'] == 'true' ||$request['banda'] === 1) ? 1:0;
        $esfuerzoFind->medicionGases = ($request['medicionGases'] == 'true' ||$request['medicionGases'] === 1) ? 1:0;
        $esfuerzoFind->fcBasal = $fcBasal;
        $esfuerzoFind->tasBasal = $tasBasal;
        $esfuerzoFind->tadBasal = $tadBasal;
        $esfuerzoFind->dapBasal = $dpBasal;
        $esfuerzoFind->fcBorg12 = $fcBorg;
        $esfuerzoFind->tasBorg12 = $tasBorg;
        $esfuerzoFind->tadBorg12 = $tadBorg;
        $esfuerzoFind->dpBorg12 = $dpBorg;
        $esfuerzoFind->w_50 = $request['w_50'];
        $esfuerzoFind->fc_w_50 = $fc50;
        $esfuerzoFind->tas_w_50 = $tas50;
        $esfuerzoFind->tad_w_50 = $request['tad_w_50'];
        $esfuerzoFind->borg_w_50 = $request['borg_w_50'];
        $esfuerzoFind->dp_w_50 = $dp50;
        $esfuerzoFind->fcMax = $fcMax;
        $esfuerzoFind->tasMax = $tasMax;
        $esfuerzoFind->tadMax = $request['tadMax'];
        $esfuerzoFind->borgMax = $request['borgMax'];
        $esfuerzoFind->tAMax_tAbasal = $tAmax_tbasal;
        $esfuerzoFind->tAMax_tAbasal_val = $tAmax_tbasal_value;
        $esfuerzoFind->tiempoEsfuerzo = $tiempoEsfuerzo;
        $esfuerzoFind->dpMax = $dpMax;
        $esfuerzoFind->motivoSuspension = $request['motivoSuspension'];
        $esfuerzoFind->pba_submax = ($request['pba_submax'] == 'true'||$request['pba_submax'] === 1) ? 1:0;
        $esfuerzoFind->fc_mayor_50 = ($request['fc_mayor_50'] == 'true' ||$request['fc_mayor_50'] === 1) ? 1:0;
        $esfuerzoFind->fc_1er_min = $fc1ermin;
        $esfuerzoFind->tas_1er_min = $tas1ermin;
        $esfuerzoFind->tad_1er_min = $request['tad_1er_min'];
        $esfuerzoFind->borg_1er_min = $request['borg_1er_min'];
        $esfuerzoFind->dp_1er_min = $dp1ermin;
        $esfuerzoFind->fc_3er_min = $fc3ermin;
        $esfuerzoFind->tas_3er_min = $tas3ermin;
        $esfuerzoFind->tad_3er_min =  $request['tad_3er_min'];
        $esfuerzoFind->borg_3er_min =  $request['borg_3er_min'];
        $esfuerzoFind->dp_3er_min =  $dp3ermin;
        $esfuerzoFind->fc_5to_min =  $request['fc_5to_min'];
        $esfuerzoFind->tas_5to_min =  $request['tas_5to_min'];
        $esfuerzoFind->tad_5to_min =   $request['tad_5to_min'];
        $esfuerzoFind->fc_8vo_min =  $request['fc_8vo_min'];
        $esfuerzoFind->tas_8vo_min =  $request['tas_8vo_min'];
        $esfuerzoFind->tad_8vo_min =  $request['tad_8vo_min'];
        $esfuerzoFind->fc_U_isq =  $fcUisq;
        $esfuerzoFind->tas_U_isq =  $tasUisq;
        $esfuerzoFind->tad_U_isq =  $request['tad_U_isq'];
        $esfuerzoFind->borg_U_isq =  $request['borg_U_isq'];
        $esfuerzoFind->scoreAngina =  $request['scoreAngina'];
        $esfuerzoFind->arritmias =  ($request['arritmias'] == 'true'||$request['arritmias'] === 1) ? 1:0;
        $esfuerzoFind->tipoArritmias =  $request['tipoArritmias'];
        $esfuerzoFind->positiva =  ($request['positiva'] == 'true'||$request['positiva'] === 1) ? 1:0;
        $esfuerzoFind->tipoCambioElectrico =  $request['tipoCambioElectrico'];
        $esfuerzoFind->MaxInfra =   $request['MaxInfra'];
        $esfuerzoFind->veteranos =  $veteranos;
        $esfuerzoFind->duke =  $duke;
        $esfuerzoFind->riesgo =  $request['riesgo'];
        $esfuerzoFind->u_isq_borg =  $UisqBorf;
        $esfuerzoFind->u_isq_dp =  $dpUisq;
        $esfuerzoFind->vel_borg_12 =  $request['vel_borg_12'];
        $esfuerzoFind->inclin_borg_12 =  $request['inclin_borg_12'];
        $esfuerzoFind->watts_ciclo_b_12 =  $request['watts_ciclo_b_12'];
        $esfuerzoFind->vel_max =  $request['vel_max'];
        $esfuerzoFind->incl_max =  $request['incl_max'];
        $esfuerzoFind->watts_ciclo_max =  $request['watts_ciclo_max'];
        $esfuerzoFind->vel_um_isq =  $request['vel_um_isq'];
        $esfuerzoFind->incl_um_isq =  $request['incl_um_isq'];
        $esfuerzoFind->watts_ciclo_u_isq =   $request['watts_ciclo_u_isq'];
        $esfuerzoFind->vo2_max_gases =  $request['vo2_max_gases'];
        $esfuerzoFind->vo2_pico_gases =  $request['vo2_pico_gases'];
        $esfuerzoFind->vo2_borg_gases =  $request['vo2_borg_gases'];
        $esfuerzoFind->r_qmax =  $request['r_qmax'];
        $esfuerzoFind->umbral_aeer_anaer = $request['umbral_aeer_anaer'];
        $esfuerzoFind->po2_teor = $request['po2_teor'];
        $esfuerzoFind->fc_max_calc =  $fcMaxCalc;
        $esfuerzoFind->fc_85 =  $fc85;
        $esfuerzoFind->fc_max_alcanzado =  $fcMaxAlcanzado;
        $esfuerzoFind->vo2t_mujer =  $vo2tM;
        $esfuerzoFind->mets_teorico_mujer =  $metsMT;
        $esfuerzoFind->vo2t_varon =  $vo2tV;
        $esfuerzoFind->mets_teorico_varon =  $metsVT;
        $esfuerzoFind->mets_teorico_general =  $metsTG;
        $esfuerzoFind->vo2r_borg_12 =  $vo2rB;
        $esfuerzoFind->mets_banda_borg_12 =  $metsBandaBorg;
        $esfuerzoFind->mets_ciclo_b12 =  $metsCicloB12;
        $esfuerzoFind->mets_gases_b12 =  $metsGasesB12;
        $esfuerzoFind->mets_borg_12 =  $metsBorg12;
        $esfuerzoFind->vo2r_max =  $vor2Max;
        $esfuerzoFind->mets_banda_max =  $metsMaxBanda;
        $esfuerzoFind->mets_ciclo_max =  $metsCicloMax;
        $esfuerzoFind->mets_gases_max =  $metsGasesMax;
        $esfuerzoFind->mets_max =  $metsMax;
        $esfuerzoFind->vo2_alcanzado =  $porvo2Alcanzado;
        $esfuerzoFind->vo2r_U_isq =  $vo2Uisq;
        $esfuerzoFind->mets_banda_U_isq =  $metsUisqBanda;
        $esfuerzoFind->mets_ciclo_U_isq =  $metsCicloUisq;
        $esfuerzoFind->mets_U_isq =  $metsUisq;
        $esfuerzoFind->mvo2 =  $mvo2;
        $esfuerzoFind->mvo2_mets =  $mvo2Mets;
        $esfuerzoFind->iem =  $iem;
        $esfuerzoFind->po2t_mujer =  $po2TM;
        $esfuerzoFind->po2t_varon =  $po2TV;
        $esfuerzoFind->po2tr =  $po2r;
        $esfuerzoFind->rfa_mujer =  $rfaM;
        $esfuerzoFind->rfa_varon =  $rfaV;
        $esfuerzoFind->resp_presora =  $respPresora;
        $esfuerzoFind->indice_tas =  $indiceTasEsfuerzo;
        $esfuerzoFind->resp_crono =  $respCrono;
        $esfuerzoFind->fcmax_fc1er =  $fcMax_fc1;
        $esfuerzoFind->fcmax_fc3er =  $fcMax_fc3;
        $esfuerzoFind->fc_rec_1_por =  $fcPorcentajeRecu1;
        $esfuerzoFind->fc_rec_3_por =  $fcPorcentajeRecu3;
        $esfuerzoFind->pbp3 =  $pbp3;
        $esfuerzoFind->pce =  $pce;
        $esfuerzoFind->tce =  $tce;
        $esfuerzoFind->vel_borg_12_mh =  $velBorg12;
        $esfuerzoFind->vel_max_mh =  $velMax;
        $esfuerzoFind->vel_u_isq_mh =  $velUisq;
        $esfuerzoFind->ch_borg_12 =  $chBorg;
        $esfuerzoFind->ch_max =  $chMax;
        $esfuerzoFind->ch_u_isq =  $chUisq;
        $esfuerzoFind->cv_borg_12 =  $cvBorg;
        $esfuerzoFind->cv_max =  $cvMax;
        $esfuerzoFind->cv_u_isq =  $cvUisq;
        $esfuerzoFind->conclusiones =  $request['conclusiones'];
        $esfuerzoFind->fecha =  $request['fecha'];
        $esfuerzoFind->recup_tas = $tas3ermin/$tas1ermin;
        $esfuerzoFind->tipo_exp = 1;
        
        $esfuerzoFind->save();

        return response(' ', 204);
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
        
        // Los administradores solo pueden eliminar sus propios esfuerzos
        if ($user->isAdmin() && $esfuerzo->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este expediente de esfuerzo'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($esfuerzo, 'can_delete')) {
            return response()->json(['error' => 'No tienes permisos para eliminar este expediente de esfuerzo'], 403);
        }
        
        $esfuerzo->delete();
        return response()->json(['message' => 'Expediente de esfuerzo eliminado exitosamente'], 204);
    }

}
