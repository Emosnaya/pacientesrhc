<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinicaDental;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HistoriaClinicaDentalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filtrar por sucursal_id
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        
        $historias = HistoriaClinicaDental::where('sucursal_id', $sucursalId)
            ->with(['paciente', 'user', 'odontogramas'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($historias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'nullable|date',
            'nombre_doctor' => 'nullable|string|max:255',
            'cedula_profesional' => 'nullable|string|max:100',
            'medicamentos_actuales' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Determinar sucursal_id
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $historia = HistoriaClinicaDental::create([
                'paciente_id' => $request->paciente_id,
                'sucursal_id' => $sucursalId,
                'user_id' => $user->id,
                'fecha' => $request->fecha ?? now(),
                'nombre_doctor' => $request->nombre_doctor ?? $user->nombre . ' ' . $user->apellidoPat,
                'cedula_profesional' => $request->cedula_profesional ?? $user->cedula,
                'alergias' => $request->alergias,
                'medicamentos_actuales' => $request->medicamentos_actuales ?? [],
                'alergico_anestesicos' => $request->alergico_anestesicos ?? false,
                'anestesicos_detalle' => $request->anestesicos_detalle,
                'alergico_medicamentos' => $request->alergico_medicamentos ?? false,
                'medicamentos_alergicos_detalle' => $request->medicamentos_alergicos_detalle,
                'embarazada' => $request->embarazada ?? false,
                'toma_anticonceptivos' => $request->toma_anticonceptivos ?? false,
                'mal_aliento' => $request->mal_aliento ?? false,
                'hipersensibilidad_dental' => $request->hipersensibilidad_dental ?? false,
                'respira_boca' => $request->respira_boca ?? false,
                'muerde_unas' => $request->muerde_unas ?? false,
                'muerde_labios' => $request->muerde_labios ?? false,
                'aprieta_dientes' => $request->aprieta_dientes ?? false,
                'veces_cepilla_dia' => $request->veces_cepilla_dia,
                'higienizacion_metodo' => $request->higienizacion_metodo,
                'ultima_visita_odontologo' => $request->ultima_visita_odontologo,
                'historia_enfermedad' => $request->historia_enfermedad,
                'motivo_consulta' => $request->motivo_consulta,
                // Antecedentes Familiares
                'af_diabetes' => $request->af_diabetes ?? false,
                'af_hipertension' => $request->af_hipertension ?? false,
                'af_cancer' => $request->af_cancer ?? false,
                'af_cardiacas' => $request->af_cardiacas ?? false,
                'af_vih' => $request->af_vih ?? false,
                'af_epilepsia' => $request->af_epilepsia ?? false,
                // Información Patológica
                'ip_diabetes' => $request->ip_diabetes ?? false,
                'ip_hipertension' => $request->ip_hipertension ?? false,
                'ip_cancer' => $request->ip_cancer ?? false,
                'ip_cardiacas' => $request->ip_cardiacas ?? false,
                'ip_veneras' => $request->ip_veneras ?? false,
                'ip_epilepsia' => $request->ip_epilepsia ?? false,
                'ip_asma' => $request->ip_asma ?? false,
                'ip_gastricas' => $request->ip_gastricas ?? false,
                'ip_cicatriz' => $request->ip_cicatriz ?? false,
                'ip_presion_alta_baja' => $request->ip_presion_alta_baja ?? false,
                // Antecedentes Toxicológicos
                'at_fuma' => $request->at_fuma ?? false,
                'at_fuma_detalle' => $request->at_fuma_detalle,
                'at_drogas' => $request->at_drogas ?? false,
                'at_drogas_detalle' => $request->at_drogas_detalle,
                'at_toma' => $request->at_toma ?? false,
                'at_toma_detalle' => $request->at_toma_detalle,
                // Antecedentes Ginecoobstétricos
                'ag_menarca' => $request->ag_menarca ?? false,
                'ag_menarca_edad' => $request->ag_menarca_edad,
                'ag_menopausia' => $request->ag_menopausia ?? false,
                'ag_menopausia_edad' => $request->ag_menopausia_edad,
                'ag_embarazo' => $request->ag_embarazo ?? false,
                // Antecedentes Odontológicos
                'ao_limpieza_6meses' => $request->ao_limpieza_6meses ?? false,
                'ao_sangrado' => $request->ao_sangrado ?? false,
                'ao_dolor_abrir' => $request->ao_dolor_abrir ?? false,
                'ao_tratamiento_ortodoncia' => $request->ao_tratamiento_ortodoncia ?? false,
                'ao_morder_labios' => $request->ao_morder_labios ?? false,
                'ao_dieta_dulces' => $request->ao_dieta_dulces ?? false,
                'ao_cepilla_dientes' => $request->ao_cepilla_dientes ?? false,
                'ao_trauma_cara' => $request->ao_trauma_cara ?? false,
                'ao_dolor_masticar' => $request->ao_dolor_masticar ?? false,
                // Examen Tejidos Blandos
                'etb_carrillos' => $request->etb_carrillos,
                'etb_encias' => $request->etb_encias,
                'etb_lengua' => $request->etb_lengua,
                'etb_paladar' => $request->etb_paladar,
                'etb_atm' => $request->etb_atm,
                'etb_labios' => $request->etb_labios,
                // Signos Vitales
                'sv_ta' => $request->sv_ta,
                'sv_pulso' => $request->sv_pulso,
                'sv_fc' => $request->sv_fc,
                'sv_peso' => $request->sv_peso,
                'sv_altura' => $request->sv_altura,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Historia clínica dental creada exitosamente',
                'data' => $historia->load(['paciente', 'user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la historia clínica dental',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $historia = HistoriaClinicaDental::with(['paciente', 'user', 'odontogramas'])->findOrFail($id);
        return response()->json($historia);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'fecha' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $historia = HistoriaClinicaDental::findOrFail($id);
            $historia->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Historia clínica dental actualizada exitosamente',
                'data' => $historia->load(['paciente', 'user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la historia clínica dental',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $historia = HistoriaClinicaDental::findOrFail($id);
            $historia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Historia clínica dental eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la historia clínica dental',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historias dentales de un paciente específico
     */
    public function getByPaciente(Request $request, $pacienteId)
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        
        $historias = HistoriaClinicaDental::where('paciente_id', $pacienteId)
            ->where('sucursal_id', $sucursalId)
            ->with(['user', 'odontogramas'])
            ->orderBy('fecha', 'desc')
            ->get();
        
        return response()->json($historias);
    }
}
