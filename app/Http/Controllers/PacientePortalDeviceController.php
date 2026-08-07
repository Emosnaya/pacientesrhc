<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacientePortalDeviceController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) {
            return null;
        }

        return Paciente::find($user->paciente_id);
    }

    public function register(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $payload = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|in:ios,android,web',
            'device_name' => 'nullable|string|max:120',
        ]);

        $token = trim($payload['token']);
        if (! str_starts_with($token, 'ExponentPushToken[') && ! str_starts_with($token, 'ExpoPushToken[')) {
            return response()->json(['message' => 'Token de Expo inválido'], 422);
        }

        $row = PacienteDeviceToken::updateOrCreate(
            ['token' => $token],
            [
                'paciente_id' => $paciente->id,
                'user_id' => Auth::id(),
                'platform' => $payload['platform'] ?? null,
                'device_name' => $payload['device_name'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'id' => $row->id]);
    }

    public function unregister(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $payload = $request->validate([
            'token' => 'required|string|max:255',
        ]);

        PacienteDeviceToken::query()
            ->where('paciente_id', $paciente->id)
            ->where('token', $payload['token'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
