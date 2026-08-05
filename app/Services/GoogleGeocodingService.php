<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeocodingService
{
    /**
     * Geocodifica una dirección con Google Maps.
     *
     * @return array{ok:bool,lat:?float,lng:?float,status:string,formatted_address:?string,message:?string}
     */
    public function geocode(string $address): array
    {
        $address = trim($address);
        if ($address === '') {
            return [
                'ok' => false,
                'lat' => null,
                'lng' => null,
                'status' => 'EMPTY',
                'formatted_address' => null,
                'message' => 'Dirección vacía',
            ];
        }

        $key = (string) config('services.google_maps.geocoding_key', '');
        if ($key === '') {
            return [
                'ok' => false,
                'lat' => null,
                'lng' => null,
                'status' => 'NO_KEY',
                'formatted_address' => null,
                'message' => 'GOOGLE_MAPS_GEOCODING_KEY no configurada',
            ];
        }

        try {
            $response = Http::timeout(12)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $key,
                'language' => 'es',
                'region' => 'mx',
            ]);

            if (! $response->ok()) {
                return [
                    'ok' => false,
                    'lat' => null,
                    'lng' => null,
                    'status' => 'HTTP_'.$response->status(),
                    'formatted_address' => null,
                    'message' => 'Error HTTP al consultar Google Geocoding',
                ];
            }

            $json = $response->json();
            $status = (string) ($json['status'] ?? 'UNKNOWN');

            if ($status !== 'OK' || empty($json['results'][0]['geometry']['location'])) {
                return [
                    'ok' => false,
                    'lat' => null,
                    'lng' => null,
                    'status' => $status,
                    'formatted_address' => null,
                    'message' => $json['error_message'] ?? 'No se encontraron coordenadas',
                ];
            }

            $location = $json['results'][0]['geometry']['location'];

            return [
                'ok' => true,
                'lat' => (float) $location['lat'],
                'lng' => (float) $location['lng'],
                'status' => 'OK',
                'formatted_address' => $json['results'][0]['formatted_address'] ?? null,
                'message' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Google Geocoding falló', ['error' => $e->getMessage()]);

            return [
                'ok' => false,
                'lat' => null,
                'lng' => null,
                'status' => 'EXCEPTION',
                'formatted_address' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function buildAddressFromParts(
        ?string $direccion,
        ?string $ciudad = null,
        ?string $estado = null,
        ?string $codigoPostal = null,
        string $pais = 'México'
    ): string {
        return collect([$direccion, $ciudad, $estado, $codigoPostal, $pais])
            ->map(fn ($part) => trim((string) $part))
            ->filter(fn ($part) => $part !== '')
            ->implode(', ');
    }
}
