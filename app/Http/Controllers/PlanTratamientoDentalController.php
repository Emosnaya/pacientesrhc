<?php

namespace App\Http\Controllers;

use App\Models\Odontograma;
use App\Models\Paciente;
use App\Models\PlanTratamientoDental;
use App\Models\PlanTratamientoDentalItem;
use App\Models\Presupuesto;
use App\Services\PresupuestoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlanTratamientoDentalController extends Controller
{
    public function __construct(private PresupuestoService $presupuestoService)
    {
    }

    private const ESTADOS_PLAN = ['activo', 'completado', 'cancelado'];

    private const ESTADOS_ITEM = ['pendiente', 'en_proceso', 'completado', 'cancelado'];

    private const MAPA_ESTADO_PROCEDIMIENTO = [
        'extraccion_indicada' => 'Extracción dental',
        'caries' => 'Tratamiento de caries / restauración',
        'obturado' => 'Revisión de obturación',
        'fracturado' => 'Rehabilitación por fractura',
        'corona' => 'Corona',
        'implante' => 'Implante dental',
        'endo_defectuosa' => 'Retratamiento endodóntico',
        'necrosis_pulpar' => 'Endodoncia (necrosis pulpar)',
        'pulpitis_irreversible' => 'Endodoncia (pulpitis irreversible)',
        'lesiones_periapicales' => 'Manejo de lesión periapical',
        'bolsas_periodontales' => 'Tratamiento periodontal (bolsas)',
        'calculo_supragingival' => 'Limpieza / profilaxis',
        'calculo_infragingival' => 'Raspado y alisado',
        'movilidad_dental' => 'Evaluación periodontal (movilidad)',
        'pseudobolsas' => 'Evaluación periodontal',
        'indice_placa' => 'Control de placa / higiene',
    ];

    public function index(Request $request, int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $planes = PlanTratamientoDental::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->with(['items', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $planes]);
    }

    public function store(Request $request, int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:180',
            'notas' => 'nullable|string|max:5000',
            'fecha' => 'nullable|date',
            'estado' => 'nullable|in:'.implode(',', self::ESTADOS_PLAN),
            'odontograma_id' => 'nullable|exists:odontogramas,id',
            'presupuesto_id' => 'nullable|exists:presupuestos,id',
            'items' => 'required|array|min:1',
            'items.*.diente' => 'nullable|string|max:10',
            'items.*.procedimiento' => 'required|string|max:255',
            'items.*.fase' => 'nullable|integer|min:1|max:20',
            'items.*.estado' => 'nullable|in:'.implode(',', self::ESTADOS_ITEM),
            'items.*.precio_estimado' => 'nullable|numeric|min:0',
            'items.*.notas' => 'nullable|string|max:2000',
            'items.*.orden' => 'nullable|integer|min:0',
        ]);

        if (! empty($validated['odontograma_id'])) {
            $odo = Odontograma::find($validated['odontograma_id']);
            if (! $odo || (int) $odo->paciente_id !== $pacienteId || (int) $odo->clinica_id !== (int) $user->clinica_efectiva_id) {
                return response()->json(['message' => 'Odontograma inválido'], 422);
            }
        }

        $plan = DB::transaction(function () use ($validated, $user, $pacienteId) {
            $plan = PlanTratamientoDental::create([
                'clinica_id' => $user->clinica_efectiva_id,
                'sucursal_id' => $user->sucursal_id,
                'paciente_id' => $pacienteId,
                'user_id' => $user->id,
                'odontograma_id' => $validated['odontograma_id'] ?? null,
                'presupuesto_id' => $validated['presupuesto_id'] ?? null,
                'titulo' => $validated['titulo'],
                'estado' => $validated['estado'] ?? 'activo',
                'fecha' => $validated['fecha'] ?? now()->toDateString(),
                'notas' => $validated['notas'] ?? null,
            ]);

            $this->syncItems($plan, $validated['items']);

            return $plan->load(['items', 'user:id,nombre,apellidoPat,apellidoMat']);
        });

        return response()->json(['success' => true, 'data' => $plan], 201);
    }

    public function update(Request $request, int $pacienteId, int $id): JsonResponse
    {
        $user = Auth::user();
        $plan = $this->findPlan($pacienteId, $id, $user);

        $validated = $request->validate([
            'titulo' => 'sometimes|string|max:180',
            'notas' => 'nullable|string|max:5000',
            'fecha' => 'nullable|date',
            'estado' => 'nullable|in:'.implode(',', self::ESTADOS_PLAN),
            'odontograma_id' => 'nullable|exists:odontogramas,id',
            'presupuesto_id' => 'nullable|exists:presupuestos,id',
            'items' => 'nullable|array|min:1',
            'items.*.diente' => 'nullable|string|max:10',
            'items.*.procedimiento' => 'required_with:items|string|max:255',
            'items.*.fase' => 'nullable|integer|min:1|max:20',
            'items.*.estado' => 'nullable|in:'.implode(',', self::ESTADOS_ITEM),
            'items.*.precio_estimado' => 'nullable|numeric|min:0',
            'items.*.notas' => 'nullable|string|max:2000',
            'items.*.orden' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($plan, $validated) {
            $plan->update(collect($validated)->except('items')->all());
            if (! empty($validated['items'])) {
                $plan->items()->delete();
                $this->syncItems($plan, $validated['items']);
            }
        });

        return response()->json(['success' => true, 'data' => $plan->fresh()->load(['items', 'user:id,nombre,apellidoPat,apellidoMat'])]);
    }

    public function updateItemEstado(Request $request, int $pacienteId, int $id, int $itemId): JsonResponse
    {
        $user = Auth::user();
        $plan = $this->findPlan($pacienteId, $id, $user);

        $validated = $request->validate([
            'estado' => 'required|in:'.implode(',', self::ESTADOS_ITEM),
        ]);

        $item = PlanTratamientoDentalItem::where('plan_tratamiento_dental_id', $plan->id)->findOrFail($itemId);
        $item->estado = $validated['estado'];
        $item->completado_at = $validated['estado'] === 'completado' ? now() : null;
        $item->save();

        $this->refreshPlanEstado($plan);

        return response()->json([
            'success' => true,
            'data' => $plan->fresh()->load(['items', 'user:id,nombre,apellidoPat,apellidoMat']),
        ]);
    }

    public function destroy(int $pacienteId, int $id): JsonResponse
    {
        $user = Auth::user();
        $plan = $this->findPlan($pacienteId, $id, $user);
        $plan->delete();

        return response()->json(['success' => true, 'message' => 'Plan eliminado']);
    }

    /**
     * Genera un presupuesto (borrador) a partir de los ítems no cancelados del plan.
     */
    public function generarPresupuesto(Request $request, int $pacienteId, int $id): JsonResponse
    {
        $user = Auth::user();
        $plan = $this->findPlan($pacienteId, $id, $user)->load('items');

        if ($plan->presupuesto_id) {
            $existente = Presupuesto::where('id', $plan->presupuesto_id)
                ->where('clinica_id', $user->clinica_efectiva_id)
                ->where('paciente_id', $pacienteId)
                ->first();
            if ($existente) {
                return response()->json([
                    'success' => true,
                    'message' => 'Este plan ya tiene un presupuesto vinculado',
                    'data' => [
                        'plan' => $plan,
                        'presupuesto_id' => $existente->id,
                        'presupuesto' => $existente->load('items'),
                        'ya_existia' => true,
                    ],
                ]);
            }
        }

        $itemsPlan = $plan->items->filter(fn ($i) => $i->estado !== 'cancelado')->values();
        if ($itemsPlan->isEmpty()) {
            return response()->json(['message' => 'No hay procedimientos activos para presupuestar'], 422);
        }

        $sinPrecio = $itemsPlan->filter(fn ($i) => $i->precio_estimado === null)->count();

        $presupuesto = DB::transaction(function () use ($user, $pacienteId, $plan, $itemsPlan) {
            $presupuesto = Presupuesto::create([
                'clinica_id' => $user->clinica_efectiva_id,
                'sucursal_id' => $plan->sucursal_id ?? $user->sucursal_id,
                'paciente_id' => $pacienteId,
                'user_id' => $user->id,
                'titulo' => 'Presupuesto — '.$plan->titulo,
                'descripcion' => 'Generado desde plan de tratamiento #'.$plan->id,
                'estado' => 'borrador',
                'fecha_emision' => now()->toDateString(),
                'fecha_vigencia' => now()->addDays(30)->toDateString(),
                'notas' => $plan->notas,
                'monto_total' => 0,
            ]);

            $items = $itemsPlan->map(function ($item) {
                $concepto = $item->procedimiento;
                if ($item->diente) {
                    $concepto = "Pieza {$item->diente}: {$item->procedimiento}";
                }

                return [
                    'concepto' => $concepto,
                    'descripcion' => trim(implode(' · ', array_filter([
                        $item->fase ? "Fase {$item->fase}" : null,
                        $item->notas,
                    ]))) ?: null,
                    'cantidad' => 1,
                    'precio_unitario' => (float) ($item->precio_estimado ?? 0),
                    'descuento' => 0,
                ];
            })->all();

            $this->presupuestoService->syncItems($presupuesto, $items);
            $plan->update(['presupuesto_id' => $presupuesto->id]);

            return $presupuesto->load('items');
        });

        $message = 'Presupuesto borrador creado';
        if ($sinPrecio > 0) {
            $message .= ". {$sinPrecio} ítem(s) sin precio estimado quedaron en \$0 — ajústalos en Presupuestos.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'plan' => $plan->fresh()->load(['items', 'user:id,nombre,apellidoPat,apellidoMat']),
                'presupuesto_id' => $presupuesto->id,
                'presupuesto' => $presupuesto,
                'ya_existia' => false,
                'items_sin_precio' => $sinPrecio,
            ],
        ], 201);
    }

    public function desdeOdontograma(Request $request, int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'odontograma_id' => 'nullable|exists:odontogramas,id',
            'titulo' => 'nullable|string|max:180',
        ]);

        $query = Odontograma::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id);

        if (! empty($validated['odontograma_id'])) {
            $odontograma = $query->where('id', $validated['odontograma_id'])->first();
        } else {
            $odontograma = $query->orderByDesc('fecha')->orderByDesc('id')->first();
        }

        if (! $odontograma) {
            return response()->json(['message' => 'No hay odontograma para este paciente'], 404);
        }

        $items = $this->itemsDesdeDientes($odontograma->dientes ?? []);
        if (count($items) === 0) {
            return response()->json(['message' => 'El odontograma no tiene hallazgos para armar un plan'], 422);
        }

        $plan = DB::transaction(function () use ($user, $pacienteId, $odontograma, $items, $validated) {
            $plan = PlanTratamientoDental::create([
                'clinica_id' => $user->clinica_efectiva_id,
                'sucursal_id' => $user->sucursal_id,
                'paciente_id' => $pacienteId,
                'user_id' => $user->id,
                'odontograma_id' => $odontograma->id,
                'titulo' => $validated['titulo'] ?? ('Plan desde odontograma — '.now()->format('d/m/Y')),
                'estado' => 'activo',
                'fecha' => now()->toDateString(),
                'notas' => $odontograma->diagnostico,
            ]);
            $this->syncItems($plan, $items);

            return $plan->load(['items', 'user:id,nombre,apellidoPat,apellidoMat']);
        });

        return response()->json(['success' => true, 'data' => $plan], 201);
    }

    private function itemsDesdeDientes($dientes): array
    {
        $list = [];
        $orden = 1;

        if (! is_array($dientes)) {
            return [];
        }

        // Puede venir como lista o mapa numerado
        foreach ($dientes as $diente) {
            if (! is_array($diente)) {
                continue;
            }
            $numero = $diente['numero'] ?? null;
            $estados = $diente['estados'] ?? (isset($diente['estado']) ? [$diente['estado']] : ['sano']);
            if (! is_array($estados)) {
                $estados = [$estados];
            }

            foreach ($estados as $estado) {
                if (! $estado || $estado === 'sano' || $estado === 'ausente') {
                    continue;
                }
                $list[] = [
                    'diente' => $numero !== null ? (string) $numero : null,
                    'procedimiento' => self::MAPA_ESTADO_PROCEDIMIENTO[$estado] ?? ucfirst(str_replace('_', ' ', $estado)),
                    'fase' => 1,
                    'estado' => 'pendiente',
                    'orden' => $orden++,
                    'notas' => $diente['notas'] ?? null,
                ];
            }
        }

        return $list;
    }

    private function syncItems(PlanTratamientoDental $plan, array $items): void
    {
        foreach ($items as $index => $item) {
            $estado = $item['estado'] ?? 'pendiente';
            $plan->items()->create([
                'diente' => $item['diente'] ?? null,
                'procedimiento' => $item['procedimiento'],
                'fase' => $item['fase'] ?? 1,
                'estado' => $estado,
                'precio_estimado' => $item['precio_estimado'] ?? null,
                'notas' => $item['notas'] ?? null,
                'orden' => $item['orden'] ?? ($index + 1),
                'completado_at' => $estado === 'completado' ? now() : null,
            ]);
        }
    }

    private function refreshPlanEstado(PlanTratamientoDental $plan): void
    {
        $plan->load('items');
        if ($plan->items->isEmpty()) {
            return;
        }

        $todosHechos = $plan->items->every(fn ($i) => in_array($i->estado, ['completado', 'cancelado'], true));
        if ($todosHechos && $plan->estado === 'activo') {
            $plan->update(['estado' => 'completado']);
        }
    }

    private function findPlan(int $pacienteId, int $id, $user): PlanTratamientoDental
    {
        $plan = PlanTratamientoDental::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        return $plan;
    }
}
