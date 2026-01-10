<?php

namespace App\Http\Controllers;

use App\Models\CualidadFisica;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CualidadFisicaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pacienteId = $request->query('paciente_id');

        if (!$pacienteId) {
            return response()->json(['error' => 'Paciente ID requerido'], 400);
        }

        $cualidadesFisicas = CualidadFisica::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($cualidadesFisicas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
        ]);

        // Verificar que el paciente pertenece a la misma clínica
        $paciente = Paciente::where('id', $validated['paciente_id'])
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $data = $request->all();
        $data['user_id'] = $user->id;
        $data['clinica_id'] = $user->clinica_id;

        $cualidadFisica = CualidadFisica::create($data);

        return response()->json($cualidadFisica, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        
        $cualidadFisica = CualidadFisica::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->with(['paciente', 'user'])
            ->firstOrFail();

        return response()->json($cualidadFisica);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        $cualidadFisica = CualidadFisica::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $cualidadFisica->update($request->all());

        return response()->json($cualidadFisica);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        $cualidadFisica = CualidadFisica::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->firstOrFail();

        $cualidadFisica->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    /**
     * Get the latest cualidad fisica for a patient
     */
    public function latest(Request $request)
    {
        $user = Auth::user();
        $pacienteId = $request->query('paciente_id');

        if (!$pacienteId) {
            return response()->json(['error' => 'Paciente ID requerido'], 400);
        }

        $cualidadFisica = CualidadFisica::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->latest()
            ->first();

        return response()->json($cualidadFisica);
    }

    /**
     * Generate PDF for viewing
     */
    public function print(Request $request, string $id)
    {
        $user = Auth::user();
        $doctorId = $request->query('doctor_id');
        
        $cualidadFisica = CualidadFisica::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->with(['paciente', 'user'])
            ->firstOrFail();

        $paciente = $cualidadFisica->paciente;

        // Obtener el doctor seleccionado o usar el usuario actual
        $selectedUser = $user;
        if ($doctorId) {
            $doctor = User::where('id', $doctorId)
                ->where('clinica_id', $user->clinica_id)
                ->first();
            if ($doctor) {
                $selectedUser = $doctor;
            }
        }

        // Obtener la firma del doctor
        $firmaBase64 = null;
        if ($selectedUser->firma_path) {
            $firmaPath = storage_path('app/public/' . $selectedUser->firma_path);
            if (file_exists($firmaPath)) {
                $firmaData = base64_encode(file_get_contents($firmaPath));
                $firmaExtension = pathinfo($firmaPath, PATHINFO_EXTENSION);
                $firmaBase64 = 'data:image/' . $firmaExtension . ';base64,' . $firmaData;
            }
        }

        $pdf = PDF::loadView('cualidadesfisicas', [
            'data' => $cualidadFisica,
            'paciente' => $paciente,
            'user' => $selectedUser,
            'firmaBase64' => $firmaBase64
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Cualidades_Fisicas_' . $paciente->registro . '.pdf');
    }

    /**
     * Generate PDF for download
     */
    public function download(Request $request, string $id)
    {
        $user = Auth::user();
        $doctorId = $request->query('doctor_id');
        
        $cualidadFisica = CualidadFisica::where('id', $id)
            ->where('clinica_id', $user->clinica_id)
            ->with(['paciente', 'user'])
            ->firstOrFail();

        $paciente = $cualidadFisica->paciente;

        // Obtener el doctor seleccionado o usar el usuario actual
        $selectedUser = $user;
        if ($doctorId) {
            $doctor = User::where('id', $doctorId)
                ->where('clinica_id', $user->clinica_id)
                ->first();
            if ($doctor) {
                $selectedUser = $doctor;
            }
        }

        // Obtener la firma del doctor
        $firmaBase64 = null;
        if ($selectedUser->firma_path) {
            $firmaPath = storage_path('app/public/' . $selectedUser->firma_path);
            if (file_exists($firmaPath)) {
                $firmaData = base64_encode(file_get_contents($firmaPath));
                $firmaExtension = pathinfo($firmaPath, PATHINFO_EXTENSION);
                $firmaBase64 = 'data:image/' . $firmaExtension . ';base64,' . $firmaData;
            }
        }

        $pdf = PDF::loadView('cualidadesfisicas', [
            'data' => $cualidadFisica,
            'paciente' => $paciente,
            'user' => $selectedUser,
            'firmaBase64' => $firmaBase64
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Cualidades_Fisicas_' . $paciente->registro . '.pdf');
    }
}
