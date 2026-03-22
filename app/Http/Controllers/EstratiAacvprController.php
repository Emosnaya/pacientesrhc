<?php

namespace App\Http\Controllers;

use App\Models\EstratiAacvpr;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstratiAacvprController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Todos los usuarios pueden ver las estratificaciones AACVPR de su clínica
        $estratificaciones = EstratiAacvpr::whereHas('paciente', function($query) use ($user) {
            $query->where('clinica_id', $user->clinica_id);
        })->with('paciente')->get();
        
        return response()->json($estratificaciones);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $estratificacion = new EstratiAacvpr();
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
            $nuevoPaciente->clinica_id = $user->clinica_id;
            $nuevoPaciente->sucursal_id = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $nuevoPaciente->save();
        } else {
            $id = intval($request->input('id'));
            $nuevoPaciente = Paciente::find($id);
            
            // Verificar que el paciente pertenece a la misma clínica
            if ($nuevoPaciente->clinica_id !== $user->clinica_id) {
                return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
            }
        }

        // Fechas
        $estratificacion->fecha_estratificacion = $data['fecha_estratificacion'] ?? null;
        $estratificacion->primeravez_rhc = $data['primeravez_rhc'] ?? null;
        $estratificacion->pe_fecha = $data['pe_fecha'] ?? null;

        // Datos de Prueba de Esfuerzo
        $estratificacion->fc_basal = $data['fc_basal'] ?? null;
        $estratificacion->fc_maxima = $data['fc_max'] ?? null;
        $estratificacion->fc_borg_12 = $data['fc_borg_12'] ?? null;
        $estratificacion->dp_borg_12 = $data['dp_borg_12'] ?? null;
        $estratificacion->mets_borg_12 = $data['mets_borg_12'] ?? null;
        $estratificacion->carga_maxima = $data['carga_maxima'] ?? null;
        $estratificacion->tolerancia_esfuerzo = $data['tolerancia_esfuerzo'] ?? null;

        // Riesgo Alto
        $estratificacion->alto_fevi_disminuida = ($data['alto_fevi_disminuida'] ?? false) == 'true' || ($data['alto_fevi_disminuida'] ?? false) === true;
        $estratificacion->alto_sintomas_reposo = ($data['alto_sintomas_reposo'] ?? false) == 'true' || ($data['alto_sintomas_reposo'] ?? false) === true;
        $estratificacion->alto_isquemia_baja_intensidad = ($data['alto_isquemia_baja_intensidad'] ?? false) == 'true' || ($data['alto_isquemia_baja_intensidad'] ?? false) === true;
        $estratificacion->alto_arritmias_ventriculares = ($data['alto_arritmias_ventriculares'] ?? false) == 'true' || ($data['alto_arritmias_ventriculares'] ?? false) === true;
        $estratificacion->alto_im_complicado = ($data['alto_im_complicado'] ?? false) == 'true' || ($data['alto_im_complicado'] ?? false) === true;
        $estratificacion->alto_capacidad_menor_5mets = ($data['alto_capacidad_menor_5mets'] ?? false) == 'true' || ($data['alto_capacidad_menor_5mets'] ?? false) === true;
        $estratificacion->alto_hemodinamica_anormal = ($data['alto_hemodinamica_anormal'] ?? false) == 'true' || ($data['alto_hemodinamica_anormal'] ?? false) === true;
        $estratificacion->alto_paro_cardiaco = ($data['alto_paro_cardiaco'] ?? false) == 'true' || ($data['alto_paro_cardiaco'] ?? false) === true;
        $estratificacion->alto_enfermedad_compleja = ($data['alto_enfermedad_compleja'] ?? false) == 'true' || ($data['alto_enfermedad_compleja'] ?? false) === true;

        // Riesgo Moderado
        $estratificacion->moderado_fevi_moderada = ($data['moderado_fevi_moderada'] ?? false) == 'true' || ($data['moderado_fevi_moderada'] ?? false) === true;
        $estratificacion->moderado_sintomas_moderados = ($data['moderado_sintomas_moderados'] ?? false) == 'true' || ($data['moderado_sintomas_moderados'] ?? false) === true;
        $estratificacion->moderado_isquemia_moderada = ($data['moderado_isquemia_moderada'] ?? false) == 'true' || ($data['moderado_isquemia_moderada'] ?? false) === true;
        $estratificacion->moderado_capacidad_5_7mets = ($data['moderado_capacidad_5_7mets'] ?? false) == 'true' || ($data['moderado_capacidad_5_7mets'] ?? false) === true;
        $estratificacion->moderado_sin_automonitoreo = ($data['moderado_sin_automonitoreo'] ?? false) == 'true' || ($data['moderado_sin_automonitoreo'] ?? false) === true;

        // Riesgo Bajo
        $estratificacion->bajo_fevi_preservada = ($data['bajo_fevi_preservada'] ?? false) == 'true' || ($data['bajo_fevi_preservada'] ?? false) === true;
        $estratificacion->bajo_sin_sintomas = ($data['bajo_sin_sintomas'] ?? false) == 'true' || ($data['bajo_sin_sintomas'] ?? false) === true;
        $estratificacion->bajo_sin_isquemia = ($data['bajo_sin_isquemia'] ?? false) == 'true' || ($data['bajo_sin_isquemia'] ?? false) === true;
        $estratificacion->bajo_capacidad_mayor_7mets = ($data['bajo_capacidad_mayor_7mets'] ?? false) == 'true' || ($data['bajo_capacidad_mayor_7mets'] ?? false) === true;
        $estratificacion->bajo_sin_arritmias = ($data['bajo_sin_arritmias'] ?? false) == 'true' || ($data['bajo_sin_arritmias'] ?? false) === true;
        $estratificacion->bajo_im_no_complicado = ($data['bajo_im_no_complicado'] ?? false) == 'true' || ($data['bajo_im_no_complicado'] ?? false) === true;
        $estratificacion->bajo_hemodinamica_normal = ($data['bajo_hemodinamica_normal'] ?? false) == 'true' || ($data['bajo_hemodinamica_normal'] ?? false) === true;
        $estratificacion->bajo_automonitoreo_adecuado = ($data['bajo_automonitoreo_adecuado'] ?? false) == 'true' || ($data['bajo_automonitoreo_adecuado'] ?? false) === true;

        // Hallazgos clínicos
        $estratificacion->hallazgo_fevi = $data['hallazgo_fevi'] ?? null;
        $estratificacion->hallazgo_mets = $data['hallazgo_mets'] ?? null;
        $estratificacion->hallazgo_sintomas = $data['hallazgo_sintomas'] ?? null;
        $estratificacion->hallazgo_isquemia = $data['hallazgo_isquemia'] ?? null;
        $estratificacion->hallazgo_arritmias = $data['hallazgo_arritmias'] ?? null;
        $estratificacion->hallazgo_hemodinamica = $data['hallazgo_hemodinamica'] ?? null;
        $estratificacion->hallazgo_procedimiento = $data['hallazgo_procedimiento'] ?? null;
        $estratificacion->hallazgo_coronaria = $data['hallazgo_coronaria'] ?? null;

        // Riesgo Global
        $estratificacion->riesgo_global = $data['riesgo_global'] ?? 'bajo';

        // Parámetros Iniciales
        $estratificacion->grupo = $data['grupo'] ?? 'a';
        $estratificacion->semanas = intval($data['semanas'] ?? 1);
        $estratificacion->sesiones = isset($data['sesiones']) ? intval($data['sesiones']) : null;
        $estratificacion->borg = intval($data['borg'] ?? 12);
        $estratificacion->fc_diana_metodo = $data['fc_diana_metodo'] ?? 'Bo';
        $estratificacion->fc_diana_str = $data['fc_diana_str'] ?? null;
        $estratificacion->dp_diana = $data['dp_diana'] ?? null;
        $estratificacion->fc_diana_manual = $data['fc_diana_manual'] ?? null;
        $estratificacion->carga_inicial = $data['carga_inicial'] ?? null;
        
        // Calcular FC Diana automáticamente
        $fcBasal = floatval($data['fc_basal'] ?? 0);
        $fcMax = floatval($data['fc_max'] ?? 0);
        $fcBorg12 = floatval($data['fc_borg_12'] ?? 0);
        $dpBorg12 = floatval($data['dp_borg_12'] ?? 0);
        
        if ($fcMax > 0 && $fcBasal > 0) {
            $karvonen = (($fcMax - $fcBasal) * 0.7) + $fcBasal;
            $blackburn = ($fcMax * 0.8);
            $narita = (78.4 + ((0.76 * $fcBasal) - (0.27 * $nuevoPaciente->edad)));

            switch ($data['fc_diana_metodo'] ?? 'Bo') {
                case 'K':
                    $fcDiana = $karvonen;
                    break;
                case 'BI':
                    $fcDiana = $blackburn;
                    break;
                case 'N':
                    $fcDiana = $narita;
                    break;
                case 'UISQ':
                case 'Manual':
                    $fcDiana = $data['fc_diana_manual'] ?? null;
                    break;
                default: // 'Bo' - Borg
                    $fcDiana = $fcBorg12;
            }
            $estratificacion->fc_diana = $fcDiana;
            
            // Calcular DP Diana automáticamente
            if ($fcDiana > 0) {
                $estratificacion->dp_diana = $dpBorg12;
            }
        } else {
            $estratificacion->fc_diana = $data['fc_diana_manual'] ?? null;
            $estratificacion->dp_diana = $data['dp_diana'] ?? null;
        }

        // Comentarios
        $estratificacion->comentarios = $data['comentarios'] ?? null;

        // Asignar el user_id del dueño del paciente y clinica_id
        $estratificacion->user_id = $nuevoPaciente->user_id;
        $estratificacion->paciente_id = $nuevoPaciente->id;
        $estratificacion->clinica_id = $nuevoPaciente->clinica_id;

        $estratificacion->save();

        return response()->json("Guardado correctamente");
    }

    /**
     * Display the specified resource.
     */
    public function show(EstratiAacvpr $estrati_aacvpr)
    {
        $user = Auth::user();
        
        // Verificar que la estratificación pertenece a la misma clínica
        $paciente = $estrati_aacvpr->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        return response()->json($estrati_aacvpr->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EstratiAacvpr $estrati_aacvpr)
    {
        $user = Auth::user();
        
        // Verificar que la estratificación pertenece a la misma clínica
        $paciente = $estrati_aacvpr->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        $expediente = EstratiAacvpr::find($request->id);
        $paciente = Paciente::find($request->paciente_id);

        // Fechas
        $expediente->fecha_estratificacion = $request['fecha_estratificacion'];
        $expediente->primeravez_rhc = $request['primeravez_rhc'];
        $expediente->pe_fecha = $request['pe_fecha'];

        // Datos de Prueba de Esfuerzo
        $expediente->fc_basal = $request['fc_basal'] ?? null;
        $expediente->fc_maxima = $request['fc_max'] ?? null;
        $expediente->fc_borg_12 = $request['fc_borg_12'] ?? null;
        $expediente->dp_borg_12 = $request['dp_borg_12'] ?? null;
        $expediente->mets_borg_12 = $request['mets_borg_12'] ?? null;
        $expediente->carga_maxima = $request['carga_maxima'] ?? null;
        $expediente->tolerancia_esfuerzo = $request['tolerancia_esfuerzo'] ?? null;

        // Riesgo Alto
        $expediente->alto_fevi_disminuida = $this->toBool($request['alto_fevi_disminuida']);
        $expediente->alto_sintomas_reposo = $this->toBool($request['alto_sintomas_reposo']);
        $expediente->alto_isquemia_baja_intensidad = $this->toBool($request['alto_isquemia_baja_intensidad']);
        $expediente->alto_arritmias_ventriculares = $this->toBool($request['alto_arritmias_ventriculares']);
        $expediente->alto_im_complicado = $this->toBool($request['alto_im_complicado']);
        $expediente->alto_capacidad_menor_5mets = $this->toBool($request['alto_capacidad_menor_5mets']);
        $expediente->alto_hemodinamica_anormal = $this->toBool($request['alto_hemodinamica_anormal']);
        $expediente->alto_paro_cardiaco = $this->toBool($request['alto_paro_cardiaco']);
        $expediente->alto_enfermedad_compleja = $this->toBool($request['alto_enfermedad_compleja']);

        // Riesgo Moderado
        $expediente->moderado_fevi_moderada = $this->toBool($request['moderado_fevi_moderada']);
        $expediente->moderado_sintomas_moderados = $this->toBool($request['moderado_sintomas_moderados']);
        $expediente->moderado_isquemia_moderada = $this->toBool($request['moderado_isquemia_moderada']);
        $expediente->moderado_capacidad_5_7mets = $this->toBool($request['moderado_capacidad_5_7mets']);
        $expediente->moderado_sin_automonitoreo = $this->toBool($request['moderado_sin_automonitoreo']);

        // Riesgo Bajo
        $expediente->bajo_fevi_preservada = $this->toBool($request['bajo_fevi_preservada']);
        $expediente->bajo_sin_sintomas = $this->toBool($request['bajo_sin_sintomas']);
        $expediente->bajo_sin_isquemia = $this->toBool($request['bajo_sin_isquemia']);
        $expediente->bajo_capacidad_mayor_7mets = $this->toBool($request['bajo_capacidad_mayor_7mets']);
        $expediente->bajo_sin_arritmias = $this->toBool($request['bajo_sin_arritmias']);
        $expediente->bajo_im_no_complicado = $this->toBool($request['bajo_im_no_complicado']);
        $expediente->bajo_hemodinamica_normal = $this->toBool($request['bajo_hemodinamica_normal']);
        $expediente->bajo_automonitoreo_adecuado = $this->toBool($request['bajo_automonitoreo_adecuado']);

        // Hallazgos
        $expediente->hallazgo_fevi = $request['hallazgo_fevi'];
        $expediente->hallazgo_mets = $request['hallazgo_mets'];
        $expediente->hallazgo_sintomas = $request['hallazgo_sintomas'];
        $expediente->hallazgo_isquemia = $request['hallazgo_isquemia'];
        $expediente->hallazgo_arritmias = $request['hallazgo_arritmias'];
        $expediente->hallazgo_hemodinamica = $request['hallazgo_hemodinamica'];
        $expediente->hallazgo_procedimiento = $request['hallazgo_procedimiento'];
        $expediente->hallazgo_coronaria = $request['hallazgo_coronaria'];

        // Riesgo Global
        $expediente->riesgo_global = $request['riesgo_global'];

        // Parámetros Iniciales
        $expediente->grupo = $request['grupo'];
        $expediente->semanas = $request['semanas'];
        $expediente->sesiones = $request['sesiones'];
        $expediente->borg = $request['borg'];
        $expediente->fc_diana_metodo = $request['fc_diana_metodo'];
        $expediente->fc_diana_str = $request['fc_diana_str'];
        $expediente->fc_diana_manual = $request['fc_diana_manual'];
        $expediente->carga_inicial = $request['carga_inicial'];
        
        // Recalcular FC Diana automáticamente
        $fcBasal = floatval($request['fc_basal'] ?? 0);
        $fcMax = floatval($request['fc_max'] ?? 0);
        $fcBorg12 = floatval($request['fc_borg_12'] ?? 0);
        $dpBorg12 = floatval($request['dp_borg_12'] ?? 0);
        
        if ($fcMax > 0 && $fcBasal > 0) {
            $karvonen = (($fcMax - $fcBasal) * 0.7) + $fcBasal;
            $blackburn = ($fcMax * 0.8);
            $narita = (78.4 + ((0.76 * $fcBasal) - (0.27 * $paciente->edad)));

            switch ($request['fc_diana_metodo'] ?? 'Bo') {
                case 'K':
                    $fcDiana = $karvonen;
                    break;
                case 'BI':
                    $fcDiana = $blackburn;
                    break;
                case 'N':
                    $fcDiana = $narita;
                    break;
                case 'UISQ':
                case 'Manual':
                    $fcDiana = $request['fc_diana_manual'] ?? null;
                    break;
                default: // 'Bo' - Borg
                    $fcDiana = $fcBorg12;
            }
            $expediente->fc_diana = $fcDiana;
            
            // Calcular DP Diana automáticamente
            if ($fcDiana > 0) {
                $expediente->dp_diana = $dpBorg12;
            }
        } else {
            $expediente->fc_diana = $request['fc_diana'] ?? null;
            $expediente->dp_diana = $request['dp_diana'] ?? null;
        }
        
        $expediente->comentarios = $request['comentarios'];

        $expediente->save();

        return response()->json($expediente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EstratiAacvpr $estrati_aacvpr)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes'], 403);
        }
        
        // Verificar que la estratificación pertenece a la misma clínica
        $paciente = $estrati_aacvpr->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }
        
        $estrati_aacvpr->delete();
        return response()->json(['message' => 'Expediente eliminado exitosamente'], 204);
    }

    /**
     * Helper para convertir valores a booleano
     */
    private function toBool($value): bool
    {
        return $value === 'true' || $value === true || $value === 1 || $value === '1';
    }
}
