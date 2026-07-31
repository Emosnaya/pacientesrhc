<?php

namespace App\Http\Controllers;

use App\Jobs\SendCitaWhatsAppNotification;
use App\Models\Cita;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\ChatParticipante;
use App\Models\Clinica;
use App\Models\Evento;
use App\Models\Paciente;
use App\Models\PortalExpedienteCompartido;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\CitaAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PacientePortalController extends Controller
{
    private const AGENDA_SLOTS = ['09:00', '10:30', '11:00', '13:30', '14:00', '16:30'];
    private const AGENDA_DOCTOR_ROLES = ['doctor', 'doctora', 'licenciado', 'enfermero', 'enfermera', 'fisioterapeuta'];

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
                    'logo' => $c->logo,
                    'logo_url' => $c->logo_url ?? null,
                    'color_principal' => $c->color_principal,
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
            $g = (int) (bool) $paciente->genero;
            $datos = [
                'nombre' => $paciente->nombre,
                'apellidoPat' => $paciente->apellidoPat,
                'apellidoMat' => $paciente->apellidoMat,
                'telefono' => $paciente->telefono,
                'email' => $paciente->email,
                'fechaNacimiento' => $paciente->fechaNacimiento?->format('Y-m-d'),
                'genero' => $g,
                'genero_label' => $g === 1 ? 'Masculino' : 'Femenino',
                'domicilio_formateado' => $paciente->domicilio_formateado,
            ];
        }

        return response()->json([
            'clinica' => [
                'id' => $clinica->id,
                'nombre' => $clinica->nombre,
                'tipo_clinica' => $clinica->tipo_clinica,
                'logo' => $clinica->logo,
                'logo_url' => $clinica->logo_url ?? null,
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

    /**
     * Datos del paciente para su perfil en el portal (información propia).
     */
    public function perfil(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($this->perfilPayload($paciente->load('clinicas')));
    }

    /**
     * Actualiza el mismo registro Paciente que usa la clínica (lista blanca de campos).
     */
    public function updatePerfil(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($request->input('fechaNacimiento') === '') {
            $request->merge(['fechaNacimiento' => null]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidoPat' => 'required|string|max:100',
            'apellidoMat' => 'nullable|string|max:100',
            'email' => 'required|email|max:191',
            'telefono' => 'nullable|string|max:50',
            'fechaNacimiento' => 'nullable|date',
            'genero' => 'required|boolean',
            'calle' => 'nullable|string|max:255',
            'num_ext' => 'nullable|string|max:50',
            'num_int' => 'nullable|string|max:50',
            'colonia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:255',
            'estado_dir' => 'nullable|string|max:255',
            'alergias' => 'nullable|string',
        ]);

        $email = strtolower(trim($validated['email']));
        $emailTaken = User::query()
            ->where('email', $email)
            ->where('id', '!=', $user->id)
            ->exists();
        if ($emailTaken) {
            return response()->json([
                'message' => 'Ese correo ya está registrado en otra cuenta.',
                'errors' => ['email' => ['Ese correo ya está registrado en otra cuenta.']],
            ], 422);
        }

        $paciente->nombre = $validated['nombre'];
        $paciente->apellidoPat = $validated['apellidoPat'];
        $paciente->apellidoMat = $validated['apellidoMat'] ?? null;
        $paciente->email = $email;
        $paciente->telefono = $validated['telefono'] ?? null;
        $paciente->genero = ($validated['genero'] === true || $validated['genero'] === 1 || $validated['genero'] === '1') ? 1 : 0;

        if (array_key_exists('fechaNacimiento', $validated)) {
            if ($validated['fechaNacimiento'] === null) {
                $paciente->fechaNacimiento = null;
                $paciente->edad = 0;
            } else {
                $paciente->fechaNacimiento = $validated['fechaNacimiento'];
                $paciente->edad = Carbon::parse($validated['fechaNacimiento'])->age;
            }
        }

        $paciente->calle = $validated['calle'] ?? null;
        $paciente->num_ext = $validated['num_ext'] ?? null;
        $paciente->num_int = $validated['num_int'] ?? null;
        $paciente->colonia = $validated['colonia'] ?? null;
        $paciente->codigo_postal = $validated['codigo_postal'] ?? null;
        $paciente->ciudad = $validated['ciudad'] ?? null;
        $paciente->estado_dir = $validated['estado_dir'] ?? null;
        $paciente->alergias = $validated['alergias'] ?? null;

        $paciente->save();

        $user->email = $email;
        $user->nombre = $validated['nombre'];
        $user->apellidoPat = $validated['apellidoPat'];
        $user->apellidoMat = $validated['apellidoMat'] ?? null;
        $user->save();

        return response()->json($this->perfilPayload($paciente->fresh()->load('clinicas')));
    }

    /**
     * @return array<string, mixed>
     */
    private function perfilPayload(Paciente $paciente): array
    {
        $g = (int) (bool) $paciente->genero;

        $motivos = $paciente->clinicas
            ->map(function ($c) {
                return [
                    'clinica_id' => $c->id,
                    'nombre' => $c->nombre,
                    'motivo_consulta' => $c->pivot->motivo_consulta,
                ];
            })
            ->values()
            ->all();

        $payload = [
            'uuid_publico' => $paciente->uuid_publico,
            'nombre' => $paciente->nombre,
            'apellidoPat' => $paciente->apellidoPat,
            'apellidoMat' => $paciente->apellidoMat,
            'email' => $paciente->email,
            'telefono' => $paciente->telefono,
            'fechaNacimiento' => $paciente->fechaNacimiento?->format('Y-m-d'),
            'genero' => $g,
            'genero_label' => $g === 1 ? 'Masculino' : 'Femenino',
            'calle' => $paciente->calle,
            'num_ext' => $paciente->num_ext,
            'num_int' => $paciente->num_int,
            'colonia' => $paciente->colonia,
            'codigo_postal' => $paciente->codigo_postal,
            'ciudad' => $paciente->ciudad,
            'estado_dir' => $paciente->estado_dir,
            'domicilio_formateado' => $paciente->domicilio_formateado,
            'motivos_consulta_clinicas' => $motivos,
            'alergias' => $paciente->alergias,
        ];

        $dom = $paciente->domicilio;
        if ($dom !== null && trim((string) $dom) !== '') {
            $payload['domicilio'] = $dom;
        }

        return $payload;
    }

    /**
     * Citas en rango para calendario: solo clínicas/consultorios con portal_visible_citas.
     */
    public function citasCalendario(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $clinicaIds = $paciente->clinicas()
            ->wherePivot('portal_visible_citas', true)
            ->pluck('clinicas.id');

        if ($clinicaIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $from = $request->query('from', Carbon::now()->subMonths(1)->toDateString());
        $to = $request->query('to', Carbon::now()->addMonths(6)->toDateString());

        $citas = Cita::query()
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $clinicaIds)
            ->whereBetween('fecha', [$from, $to])
            ->with(['sucursal', 'clinica'])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(function (Cita $c) {
                return [
                    'id' => $c->id,
                    'fecha' => $c->fecha?->format('Y-m-d'),
                    'hora' => $c->hora ? Carbon::parse($c->hora)->format('H:i') : null,
                    'estado' => $c->estado,
                    'clinica' => $c->clinica ? [
                        'id' => $c->clinica->id,
                        'nombre' => $c->clinica->nombre,
                    ] : null,
                    'sucursal' => $c->sucursal ? [
                        'id' => $c->sucursal->id,
                        'nombre' => $c->sucursal->nombre,
                    ] : null,
                ];
            });

        return response()->json(['data' => $citas]);
    }

    /**
     * Catálogo para nueva cita: clínicas vinculadas o búsqueda de nuevas clínicas/consultorios.
     */
    public function agendaClinicas(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $scope = $request->query('scope', 'linked'); // linked | nearby
        $q = trim((string) $request->query('q', ''));

        $linked = $paciente->clinicas()->withPivot([
            'portal_visible_citas',
            'portal_agenda_bloqueado',
            'portal_agenda_bloqueado_hasta',
            'portal_agenda_bloqueo_motivo',
        ])->get();

        $linkedIds = $linked->pluck('id')->all();

        if ($scope === 'linked') {
            $rows = $linked
                ->filter(fn ($c) => (bool) ($c->pivot->portal_visible_citas ?? false))
                ->map(fn ($c) => $this->agendaClinicaPayload($c, true, $c->pivot))
                ->values();

            return response()->json(['data' => $rows]);
        }

        $query = Clinica::query()->where('activa', true);
        if (! empty($linkedIds)) {
            $query->whereNotIn('id', $linkedIds);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('direccion', 'like', "%{$q}%")
                    ->orWhere('tipo_clinica', 'like', "%{$q}%");
            });
        }

        $rows = $query->orderBy('nombre')->limit(30)->get()
            ->map(fn ($c) => $this->agendaClinicaPayload($c, false, null))
            ->values();

        return response()->json(['data' => $rows]);
    }

    /**
     * Disponibilidad por fecha para una clínica/consultorio.
     */
    public function agendaDisponibilidad(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'fecha' => 'required|date_format:Y-m-d',
            'doctor_id' => 'nullable|integer|exists:users,id',
            'especialidad' => 'nullable|string|max:120',
        ]);

        $clinica = Clinica::findOrFail((int) $validated['clinica_id']);
        if (! $clinica->activa) {
            return response()->json(['message' => 'La clínica/consultorio no está disponible'], 422);
        }

        $fecha = (string) $validated['fecha'];
        $doctorId = isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null;
        $especialidad = isset($validated['especialidad']) ? trim((string) $validated['especialidad']) : null;
        if (! empty($especialidad)) {
            $especialidad = $this->agendaResolveEspecialidad($clinica, $especialidad);
            if (! $especialidad) {
                return response()->json([
                    'message' => 'La especialidad seleccionada no está habilitada en esta clínica/consultorio',
                    'especialidades_habilitadas' => $this->agendaEspecialidadesEtiquetas($clinica),
                ], 422);
            }
        }

        $slots = collect(self::AGENDA_SLOTS)->map(function (string $slot) use ($clinica, $paciente, $fecha, $doctorId, $especialidad) {
            $check = $this->agendaCanBook($clinica, $paciente, $fecha, $slot, $doctorId, $especialidad);
            return [
                'hora' => $slot,
                'disponible' => $check['ok'],
                'motivo' => $check['ok'] ? null : $check['message'],
            ];
        })->values();

        return response()->json(['data' => $slots]);
    }

    /**
     * Crear cita desde el portal paciente.
     */
    public function agendaCrearCita(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
            'doctor_id' => 'nullable|integer|exists:users,id',
            'especialidad' => 'nullable|string|max:120',
            'notas' => 'nullable|string|max:1000',
        ]);

        $clinica = Clinica::findOrFail((int) $validated['clinica_id']);
        if (! $clinica->activa) {
            return response()->json(['message' => 'La clínica/consultorio no está disponible'], 422);
        }

        $doctorId = isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null;
        $especialidad = isset($validated['especialidad']) ? trim((string) $validated['especialidad']) : null;
        if (! empty($especialidad)) {
            $especialidad = $this->agendaResolveEspecialidad($clinica, $especialidad);
            if (! $especialidad) {
                return response()->json([
                    'message' => 'La especialidad seleccionada no está habilitada en esta clínica/consultorio',
                    'especialidades_habilitadas' => $this->agendaEspecialidadesEtiquetas($clinica),
                ], 422);
            }
        }
        if ($doctorId && ! User::query()->where('id', $doctorId)->where('clinica_id', $clinica->id)->exists()) {
            return response()->json(['message' => 'El doctor seleccionado no pertenece a esta clínica/consultorio'], 422);
        }
        if (! $doctorId && empty($especialidad)) {
            return response()->json(['message' => 'Debes indicar la especialidad si no seleccionas doctor'], 422);
        }

        $check = $this->agendaCanBook(
            $clinica,
            $paciente,
            (string) $validated['fecha'],
            (string) $validated['hora'],
            $doctorId,
            $especialidad
        );
        if (! $check['ok']) {
            return response()->json(['message' => $check['message']], 422);
        }

        $requiereConfirmacionClinica = ! $doctorId;

        $pivot = $paciente->clinicas()->where('clinicas.id', $clinica->id)->first()?->pivot;
        if (! $pivot) {
            $sucursalPrincipal = Sucursal::query()->where('clinica_id', $clinica->id)->where('es_principal', true)->value('id');
            $paciente->clinicas()->syncWithoutDetaching([
                $clinica->id => [
                    'sucursal_id' => $sucursalPrincipal,
                    'user_id' => null,
                    'vinculado_at' => now(),
                    'portal_visible_citas' => true,
                    'portal_visible_datos_basicos' => true,
                    'portal_visible_expediente_resumen' => false,
                    'portal_agenda_bloqueado' => false,
                ],
            ]);
            $pivot = $paciente->clinicas()->where('clinicas.id', $clinica->id)->first()?->pivot;
        }

        $adminId = User::query()
            ->where('clinica_id', $clinica->id)
            ->where(function ($q) {
                $q->where('isAdmin', 1)->orWhere('isSuperAdmin', 1);
            })
            ->value('id')
            ?? User::query()->where('clinica_id', $clinica->id)->value('id');

        if (! $adminId) {
            return response()->json(['message' => 'No hay personal disponible para agendar en esta clínica/consultorio'], 422);
        }

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'admin_id' => (int) $adminId,
            'user_id' => $doctorId,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $pivot?->sucursal_id,
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'estado' => $requiereConfirmacionClinica
                ? 'pendiente'
                : app(CitaAvailabilityService::class)->estadoInicial($clinica),
            'primera_vez' => false,
            'notas' => $this->agendaBuildNotas(
                $validated['notas'] ?? null,
                $especialidad
            ),
            'reagenda_intentos' => 0,
        ]);

        $chatConversacion = $this->agendaEnsurePatientChatConversation(
            $clinica,
            $paciente,
            (int) $adminId,
            (string) $validated['fecha'],
            (string) $validated['hora'],
            $especialidad
        );

        SendCitaWhatsAppNotification::dispatch($cita->id, 'confirmacion');

        return response()->json([
            'message' => $requiereConfirmacionClinica
                ? 'Solicitud de cita enviada. La clínica/consultorio debe confirmar el horario.'
                : 'Cita agendada correctamente',
            'data' => [
                'id' => $cita->id,
                'fecha' => $cita->fecha?->format('Y-m-d'),
                'hora' => Carbon::parse($cita->hora)->format('H:i'),
                'estado' => $cita->estado,
                'clinica' => ['id' => $clinica->id, 'nombre' => $clinica->nombre],
                'doctor_id' => $doctorId,
                'especialidad' => $especialidad,
                'chat_conversacion_id' => $chatConversacion?->id,
                'requiere_confirmacion_clinica' => $requiereConfirmacionClinica,
                'siguiente_paso' => $requiereConfirmacionClinica
                    ? 'Si no hay disponibilidad con staff en ese horario, la clínica podrá proponerte otros horarios o contactarte por chat.'
                    : null,
            ],
        ], 201);
    }

    /**
     * Reagendar cita con máximo de intentos configurable.
     */
    public function agendaReagendarCita(Request $request, int $id): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
            'doctor_id' => 'nullable|integer|exists:users,id',
            'especialidad' => 'nullable|string|max:120',
        ]);

        $cita = Cita::query()->where('id', $id)->where('paciente_id', $paciente->id)->first();
        if (! $cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        if (in_array($cita->estado, ['cancelada', 'completada'], true)) {
            return response()->json(['message' => 'Esta cita ya no puede reagendarse'], 422);
        }

        $clinica = Clinica::findOrFail((int) $cita->clinica_id);
        $maxReagendas = max(0, (int) ($clinica->portal_max_reagendas_paciente ?? 2));
        $bloqueoDias = max(0, (int) ($clinica->portal_bloqueo_dias_post_cancelacion ?? 7));
        $intentos = (int) ($cita->reagenda_intentos ?? 0);

        if ($intentos >= $maxReagendas) {
            $cita->estado = 'cancelada';
            $cita->cancelada_por_regla = true;
            $cita->motivo_cancelacion = 'Límite de reagendas alcanzado';
            $cita->save();
            SendCitaWhatsAppNotification::dispatch($cita->id, 'cancelacion');

            $paciente->clinicas()->updateExistingPivot($clinica->id, [
                'portal_agenda_bloqueado' => true,
                'portal_agenda_bloqueado_hasta' => now()->addDays($bloqueoDias)->toDateString(),
                'portal_agenda_bloqueo_motivo' => 'Límite de reagendas alcanzado',
            ]);

            return response()->json([
                'message' => "La cita fue cancelada por exceder el máximo de reagendas. Podrás agendar nuevamente en {$bloqueoDias} día(s).",
            ], 422);
        }

        $doctorId = array_key_exists('doctor_id', $validated)
            ? ((int) $validated['doctor_id'] ?: null)
            : $cita->user_id;
        $especialidad = isset($validated['especialidad']) ? trim((string) $validated['especialidad']) : null;
        if (! empty($especialidad)) {
            $especialidad = $this->agendaResolveEspecialidad($clinica, $especialidad);
            if (! $especialidad) {
                return response()->json([
                    'message' => 'La especialidad seleccionada no está habilitada en esta clínica/consultorio',
                    'especialidades_habilitadas' => $this->agendaEspecialidadesEtiquetas($clinica),
                ], 422);
            }
        }
        if (! $doctorId && empty($especialidad)) {
            return response()->json(['message' => 'Debes indicar la especialidad si no seleccionas doctor'], 422);
        }

        $check = $this->agendaCanBook(
            $clinica,
            $paciente,
            (string) $validated['fecha'],
            (string) $validated['hora'],
            $doctorId,
            $especialidad
        );
        if (! $check['ok']) {
            return response()->json(['message' => $check['message']], 422);
        }

        $nueva = Cita::create([
            'paciente_id' => $cita->paciente_id,
            'admin_id' => $cita->admin_id,
            'user_id' => $doctorId,
            'clinica_id' => $cita->clinica_id,
            'sucursal_id' => $cita->sucursal_id,
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'estado' => 'pendiente',
            'primera_vez' => false,
            'notas' => $this->agendaBuildNotas($cita->notas, $especialidad),
            'reagenda_intentos' => $intentos + 1,
            'reagendada_de_cita_id' => $cita->id,
        ]);

        $cita->estado = 'cancelada';
        $cita->motivo_cancelacion = 'Reagendada por paciente';
        $cita->save();
        SendCitaWhatsAppNotification::dispatch($nueva->id, 'reagendada');

        return response()->json([
            'message' => 'Cita reagendada correctamente',
            'data' => [
                'id' => $nueva->id,
                'fecha' => $nueva->fecha?->format('Y-m-d'),
                'hora' => Carbon::parse($nueva->hora)->format('H:i'),
                'reagenda_intentos' => $nueva->reagenda_intentos,
                'reagendas_restantes' => max(0, $maxReagendas - (int) $nueva->reagenda_intentos),
            ],
        ]);
    }

    /**
     * @return array{ok:bool,message:?string}
     */
    private function agendaCanBook(
        Clinica $clinica,
        Paciente $paciente,
        string $fecha,
        string $hora,
        ?int $doctorId = null,
        ?string $especialidad = null
    ): array
    {
        $availability = app(CitaAvailabilityService::class);

        if ($availability->isPastSlot($fecha, $hora)) {
            return ['ok' => false, 'message' => 'Ese horario ya no está disponible'];
        }

        $pivot = $paciente->clinicas()->where('clinicas.id', $clinica->id)->first()?->pivot;
        if ($pivot && (bool) ($pivot->portal_agenda_bloqueado ?? false)) {
            $bloqueadoHasta = $pivot->portal_agenda_bloqueado_hasta ? Carbon::parse($pivot->portal_agenda_bloqueado_hasta) : null;
            if (! $bloqueadoHasta || $bloqueadoHasta->isFuture() || $bloqueadoHasta->isToday()) {
                $msg = 'No puedes agendar en este espacio por el momento';
                if ($bloqueadoHasta) {
                    $msg .= ' (bloqueo hasta '.$bloqueadoHasta->format('Y-m-d').')';
                }
                return ['ok' => false, 'message' => $msg];
            }
        }

        $sucursalId = $pivot?->sucursal_id
            ?? Sucursal::query()->where('clinica_id', $clinica->id)->where('es_principal', true)->value('id');

        return $availability->canBook(
            $clinica,
            $fecha,
            $hora,
            $sucursalId ? (int) $sucursalId : null,
            $doctorId,
            $paciente->id
        );
    }

    private function agendaBuildNotas(?string $notas, ?string $especialidad): ?string
    {
        $notas = $notas !== null ? trim($notas) : '';
        $especialidad = $especialidad !== null ? trim($especialidad) : '';

        if ($especialidad === '') {
            return $notas !== '' ? $notas : null;
        }

        $prefix = '[Especialidad solicitada: '.$this->agendaEspecialidadEtiqueta($especialidad).']';
        if ($notas === '') {
            return $prefix;
        }

        if (str_starts_with($notas, '[Especialidad solicitada:')) {
            $rest = preg_replace('/^\[Especialidad solicitada:\s*.*?\]\s*/u', '', $notas) ?? '';
            return trim($prefix."\n".$rest);
        }

        return $prefix."\n".$notas;
    }

    private function agendaEnsurePatientChatConversation(
        Clinica $clinica,
        Paciente $paciente,
        int $staffUserId,
        string $fecha,
        string $hora,
        ?string $especialidad
    ): ?ChatConversacion {
        $patientUserId = (int) Auth::id();
        if (! $patientUserId || $patientUserId === $staffUserId) {
            return null;
        }

        $conv = ChatConversacion::query()
            ->where('clinica_id', $clinica->id)
            ->where('tipo', 'directo')
            ->whereHas('participantes', fn ($q) => $q->where('user_id', $patientUserId))
            ->whereHas('participantes', fn ($q) => $q->where('user_id', $staffUserId))
            ->first();

        if (! $conv) {
            $conv = ChatConversacion::create([
                'clinica_id' => $clinica->id,
                'tipo' => 'directo',
                'nombre' => null,
                'created_by' => $patientUserId,
            ]);

            ChatParticipante::create([
                'conversacion_id' => $conv->id,
                'user_id' => $patientUserId,
                'last_read_at' => now(),
            ]);

            ChatParticipante::create([
                'conversacion_id' => $conv->id,
                'user_id' => $staffUserId,
                'last_read_at' => null,
            ]);
        }

        $especialidadLabel = $especialidad ? $this->agendaEspecialidadEtiqueta($especialidad) : 'General';
        $mensaje = "Hola, acabo de solicitar una cita para {$fecha} a las {$hora}. Especialidad: {$especialidadLabel}.";

        ChatMensaje::create([
            'conversacion_id' => $conv->id,
            'user_id' => $patientUserId,
            'mensaje' => $mensaje,
        ]);

        ChatParticipante::where('conversacion_id', $conv->id)
            ->where('user_id', $patientUserId)
            ->update(['last_read_at' => now()]);

        return $conv;
    }

    /**
     * @return array<int,string>
     */
    private function agendaEspecialidadesPermitidas(Clinica $clinica): array
    {
        $modulos = collect($clinica->modulos_efectivos ?? [])
            ->filter(fn ($m) => is_string($m) && trim($m) !== '')
            ->map(fn ($m) => $this->agendaEspecialidadClave((string) $m))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! empty($modulos)) {
            return $modulos;
        }

        $fallback = $this->agendaEspecialidadClave((string) ($clinica->tipo_clinica ?? ''));
        return $fallback ? [$fallback] : [];
    }

    /**
     * @return array<int,string>
     */
    private function agendaEspecialidadesEtiquetas(Clinica $clinica): array
    {
        return collect($this->agendaEspecialidadesPermitidas($clinica))
            ->map(fn ($k) => $this->agendaEspecialidadEtiqueta($k))
            ->values()
            ->all();
    }

    private function agendaResolveEspecialidad(Clinica $clinica, string $input): ?string
    {
        $needle = $this->agendaEspecialidadClave($input);
        if ($needle === '') {
            return null;
        }

        $permitidas = $this->agendaEspecialidadesPermitidas($clinica);
        if (in_array($needle, $permitidas, true)) {
            return $needle;
        }

        foreach ($permitidas as $key) {
            $labelSlug = $this->agendaEspecialidadClave($this->agendaEspecialidadEtiqueta($key));
            if ($needle === $labelSlug) {
                return $key;
            }
        }

        return null;
    }

    private function agendaEspecialidadClave(string $value): string
    {
        $slug = (string) Str::of($value)
            ->lower()
            ->ascii()
            ->replace('-', '_')
            ->replace(' ', '_')
            ->value();

        $slug = preg_replace('/[^a-z0-9_]/', '', $slug) ?? '';
        return trim($slug, '_');
    }

    private function agendaEspecialidadEtiqueta(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }

    private function agendaClinicaPayload(Clinica $clinica, bool $vinculada, $pivot = null): array
    {
        $doctores = User::query()
            ->where('clinica_id', $clinica->id)
            ->whereIn('rol', self::AGENDA_DOCTOR_ROLES)
            ->select(['id', 'nombre', 'apellidoPat', 'apellidoMat', 'rol'])
            ->limit(20)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'nombre' => trim(($u->nombre ?? '').' '.($u->apellidoPat ?? '').' '.($u->apellidoMat ?? '')),
                'rol' => $u->rol,
            ])
            ->values();

        $especialidades = collect($this->agendaEspecialidadesEtiquetas($clinica));

        return [
            'id' => $clinica->id,
            'nombre' => $clinica->nombre,
            'tipo_clinica' => $clinica->tipo_clinica,
            'direccion' => $clinica->direccion,
            'logo' => $clinica->logo,
            'logo_url' => $clinica->logo_url,
            'color_principal' => $clinica->color_principal,
            'vinculada' => $vinculada,
            'portal_visible_citas' => $vinculada ? (bool) ($pivot?->portal_visible_citas ?? false) : true,
            'agenda_bloqueado' => $vinculada ? (bool) ($pivot?->portal_agenda_bloqueado ?? false) : false,
            'agenda_bloqueado_hasta' => $vinculada && ! empty($pivot?->portal_agenda_bloqueado_hasta)
                ? Carbon::parse($pivot->portal_agenda_bloqueado_hasta)->format('Y-m-d')
                : null,
            'agenda_bloqueo_motivo' => $vinculada ? ($pivot?->portal_agenda_bloqueo_motivo ?? null) : null,
            'permite_multiples_citas_mismo_horario' => app(CitaAvailabilityService::class)->modoSolapamiento($clinica) === CitaAvailabilityService::MODO_PERMITIR,
            'cita_estado_inicial' => app(CitaAvailabilityService::class)->estadoInicial($clinica),
            'citas_solapamiento_modo' => app(CitaAvailabilityService::class)->modoSolapamiento($clinica),
            'especialidades' => $especialidades,
            'doctores' => $doctores,
        ];
    }

    /**
     * Expedientes que el personal marcó como visibles para este paciente en el portal.
     */
    public function expedientesCompartidos(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allowedClinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $rows = PortalExpedienteCompartido::query()
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $allowedClinicaIds)
            ->with(['clinica:id,nombre'])
            ->orderByDesc('fecha_snapshot')
            ->orderByDesc('id')
            ->get()
            ->map(function (PortalExpedienteCompartido $r) {
                return [
                    'id' => $r->id,
                    'tipo_exp' => (int) $r->tipo_exp,
                    'expediente_id' => (int) $r->expediente_id,
                    'tipo_nombre' => $r->tipo_nombre_snapshot,
                    'fecha' => $r->fecha_snapshot?->format('Y-m-d'),
                    'clinica' => $r->clinica ? [
                        'id' => $r->clinica->id,
                        'nombre' => $r->clinica->nombre,
                    ] : null,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    /**
     * PDF de un expediente que el personal compartió al portal (LFPDPPP: solo filas en portal_expediente_compartidos).
     * Tipos soportados se amplían según match abajo.
     */
    public function documentoCompartidoPdf(Request $request, int $id)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $row = PortalExpedienteCompartido::query()
            ->where('paciente_id', $paciente->id)
            ->whereKey($id)
            ->first();

        if (! $row) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        if (! $paciente->clinicas()->where('clinicas.id', $row->clinica_id)->exists()) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $tipo = (int) $row->tipo_exp;
        $eid = (int) $row->expediente_id;

        if ($tipo === 6) {
            $nutri = \App\Models\ReporteNutri::query()->find($eid);
            if (! $nutri || (int) $nutri->paciente_id !== (int) $paciente->id) {
                return response()->json(['message' => 'Documento no encontrado'], 404);
            }
        }

        $sub = Request::create($request->url(), 'GET', ['id' => $eid]);
        $sub->merge(['id' => $eid]);

        return match ($tipo) {
            6 => app(PDFController::class)->nutriPdf($sub),
            default => response()->json([
                'message' => 'La vista en PDF para este tipo de expediente aún no está habilitada en el portal.',
            ], 501),
        };
    }

    /**
     * Obtener UUID público del paciente para generar QR de vinculación.
     */
    public function miQr(): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Si no tiene UUID, generarlo
        if (empty($paciente->uuid_publico)) {
            $paciente->uuid_publico = \Illuminate\Support\Str::uuid()->toString();
            $paciente->save();
        }

        return response()->json([
            'uuid' => $paciente->uuid_publico,
            'nombre_completo' => trim("{$paciente->nombre} {$paciente->apellidoPat} {$paciente->apellidoMat}"),
        ]);
    }
}
