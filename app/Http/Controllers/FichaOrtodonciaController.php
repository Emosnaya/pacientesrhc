<?php

namespace App\Http\Controllers;

use App\Models\FichaOrtodoncia;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FichaOrtodonciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = FichaOrtodoncia::where('clinica_id', $user->clinica_efectiva_id)
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
            'clase_angle' => 'nullable|string|max:40',
            'patron_esqueletal' => 'nullable|string|max:80',
            'overjet_mm' => 'nullable|numeric|min:0|max:99',
            'overbite_mm' => 'nullable|numeric|min:0|max:99',
            'apinamiento' => 'nullable|string|max:80',
            'habitos' => 'nullable|string|max:255',
            'tipo_aparato' => 'nullable|string|max:120',
            'fase' => 'nullable|string|max:80',
            'proximo_control' => 'nullable|date',
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

        $ficha = FichaOrtodoncia::create([
            ...$request->only([
                'paciente_id', 'fecha', 'clase_angle', 'patron_esqueletal', 'overjet_mm', 'overbite_mm',
                'apinamiento', 'habitos', 'tipo_aparato', 'fase', 'proximo_control',
                'diagnostico', 'plan_tratamiento', 'observaciones',
            ]),
            'fecha' => $request->fecha ?? now()->toDateString(),
            'clinica_id' => $user->clinica_efectiva_id,
            'sucursal_id' => $request->sucursal_id ?? $user->sucursal_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ficha de ortodoncia creada',
            'data' => $ficha,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $ficha = FichaOrtodoncia::with(['paciente', 'user:id,nombre,apellidoPat'])->findOrFail($id);

        if ((int) $ficha->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($ficha);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $ficha = FichaOrtodoncia::findOrFail($id);

        if ((int) $ficha->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ficha->update($request->only([
            'fecha', 'clase_angle', 'patron_esqueletal', 'overjet_mm', 'overbite_mm',
            'apinamiento', 'habitos', 'tipo_aparato', 'fase', 'proximo_control',
            'diagnostico', 'plan_tratamiento', 'observaciones',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Ficha de ortodoncia actualizada',
            'data' => $ficha->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            return response()->json(['error' => 'Solo administradores pueden eliminar'], 403);
        }

        $ficha = FichaOrtodoncia::findOrFail($id);
        if ((int) $ficha->clinica_id !== (int) $user->clinica_efectiva_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ficha->delete();

        return response()->json(['success' => true, 'message' => 'Ficha de ortodoncia eliminada']);
    }

    public function getByPaciente(int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::find($pacienteId);
        if (! $paciente || ! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $list = FichaOrtodoncia::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($list);
    }
}
