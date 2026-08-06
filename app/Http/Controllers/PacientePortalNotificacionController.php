<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteNotificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacientePortalNotificacionController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) {
            return null;
        }

        return Paciente::find($user->paciente_id);
    }

    public function index(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $limit = min(100, max(1, (int) $request->query('limit', 50)));

        $items = PacienteNotificacion::query()
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (PacienteNotificacion $n) => [
                'id' => $n->id,
                'tipo' => $n->tipo,
                'titulo' => $n->titulo,
                'cuerpo' => $n->cuerpo,
                'data' => $n->data,
                'leida' => $n->leida_at !== null,
                'leida_at' => $n->leida_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ]);

        $unread = PacienteNotificacion::query()
            ->where('paciente_id', $paciente->id)
            ->whereNull('leida_at')
            ->count();

        return response()->json([
            'data' => $items,
            'unread_count' => $unread,
        ]);
    }

    public function marcarLeidas(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'integer',
        ]);

        $query = PacienteNotificacion::query()
            ->where('paciente_id', $paciente->id)
            ->whereNull('leida_at');

        if (! empty($validated['ids'])) {
            $query->whereIn('id', $validated['ids']);
        }

        $updated = $query->update(['leida_at' => now()]);

        return response()->json([
            'message' => 'Notificaciones marcadas como leídas',
            'updated' => $updated,
        ]);
    }
}
