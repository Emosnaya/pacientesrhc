<?php

namespace App\Http\Controllers;

use App\Models\Landmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandmarkController extends Controller
{
    /**
     * Catálogo de hospitales / plazas para alta de sucursal y búsqueda de pacientes.
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $ciudad = trim((string) $request->query('ciudad', 'Ciudad de México'));
        $tipo = trim((string) $request->query('tipo', ''));
        $limit = min(200, max(10, (int) $request->query('limit', 100)));
        $nearLat = $request->query('lat');
        $nearLng = $request->query('lng');
        $radiusKm = min(40, max(1, (float) $request->query('radius_km', 12)));

        $query = Landmark::query()->activos();

        if ($ciudad !== '' && strtolower($ciudad) !== 'all' && strtolower($ciudad) !== 'todas') {
            // Incluye CDMX + zona metropolitana habitual (Edomex) si piden CDMX.
            if (stripos($ciudad, 'México') !== false || stripos($ciudad, 'Mexico') !== false || strtoupper($ciudad) === 'CDMX') {
                $query->where(function ($sub) {
                    $sub->where('ciudad', 'like', '%México%')
                        ->orWhere('ciudad', 'like', '%Mexico%')
                        ->orWhere('estado', 'CDMX')
                        ->orWhere('estado', 'Edomex');
                });
            } else {
                $query->enCiudad($ciudad);
            }
        }

        if ($tipo !== '') {
            $query->where('tipo', $tipo);
        }

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('nombre', 'like', $like)
                    ->orWhere('alcaldia', 'like', $like)
                    ->orWhere('direccion', 'like', $like);
            });
        }

        if (is_numeric($nearLat) && is_numeric($nearLng)) {
            $lat = (float) $nearLat;
            $lng = (float) $nearLng;
            $haversine = '(6371 * acos(least(1, greatest(-1, cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud))))))';
            $query->select('landmarks.*')
                ->selectRaw("{$haversine} as distancia_km", [$lat, $lng, $lat])
                ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm])
                ->orderBy('distancia_km');
        } else {
            $query->orderBy('orden')->orderBy('nombre');
        }

        $rows = $query->limit($limit)->get()->map(fn (Landmark $l) => [
            'id' => $l->id,
            'nombre' => $l->nombre,
            'slug' => $l->slug,
            'tipo' => $l->tipo,
            'ciudad' => $l->ciudad,
            'alcaldia' => $l->alcaldia,
            'estado' => $l->estado,
            'direccion' => $l->direccion,
            'latitud' => $l->latitud,
            'longitud' => $l->longitud,
            'distancia_km' => isset($l->distancia_km) ? round((float) $l->distancia_km, 2) : null,
        ]);

        return response()->json([
            'data' => $rows,
            'meta' => [
                'count' => $rows->count(),
                'ciudad' => $ciudad !== '' ? $ciudad : null,
                'q' => $q !== '' ? $q : null,
            ],
        ]);
    }
}
