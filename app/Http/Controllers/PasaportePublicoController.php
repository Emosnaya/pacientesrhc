<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Ficha pública limitada del pasaporte de salud (ICE / emergencia).
 * No expone email, teléfono del paciente ni documentos.
 */
class PasaportePublicoController extends Controller
{
    public function show(string $uuid): JsonResponse
    {
        if (! Str::isUuid($uuid)) {
            return response()->json(['message' => 'Pasaporte no encontrado'], 404);
        }

        $paciente = Paciente::query()
            ->where('uuid_publico', $uuid)
            ->first();

        if (! $paciente) {
            return response()->json(['message' => 'Pasaporte no encontrado'], 404);
        }

        $nombre = trim(implode(' ', array_filter([
            $paciente->nombre,
            $paciente->apellidoPat,
            $paciente->apellidoMat,
        ])));

        return response()->json([
            'uuid' => $paciente->uuid_publico,
            'nombre' => $nombre !== '' ? $nombre : 'Paciente',
            'grupo_sanguineo' => $paciente->grupo_sanguineo,
            'alergias' => $paciente->alergias,
            'contacto_emergencia_nombre' => $paciente->contacto_emergencia_nombre,
            'contacto_emergencia_telefono' => $paciente->contacto_emergencia_telefono,
            'notas_emergencia' => $paciente->notas_emergencia,
            'url' => rtrim((string) config('app.frontend_url'), '/') . '/pasaporte/' . $paciente->uuid_publico,
        ]);
    }
}
