<?php

namespace App\Support;

use App\Models\Landmark;
use App\Models\Sucursal;

class SucursalLocationPayload
{
    /**
     * Extrae campos de ubicación desde consultorio_data / form de sucursal.
     */
    public static function fromArray(array $data): array
    {
        $attrs = [
            'direccion' => $data['direccion'] ?? null,
            'ciudad' => $data['ciudad'] ?? null,
            'estado' => $data['estado'] ?? null,
            'codigo_postal' => $data['codigo_postal'] ?? null,
            'landmark_id' => ! empty($data['landmark_id']) ? (int) $data['landmark_id'] : null,
            'landmark_detalle' => $data['landmark_detalle'] ?? null,
        ];

        $lat = $data['latitud'] ?? null;
        $lng = $data['longitud'] ?? null;
        $manual = filter_var($data['coords_manuales'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '') {
            $attrs['latitud'] = (float) $lat;
            $attrs['longitud'] = (float) $lng;
            $attrs['coords_manuales'] = true;
            $attrs['geocode_status'] = 'MANUAL';
            $attrs['geocoded_at'] = now();
            $manual = true;
        }

        return [
            'attrs' => array_filter($attrs, fn ($v) => $v !== null && $v !== ''),
            'manual' => $manual,
            'lat' => isset($attrs['latitud']) ? (float) $attrs['latitud'] : null,
            'lng' => isset($attrs['longitud']) ? (float) $attrs['longitud'] : null,
        ];
    }

    public static function applyAfterCreate(Sucursal $sucursal, array $location): void
    {
        if (! empty($location['manual']) && $location['lat'] !== null && $location['lng'] !== null) {
            $sucursal->setManualCoords($location['lat'], $location['lng']);

            return;
        }

        if (! empty($sucursal->landmark_id) && ! $sucursal->tiene_coordenadas) {
            $sucursal->applyLandmarkCoordsIfNeeded();
        }
    }

    public static function landmarkLabel(?int $landmarkId): ?string
    {
        if (! $landmarkId) {
            return null;
        }

        return Landmark::query()->whereKey($landmarkId)->value('nombre');
    }
}
