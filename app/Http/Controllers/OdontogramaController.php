<?php

namespace App\Http\Controllers;

use App\Models\Odontograma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OdontogramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        
        $odontogramas = Odontograma::where('sucursal_id', $sucursalId)
            ->with(['paciente', 'historiaClinicaDental'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($odontogramas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'historia_clinica_dental_id' => 'nullable|exists:historia_clinica_dental,id',
            'fecha' => 'nullable|date',
            'dientes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            // Si no se envían dientes, inicializar con estructura vacía
            $dientes = $request->dientes ?? Odontograma::inicializarDientes();

            $odontograma = Odontograma::create([
                'paciente_id' => $request->paciente_id,
                'sucursal_id' => $sucursalId,
                'historia_clinica_dental_id' => $request->historia_clinica_dental_id,
                'fecha' => $request->fecha ?? now(),
                'dientes' => $dientes,
                // Análisis Periodontal
                'ap_calculo_supragingival' => $request->ap_calculo_supragingival ?? false,
                'ap_calculo_infragingival' => $request->ap_calculo_infragingival ?? false,
                'ap_movilidad_dental' => $request->ap_movilidad_dental ?? false,
                'ap_bolsas_periodontales' => $request->ap_bolsas_periodontales ?? false,
                'ap_pseudobolsas' => $request->ap_pseudobolsas ?? false,
                'ap_indice_placa' => $request->ap_indice_placa ?? false,
                'ap_calculo_supragingival_dientes' => $request->ap_calculo_supragingival_dientes,
                'ap_calculo_infragingival_dientes' => $request->ap_calculo_infragingival_dientes,
                'ap_movilidad_dental_dientes' => $request->ap_movilidad_dental_dientes,
                'ap_bolsas_periodontales_dientes' => $request->ap_bolsas_periodontales_dientes,
                'ap_pseudobolsas_dientes' => $request->ap_pseudobolsas_dientes,
                'ap_indice_placa_dientes' => $request->ap_indice_placa_dientes,
                // Análisis Endodóntico
                'ae_endo_defectuosa' => $request->ae_endo_defectuosa ?? false,
                'ae_necrosis_pulpar' => $request->ae_necrosis_pulpar ?? false,
                'ae_pulpitis_irreversible' => $request->ae_pulpitis_irreversible ?? false,
                'ae_lesiones_periapicales' => $request->ae_lesiones_periapicales ?? false,
                'ae_endo_defectuosa_dientes' => $request->ae_endo_defectuosa_dientes,
                'ae_necrosis_pulpar_dientes' => $request->ae_necrosis_pulpar_dientes,
                'ae_pulpitis_irreversible_dientes' => $request->ae_pulpitis_irreversible_dientes,
                'ae_lesiones_periapicales_dientes' => $request->ae_lesiones_periapicales_dientes,
                // Diagnóstico
                'diagnostico' => $request->diagnostico,
                'pronostico' => $request->pronostico,
                'observaciones' => $request->observaciones,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Odontograma creado exitosamente',
                'data' => $odontograma->load(['paciente', 'historiaClinicaDental'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el odontograma',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $odontograma = Odontograma::with(['paciente', 'historiaClinicaDental'])->findOrFail($id);
        return response()->json($odontograma);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'fecha' => 'nullable|date',
            'dientes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $odontograma = Odontograma::findOrFail($id);
            $odontograma->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Odontograma actualizado exitosamente',
                'data' => $odontograma->load(['paciente', 'historiaClinicaDental'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el odontograma',
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
            $odontograma = Odontograma::findOrFail($id);
            $odontograma->delete();

            return response()->json([
                'success' => true,
                'message' => 'Odontograma eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el odontograma',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener odontogramas de un paciente específico
     */
    public function getByPaciente(Request $request, $pacienteId)
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        
        $odontogramas = Odontograma::where('paciente_id', $pacienteId)
            ->where('sucursal_id', $sucursalId)
            ->with(['historiaClinicaDental'])
            ->orderBy('fecha', 'desc')
            ->get();
        
        return response()->json($odontogramas);
    }

    /**
     * Obtener el odontograma más reciente de un paciente
     */
    public function getLatestByPaciente(Request $request, $pacienteId)
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        
        $odontograma = Odontograma::where('paciente_id', $pacienteId)
            ->where('sucursal_id', $sucursalId)
            ->with(['historiaClinicaDental'])
            ->orderBy('fecha', 'desc')
            ->first();
        
        if (!$odontograma) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró odontograma para este paciente'
            ], 404);
        }
        
        return response()->json($odontograma);
    }
}
