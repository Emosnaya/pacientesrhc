<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteNutricionPlan;
use App\Models\PacienteNutricionSeguimiento;
use App\Traits\ClinicaScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacienteNutricionPlanController extends Controller
{
    use ClinicaScope;

    private function resolvePacienteEnClinica(int $pacienteId, int $clinicaId): ?Paciente
    {
        $paciente = Paciente::find($pacienteId);
        if (! $paciente) return null;
        return $paciente->belongsToClinicaWorkspace($clinicaId) ? $paciente : null;
    }

    public function index(Request $request, int $pacienteId)
    {
        $clinicaId = $this->getClinicaIdFromRequest($request);
        if (! $clinicaId) return response()->json(['message' => 'No autorizado'], 403);

        $paciente = $this->resolvePacienteEnClinica($pacienteId, $clinicaId);
        if (! $paciente) return response()->json(['message' => 'Paciente no pertenece a la clínica'], 403);

        $planes = PacienteNutricionPlan::query()
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->with('user:id,nombre,apellidoPat,apellidoMat')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $planes]);
    }

    public function store(Request $request, int $pacienteId)
    {
        $clinicaId = $this->getClinicaIdFromRequest($request);
        if (! $clinicaId) return response()->json(['message' => 'No autorizado'], 403);

        $paciente = $this->resolvePacienteEnClinica($pacienteId, $clinicaId);
        if (! $paciente) return response()->json(['message' => 'Paciente no pertenece a la clínica'], 403);

        $payload = $request->validate([
            'titulo' => 'required|string|max:160',
            'objetivo' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'kcal_objetivo' => 'nullable|numeric|min:0|max:99999.99',
            'macros' => 'nullable|array',
            'plan_alimenticio' => 'nullable|array',
            'plan_ejercicio' => 'nullable|array',
            'plan_alimenticio_texto' => 'nullable|string',
            'plan_ejercicio_texto' => 'nullable|string',
            'notas' => 'nullable|string',
            'estado' => 'nullable|in:borrador,activo,cerrado',
            'publicado_en_portal' => 'nullable|boolean',
        ]);

        $planAlimenticio = $payload['plan_alimenticio'] ?? [];
        if (!empty($payload['plan_alimenticio_texto'])) {
            $planAlimenticio['descripcion'] = $payload['plan_alimenticio_texto'];
        }

        $planEjercicio = $payload['plan_ejercicio'] ?? [];
        if (!empty($payload['plan_ejercicio_texto'])) {
            $planEjercicio['descripcion'] = $payload['plan_ejercicio_texto'];
        }

        $plan = PacienteNutricionPlan::create([
            'paciente_id' => $pacienteId,
            'clinica_id' => $clinicaId,
            'sucursal_id' => $paciente->sucursal_id,
            'user_id' => Auth::id(),
            'titulo' => $payload['titulo'],
            'objetivo' => $payload['objetivo'] ?? null,
            'fecha_inicio' => $payload['fecha_inicio'] ?? null,
            'fecha_fin' => $payload['fecha_fin'] ?? null,
            'kcal_objetivo' => $payload['kcal_objetivo'] ?? null,
            'macros' => $payload['macros'] ?? null,
            'plan_alimenticio' => !empty($planAlimenticio) ? $planAlimenticio : null,
            'plan_ejercicio' => !empty($planEjercicio) ? $planEjercicio : null,
            'notas' => $payload['notas'] ?? null,
            'estado' => $payload['estado'] ?? 'activo',
            'publicado_en_portal' => $payload['publicado_en_portal'] ?? true,
            'version' => 1,
        ]);

        return response()->json(['data' => $plan->fresh('user:id,nombre,apellidoPat,apellidoMat')], 201);
    }

    public function update(Request $request, int $pacienteId, int $planId)
    {
        $clinicaId = $this->getClinicaIdFromRequest($request);
        if (! $clinicaId) return response()->json(['message' => 'No autorizado'], 403);

        $plan = PacienteNutricionPlan::query()
            ->where('id', $planId)
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->firstOrFail();

        $payload = $request->validate([
            'titulo' => 'sometimes|required|string|max:160',
            'objetivo' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'kcal_objetivo' => 'nullable|numeric|min:0|max:99999.99',
            'macros' => 'nullable|array',
            'plan_alimenticio' => 'nullable|array',
            'plan_ejercicio' => 'nullable|array',
            'plan_alimenticio_texto' => 'nullable|string',
            'plan_ejercicio_texto' => 'nullable|string',
            'notas' => 'nullable|string',
            'estado' => 'nullable|in:borrador,activo,cerrado',
            'publicado_en_portal' => 'nullable|boolean',
        ]);

        if (array_key_exists('plan_alimenticio_texto', $payload)) {
            $planAlim = $payload['plan_alimenticio'] ?? ($plan->plan_alimenticio ?? []);
            $planAlim = is_array($planAlim) ? $planAlim : [];
            $planAlim['descripcion'] = $payload['plan_alimenticio_texto'];
            $payload['plan_alimenticio'] = $planAlim;
        }

        if (array_key_exists('plan_ejercicio_texto', $payload)) {
            $planEjer = $payload['plan_ejercicio'] ?? ($plan->plan_ejercicio ?? []);
            $planEjer = is_array($planEjer) ? $planEjer : [];
            $planEjer['descripcion'] = $payload['plan_ejercicio_texto'];
            $payload['plan_ejercicio'] = $planEjer;
        }

        unset($payload['plan_alimenticio_texto'], $payload['plan_ejercicio_texto']);

        if (!empty($payload)) {
            $payload['version'] = (int) $plan->version + 1;
            $plan->update($payload);
        }

        return response()->json(['data' => $plan->fresh('user:id,nombre,apellidoPat,apellidoMat')]);
    }

    public function destroy(Request $request, int $pacienteId, int $planId)
    {
        $clinicaId = $this->getClinicaIdFromRequest($request);
        if (! $clinicaId) return response()->json(['message' => 'No autorizado'], 403);

        $plan = PacienteNutricionPlan::query()
            ->where('id', $planId)
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->firstOrFail();

        $plan->delete();

        return response()->json(['message' => 'Plan eliminado']);
    }

    public function seguimientoIndex(Request $request, int $pacienteId)
    {
        $clinicaId = $this->getClinicaIdFromRequest($request);
        if (! $clinicaId) return response()->json(['message' => 'No autorizado'], 403);

        $paciente = $this->resolvePacienteEnClinica($pacienteId, $clinicaId);
        if (! $paciente) return response()->json(['message' => 'Paciente no pertenece a la clínica'], 403);

        $query = PacienteNutricionSeguimiento::query()
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->with(['plan:id,titulo', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->orderByDesc('fecha');

        if ($request->filled('from')) {
            $query->whereDate('fecha', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('fecha', '<=', $request->query('to'));
        }

        $items = $query->get();

        $resumen = [
            'total_registros' => $items->count(),
            'adherencia_promedio' => $items->whereNotNull('cumplio_plan')->count() > 0
                ? round($items->where('cumplio_plan', true)->count() * 100 / $items->whereNotNull('cumplio_plan')->count(), 1)
                : null,
            'agua_promedio_ml' => $items->whereNotNull('agua_ml')->count() > 0
                ? (int) round($items->whereNotNull('agua_ml')->avg('agua_ml'))
                : null,
        ];

        return response()->json(['data' => $items, 'resumen' => $resumen]);
    }

    public function seguimientoStore(Request $request, int $pacienteId)
    {
        $clinicaId = $this->getClinicaIdFromRequest($request);
        if (! $clinicaId) return response()->json(['message' => 'No autorizado'], 403);

        $paciente = $this->resolvePacienteEnClinica($pacienteId, $clinicaId);
        if (! $paciente) return response()->json(['message' => 'Paciente no pertenece a la clínica'], 403);

        $payload = $request->validate([
            'fecha' => 'required|date',
            'plan_id' => 'nullable|integer|exists:paciente_nutricion_planes,id',
            'comidas' => 'nullable|array',
            'comidas_texto' => 'nullable|string',
            'agua_ml' => 'nullable|integer|min:0|max:10000',
            'ejercicio' => 'nullable|array',
            'ejercicio_texto' => 'nullable|string',
            'ejercicio_min' => 'nullable|integer|min:0|max:1000',
            'habitos' => 'nullable|array',
            'cumplio_plan' => 'nullable|boolean',
            'energia_nivel' => 'nullable|integer|min:1|max:10',
            'hambre_nivel' => 'nullable|integer|min:1|max:10',
            'notas_paciente' => 'nullable|string',
            'notas_clinica' => 'nullable|string',
            'completado' => 'nullable|boolean',
        ]);

        if (!empty($payload['plan_id'])) {
            $planValido = PacienteNutricionPlan::query()
                ->where('id', (int) $payload['plan_id'])
                ->where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->exists();

            if (! $planValido) {
                return response()->json(['message' => 'El plan seleccionado no pertenece al paciente en esta clínica'], 422);
            }
        }

        $comidas = $payload['comidas'] ?? [];
        if (!empty($payload['comidas_texto'])) {
            $comidas['descripcion'] = $payload['comidas_texto'];
        }

        $ejercicio = $payload['ejercicio'] ?? [];
        if (!empty($payload['ejercicio_texto'])) {
            $ejercicio['descripcion'] = $payload['ejercicio_texto'];
        }
        if (isset($payload['ejercicio_min'])) {
            $ejercicio['minutos'] = (int) $payload['ejercicio_min'];
        }

        $seguimiento = PacienteNutricionSeguimiento::updateOrCreate(
            [
                'paciente_id' => $pacienteId,
                'clinica_id' => $clinicaId,
                'fecha' => $payload['fecha'],
            ],
            [
                'sucursal_id' => $paciente->sucursal_id,
                'plan_id' => $payload['plan_id'] ?? null,
                'user_id' => Auth::id(),
                'comidas' => !empty($comidas) ? $comidas : null,
                'agua_ml' => $payload['agua_ml'] ?? null,
                'ejercicio' => !empty($ejercicio) ? $ejercicio : null,
                'habitos' => $payload['habitos'] ?? null,
                'cumplio_plan' => $payload['cumplio_plan'] ?? null,
                'energia_nivel' => $payload['energia_nivel'] ?? null,
                'hambre_nivel' => $payload['hambre_nivel'] ?? null,
                'notas_paciente' => $payload['notas_paciente'] ?? null,
                'notas_clinica' => $payload['notas_clinica'] ?? null,
                'completado' => $payload['completado'] ?? false,
                'capturado_por' => 'staff',
            ]
        );

        return response()->json(['data' => $seguimiento->fresh(['plan:id,titulo', 'user:id,nombre,apellidoPat,apellidoMat'])]);
    }
}
