<?php

namespace App\Http\Controllers;

use App\Services\GoogleGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodeController extends Controller
{
    /**
     * Preview de geocodificación para confirmar pin en alta/edición.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'direccion' => 'nullable|string|max:500',
            'ciudad' => 'nullable|string|max:120',
            'estado' => 'nullable|string|max:120',
            'codigo_postal' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $service = app(GoogleGeocodingService::class);
        $address = trim((string) ($validated['address'] ?? ''));
        if ($address === '') {
            $address = $service->buildAddressFromParts(
                $validated['direccion'] ?? null,
                $validated['ciudad'] ?? null,
                $validated['estado'] ?? null,
                $validated['codigo_postal'] ?? null
            );
        }

        if ($address === '' || $address === 'México') {
            return response()->json([
                'ok' => false,
                'status' => 'EMPTY',
                'message' => 'Escribe una dirección o elige un hospital de referencia.',
            ], 422);
        }

        $result = $service->geocode($address);

        return response()->json([
            'ok' => (bool) ($result['ok'] ?? false),
            'status' => $result['status'] ?? 'ERROR',
            'latitud' => $result['lat'] ?? null,
            'longitud' => $result['lng'] ?? null,
            'formatted_address' => $result['formatted_address'] ?? null,
            'address' => $address,
        ], ($result['ok'] ?? false) ? 200 : 422);
    }
}
