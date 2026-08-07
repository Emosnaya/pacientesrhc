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
use App\Models\PacienteDeviceToken;
use App\Models\Pago;
use App\Models\PortalExpedienteCompartido;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\CitaAvailabilityService;
use App\Services\CitaSolicitudService;
use App\Services\SucursalHorarioService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'alergias' => 'nullable|string|max:2000',
            'grupo_sanguineo' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'contacto_emergencia_nombre' => 'nullable|string|max:150',
            'contacto_emergencia_telefono' => 'nullable|string|max:50',
            'notas_emergencia' => 'nullable|string|max:2000',
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
        $paciente->grupo_sanguineo = $validated['grupo_sanguineo'] ?? null;
        $paciente->contacto_emergencia_nombre = $validated['contacto_emergencia_nombre'] ?? null;
        $paciente->contacto_emergencia_telefono = $validated['contacto_emergencia_telefono'] ?? null;
        $paciente->notas_emergencia = $validated['notas_emergencia'] ?? null;

        $paciente->save();

        $user->email = $email;
        $user->nombre = $validated['nombre'];
        $user->apellidoPat = $validated['apellidoPat'];
        $user->apellidoMat = $validated['apellidoMat'] ?? null;
        $user->save();

        return response()->json($this->perfilPayload($paciente->fresh()->load('clinicas')));
    }

    /**
     * Elimina el acceso de portal/app del paciente (requisito Apple / Google).
     * No borra el expediente clínico que conserva la clínica.
     */
    public function eliminarCuenta(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        $user = Auth::user();
        if (! $paciente || ! $user) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'confirmacion' => 'required|string|in:ELIMINAR',
        ]);

        try {
            DB::transaction(function () use ($user, $paciente) {
                PacienteDeviceToken::query()
                    ->where('paciente_id', $paciente->id)
                    ->delete();

                // Revocar tokens Sanctum
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                $stamp = now()->format('YmdHis');
                $anonEmail = "deleted+{$user->id}.{$stamp}@deleted.lynkamed.local";

                $user->email = $anonEmail;
                $user->password = bcrypt(bin2hex(random_bytes(24)));
                $user->password_set_at = null;
                // Desvincula acceso portal; el registro Paciente permanece para la clínica.
                $user->paciente_id = null;
                $user->save();

                // Quitar foto de perfil del portal si existe
                if ($paciente->foto && Storage::disk('public')->exists($paciente->foto)) {
                    Storage::disk('public')->delete($paciente->foto);
                    $paciente->foto = null;
                    $paciente->save();
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Error eliminando cuenta portal paciente: '.$e->getMessage());

            return response()->json(['message' => 'No se pudo eliminar la cuenta. Intenta más tarde.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tu cuenta de LynkaMed Paciente fue eliminada.',
        ]);
    }

    /**
     * POST /api/paciente-portal/perfil/foto
     * Sube o reemplaza la foto del paciente (galería o cámara desde la app).
     */
    public function updateFoto(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        try {
            if ($paciente->foto && Storage::disk('public')->exists($paciente->foto)) {
                Storage::disk('public')->delete($paciente->foto);
            }

            $file = $request->file('foto');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $path = $file->storeAs(
                'pacientes/fotos',
                'paciente_'.$paciente->id.'_'.time().'.'.$ext,
                'public'
            );

            $paciente->foto = $path;
            $paciente->save();

            return response()->json($this->perfilPayload($paciente->fresh()->load('clinicas')));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'No se pudo guardar la foto'], 500);
        }
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
            'grupo_sanguineo' => $paciente->grupo_sanguineo,
            'contacto_emergencia_nombre' => $paciente->contacto_emergencia_nombre,
            'contacto_emergencia_telefono' => $paciente->contacto_emergencia_telefono,
            'notas_emergencia' => $paciente->notas_emergencia,
            'foto' => $paciente->foto,
            'foto_url' => $paciente->foto_url,
            'pasaporte_url' => rtrim((string) config('app.frontend_url'), '/') . '/pasaporte/' . $paciente->uuid_publico,
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
            ->with(['sucursal', 'clinica', 'user:id,nombre,apellidoPat,apellidoMat,rol', 'eventos'])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(function (Cita $c) {
                $maxReagendas = max(0, (int) ($c->clinica->portal_max_reagendas_paciente ?? 2));
                $intentos = (int) ($c->reagenda_intentos ?? 0);
                $reagendasRestantes = max(0, $maxReagendas - $intentos);
                $cancelableEstado = ! in_array($c->estado, ['cancelada', 'completada'], true);
                $futura = $c->esFutura();
                // Clínica aún debe aceptar la solicitud (sin doctor / flag).
                $esperaClinica = (bool) ($c->requiere_confirmacion && empty($c->user_id));
                // Paciente debe confirmar asistencia (pendiente y ya validada por clínica).
                $puedeConfirmar = $cancelableEstado
                    && $futura
                    && $c->estado === 'pendiente'
                    && ! $esperaClinica;

                $doctorNombre = $c->user
                    ? trim(($c->user->nombre ?? '').' '.($c->user->apellidoPat ?? '').' '.($c->user->apellidoMat ?? ''))
                    : null;

                return [
                    'id' => $c->id,
                    'fecha' => $c->fecha?->format('Y-m-d'),
                    'hora' => $c->hora ? Carbon::parse($c->hora)->format('H:i') : null,
                    'estado' => $c->estado,
                    'notas' => $c->notas,
                    'especialidad' => $c->especialidad_solicitada
                        ? $this->agendaEspecialidadEtiqueta($c->especialidad_solicitada)
                        : $this->agendaEspecialidadDesdeNotas($c->notas),
                    'especialidad_solicitada' => $c->especialidad_solicitada,
                    'origen' => $c->origen ?? 'panel',
                    'requiere_confirmacion' => (bool) ($c->requiere_confirmacion || ($c->estado === 'pendiente' && empty($c->user_id))),
                    'espera_clinica' => $esperaClinica,
                    'puede_confirmar' => $puedeConfirmar,
                    'puede_cancelar' => $cancelableEstado && $futura,
                    'contactado_at' => $c->contactado_at?->toIso8601String(),
                    'motivo_cancelacion' => $c->motivo_cancelacion,
                    'doctor' => $doctorNombre ? [
                        'id' => $c->user->id,
                        'nombre' => $doctorNombre,
                        'rol' => $c->user->rol,
                    ] : null,
                    'clinica' => $c->clinica ? [
                        'id' => $c->clinica->id,
                        'nombre' => $c->clinica->nombre,
                    ] : null,
                    'sucursal' => $c->sucursal ? [
                        'id' => $c->sucursal->id,
                        'nombre' => $c->sucursal->nombre,
                    ] : null,
                    'reagenda_intentos' => $intentos,
                    'reagendas_restantes' => $reagendasRestantes,
                    'puede_reagendar' => $cancelableEstado && $futura && $reagendasRestantes > 0,
                    'eventos' => $c->eventos->map(fn ($e) => [
                        'tipo' => $e->tipo,
                        'actor' => $e->actor,
                        'mensaje' => $e->mensaje,
                        'created_at' => $e->created_at?->toIso8601String(),
                    ])->values(),
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
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
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

        $sucursal = $this->agendaResolveSucursal($clinica, $validated['sucursal_id'] ?? null, $paciente);
        if ($sucursal instanceof JsonResponse) {
            return $sucursal;
        }

        $candidateSlots = app(SucursalHorarioService::class)->slotsParaFecha($sucursal, $fecha);
        if (empty($candidateSlots)) {
            // Fallback a slots legacy si la sucursal aún no tiene horario configurado.
            $candidateSlots = self::AGENDA_SLOTS;
        }

        $slots = collect($candidateSlots)->map(function (string $slot) use ($clinica, $paciente, $fecha, $doctorId, $especialidad, $sucursal) {
            $check = $this->agendaCanBook($clinica, $paciente, $fecha, $slot, $doctorId, $especialidad, $sucursal->id);
            return [
                'hora' => $slot,
                'disponible' => $check['ok'],
                'motivo' => $check['ok'] ? null : $check['message'],
            ];
        })->values();

        return response()->json([
            'data' => $slots,
            'meta' => [
                'sucursal_id' => $sucursal->id,
                'clinica_id' => $clinica->id,
            ],
        ]);
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
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
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

        $sucursal = $this->agendaResolveSucursal($clinica, $validated['sucursal_id'] ?? null, $paciente);
        if ($sucursal instanceof JsonResponse) {
            return $sucursal;
        }

        $check = $this->agendaCanBook(
            $clinica,
            $paciente,
            (string) $validated['fecha'],
            (string) $validated['hora'],
            $doctorId,
            $especialidad,
            $sucursal->id
        );
        if (! $check['ok']) {
            return response()->json(['message' => $check['message']], 422);
        }

        $requiereConfirmacionClinica = ! $doctorId;
        $estadoInicial = $requiereConfirmacionClinica
            ? 'pendiente'
            : app(CitaAvailabilityService::class)->estadoInicial($clinica);

        $pivot = $paciente->clinicas()->where('clinicas.id', $clinica->id)->first()?->pivot;
        if (! $pivot) {
            $paciente->clinicas()->syncWithoutDetaching([
                $clinica->id => [
                    'sucursal_id' => $sucursal->id,
                    'user_id' => null,
                    'vinculado_at' => now(),
                    'portal_visible_citas' => true,
                    'portal_visible_datos_basicos' => true,
                    'portal_visible_expediente_resumen' => false,
                    'portal_agenda_bloqueado' => false,
                ],
            ]);
            $pivot = $paciente->clinicas()->where('clinicas.id', $clinica->id)->first()?->pivot;
        } elseif (empty($pivot->sucursal_id)) {
            $paciente->clinicas()->updateExistingPivot($clinica->id, [
                'sucursal_id' => $sucursal->id,
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
            'sucursal_id' => $sucursal->id,
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'estado' => $estadoInicial,
            'primera_vez' => false,
            'notas' => $this->agendaBuildNotas(
                $validated['notas'] ?? null,
                $especialidad
            ),
            'especialidad_solicitada' => $especialidad,
            'origen' => 'portal',
            'requiere_confirmacion' => $requiereConfirmacionClinica,
            'reagenda_intentos' => 0,
        ]);

        $solicitudService = app(CitaSolicitudService::class);
        if ($requiereConfirmacionClinica) {
            $solicitudService->registrarEvento(
                $cita,
                'solicitado',
                'paciente',
                Auth::id() ? (int) Auth::id() : null,
                'Solicitud enviada sin profesional asignado',
                ['especialidad' => $especialidad, 'doctor_id' => $doctorId]
            );
        } elseif ($estadoInicial === 'confirmada') {
            $solicitudService->registrarEvento(
                $cita,
                'agendada',
                'paciente',
                Auth::id() ? (int) Auth::id() : null,
                'Cita agendada y confirmada desde la app',
                ['especialidad' => $especialidad, 'doctor_id' => $doctorId]
            );
        } else {
            $solicitudService->registrarEvento(
                $cita,
                'pendiente_confirmacion',
                'paciente',
                Auth::id() ? (int) Auth::id() : null,
                'Cita agendada; confirma tu asistencia',
                ['especialidad' => $especialidad, 'doctor_id' => $doctorId]
            );
        }

        $chatConversacion = $this->agendaEnsurePatientChatConversation(
            $clinica,
            $paciente,
            (int) $adminId,
            (string) $validated['fecha'],
            (string) $validated['hora'],
            $especialidad
        );

        SendCitaWhatsAppNotification::dispatch($cita->id, 'confirmacion');

        if ($requiereConfirmacionClinica) {
            $solicitudService->notificarClinicaNuevaSolicitud($cita);
        }

        return response()->json([
            'message' => $requiereConfirmacionClinica
                ? 'Solicitud de cita enviada. La clínica/consultorio debe confirmar el horario.'
                : ($estadoInicial === 'confirmada'
                    ? 'Cita agendada correctamente'
                    : 'Cita agendada. Confirma tu asistencia cuando puedas.'),
            'data' => [
                'id' => $cita->id,
                'fecha' => $cita->fecha?->format('Y-m-d'),
                'hora' => Carbon::parse($cita->hora)->format('H:i'),
                'estado' => $cita->estado,
                'clinica' => ['id' => $clinica->id, 'nombre' => $clinica->nombre],
                'sucursal_id' => $sucursal->id,
                'doctor_id' => $doctorId,
                'especialidad' => $especialidad,
                'especialidad_solicitada' => $especialidad,
                'requiere_confirmacion' => $requiereConfirmacionClinica,
                'puede_confirmar' => $estadoInicial === 'pendiente' && ! $requiereConfirmacionClinica,
                'chat_conversacion_id' => $chatConversacion?->id,
                'requiere_confirmacion_clinica' => $requiereConfirmacionClinica,
                'siguiente_paso' => $requiereConfirmacionClinica
                    ? 'Si no hay disponibilidad con staff en ese horario, la clínica podrá proponerte otros horarios o contactarte por chat.'
                    : ($estadoInicial === 'pendiente' ? 'Confirma tu asistencia desde Mis citas.' : null),
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

        $especialidadFinal = $especialidad ?: $cita->especialidad_solicitada;
        $requiereConfirmacion = ! $doctorId;
        $estadoNueva = $requiereConfirmacion
            ? 'pendiente'
            : app(CitaAvailabilityService::class)->estadoInicial($clinica);

        $nueva = Cita::create([
            'paciente_id' => $cita->paciente_id,
            'admin_id' => $cita->admin_id,
            'user_id' => $doctorId,
            'clinica_id' => $cita->clinica_id,
            'sucursal_id' => $cita->sucursal_id,
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'estado' => $estadoNueva,
            'primera_vez' => false,
            'notas' => $this->agendaBuildNotas($cita->notas, $especialidadFinal),
            'especialidad_solicitada' => $especialidadFinal,
            'origen' => 'portal',
            'requiere_confirmacion' => $requiereConfirmacion,
            'reagenda_intentos' => $intentos + 1,
            'reagendada_de_cita_id' => $cita->id,
        ]);

        $cita->estado = 'cancelada';
        $cita->motivo_cancelacion = 'Reagendada por paciente';
        $cita->save();

        $solicitudService = app(CitaSolicitudService::class);
        $solicitudService->registrarEvento($cita, 'cancelado', 'paciente', Auth::id() ? (int) Auth::id() : null, 'Reagendada por paciente');
        if ($requiereConfirmacion) {
            $solicitudService->registrarEvento($nueva, 'solicitado', 'paciente', Auth::id() ? (int) Auth::id() : null, 'Cita reagendada; la clínica debe confirmar');
            $solicitudService->notificarClinicaNuevaSolicitud($nueva);
        } elseif ($estadoNueva === 'confirmada') {
            $solicitudService->registrarEvento($nueva, 'agendada', 'paciente', Auth::id() ? (int) Auth::id() : null, 'Cita reagendada y confirmada');
        } else {
            $solicitudService->registrarEvento($nueva, 'pendiente_confirmacion', 'paciente', Auth::id() ? (int) Auth::id() : null, 'Cita reagendada; confirma tu asistencia');
        }

        SendCitaWhatsAppNotification::dispatch($nueva->id, 'reagendada');

        return response()->json([
            'message' => $estadoNueva === 'confirmada'
                ? 'Cita reagendada correctamente'
                : ($requiereConfirmacion
                    ? 'Cita reagendada. La clínica debe confirmar el nuevo horario.'
                    : 'Cita reagendada. Confirma tu asistencia cuando puedas.'),
            'data' => [
                'id' => $nueva->id,
                'fecha' => $nueva->fecha?->format('Y-m-d'),
                'hora' => Carbon::parse($nueva->hora)->format('H:i'),
                'estado' => $nueva->estado,
                'requiere_confirmacion' => $requiereConfirmacion,
                'puede_confirmar' => $estadoNueva === 'pendiente' && ! $requiereConfirmacion,
                'reagenda_intentos' => $nueva->reagenda_intentos,
                'reagendas_restantes' => max(0, $maxReagendas - (int) $nueva->reagenda_intentos),
            ],
        ]);
    }

    /**
     * Confirmar asistencia desde la app del paciente.
     */
    public function agendaConfirmarCita(Request $request, int $id): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $cita = Cita::query()->where('id', $id)->where('paciente_id', $paciente->id)->first();
        if (! $cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        if (in_array($cita->estado, ['cancelada', 'completada'], true)) {
            return response()->json(['message' => 'Esta cita ya no se puede confirmar'], 422);
        }
        if ($cita->estado === 'confirmada') {
            return response()->json([
                'message' => 'Esta cita ya está confirmada',
                'data' => ['id' => $cita->id, 'estado' => $cita->estado],
            ]);
        }
        if ($cita->estado !== 'pendiente') {
            return response()->json(['message' => 'Solo puedes confirmar citas pendientes'], 422);
        }

        $esperaClinica = (bool) ($cita->requiere_confirmacion && empty($cita->user_id));
        if ($esperaClinica) {
            return response()->json([
                'message' => 'La clínica aún debe aceptar o proponerte un horario. Mientras tanto puedes escribirle por chat.',
            ], 422);
        }
        if (! $cita->esFutura()) {
            return response()->json(['message' => 'No puedes confirmar una cita pasada'], 422);
        }

        $cita->estado = 'confirmada';
        $cita->requiere_confirmacion = false;
        $cita->confirmacion_whatsapp = $cita->confirmacion_whatsapp ?: 'confirmada';
        $cita->save();

        app(CitaSolicitudService::class)->registrarEvento(
            $cita,
            'confirmado',
            'paciente',
            Auth::id() ? (int) Auth::id() : null,
            'Asistencia confirmada por el paciente'
        );

        SendCitaWhatsAppNotification::dispatch($cita->id, 'estado');

        return response()->json([
            'message' => 'Asistencia confirmada. ¡Te esperamos!',
            'data' => [
                'id' => $cita->id,
                'estado' => $cita->estado,
                'puede_confirmar' => false,
            ],
        ]);
    }

    /**
     * Cancelar cita desde el portal del paciente.
     */
    public function agendaCancelarCita(Request $request, int $id): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        $cita = Cita::query()->where('id', $id)->where('paciente_id', $paciente->id)->first();
        if (! $cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        if (in_array($cita->estado, ['cancelada', 'completada'], true)) {
            return response()->json(['message' => 'Esta cita ya no se puede cancelar'], 422);
        }

        $cita->estado = 'cancelada';
        $cita->motivo_cancelacion = $validated['motivo'] ?? 'Cancelada por el paciente';
        $cita->save();

        app(CitaSolicitudService::class)->registrarEvento(
            $cita,
            'cancelado',
            'paciente',
            Auth::id() ? (int) Auth::id() : null,
            $cita->motivo_cancelacion
        );

        SendCitaWhatsAppNotification::dispatch($cita->id, 'cancelacion');

        return response()->json([
            'message' => 'Cita cancelada',
            'data' => [
                'id' => $cita->id,
                'estado' => $cita->estado,
                'motivo_cancelacion' => $cita->motivo_cancelacion,
            ],
        ]);
    }

    /**
     * @return Sucursal|\Illuminate\Http\JsonResponse
     */
    private function agendaResolveSucursal(Clinica $clinica, $sucursalId, Paciente $paciente)
    {
        if ($sucursalId) {
            $sucursal = Sucursal::query()
                ->where('id', (int) $sucursalId)
                ->where('clinica_id', $clinica->id)
                ->first();

            if (! $sucursal || ! $sucursal->activa) {
                return response()->json(['message' => 'La sucursal no pertenece a esta clínica o no está activa'], 422);
            }

            return $sucursal;
        }

        $pivot = $paciente->clinicas()->where('clinicas.id', $clinica->id)->first()?->pivot;
        if ($pivot?->sucursal_id) {
            $fromPivot = Sucursal::query()->find($pivot->sucursal_id);
            if ($fromPivot && $fromPivot->clinica_id === $clinica->id) {
                return $fromPivot;
            }
        }

        $principal = Sucursal::query()
            ->where('clinica_id', $clinica->id)
            ->where('activa', true)
            ->orderByDesc('es_principal')
            ->orderBy('id')
            ->first();

        if (! $principal) {
            return response()->json(['message' => 'Esta clínica no tiene sucursales activas'], 422);
        }

        return $principal;
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
        ?string $especialidad = null,
        ?int $sucursalId = null
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

        if (! $sucursalId) {
            $sucursalId = $pivot?->sucursal_id
                ?? Sucursal::query()->where('clinica_id', $clinica->id)->where('es_principal', true)->value('id');
        }

        return $availability->canBook(
            $clinica,
            $fecha,
            $hora,
            $sucursalId ? (int) $sucursalId : null,
            $doctorId,
            $paciente->id
        );
    }

    /**
     * Extrae la especialidad que el paciente pidió al agendar, guardada como prefijo en las notas.
     */
    private function agendaEspecialidadDesdeNotas(?string $notas): ?string
    {
        if (! $notas) {
            return null;
        }

        if (preg_match('/^\[Especialidad solicitada:\s*(.*?)\]/u', trim($notas), $matches)) {
            $valor = trim($matches[1] ?? '');
            return $valor !== '' ? $valor : null;
        }

        return null;
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
        $tipos = config('clinica_tipos.tipos', []);
        if (is_array($tipos) && isset($tipos[$key]['nombre'])) {
            return (string) $tipos[$key]['nombre'];
        }

        $modulos = config('clinica_tipos.modulos_seleccionables', []);
        if (is_array($modulos) && isset($modulos[$key]['nombre'])) {
            return (string) $modulos[$key]['nombre'];
        }

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
        $pacienteId = (int) $paciente->id;

        $modelMap = [
            1 => \App\Models\Esfuerzo::class,
            2 => \App\Models\Estratificacion::class,
            3 => \App\Models\Clinico::class,
            4 => \App\Models\ReporteFinal::class,
            5 => \App\Models\ReportePsico::class,
            6 => \App\Models\ReporteNutri::class,
            8 => \App\Models\ExpedientePulmonar::class,
            11 => \App\Models\HistoriaClinicaFisioterapia::class,
            12 => \App\Models\NotaEvolucionFisioterapia::class,
            13 => \App\Models\NotaAltaFisioterapia::class,
            18 => \App\Models\Odontograma::class,
            19 => \App\Models\NotaSeguimientoPulmonar::class,
            20 => \App\Models\EstratiAacvpr::class,
            21 => \App\Models\Periodontograma::class,
            22 => \App\Models\FichaEndodoncia::class,
            23 => \App\Models\FichaOrtodoncia::class,
        ];

        if (! isset($modelMap[$tipo])) {
            return response()->json([
                'message' => 'La vista en PDF para este tipo de expediente aún no está habilitada en el portal.',
            ], 501);
        }

        $model = $modelMap[$tipo]::query()->find($eid);
        if (! $model || (int) $model->paciente_id !== $pacienteId) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $sub = Request::create($request->url(), 'GET', ['id' => $eid]);
        $sub->merge(['id' => $eid]);
        $pdf = app(PDFController::class);

        return match ($tipo) {
            1 => $pdf->esfuerzoPdf($sub),
            2 => $pdf->estratificacionPdf($sub),
            3 => $pdf->clinicoPdf($sub),
            4 => $pdf->reportePdf($sub),
            5 => $pdf->psicoPdf($sub),
            6 => $pdf->nutriPdf($sub),
            8 => $pdf->pulmonarPdf($sub),
            11 => $pdf->historiaFisioterapiaPdf($sub),
            12 => $pdf->notaEvolucionFisioterapiaPdf($sub),
            13 => $pdf->notaAltaFisioterapiaPdf($sub),
            18 => $pdf->odontogramaPdf($sub),
            19 => $pdf->notaSeguimientoPulmonarPdf($sub),
            20 => $pdf->estratiAacvprPdf($sub),
            21 => $pdf->periodontogramaPdf($sub),
            22 => $pdf->fichaEndodonciaPdf($sub),
            23 => $pdf->fichaOrtodonciaPdf($sub),
            default => response()->json([
                'message' => 'La vista en PDF para este tipo de expediente aún no está habilitada en el portal.',
            ], 501),
        };
    }

    /**
     * Historial de pagos del paciente en las clínicas donde está vinculado (paginado).
     */
    public function pagos(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $perPage = min(50, max(5, (int) $request->input('per_page', 10)));
        $clinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $query = Pago::query()
            ->with(['clinica:id,nombre', 'sucursal:id,nombre', 'presupuesto:id,titulo'])
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $clinicaIds);

        if ($request->filled('clinica_id')) {
            $query->where('clinica_id', (int) $request->input('clinica_id'));
        }

        $paginator = $query
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->paginate($perPage);

        $data = collect($paginator->items())->map(fn (Pago $pago) => [
            'id' => $pago->id,
            'monto' => (float) $pago->monto,
            'concepto' => $pago->concepto,
            'referencia' => $pago->referencia,
            'metodo_pago' => $pago->metodo_pago,
            'metodo_pago_label' => Pago::etiquetaMetodo($pago->metodo_pago),
            'fecha_pago' => $pago->fecha_pago?->toDateString() ?? $pago->created_at?->toDateString(),
            'clinica' => $pago->clinica ? ['id' => $pago->clinica->id, 'nombre' => $pago->clinica->nombre] : null,
            'sucursal' => $pago->sucursal ? ['id' => $pago->sucursal->id, 'nombre' => $pago->sucursal->nombre] : null,
            'presupuesto' => $pago->presupuesto ? [
                'id' => $pago->presupuesto->id,
                'titulo' => $pago->presupuesto->titulo,
            ] : null,
        ])->values();

        // El monto está cifrado en base de datos: se agrega en PHP, no en SQL.
        $todosLosPagos = Pago::query()
            ->with('clinica:id,nombre')
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $clinicaIds)
            ->get();

        $totalPagadoGlobal = (float) $todosLosPagos->sum(fn (Pago $pago) => (float) $pago->monto);

        $porClinica = $todosLosPagos
            ->groupBy('clinica_id')
            ->map(fn ($pagos, $clinicaId) => [
                'clinica_id' => (int) $clinicaId,
                'clinica_nombre' => $pagos->first()?->clinica?->nombre,
                'total_pagado' => round((float) $pagos->sum(fn (Pago $pago) => (float) $pago->monto), 2),
                'pagos_count' => $pagos->count(),
                'ultimo_pago' => $pagos
                    ->map(fn (Pago $pago) => $pago->fecha_pago?->toDateString() ?? $pago->created_at?->toDateString())
                    ->filter()
                    ->sort()
                    ->last(),
            ])
            ->sortByDesc('total_pagado')
            ->values();

        $clinicaFiltrada = $request->filled('clinica_id') ? (int) $request->input('clinica_id') : null;
        $totalPagado = $clinicaFiltrada
            ? (float) ($porClinica->firstWhere('clinica_id', $clinicaFiltrada)['total_pagado'] ?? 0)
            : $totalPagadoGlobal;

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pagado' => round($totalPagado, 2),
                'total_pagado_global' => round($totalPagadoGlobal, 2),
                'por_clinica' => $porClinica,
            ],
        ]);
    }

    /**
     * Recibo en PDF de un pago propio del paciente.
     */
    public function pagoReciboPdf(int $id)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $clinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $pago = Pago::query()
            ->with(['paciente', 'usuario', 'sucursal', 'clinica'])
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $clinicaIds)
            ->find($id);

        if (! $pago) {
            return response()->json(['message' => 'Recibo no encontrado'], 404);
        }

        $clinica = $pago->clinica;
        $sucursal = $pago->sucursal;
        $clinicaLogo = null;

        if ($clinica?->logo) {
            $logoPath = storage_path('app/public/' . $clinica->logo);
            if (file_exists($logoPath)) {
                $clinicaLogo = 'data:' . (mime_content_type($logoPath) ?: 'image/png')
                    . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'finanzas.recibo',
            compact('pago', 'clinica', 'sucursal', 'clinicaLogo')
        );

        return $pdf->stream('Recibo_' . str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT) . '.pdf');
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

        $url = rtrim((string) config('app.frontend_url'), '/') . '/pasaporte/' . $paciente->uuid_publico;

        return response()->json([
            'uuid' => $paciente->uuid_publico,
            'nombre_completo' => trim("{$paciente->nombre} {$paciente->apellidoPat} {$paciente->apellidoMat}"),
            'url' => $url,
            'grupo_sanguineo' => $paciente->grupo_sanguineo,
            'alergias' => $paciente->alergias,
            'contacto_emergencia_nombre' => $paciente->contacto_emergencia_nombre,
            'contacto_emergencia_telefono' => $paciente->contacto_emergencia_telefono,
            'notas_emergencia' => $paciente->notas_emergencia,
        ]);
    }
}
