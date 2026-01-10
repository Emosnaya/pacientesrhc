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
            'pe_inicial_carga_maxima' => 'nullable|numeric',
            'pe_inicial_vo2_pico' => 'nullable|integer',
            'pe_inicial_vo2_pico_ml' => 'nullable|numeric',
            'pe_inicial_fc_pico' => 'nullable|integer',
            'pe_inicial_pulso_oxigeno' => 'nullable|integer',
            'pe_inicial_borg_modificado' => 'nullable|numeric',
            'pe_inicial_ve_maxima' => 'nullable|integer',
            'pe_inicial_dinamometria' => 'nullable|numeric',
            'pe_inicial_sit_up' => 'nullable|integer',
            'pe_final_rubro' => 'nullable|string',
            'pe_final_fc_basal' => 'nullable|integer',
            'pe_final_spo2' => 'nullable|integer',
            'pe_final_carga_maxima' => 'nullable|numeric',
            'pe_final_vo2_pico' => 'nullable|integer',
            'pe_final_vo2_pico_ml' => 'nullable|numeric',
            'pe_final_fc_pico' => 'nullable|integer',
            'pe_final_pulso_oxigeno' => 'nullable|integer',
            'pe_final_borg_modificado' => 'nullable|numeric',
            'pe_final_ve_maxima' => 'nullable|integer',
            'pe_final_dinamometria' => 'nullable|numeric',
            'pe_final_sit_up' => 'nullable|integer',
            'resultados_clinicos' => 'nullable|string',
            'plan' => 'nullable|string'
        ]);

        $validated['user_id'] = $user->id;
        $validated['clinica_id'] = $user->clinica_id;
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
            'pe_inicial_carga_maxima' => 'nullable|numeric',
            'pe_inicial_vo2_pico' => 'nullable|integer',
            'pe_inicial_vo2_pico_ml' => 'nullable|numeric',
            'pe_inicial_fc_pico' => 'nullable|integer',
            'pe_inicial_pulso_oxigeno' => 'nullable|integer',
            'pe_inicial_borg_modificado' => 'nullable|numeric',
            'pe_inicial_ve_maxima' => 'nullable|integer',
            'pe_inicial_dinamometria' => 'nullable|numeric',
            'pe_inicial_sit_up' => 'nullable|integer',
            'pe_final_rubro' => 'nullable|string',
            'pe_final_fc_basal' => 'nullable|integer',
            'pe_final_spo2' => 'nullable|integer',
            'pe_final_carga_maxima' => 'nullable|numeric',
            'pe_final_vo2_pico' => 'nullable|integer',
            'pe_final_vo2_pico_ml' => 'nullable|numeric',
            'pe_final_fc_pico' => 'nullable|integer',
            'pe_final_pulso_oxigeno' => 'nullable|integer',
            'pe_final_borg_modificado' => 'nullable|numeric',
            'pe_final_ve_maxima' => 'nullable|integer',
            'pe_final_dinamometria' => 'nullable|numeric',
            'pe_final_sit_up' => 'nullable|integer',
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
