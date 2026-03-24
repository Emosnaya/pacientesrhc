<?php

namespace App\Http\Controllers;

use App\Models\Vademecum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VademecumController extends Controller
{
    /**
     * Buscar medicamentos (local + RxNorm fallback)
     */
    public function buscar(Request $request)
    {
        $termino = $request->get('q', '');
        $categoria = $request->get('categoria');
        $limite = min($request->get('limite', 20), 50);

        if (strlen($termino) < 2) {
            return response()->json(['resultados' => [], 'fuente' => 'local']);
        }

        // Búsqueda local primero
        $query = Vademecum::activos()->search($termino);

        if ($categoria) {
            $query->categoria($categoria);
        }

        $resultadosLocales = $query->orderBy('nombre_generico')
            ->limit($limite)
            ->get()
            ->map(fn($m) => $this->formatearMedicamento($m, 'local'));

        // Si hay pocos resultados locales, buscar en RxNorm
        $resultadosExternos = collect();
        if ($resultadosLocales->count() < 5 && strlen($termino) >= 3) {
            $resultadosExternos = $this->buscarRxNorm($termino, $limite - $resultadosLocales->count());
        }

        return response()->json([
            'resultados' => $resultadosLocales->merge($resultadosExternos)->unique('nombre_generico')->values(),
            'fuente' => $resultadosExternos->isNotEmpty() ? 'mixto' : 'local',
            'total' => $resultadosLocales->count() + $resultadosExternos->count(),
        ]);
    }

    /**
     * Buscar en RxNorm (NIH) como fallback
     */
    private function buscarRxNorm(string $termino, int $limite = 10)
    {
        try {
            $response = Http::timeout(3)->get('https://rxnav.nlm.nih.gov/REST/drugs.json', [
                'name' => $termino,
            ]);

            if (!$response->successful()) {
                return collect();
            }

            $data = $response->json();
            $conceptGroup = $data['drugGroup']['conceptGroup'] ?? [];

            $resultados = collect();
            foreach ($conceptGroup as $group) {
                if (!isset($group['conceptProperties'])) continue;
                
                foreach ($group['conceptProperties'] as $drug) {
                    $resultados->push([
                        'id' => 'rxnorm_' . $drug['rxcui'],
                        'nombre_generico' => $drug['name'],
                        'nombre_comercial' => null,
                        'nombre_completo' => $drug['name'],
                        'presentacion' => $group['tty'] ?? null,
                        'presentacion_completa' => $group['tty'] ?? '—',
                        'concentracion' => null,
                        'via_administracion' => null,
                        'categoria' => null,
                        'dosis_sugerida' => null,
                        'duracion_sugerida' => null,
                        'requiere_receta' => true,
                        'controlado' => false,
                        'fuente' => 'rxnorm',
                        'rxcui' => $drug['rxcui'],
                    ]);

                    if ($resultados->count() >= $limite) break 2;
                }
            }

            return $resultados;
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function formatearMedicamento(Vademecum $m, string $fuente): array
    {
        return [
            'id' => $m->id,
            'nombre_generico' => $m->nombre_generico,
            'nombre_comercial' => $m->nombre_comercial,
            'nombre_completo' => $m->nombre_completo,
            'presentacion' => $m->presentacion,
            'presentacion_completa' => $m->presentacion_completa,
            'concentracion' => $m->concentracion,
            'via_administracion' => $m->via_administracion,
            'categoria' => $m->categoria,
            'dosis_sugerida' => $m->dosis_sugerida,
            'duracion_sugerida' => $m->duracion_sugerida,
            'requiere_receta' => $m->requiere_receta,
            'controlado' => $m->controlado,
            'fuente' => $fuente,
        ];
    }

    /**
     * Listar categorías disponibles
     */
    public function categorias()
    {
        $categorias = Vademecum::activos()
            ->distinct()
            ->whereNotNull('categoria')
            ->pluck('categoria')
            ->sort()
            ->values();

        return response()->json($categorias);
    }

    /**
     * Guardar medicamento personalizado
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_generico' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'presentacion' => 'nullable|string|max:100',
            'concentracion' => 'nullable|string|max:100',
            'via_administracion' => 'nullable|string|max:50',
            'categoria' => 'nullable|string|max:100',
            'dosis_sugerida' => 'nullable|string|max:500',
            'duracion_sugerida' => 'nullable|string|max:100',
        ]);

        // Verificar si ya existe
        $existe = Vademecum::where('nombre_generico', $request->nombre_generico)
            ->where('concentracion', $request->concentracion)
            ->first();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Medicamento ya existe',
                'medicamento' => $this->formatearMedicamento($existe, 'local'),
            ]);
        }

        $medicamento = Vademecum::create($request->only([
            'nombre_generico',
            'nombre_comercial',
            'presentacion',
            'concentracion',
            'via_administracion',
            'categoria',
            'dosis_sugerida',
            'duracion_sugerida',
            'indicaciones',
            'contraindicaciones',
        ]));

        return response()->json([
            'mensaje' => 'Medicamento guardado',
            'medicamento' => $this->formatearMedicamento($medicamento, 'local'),
        ], 201);
    }

    /**
     * Importar desde RxNorm a local
     */
    public function importarRxNorm(Request $request)
    {
        $request->validate([
            'nombre_generico' => 'required|string',
            'rxcui' => 'required|string',
        ]);

        // Verificar si ya existe
        $existe = Vademecum::where('nombre_generico', $request->nombre_generico)->first();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Medicamento ya existe en el catálogo',
                'medicamento' => $this->formatearMedicamento($existe, 'local'),
            ]);
        }

        $medicamento = Vademecum::create([
            'nombre_generico' => $request->nombre_generico,
            'nombre_comercial' => $request->nombre_comercial,
            'presentacion' => $request->presentacion,
        ]);

        return response()->json([
            'mensaje' => 'Medicamento importado',
            'medicamento' => $this->formatearMedicamento($medicamento, 'local'),
        ], 201);
    }
}
