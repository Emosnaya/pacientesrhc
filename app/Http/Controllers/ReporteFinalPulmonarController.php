<?php

namespace App\Http\Controllers;

use App\Models\ReporteFinalPulmonar;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteFinalPulmonarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pacienteId = $request->query('paciente_id');

        if (!$pacienteId) {
            return response()->json(['error' => 'paciente_id is required'], 400);
        }

        $reportes = ReporteFinalPulmonar::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->with(['user', 'paciente'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reportes);
    }

    /**
     * Generate automatic report from two pulmonary stress tests
     */
    public function generateFromComparison(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'prueba_inicial_id' => 'required|exists:prueba_esfuerzo_pulmonars,id',
            'prueba_final_id' => 'required|exists:prueba_esfuerzo_pulmonars,id',
        ]);

        $pruebaInicial = \App\Models\PruebaEsfuerzoPulmonar::with('paciente')->findOrFail($validated['prueba_inicial_id']);
        $pruebaFinal = \App\Models\PruebaEsfuerzoPulmonar::with('paciente')->findOrFail($validated['prueba_final_id']);
        
        $paciente = $pruebaInicial->paciente;
        
        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }

        // Verificar que ambas pruebas son del mismo paciente
        if ($pruebaInicial->paciente_id !== $pruebaFinal->paciente_id) {
            return response()->json(['error' => 'Las pruebas deben ser del mismo paciente'], 400);
        }

        // Crear reporte final automáticamente con datos de ambas pruebas
        $reporte = new ReporteFinalPulmonar();
        $reporte->paciente_id = $paciente->id;
        $reporte->user_id = $user->id;
        $reporte->clinica_id = $user->clinica_id;
        $reporte->tipo_exp = 15;
        
        // Fechas
        $reporte->fecha_inicio = $pruebaInicial->fecha_realizacion;
        $reporte->fecha_termino = $pruebaFinal->fecha_realizacion;
        
        // Datos de prueba inicial
        $reporte->pe_inicial_fc_basal = $pruebaInicial->basal_fc;
        $reporte->pe_inicial_spo2 = $pruebaInicial->basal_saturacion;
        $reporte->pe_inicial_litros_oxigeno = $pruebaInicial->oxigeno_litros;
        $reporte->pe_inicial_carga_maxima = $pruebaInicial->max_mets;
        $reporte->pe_inicial_vo2_pico_ml = $pruebaInicial->vo2_equivalente;
        $reporte->pe_inicial_fc_pico = $pruebaInicial->max_fc_pico;
        $reporte->pe_inicial_porcentaje_fcmax = $pruebaInicial->porcentaje_fcmax;
        $reporte->pe_inicial_borg_disnea = $pruebaInicial->borg_max_disnea;
        $reporte->pe_inicial_borg_fatiga = $pruebaInicial->borg_max_fatiga;
        $reporte->pe_inicial_spo2_minima = $pruebaInicial->saturacion_minima;
        $reporte->pe_inicial_dinamometria = null; // No está en prueba_esfuerzo_pulmonars
        $reporte->pe_inicial_sit_to_stand_30seg = null; // No está en prueba_esfuerzo_pulmonars
        
        // Datos de prueba final
        $reporte->pe_final_fc_basal = $pruebaFinal->basal_fc;
        $reporte->pe_final_spo2 = $pruebaFinal->basal_saturacion;
        $reporte->pe_final_litros_oxigeno = $pruebaFinal->oxigeno_litros;
        $reporte->pe_final_carga_maxima = $pruebaFinal->max_mets;
        $reporte->pe_final_vo2_pico_ml = $pruebaFinal->vo2_equivalente;
        $reporte->pe_final_fc_pico = $pruebaFinal->max_fc_pico;
        $reporte->pe_final_porcentaje_fcmax = $pruebaFinal->porcentaje_fcmax;
        $reporte->pe_final_borg_disnea = $pruebaFinal->borg_max_disnea;
        $reporte->pe_final_borg_fatiga = $pruebaFinal->borg_max_fatiga;
        $reporte->pe_final_spo2_minima = $pruebaFinal->saturacion_minima;
        $reporte->pe_final_dinamometria = null; // No está en prueba_esfuerzo_pulmonars
        $reporte->pe_final_sit_to_stand_30seg = null;
        
        // Diagnóstico del paciente
        $reporte->diagnostico = $paciente->diagnostico;
        
        $reporte->save();

        return response()->json([
            'message' => 'Reporte Final Pulmonar generado exitosamente',
            'reporte' => $reporte->load(['user', 'paciente'])
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_termino' => 'nullable|date',
            'diagnostico' => 'nullable|string',
            'descripcion_programa' => 'nullable|string',
            'pe_inicial_rubro' => 'nullable|string',
            'pe_inicial_fc_basal' => 'nullable|integer',
            'pe_inicial_spo2' => 'nullable|integer',
            'pe_inicial_litros_oxigeno' => 'nullable|numeric',
            'pe_inicial_carga_maxima' => 'nullable|numeric',
            'pe_inicial_vo2_pico' => 'nullable|integer',
            'pe_inicial_vo2_pico_ml' => 'nullable|numeric',
            'pe_inicial_fc_pico' => 'nullable|integer',
            'pe_inicial_porcentaje_fcmax' => 'nullable|numeric',
            'pe_inicial_spo2_minima' => 'nullable|integer',
            'pe_inicial_borg_disnea' => 'nullable|numeric',
            'pe_inicial_borg_fatiga' => 'nullable|numeric',
            'pe_inicial_dinamometria' => 'nullable|numeric',
            'pe_inicial_sit_to_stand_30seg' => 'nullable|integer',
            'pe_final_rubro' => 'nullable|string',
            'pe_final_fc_basal' => 'nullable|integer',
            'pe_final_spo2' => 'nullable|integer',
            'pe_final_litros_oxigeno' => 'nullable|numeric',
            'pe_final_carga_maxima' => 'nullable|numeric',
            'pe_final_vo2_pico' => 'nullable|integer',
            'pe_final_vo2_pico_ml' => 'nullable|numeric',
            'pe_final_fc_pico' => 'nullable|integer',
            'pe_final_porcentaje_fcmax' => 'nullable|numeric',
            'pe_final_spo2_minima' => 'nullable|integer',
            'pe_final_borg_disnea' => 'nullable|numeric',
            'pe_final_borg_fatiga' => 'nullable|numeric',
            'pe_final_dinamometria' => 'nullable|numeric',
            'pe_final_sit_to_stand_30seg' => 'nullable|integer',
            'resultados_clinicos' => 'nullable|string',
            'plan' => 'nullable|string'
        ]);

        // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

        $validated['user_id'] = $user->id;
        $validated['clinica_id'] = $user->clinica_id;
        $validated['sucursal_id'] = $sucursalId;
        $validated['tipo_exp'] = 15;

        $reporte = ReporteFinalPulmonar::create($validated);

        return response()->json($reporte, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();

        $reporte = ReporteFinalPulmonar::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->with(['user', 'paciente'])
            ->firstOrFail();

        return response()->json($reporte);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $reporte = ReporteFinalPulmonar::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $validated = $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_termino' => 'nullable|date',
            'diagnostico' => 'nullable|string',
            'descripcion_programa' => 'nullable|string',
            'pe_inicial_rubro' => 'nullable|string',
            'pe_inicial_fc_basal' => 'nullable|integer',
            'pe_inicial_spo2' => 'nullable|integer',
            'pe_inicial_litros_oxigeno' => 'nullable|numeric',
            'pe_inicial_carga_maxima' => 'nullable|numeric',
            'pe_inicial_vo2_pico' => 'nullable|integer',
            'pe_inicial_vo2_pico_ml' => 'nullable|numeric',
            'pe_inicial_fc_pico' => 'nullable|integer',
            'pe_inicial_porcentaje_fcmax' => 'nullable|numeric',
            'pe_inicial_spo2_minima' => 'nullable|integer',
            'pe_inicial_borg_disnea' => 'nullable|numeric',
            'pe_inicial_borg_fatiga' => 'nullable|numeric',
            'pe_inicial_dinamometria' => 'nullable|numeric',
            'pe_inicial_sit_to_stand_30seg' => 'nullable|integer',
            'pe_final_rubro' => 'nullable|string',
            'pe_final_fc_basal' => 'nullable|integer',
            'pe_final_spo2' => 'nullable|integer',
            'pe_final_litros_oxigeno' => 'nullable|numeric',
            'pe_final_carga_maxima' => 'nullable|numeric',
            'pe_final_vo2_pico' => 'nullable|integer',
            'pe_final_vo2_pico_ml' => 'nullable|numeric',
            'pe_final_fc_pico' => 'nullable|integer',
            'pe_final_porcentaje_fcmax' => 'nullable|numeric',
            'pe_final_spo2_minima' => 'nullable|integer',
            'pe_final_borg_disnea' => 'nullable|numeric',
            'pe_final_borg_fatiga' => 'nullable|numeric',
            'pe_final_dinamometria' => 'nullable|numeric',
            'pe_final_sit_to_stand_30seg' => 'nullable|integer',
            'resultados_clinicos' => 'nullable|string',
            'plan' => 'nullable|string'
        ]);

        $reporte->update($validated);

        return response()->json($reporte);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $reporte = ReporteFinalPulmonar::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $reporte->delete();

        return response()->json(['message' => 'Reporte eliminado exitosamente']);
    }

    /**
     * Print PDF
     */
    public function print(Request $request, $id)
    {
        $user = Auth::user();
        $doctorFirmaId = $request->query('doctor_firma_id');

        $data = ReporteFinalPulmonar::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $paciente = Paciente::findOrFail($data->paciente_id);

        // Determinar qué usuario usará para la firma
        if ($doctorFirmaId) {
            $firmaUser = User::findOrFail($doctorFirmaId);
        } else {
            $firmaUser = $data->user_id ? User::find($data->user_id) : $user;
        }

        // Preparar firma digital si existe
        $firmaBase64 = null;
        if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
            $imagePath = public_path('storage/' . $firmaUser->firma_digital);
            $imageData = file_get_contents($imagePath);
            $imageType = mime_content_type($imagePath);
            $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
        }

        $pdf = PDF::loadView('reportefinalpulmonar', [
            'data' => $data,
            'paciente' => $paciente,
            'user' => $firmaUser,
            'firmaBase64' => $firmaBase64
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Reporte_Final_Pulmonar_' . $paciente->registro . '.pdf');
    }

    /**
     * Download PDF
     */
    public function download(Request $request, $id)
    {
        $user = Auth::user();
        $doctorFirmaId = $request->query('doctor_firma_id');

        $data = ReporteFinalPulmonar::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $paciente = Paciente::findOrFail($data->paciente_id);

        // Determinar qué usuario usará para la firma
        if ($doctorFirmaId) {
            $firmaUser = User::findOrFail($doctorFirmaId);
        } else {
            $firmaUser = $data->user_id ? User::find($data->user_id) : $user;
        }

        // Preparar firma digital si existe
        $firmaBase64 = null;
        if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
            $imagePath = public_path('storage/' . $firmaUser->firma_digital);
            $imageData = file_get_contents($imagePath);
            $imageType = mime_content_type($imagePath);
            $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
        }

        $pdf = PDF::loadView('reportefinalpulmonar', [
            'data' => $data,
            'paciente' => $paciente,
            'user' => $firmaUser,
            'firmaBase64' => $firmaBase64
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Reporte_Final_Pulmonar_' . $paciente->registro . '.pdf');
    }
}
