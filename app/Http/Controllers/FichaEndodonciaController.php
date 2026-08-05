<?php

namespace App\Http\Controllers;

use App\Models\FichaEndodoncia;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FichaEndodonciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = FichaEndodoncia::where('clinica_id', $user->clinica_efectiva_id)
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
            'pieza' => 'nullable|integer|min:11|max:85',
            'diagnostico_pulpar' => 'nullable|string|max:120',
            'diagnostico_periapical' => 'nullable|string|max:120',
            'dolor' => 'nullable|string|max:80',
            'pruebas' => 'nullable|array',
            'hallazgos_rx' => 'nullable|string',
            'etapa' => 'nullable|string|max:80',
            'tecnica' => 'nullable|string|max:120',
            'material_obturacion' => 'nullable|string|max:120',
            'conductos' => 'nullable|integer|min:0|max:10',
            'tratamiento_realizado' => 'nullable|string',
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

        $ficha = FichaEndodoncia::create([
            ...$request->only([
                'paciente_id', 'fecha', 'pieza', 'diagnostico_pulpar', 'diagnostico_periapical',
                'dolor', 'pruebas', 'hallazgos_rx', 'etapa', 'tecnica', 'material_obturacion',
                'conductos', 'tratamiento_realizado', 'plan_tratamiento', 'observaciones',
            ]),
            'fecha' => $request->fecha ?? now()->toDateString(),
            'clinica_id' => $user->clinica_efectiva_id,
            'sucursal_id' => $request->sucursal_id ?? $user->sucursal_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ficha de endodoncia creada',
            'data' => $ficha,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $ficha = FichaEndodoncia::with(['paciente', 'user:id,nombre,apellidoPat'])->findOrFail($id);

        if ((int) $ficha->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($ficha);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $ficha = FichaEndodoncia::findOrFail($id);

        if ((int) $ficha->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ficha->update($request->only([
            'fecha', 'pieza', 'diagnostico_pulpar', 'diagnostico_periapical', 'dolor', 'pruebas',
            'hallazgos_rx', 'etapa', 'tecnica', 'material_obturacion', 'conductos',
            'tratamiento_realizado', 'plan_tratamiento', 'observaciones',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Ficha de endodoncia actualizada',
            'data' => $ficha->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            return response()->json(['error' => 'Solo administradores pueden eliminar'], 403);
        }

        $ficha = FichaEndodoncia::findOrFail($id);
        if ((int) $ficha->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ficha->delete();

        return response()->json(['success' => true, 'message' => 'Ficha de endodoncia eliminada']);
    }

    public function getByPaciente(int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::find($pacienteId);
        if (! $paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $list = FichaEndodoncia::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($list);
    }
}
