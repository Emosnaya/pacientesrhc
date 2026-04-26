<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Presupuesto;
use App\Services\PresupuestoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PresupuestoController extends Controller
{
    public function __construct(private PresupuestoService $service)
    {
    }

    private function assertAdminAccess(int $clinicaId): ?JsonResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAdminAccess($clinicaId)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return null;
    }

    private function getPacienteOrFail(int $pacienteId, int $clinicaId): Paciente
    {
        return Paciente::forClinicaWorkspace($clinicaId)->findOrFail($pacienteId);
    }

    private function withStats(Presupuesto $presupuesto): array
    {
        $stats = $this->service->calcularAvancePagos($presupuesto);

        return [
            'id' => $presupuesto->id,
            'clinica_id' => $presupuesto->clinica_id,
            'sucursal_id' => $presupuesto->sucursal_id,
            'paciente_id' => $presupuesto->paciente_id,
            'user_id' => $presupuesto->user_id,
            'titulo' => $presupuesto->titulo,
            'descripcion' => $presupuesto->descripcion,
            'estado' => $presupuesto->estado,
            'fecha_emision' => $presupuesto->fecha_emision?->toDateString(),
            'fecha_vigencia' => $presupuesto->fecha_vigencia?->toDateString(),
            'monto_total' => (float) $presupuesto->monto_total,
            'notas' => $presupuesto->notas,
            'condiciones_pago' => $presupuesto->condiciones_pago,
            'paciente_aceptado_at' => $presupuesto->paciente_aceptado_at,
            'paciente_rechazado_at' => $presupuesto->paciente_rechazado_at,
            'paciente_aceptacion_tipo' => $presupuesto->paciente_aceptacion_tipo,
            'firma_paciente' => $presupuesto->firma_paciente,
            'paciente_aceptacion_ip' => $presupuesto->paciente_aceptacion_ip,
            'items' => $presupuesto->items,
            'clinica' => $presupuesto->clinica,
            'sucursal' => $presupuesto->sucursal,
            'creado_por' => $presupuesto->user,
            'created_at' => $presupuesto->created_at,
            'updated_at' => $presupuesto->updated_at,
            ...$stats,
        ];
    }

    private function enviarCorreoNuevoPresupuesto(Presupuesto $presupuesto): void
    {
        $presupuesto->loadMissing(['paciente:id,nombre,apellidoPat,apellidoMat,email', 'clinica:id,nombre']);

        $email = trim((string) ($presupuesto->paciente?->email ?? ''));
        if ($email === '') {
            return;
        }

        $clinicaNombre = $presupuesto->clinica?->nombre ?: 'Tu clínica';
        $nombrePaciente = trim(
            ($presupuesto->paciente?->nombre ?? '') . ' ' .
            ($presupuesto->paciente?->apellidoPat ?? '') . ' ' .
            ($presupuesto->paciente?->apellidoMat ?? '')
        );

        $portalUrl = rtrim((string) config('app.frontend_url'), '/') . '/portal/presupuestos';
        $subject = "Nuevo presupuesto disponible - {$clinicaNombre}";

        Mail::send('emails.presupuesto-nuevo', [
            'nombrePaciente' => $nombrePaciente,
            'clinicaNombre' => $clinicaNombre,
            'titulo' => $presupuesto->titulo,
            'montoTotal' => (float) $presupuesto->monto_total,
            'portalUrl' => $portalUrl,
        ], function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }

    public function indexByPaciente(Request $request, int $pacienteId): JsonResponse
    {
        $user = $request->user();
        $clinicaId = (int) $user->clinica_efectiva_id;

        if ($resp = $this->assertAdminAccess($clinicaId)) {
            return $resp;
        }

        $this->getPacienteOrFail($pacienteId, $clinicaId);

        $rows = Presupuesto::query()
            ->where('clinica_id', $clinicaId)
            ->where('paciente_id', $pacienteId)
            ->with(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Presupuesto $p) => $this->withStats($p))
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request, int $pacienteId): JsonResponse
    {
        $user = $request->user();
        $clinicaId = (int) $user->clinica_efectiva_id;

        if ($resp = $this->assertAdminAccess($clinicaId)) {
            return $resp;
        }

        $paciente = $this->getPacienteOrFail($pacienteId, $clinicaId);

        $validated = $request->validate([
            'titulo' => 'required|string|max:180',
            'descripcion' => 'nullable|string',
            'fecha_emision' => 'nullable|date',
            'fecha_vigencia' => 'nullable|date|after_or_equal:fecha_emision',
            'notas' => 'nullable|string',
            'condiciones_pago' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.concepto' => 'required|string|max:255',
            'items.*.descripcion' => 'nullable|string',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ]);

        $presupuesto = Presupuesto::create([
            'clinica_id' => $clinicaId,
            'sucursal_id' => $paciente->sucursal_id,
            'paciente_id' => $paciente->id,
            'user_id' => $user->id,
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => 'enviado',
            'fecha_emision' => $validated['fecha_emision'] ?? now()->toDateString(),
            'fecha_vigencia' => $validated['fecha_vigencia'] ?? null,
            'notas' => $validated['notas'] ?? null,
            'condiciones_pago' => $validated['condiciones_pago'] ?? null,
            'monto_total' => 0,
        ]);

        $this->service->syncItems($presupuesto, $validated['items']);
        $presupuesto->load(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat']);

        try {
            $this->enviarCorreoNuevoPresupuesto($presupuesto);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Presupuesto creado y enviado al paciente',
            'data' => $this->withStats($presupuesto),
        ], 201);
    }

    public function show(Request $request, int $pacienteId, int $id): JsonResponse
    {
        $user = $request->user();
        $clinicaId = (int) $user->clinica_efectiva_id;

        if ($resp = $this->assertAdminAccess($clinicaId)) {
            return $resp;
        }

        $this->getPacienteOrFail($pacienteId, $clinicaId);

        $presupuesto = Presupuesto::query()
            ->where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('paciente_id', $pacienteId)
            ->with(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->firstOrFail();

        return response()->json(['data' => $this->withStats($presupuesto)]);
    }

    public function update(Request $request, int $pacienteId, int $id): JsonResponse
    {
        $user = $request->user();
        $clinicaId = (int) $user->clinica_efectiva_id;

        if ($resp = $this->assertAdminAccess($clinicaId)) {
            return $resp;
        }

        $this->getPacienteOrFail($pacienteId, $clinicaId);

        $presupuesto = Presupuesto::query()
            ->where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('paciente_id', $pacienteId)
            ->firstOrFail();

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:180',
            'descripcion' => 'nullable|string',
            'fecha_emision' => 'nullable|date',
            'fecha_vigencia' => 'nullable|date|after_or_equal:fecha_emision',
            'notas' => 'nullable|string',
            'condiciones_pago' => 'nullable|string',
            'items' => 'nullable|array|min:1',
            'items.*.concepto' => 'required_with:items|string|max:255',
            'items.*.descripcion' => 'nullable|string',
            'items.*.cantidad' => 'required_with:items|numeric|min:0.01',
            'items.*.precio_unitario' => 'required_with:items|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ]);

        $presupuesto->fill([
            'titulo' => $validated['titulo'] ?? $presupuesto->titulo,
            'descripcion' => array_key_exists('descripcion', $validated) ? $validated['descripcion'] : $presupuesto->descripcion,
            'fecha_emision' => array_key_exists('fecha_emision', $validated) ? $validated['fecha_emision'] : $presupuesto->fecha_emision,
            'fecha_vigencia' => array_key_exists('fecha_vigencia', $validated) ? $validated['fecha_vigencia'] : $presupuesto->fecha_vigencia,
            'notas' => array_key_exists('notas', $validated) ? $validated['notas'] : $presupuesto->notas,
            'condiciones_pago' => array_key_exists('condiciones_pago', $validated) ? $validated['condiciones_pago'] : $presupuesto->condiciones_pago,
        ]);
        $presupuesto->save();

        if (! empty($validated['items'])) {
            $this->service->syncItems($presupuesto, $validated['items']);
        }

        $presupuesto->load(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat']);

        return response()->json([
            'message' => 'Presupuesto actualizado correctamente',
            'data' => $this->withStats($presupuesto),
        ]);
    }

    public function updateEstado(Request $request, int $pacienteId, int $id): JsonResponse
    {
        $user = $request->user();
        $clinicaId = (int) $user->clinica_efectiva_id;

        if ($resp = $this->assertAdminAccess($clinicaId)) {
            return $resp;
        }

        $this->getPacienteOrFail($pacienteId, $clinicaId);

        $presupuesto = Presupuesto::query()
            ->where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('paciente_id', $pacienteId)
            ->firstOrFail();

        $validated = $request->validate([
            'estado' => 'required|in:enviado,aceptado,rechazado,cancelado,completado',
        ]);

        $presupuesto->estado = $validated['estado'];
        $presupuesto->save();
        $presupuesto->load(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat']);

        return response()->json([
            'message' => 'Estado actualizado',
            'data' => $this->withStats($presupuesto),
        ]);
    }

    public function destroy(Request $request, int $pacienteId, int $id): JsonResponse
    {
        $user = $request->user();
        $clinicaId = (int) $user->clinica_efectiva_id;

        if ($resp = $this->assertAdminAccess($clinicaId)) {
            return $resp;
        }

        $this->getPacienteOrFail($pacienteId, $clinicaId);

        $presupuesto = Presupuesto::query()
            ->where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('paciente_id', $pacienteId)
            ->firstOrFail();

        $presupuesto->delete();

        return response()->json(['message' => 'Presupuesto eliminado']);
    }

    private function pacientePortalAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) {
            return null;
        }

        return Paciente::query()->find($user->paciente_id);
    }

    public function portalIndex(Request $request): JsonResponse
    {
        $paciente = $this->pacientePortalAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allowedClinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $rows = Presupuesto::query()
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $allowedClinicaIds)
            ->with(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Presupuesto $p) => $this->withStats($p))
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function portalIndexByClinica(Request $request, int $clinicaId): JsonResponse
    {
        $paciente = $this->pacientePortalAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pivot = $paciente->clinicas()->where('clinicas.id', $clinicaId)->first();
        if (! $pivot) {
            return response()->json(['message' => 'Clínica no vinculada'], 404);
        }

        $rows = Presupuesto::query()
            ->where('paciente_id', $paciente->id)
            ->where('clinica_id', $clinicaId)
            ->with(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Presupuesto $p) => $this->withStats($p))
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function portalShow(Request $request, int $id): JsonResponse
    {
        $paciente = $this->pacientePortalAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allowedClinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $presupuesto = Presupuesto::query()
            ->where('id', $id)
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $allowedClinicaIds)
            ->with(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat'])
            ->firstOrFail();

        return response()->json(['data' => $this->withStats($presupuesto)]);
    }

    public function portalAceptar(Request $request, int $id): JsonResponse
    {
        $paciente = $this->pacientePortalAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allowedClinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $presupuesto = Presupuesto::query()
            ->where('id', $id)
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $allowedClinicaIds)
            ->firstOrFail();

        $validated = $request->validate([
            'accion' => 'required|in:aceptar,rechazar',
            'aceptacion_tipo' => 'nullable|in:visualizacion,firma',
            'firma_paciente' => 'nullable|string',
        ]);

        if (($validated['accion'] ?? null) === 'aceptar') {
            $tipo = $validated['aceptacion_tipo'] ?? 'visualizacion';
            if ($tipo === 'firma') {
                $firma = $validated['firma_paciente'] ?? null;
                if (! $firma || ! preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $firma)) {
                    return response()->json([
                        'message' => 'La firma digital es requerida y debe ser una imagen válida en base64.',
                    ], 422);
                }
                $presupuesto->firma_paciente = $firma;
            }

            $presupuesto->estado = 'aceptado';
            $presupuesto->paciente_aceptado_at = now();
            $presupuesto->paciente_rechazado_at = null;
            $presupuesto->paciente_aceptacion_tipo = $tipo;
            $presupuesto->paciente_aceptacion_ip = (string) $request->ip();
            $presupuesto->paciente_aceptacion_user_agent = substr((string) ($request->userAgent() ?? ''), 0, 1000);
            $mensaje = 'Presupuesto aceptado correctamente';
        } else {
            $presupuesto->estado = 'rechazado';
            $presupuesto->paciente_rechazado_at = now();
            $presupuesto->paciente_aceptado_at = null;
            $presupuesto->paciente_aceptacion_tipo = null;
            $presupuesto->firma_paciente = null;
            $mensaje = 'Presupuesto rechazado';
        }

        $presupuesto->save();
        $presupuesto->load(['items', 'clinica:id,nombre,tipo_clinica', 'sucursal:id,nombre', 'user:id,nombre,apellidoPat,apellidoMat']);

        return response()->json([
            'message' => $mensaje,
            'data' => $this->withStats($presupuesto),
        ]);
    }
}
