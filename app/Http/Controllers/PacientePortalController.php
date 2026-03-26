<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacientePortalController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) {
            return null;
        }

        return Paciente::query()->find($user->paciente_id);
    }

    private function pivotParaClinica(Paciente $paciente, int $clinicaId): ?object
    {
        $row = $paciente->clinicas()->where('clinicas.id', $clinicaId)->first();

        return $row?->pivot;
    }

    public function clinicas(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $rows = $paciente->clinicas()
            ->get()
            ->map(function ($c) {
                $p = $c->pivot;

                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'tipo_clinica' => $c->tipo_clinica,
                    'logo_url' => $c->logo_url ?? null,
                    'vinculado_at' => $p->vinculado_at,
                    'portal_visible_citas' => (bool) ($p->portal_visible_citas ?? false),
                    'portal_visible_datos_basicos' => (bool) ($p->portal_visible_datos_basicos ?? false),
                    'portal_visible_expediente_resumen' => (bool) ($p->portal_visible_expediente_resumen ?? false),
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function clinicaResumen(Request $request, int $clinicaId): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pivot = $this->pivotParaClinica($paciente, $clinicaId);
        if (! $pivot) {
            return response()->json(['message' => 'Clínica no vinculada'], 404);
        }

        $clinica = Clinica::query()->find($clinicaId);
        if (! $clinica) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        $datos = null;
        if ($pivot->portal_visible_datos_basicos ?? false) {
            $datos = [
                'nombre' => $paciente->nombre,
                'apellidoPat' => $paciente->apellidoPat,
                'apellidoMat' => $paciente->apellidoMat,
                'telefono' => $paciente->telefono,
                'email' => $paciente->email,
                'fechaNacimiento' => $paciente->fechaNacimiento,
                'genero' => $paciente->genero,
                'domicilio_formateado' => $paciente->domicilio_formateado,
            ];
        }

        return response()->json([
            'clinica' => [
                'id' => $clinica->id,
                'nombre' => $clinica->nombre,
                'tipo_clinica' => $clinica->tipo_clinica,
            ],
            'visibilidad' => [
                'citas' => (bool) ($pivot->portal_visible_citas ?? false),
                'datos_basicos' => (bool) ($pivot->portal_visible_datos_basicos ?? false),
                'expediente_resumen' => (bool) ($pivot->portal_visible_expediente_resumen ?? false),
            ],
            'datos_paciente' => $datos,
        ]);
    }

    public function citas(Request $request, int $clinicaId): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pivot = $this->pivotParaClinica($paciente, $clinicaId);
        if (! $pivot || ! ($pivot->portal_visible_citas ?? false)) {
            return response()->json(['message' => 'Las citas no están habilitadas para esta clínica.'], 403);
        }

        $query = Cita::query()
            ->forClinica($clinicaId)
            ->forPaciente($paciente->id)
            ->with(['sucursal'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc');

        $citas = $query->limit(100)->get()->map(function (Cita $c) {
            return [
                'id' => $c->id,
                'fecha' => $c->fecha?->format('Y-m-d'),
                'hora' => $c->hora ? \Carbon\Carbon::parse($c->hora)->format('H:i') : null,
                'estado' => $c->estado,
                'sucursal' => $c->sucursal ? ['id' => $c->sucursal->id, 'nombre' => $c->sucursal->nombre] : null,
            ];
        });

        return response()->json(['data' => $citas]);
    }
}
