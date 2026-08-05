<?php

namespace App\Http\Controllers;

use App\Models\Sillon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SillonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

        $query = Sillon::where('clinica_id', $user->clinica_efectiva_id)
            ->orderBy('orden')
            ->orderBy('nombre');

        if ($sucursalId) {
            $query->where(function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->orWhereNull('sucursal_id');
            });
        }

        if ($request->boolean('solo_activos', true)) {
            $query->where('activo', true);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'nombre' => 'required|string|max:80',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'activo' => 'sometimes|boolean',
            'orden' => 'nullable|integer|min:0|max:999',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $sillon = Sillon::create([
            'clinica_id' => $user->clinica_efectiva_id,
            'sucursal_id' => $validated['sucursal_id'] ?? $user->sucursal_id,
            'nombre' => $validated['nombre'],
            'color' => $validated['color'] ?? '#3B82F6',
            'activo' => $validated['activo'] ?? true,
            'orden' => $validated['orden'] ?? 0,
        ]);

        return response()->json(['success' => true, 'data' => $sillon], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $sillon = Sillon::where('clinica_id', $user->clinica_efectiva_id)->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:80',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'activo' => 'sometimes|boolean',
            'orden' => 'nullable|integer|min:0|max:999',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $sillon->update($validated);

        return response()->json(['success' => true, 'data' => $sillon->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $sillon = Sillon::where('clinica_id', $user->clinica_efectiva_id)->findOrFail($id);
        $sillon->update(['activo' => false]);

        return response()->json(['success' => true, 'message' => 'Sillón desactivado']);
    }
}
