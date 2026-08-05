<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Periodontograma;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PeriodontogramaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Periodontograma::where('clinica_id', $user->clinica_efectiva_id)
            ->with(['paciente:id,nombre,apellidoPat,apellidoMat'])
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($request->paciente_id) {
            $query->where('paciente_id', $request->paciente_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'nullable|date',
            'dientes' => 'nullable|array',
            'diagnostico' => 'nullable|string',
            'plan_tratamiento' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $paciente = Paciente::find($request->paciente_id);
        if (! $paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }

        $dientes = $request->dientes ?? Periodontograma::inicializarDientes();
        $resumen = Periodontograma::calcularResumen($dientes);

        $periodo = Periodontograma::create([
            'paciente_id' => $request->paciente_id,
            'clinica_id' => $user->clinica_efectiva_id,
            'sucursal_id' => $request->sucursal_id ?? $user->sucursal_id,
            'user_id' => $user->id,
            'fecha' => $request->fecha ?? now()->toDateString(),
            'dientes' => $dientes,
            'diagnostico' => $request->diagnostico,
            'plan_tratamiento' => $request->plan_tratamiento,
            'observaciones' => $request->observaciones,
            ...$resumen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Periodontograma creado',
            'data' => $periodo,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $periodo = Periodontograma::with(['paciente', 'user:id,nombre,apellidoPat'])->findOrFail($id);

        if ((int) $periodo->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($periodo);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $periodo = Periodontograma::findOrFail($id);

        if ((int) $periodo->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $data = $request->only([
            'fecha', 'dientes', 'diagnostico', 'plan_tratamiento', 'observaciones',
        ]);

        if (isset($data['dientes'])) {
            $data = array_merge($data, Periodontograma::calcularResumen($data['dientes']));
        }

        $periodo->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Periodontograma actualizado',
            'data' => $periodo->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            return response()->json(['error' => 'Solo administradores pueden eliminar'], 403);
        }

        $periodo = Periodontograma::findOrFail($id);
        if ((int) $periodo->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $periodo->delete();

        return response()->json(['success' => true, 'message' => 'Periodontograma eliminado']);
    }

    public function getByPaciente(int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::find($pacienteId);
        if (! $paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $list = Periodontograma::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($list);
    }
}
