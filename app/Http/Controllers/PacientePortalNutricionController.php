<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteNutricionPlan;
use App\Models\PacienteNutricionSeguimiento;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacientePortalNutricionController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) return null;
        return Paciente::find($user->paciente_id);
    }

    public function planes(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $query = PacienteNutricionPlan::query()
            ->where('paciente_id', $paciente->id)
            ->where('publicado_en_portal', true)
            ->whereIn('estado', ['activo', 'cerrado'])
            ->with('clinica:id,nombre')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('created_at');

        if ($request->filled('clinica_id')) {
            $query->where('clinica_id', (int) $request->query('clinica_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function planActivo(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $query = PacienteNutricionPlan::query()
            ->where('paciente_id', $paciente->id)
            ->where('publicado_en_portal', true)
            ->where('estado', 'activo')
            ->with('clinica:id,nombre')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('created_at');

        if ($request->filled('clinica_id')) {
            $query->where('clinica_id', (int) $request->query('clinica_id'));
        }

        return response()->json(['data' => $query->first()]);
    }

    public function seguimientoIndex(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $days = max(1, min((int) $request->query('days', 30), 120));
        $from = now()->subDays($days - 1)->toDateString();

        $query = PacienteNutricionSeguimiento::query()
            ->where('paciente_id', $paciente->id)
            ->whereDate('fecha', '>=', $from)
            ->with('plan:id,titulo')
            ->orderByDesc('fecha');

        if ($request->filled('clinica_id')) {
            $query->where('clinica_id', (int) $request->query('clinica_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function seguimientoStore(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $payload = $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'fecha' => 'required|date',
            'plan_id' => 'nullable|integer|exists:paciente_nutricion_planes,id',
            'comidas' => 'nullable|array',
            'comidas_texto' => 'nullable|string',
            'agua_ml' => 'nullable|integer|min:0|max:10000',
            'ejercicio' => 'nullable|array',
            'ejercicio_texto' => 'nullable|string',
            'ejercicio_min' => 'nullable|integer|min:0|max:1000',
            'cumplio_plan' => 'nullable|boolean',
            'energia_nivel' => 'nullable|integer|min:1|max:10',
            'hambre_nivel' => 'nullable|integer|min:1|max:10',
            'estado_animo' => 'nullable|string|in:bien,cansado,hambre,estresado,motivado',
            'foto_comida_url' => 'nullable|string|max:500',
            'notas_paciente' => 'nullable|string',
            'completado' => 'nullable|boolean',
            'pasos' => 'nullable|integer|min:0|max:200000',
            'ritmo_cardiaco' => 'nullable|integer|min:30|max:250',
            'habitos' => 'nullable|array',
        ]);

        $pertenece = $paciente->clinicas()->where('clinicas.id', $payload['clinica_id'])->exists();
        if (! $pertenece) {
            return response()->json(['message' => 'No perteneces a esa clínica'], 403);
        }

        if (! empty($payload['plan_id'])) {
            $planValido = PacienteNutricionPlan::query()
                ->where('id', (int) $payload['plan_id'])
                ->where('paciente_id', $paciente->id)
                ->where('clinica_id', (int) $payload['clinica_id'])
                ->where('publicado_en_portal', true)
                ->exists();

            if (! $planValido) {
                return response()->json(['message' => 'El plan seleccionado no es válido para este espacio'], 422);
            }
        }

        $comidas = $payload['comidas'] ?? [];
        if (! empty($payload['comidas_texto'])) {
            $comidas['descripcion'] = $payload['comidas_texto'];
        }
        if (! empty($payload['foto_comida_url'])) {
            $comidas['foto_dia'] = $payload['foto_comida_url'];
        }

        $ejercicio = $payload['ejercicio'] ?? [];
        if (! empty($payload['ejercicio_texto'])) {
            $ejercicio['descripcion'] = $payload['ejercicio_texto'];
        }
        if (isset($payload['ejercicio_min'])) {
            $ejercicio['minutos'] = (int) $payload['ejercicio_min'];
        }

        $habitos = $payload['habitos'] ?? [];
        if (isset($payload['pasos'])) {
            $habitos['pasos'] = (int) $payload['pasos'];
        }
        if (isset($payload['ritmo_cardiaco'])) {
            $habitos['ritmo_cardiaco'] = (int) $payload['ritmo_cardiaco'];
        }
        if (! empty($payload['estado_animo'])) {
            $habitos['estado_animo'] = $payload['estado_animo'];
        }
        if (! empty($payload['foto_comida_url'])) {
            $habitos['foto_comida_url'] = $payload['foto_comida_url'];
        }

        $existente = PacienteNutricionSeguimiento::query()
            ->where('paciente_id', $paciente->id)
            ->where('clinica_id', (int) $payload['clinica_id'])
            ->whereDate('fecha', $payload['fecha'])
            ->first();

        if ($existente?->habitos && is_array($existente->habitos)) {
            $habitos = array_merge($existente->habitos, $habitos);
        }
        if ($existente?->comidas && is_array($existente->comidas) && ! empty($comidas)) {
            $comidas = array_merge($existente->comidas, $comidas);
        }

        $seguimiento = PacienteNutricionSeguimiento::updateOrCreate(
            [
                'paciente_id' => $paciente->id,
                'clinica_id' => (int) $payload['clinica_id'],
                'fecha' => $payload['fecha'],
            ],
            [
                'plan_id' => $payload['plan_id'] ?? $existente?->plan_id,
                'user_id' => Auth::id(),
                'comidas' => ! empty($comidas) ? $comidas : ($existente?->comidas),
                'agua_ml' => array_key_exists('agua_ml', $payload) ? $payload['agua_ml'] : $existente?->agua_ml,
                'ejercicio' => ! empty($ejercicio) ? $ejercicio : ($existente?->ejercicio),
                'habitos' => ! empty($habitos) ? $habitos : ($existente?->habitos),
                'cumplio_plan' => array_key_exists('cumplio_plan', $payload) ? $payload['cumplio_plan'] : $existente?->cumplio_plan,
                'energia_nivel' => array_key_exists('energia_nivel', $payload) ? $payload['energia_nivel'] : $existente?->energia_nivel,
                'hambre_nivel' => array_key_exists('hambre_nivel', $payload) ? $payload['hambre_nivel'] : $existente?->hambre_nivel,
                'notas_paciente' => array_key_exists('notas_paciente', $payload) ? $payload['notas_paciente'] : $existente?->notas_paciente,
                'completado' => $payload['completado'] ?? ($existente?->completado ?? true),
                'capturado_por' => 'paciente',
            ]
        );

        return response()->json(['data' => $seguimiento->fresh('plan:id,titulo')]);
    }

    public function uploadFotoComida(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'fecha' => 'nullable|date',
        ]);

        $pertenece = $paciente->clinicas()->where('clinicas.id', (int) $request->clinica_id)->exists();
        if (! $pertenece) {
            return response()->json(['message' => 'No perteneces a esa clínica'], 403);
        }

        $file = $request->file('foto');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $fecha = $request->input('fecha') ?: now()->toDateString();
        $path = $file->storeAs(
            'pacientes/nutricion',
            'comida_'.$paciente->id.'_'.$fecha.'_'.time().'.'.$ext,
            'public'
        );

        $url = asset('storage/'.$path);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => $url,
        ]);
    }

    public function coachTip(Request $request, AIService $ai)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $payload = $request->validate([
            'estado_animo' => 'nullable|string|max:40',
            'agua_pct' => 'nullable|integer|min:0|max:200',
            'comidas_hechas' => 'nullable|integer|min:0|max:20',
            'comidas_total' => 'nullable|integer|min:0|max:20',
            'ejercicios_hechos' => 'nullable|integer|min:0|max:50',
            'ejercicios_total' => 'nullable|integer|min:0|max:50',
            'completado' => 'nullable|boolean',
        ]);

        $nombre = trim(($paciente->nombre ?? '').' '.($paciente->apellidoPat ?? ''));
        $result = $ai->wellnessCoachTip(array_merge($payload, [
            'nombre' => $nombre !== '' ? explode(' ', $nombre)[0] : 'campeón/a',
        ]));

        return response()->json([
            'success' => (bool) ($result['success'] ?? false),
            'tip' => $result['text'] ?? null,
            'fallback' => ! empty($result['fallback']),
        ]);
    }
}
