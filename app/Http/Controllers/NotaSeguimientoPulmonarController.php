<?php

namespace App\Http\Controllers;

use App\Models\NotaSeguimientoPulmonar;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaSeguimientoPulmonarController extends Controller
{
    /**
     * Lista de notas de seguimiento pulmonar de un paciente.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pacienteId = $request->query('paciente_id');

        if (!$pacienteId) {
            return response()->json(['error' => 'paciente_id is required'], 400);
        }

        $paciente = Paciente::find($pacienteId);
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los expedientes de este paciente'], 403);
        }

        $notas = NotaSeguimientoPulmonar::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->with(['user', 'paciente', 'expedientePulmonar'])
            ->orderBy('fecha_consulta', 'desc')
            ->orderBy('hora_consulta', 'desc')
            ->get();

        return response()->json($notas);
    }

    /**
     * Crear una nueva nota de seguimiento pulmonar.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_consulta' => 'required|date',
            'hora_consulta' => 'required|date_format:H:i',
            'ficha_identificacion' => 'nullable|string',
            'diagnosticos' => 'nullable|string',
            's_subjetivo' => 'nullable|string',
            'o_objetivo' => 'nullable|string',
            'a_apreciacion' => 'nullable|string',
            'p_plan' => 'nullable|string',
            'expediente_pulmonar_id' => 'nullable|exists:expediente_pulmonars,id',
        ]);

        $paciente = Paciente::findOrFail($validated['paciente_id']);
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }

        $validated['user_id'] = $user->id;
        $validated['clinica_id'] = $user->clinica_id;
        $validated['tipo_exp'] = 19;

        $nota = NotaSeguimientoPulmonar::create($validated);
        $nota->load(['user', 'paciente', 'expedientePulmonar']);

        return response()->json($nota, 201);
    }

    /**
     * Mostrar una nota de seguimiento pulmonar.
     */
    public function show($id)
    {
        $user = Auth::user();
        $nota = NotaSeguimientoPulmonar::with(['user', 'paciente', 'expedientePulmonar'])->findOrFail($id);

        if ($nota->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        return response()->json($nota);
    }

    /**
     * Actualizar una nota de seguimiento pulmonar.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $nota = NotaSeguimientoPulmonar::findOrFail($id);

        if ($nota->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        $validated = $request->validate([
            'fecha_consulta' => 'sometimes|date',
            'hora_consulta' => 'sometimes|date_format:H:i',
            'ficha_identificacion' => 'nullable|string',
            'diagnosticos' => 'nullable|string',
            's_subjetivo' => 'nullable|string',
            'o_objetivo' => 'nullable|string',
            'a_apreciacion' => 'nullable|string',
            'p_plan' => 'nullable|string',
            'expediente_pulmonar_id' => 'nullable|exists:expediente_pulmonars,id',
        ]);

        $nota->update($validated);
        $nota->load(['user', 'paciente', 'expedientePulmonar']);

        return response()->json($nota);
    }

    /**
     * Eliminar una nota de seguimiento pulmonar.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $nota = NotaSeguimientoPulmonar::findOrFail($id);

        if ($nota->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        $nota->delete();
        return response()->json(['message' => 'Nota de seguimiento pulmonar eliminada correctamente'], 200);
    }
}
