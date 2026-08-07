<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\ChatParticipante;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\Sillon;
use App\Models\User;
use App\Models\Evento;
use App\Jobs\SendCitaWhatsAppNotification;
use App\Services\CitaAvailabilityService;
use App\Services\CitaSolicitudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Notifications\CitaInvitationMail;
use Carbon\Carbon;

class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Cita::with(['paciente', 'admin', 'sillon']);

            // Filtrar por clínica del usuario autenticado
            $query->forClinica($user->clinica_efectiva_id);
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            // Si no viene en el request, usar la del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
            
            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            // Filtros
            if ($request->has('fecha')) {
                $query->byDate($request->fecha);
            }

            if ($request->has('mes') && $request->has('año')) {
                $query->byMonth($request->mes, $request->año);
            }

            if ($request->has('estado')) {
                $query->byStatus($request->estado);
            }

            if ($request->has('paciente_id')) {
                $query->forPaciente($request->paciente_id);
            }

            $citas = $query->orderBy('fecha', 'desc')
                          ->orderBy('hora', 'desc')
                          ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $citas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las citas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $paciente = null;
            $nuevoPaciente = null;

            // Si se proporciona un paciente existente
            if ($request->has('paciente_id') && $request->paciente_id) {
                $validator = Validator::make($request->all(), [
                    'paciente_id' => 'required|exists:pacientes,id',
                    'user_id' => 'nullable|exists:users,id',
                    'sillon_id' => 'nullable|exists:sillones,id',
                    'custom_email' => 'nullable|email|max:255',
                    'fecha' => 'required|date',
                    'hora' => 'required|date_format:H:i',
                    'estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada',
                    'primera_vez' => 'sometimes|boolean',
                    'notas' => 'nullable|string|max:1000'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos de validación incorrectos',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $paciente = Paciente::findOrFail($request->paciente_id);

                // Verificar que el paciente pertenece a la misma clínica
                if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes acceso a este paciente'
                    ], 403);
                }
            }
            // Si se proporciona un nuevo paciente
            elseif ($request->has('nuevo_paciente') && $request->nuevo_paciente) {
                $validator = Validator::make($request->all(), [
                    'nuevo_paciente' => 'required|array',
                    'nuevo_paciente.nombre' => 'required|string|max:255',
                    'nuevo_paciente.apellidoPat' => 'required|string|max:255',
                    'nuevo_paciente.apellidoMat' => 'required|string|max:255',
                    'nuevo_paciente.telefono' => 'nullable|string|max:20',
                    'nuevo_paciente.whatsapp_notificaciones' => 'sometimes|boolean',
                    'nuevo_paciente.email' => 'nullable|email|max:255',
                    'nuevo_paciente.fechaNacimiento' => 'required|date',
                    'nuevo_paciente.genero' => 'required|in:masculino,femenino',
                    'nuevo_paciente.registro' => 'nullable|string|max:50',
                    'nuevo_paciente.tipo_paciente' => 'nullable|string|max:255',
                    'nuevo_paciente.user_id' => 'nullable|exists:users,id',
                    'user_id' => 'nullable|exists:users,id',
                    'sillon_id' => 'nullable|exists:sillones,id',
                    'custom_email' => 'nullable|email|max:255',
                    'fecha' => 'required|date',
                    'hora' => 'required|date_format:H:i',
                    'estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada',
                    'primera_vez' => 'sometimes|boolean',
                    'notas' => 'nullable|string|max:1000'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos de validación incorrectos',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Crear nuevo paciente
                $pacienteData = $request->nuevo_paciente;
                $clinicaId = $user->clinica_efectiva_id;
                $clinica = Clinica::find($clinicaId);
                $tipoPacienteDefault = $clinica?->tipo_clinica ?: 'general';
                $sucursalIdPaciente = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
                $peso = $pacienteData['peso'] ?? 0;
                $talla = $pacienteData['talla'] ?? 0;
                $imc = $talla > 0 ? ($peso / ($talla * $talla)) : 0;
                $fechaNacimiento = $pacienteData['fechaNacimiento'];
                $edad = Carbon::parse($fechaNacimiento)->age;

                $nuevoPaciente = new Paciente();
                $registroSolicitado = trim((string) ($pacienteData['registro'] ?? ''));
                $nuevoPaciente->registro = $registroSolicitado !== ''
                    ? $registroSolicitado
                    : Paciente::siguienteRegistroParaClinica(
                        (int) $clinicaId,
                        $sucursalIdPaciente ? (int) $sucursalIdPaciente : null
                    );
                $nuevoPaciente->nombre = $pacienteData['nombre'];
                $nuevoPaciente->apellidoPat = $pacienteData['apellidoPat'];
                $nuevoPaciente->apellidoMat = $pacienteData['apellidoMat'];
                $nuevoPaciente->telefono = $pacienteData['telefono'] ?? null;
                $nuevoPaciente->whatsapp_notificaciones = (bool) ($pacienteData['whatsapp_notificaciones'] ?? false);
                $nuevoPaciente->email = $pacienteData['email'] ?? null;
                $nuevoPaciente->domicilio = $pacienteData['domicilio'] ?? null;
                $nuevoPaciente->profesion = $pacienteData['profesion'] ?? null;
                $nuevoPaciente->cintura = $pacienteData['cintura'] ?? null;
                $nuevoPaciente->estadoCivil = $pacienteData['estadoCivil'] ?? null;
                $nuevoPaciente->diagnostico = $pacienteData['diagnostico'] ?? null;
                $nuevoPaciente->medicamentos = $pacienteData['medicamentos'] ?? null;
                $nuevoPaciente->envio = $pacienteData['envio'] ?? null;
                $nuevoPaciente->talla = $talla;
                $nuevoPaciente->peso = $peso;
                $nuevoPaciente->fechaNacimiento = $fechaNacimiento;
                $nuevoPaciente->edad = $edad;
                $nuevoPaciente->imc = $imc;
                $nuevoPaciente->genero = $pacienteData['genero'] === 'masculino' ? 1 : 0;
                $nuevoPaciente->tipo_paciente = $pacienteData['tipo_paciente'] ?? $tipoPacienteDefault;
                $nuevoPaciente->color = $pacienteData['color'] ?? null;

                // Asignar el paciente al doctor seleccionado o al usuario actual
                if (!empty($pacienteData['user_id'])) {
                    // Si se proporcionó un doctor, asignar a ese doctor
                    $doctorId = $pacienteData['user_id'];
                    // Verificar que el doctor existe y pertenece a la misma clínica
                    $doctor = User::where('id', $doctorId)
                        ->where('clinica_id', $user->clinica_efectiva_id)
                        ->first();
                    
                    if (!$doctor) {
                        return response()->json([
                            'success' => false,
                            'message' => 'El doctor seleccionado no es válido'
                        ], 422);
                    }
                    
                    $nuevoPaciente->user_id = $doctorId;
                } else {
                    // Si no se seleccionó doctor, asignar al usuario actual
                    $nuevoPaciente->user_id = $user->id;
                }
                
                $nuevoPaciente->clinica_id = $clinicaId;
                $nuevoPaciente->sucursal_id = $sucursalIdPaciente;
                $nuevoPaciente->save();

                if (! $nuevoPaciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
                    $nuevoPaciente->clinicas()->attach($clinicaId, [
                        'sucursal_id' => $sucursalIdPaciente,
                        'user_id' => $nuevoPaciente->user_id,
                        'vinculado_at' => now(),
                        'tipo_paciente' => $nuevoPaciente->tipo_paciente,
                        'numero_expediente' => $nuevoPaciente->registro,
                        'portal_visible_citas' => true,
                        'portal_visible_datos_basicos' => true,
                    ]);
                }

                $paciente = $nuevoPaciente;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un paciente existente o crear uno nuevo'
                ], 422);
            }

            // Determinar user_id: 
            // - Si viene en request y es null explícitamente, usar null (no enviar correos)
            // - Si viene en request con valor, usar ese valor
            // - Si no viene en request (string vacío o no presente), usar doctor del paciente
            $userId = null;
            if ($request->has('user_id')) {
                $userId = $request->user_id; // puede ser null, int o string vacío
                if ($userId === '' || $userId === 'null') {
                    $userId = null; // forzar null si viene vacío o "null"
                }
            } else {
                $userId = $paciente->user_id; // usar doctor del paciente por defecto
            }

            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $clinica = Clinica::find($user->clinica_efectiva_id);
            if (! $clinica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clínica no encontrada'
                ], 404);
            }

            $availability = app(CitaAvailabilityService::class);
            $check = $availability->canBook(
                $clinica,
                $request->fecha,
                $request->hora,
                $sucursalId ? (int) $sucursalId : null,
                $userId ? (int) $userId : null,
                $paciente->id
            );

            if (! $check['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $check['message'] ?? 'Horario no disponible'
                ], 422);
            }

            $sillonId = $this->normalizeSillonId($request->input('sillon_id'));
            $sillonError = $this->assertSillonDisponible(
                $sillonId,
                (string) $request->fecha,
                (string) $request->hora,
                $sucursalId ? (int) $sucursalId : null
            );
            if ($sillonError) {
                return response()->json([
                    'success' => false,
                    'message' => $sillonError,
                ], 422);
            }

            $estadoInicial = $request->estado
                ?? $availability->estadoInicial($clinica);

            // Crear la cita respetando estado y solapamiento configurados
            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'admin_id' => $user->id,
                'user_id' => $userId,
                'sillon_id' => $sillonId,
                'clinica_id' => $user->clinica_efectiva_id,
                'sucursal_id' => $sucursalId,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'estado' => $estadoInicial,
                'primera_vez' => $request->primera_vez ?? true,
                'notas' => $request->notas,
                'custom_email' => $request->custom_email ?? null
            ]);

            $cita->load(['paciente', 'admin', 'user.clinica', 'sillon']);

            // Correo siempre (fallback / respaldo). WhatsApp en cola si aplica.
            $this->sendCalendarInvitation($cita, 'create');
            SendCitaWhatsAppNotification::dispatch($cita->id, 'confirmacion');

            // Notificación in-app + push al paciente
            try {
                $solicitudService = app(CitaSolicitudService::class);
                if ($estadoInicial === 'confirmada') {
                    $solicitudService->registrarEvento(
                        $cita,
                        'agendada',
                        'clinica',
                        $user->id,
                        'Tu clínica te agendó una cita confirmada'
                    );
                } else {
                    $solicitudService->registrarEvento(
                        $cita,
                        'pendiente_confirmacion',
                        'clinica',
                        $user->id,
                        'Tu clínica te agendó una cita; confirma tu asistencia'
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning('No se pudo notificar cita creada al paciente: '.$e->getMessage());
            }

            $response = [
                'success' => true,
                'message' => 'Cita creada exitosamente',
                'data' => $cita
            ];

            // Si se creó un nuevo paciente, incluir información adicional
            if ($nuevoPaciente) {
                $response['message'] = 'Paciente y cita creados exitosamente';
                $response['paciente_creado'] = true;
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $cita = Cita::with(['paciente', 'admin', 'sillon'])->findOrFail($id);
            $user = Auth::user();

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $cita
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'sillon_id' => 'nullable|exists:sillones,id',
                'custom_email' => 'nullable|email|max:255',
                'fecha' => 'sometimes|date',
                'hora' => 'sometimes|date_format:H:i',
                'estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada',
                'primera_vez' => 'sometimes|boolean',
                'notas' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $fechaNueva = $request->input('fecha', optional($cita->fecha)->format('Y-m-d') ?? $cita->fecha);
            $horaNueva = $request->input('hora', $cita->hora instanceof \DateTimeInterface
                ? $cita->hora->format('H:i')
                : substr((string) $cita->hora, 0, 5));
            $doctorId = $request->has('user_id') ? $request->user_id : $cita->user_id;
            $sillonId = $request->has('sillon_id')
                ? $this->normalizeSillonId($request->input('sillon_id'))
                : $cita->sillon_id;
            $fechaCambio = $request->has('fecha')
                && (string) $fechaNueva !== optional($cita->fecha)->format('Y-m-d');
            $horaActual = $cita->hora instanceof \DateTimeInterface
                ? $cita->hora->format('H:i')
                : substr((string) $cita->hora, 0, 5);
            $horaCambio = $request->has('hora') && (string) $horaNueva !== $horaActual;
            $doctorCambio = $request->has('user_id') && (int) $doctorId !== (int) $cita->user_id;
            $estadoAnterior = $cita->estado;

            $debeRevalidarCupo = $request->has('fecha')
                || $request->has('hora')
                || $request->has('user_id')
                || ($request->has('estado') && $request->estado === 'confirmada');

            if ($debeRevalidarCupo) {
                $clinica = Clinica::find($cita->clinica_id);
                $availability = app(CitaAvailabilityService::class);
                $check = $availability->canBook(
                    $clinica,
                    (string) $fechaNueva,
                    (string) $horaNueva,
                    $cita->sucursal_id ? (int) $cita->sucursal_id : null,
                    $doctorId ? (int) $doctorId : null,
                    $cita->paciente_id,
                    $cita->id
                );

                if (! $check['ok']) {
                    return response()->json([
                        'success' => false,
                        'message' => $check['message'] ?? 'Horario no disponible'
                    ], 422);
                }
            }

            if ($request->has('fecha') || $request->has('hora') || $request->has('sillon_id')) {
                $sillonError = $this->assertSillonDisponible(
                    $sillonId ? (int) $sillonId : null,
                    (string) $fechaNueva,
                    (string) $horaNueva,
                    $cita->sucursal_id ? (int) $cita->sucursal_id : null,
                    $cita->id
                );
                if ($sillonError) {
                    return response()->json([
                        'success' => false,
                        'message' => $sillonError,
                    ], 422);
                }
            }

            $payload = $request->only(['user_id', 'custom_email', 'fecha', 'hora', 'estado', 'primera_vez', 'notas']);
            if ($request->has('sillon_id')) {
                $payload['sillon_id'] = $sillonId;
            }
            if ($request->has('estado') && $request->estado === 'confirmada') {
                $payload['requiere_confirmacion'] = false;
            }
            $cita->update($payload);
            $cita->load(['paciente', 'admin', 'user', 'clinica', 'sillon']);
            $estadoCambio = $request->has('estado') && $cita->estado !== $estadoAnterior;

            $solicitudService = app(CitaSolicitudService::class);
            if ($fechaCambio || $horaCambio) {
                $solicitudService->registrarEvento(
                    $cita,
                    'modificado',
                    'clinica',
                    $user->id,
                    'Fecha u hora modificada por la clínica',
                    ['fecha' => $fechaNueva, 'hora' => $horaNueva]
                );
                $this->sendCalendarInvitation($cita, 'update');
                SendCitaWhatsAppNotification::dispatch($cita->id, 'reagendada');
            } elseif ($estadoCambio) {
                $tipoEvento = $cita->estado === 'cancelada' ? 'cancelado' : 'confirmado';
                $solicitudService->registrarEvento(
                    $cita,
                    $tipoEvento,
                    'clinica',
                    $user->id,
                    $cita->estado === 'cancelada' ? 'Cita cancelada por la clínica' : 'Cita confirmada por la clínica'
                );
                $tipo = $cita->estado === 'cancelada' ? 'cancelacion' : 'estado';
                SendCitaWhatsAppNotification::dispatch($cita->id, $tipo);
                if ($cita->estado === 'confirmada') {
                    $this->sendCalendarInvitation($cita, 'update');
                }
            } elseif ($doctorCambio) {
                $solicitudService->registrarEvento(
                    $cita,
                    'doctor_asignado',
                    'clinica',
                    $user->id,
                    'Profesional asignado',
                    ['user_id' => $doctorId]
                );
                SendCitaWhatsAppNotification::dispatch($cita->id, 'doctor_asignado');
            }

            return response()->json([
                'success' => true,
                'message' => 'Cita actualizada exitosamente',
                'data' => $cita
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            // Cambiar estado a cancelada en lugar de eliminar
            $cita->estado = 'cancelada';
            $cita->motivo_cancelacion = $request->motivo_cancelacion ?? 'Sin motivo especificado';
            $cita->save();

            // Enviar notificación de cancelación
            $this->sendCalendarInvitation($cita, 'cancel');
            SendCitaWhatsAppNotification::dispatch($cita->id, 'cancelacion');

            return response()->json([
                'success' => true,
                'message' => 'Cita cancelada exitosamente',
                'data' => $cita
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente una cita de la base de datos
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            // Guardar información antes de eliminar
            $citaInfo = [
                'id' => $cita->id,
                'paciente' => $cita->paciente->nombre ?? 'N/A',
                'fecha' => $cita->fecha,
                'hora' => $cita->hora
            ];

            // Eliminar permanentemente
            $cita->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cita eliminada permanentemente',
                'data' => $citaInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener citas por mes para el calendario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCalendarData(Request $request)
    {
        try {
            $user = Auth::user();
            $mes = $request->get('mes', now()->month);
            $ano = $request->get('año', now()->year);

            $query = Cita::with(['paciente', 'admin', 'user', 'sillon'])
                        ->forClinica($user->clinica_efectiva_id)
                        ->byMonth($mes, $ano);
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            // Si no viene en el request, usar la del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
            
            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            $citas = $query->orderBy('fecha')
                          ->orderBy('hora')
                          ->get();

            // Agrupar citas por fecha para el calendario
            $citasPorFecha = $citas->groupBy(function ($cita) {
                return $cita->fecha->format('Y-m-d');
            });

            return response()->json([
                'success' => true,
                'data' => $citasPorFecha
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del calendario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de una cita
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:pendiente,confirmada,cancelada,completada'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado inválido',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            // Log para depuración
            \Log::info('Cambiando estado de cita', [
                'cita_id' => $id,
                'fecha_antes' => $cita->fecha,
                'hora_antes' => $cita->hora,
                'estado_antes' => $cita->estado,
                'nuevo_estado' => $request->estado
            ]);

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $estadoAnterior = $cita->estado;
            $cita->update(['estado' => $request->estado]);
            $cita->load(['paciente', 'admin', 'user', 'clinica']);

            if ($cita->estado !== $estadoAnterior) {
                $tipo = $cita->estado === 'cancelada' ? 'cancelacion' : 'estado';
                SendCitaWhatsAppNotification::dispatch($cita->id, $tipo);
            }

            // Log después de actualizar
            \Log::info('Estado de cita actualizado', [
                'cita_id' => $id,
                'fecha_despues' => $cita->fecha,
                'hora_despues' => $cita->hora,
                'estado_despues' => $cita->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado de la cita actualizado exitosamente',
                'data' => $cita
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear/abrir chat directo entre staff y paciente para una cita.
     */
    public function abrirChatPaciente(Request $request, $id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $pacienteUser = User::query()
                ->where('paciente_id', $cita->paciente_id)
                ->orderByDesc('es_paciente_portal')
                ->orderBy('id')
                ->first();

            if (! $pacienteUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'El paciente aún no tiene usuario de portal para chat'
                ], 422);
            }

            $staffUserId = (int) $user->id;
            $pacienteUserId = (int) $pacienteUser->id;

            $conv = ChatConversacion::query()
                ->where('clinica_id', $cita->clinica_id)
                ->where('tipo', 'directo')
                ->whereHas('participantes', fn ($q) => $q->where('user_id', $staffUserId))
                ->whereHas('participantes', fn ($q) => $q->where('user_id', $pacienteUserId))
                ->first();

            if (! $conv) {
                $conv = ChatConversacion::create([
                    'clinica_id' => $cita->clinica_id,
                    'tipo' => 'directo',
                    'nombre' => null,
                    'created_by' => $staffUserId,
                ]);

                ChatParticipante::create([
                    'conversacion_id' => $conv->id,
                    'user_id' => $staffUserId,
                    'last_read_at' => now(),
                ]);

                ChatParticipante::create([
                    'conversacion_id' => $conv->id,
                    'user_id' => $pacienteUserId,
                    'last_read_at' => null,
                ]);
            }

            $mensajeInicial = trim((string) $request->input('mensaje_inicial', ''));
            if ($mensajeInicial !== '') {
                ChatMensaje::create([
                    'conversacion_id' => $conv->id,
                    'user_id' => $staffUserId,
                    'mensaje' => $mensajeInicial,
                ]);
                ChatParticipante::where('conversacion_id', $conv->id)
                    ->where('user_id', $staffUserId)
                    ->update(['last_read_at' => now()]);
            }

            if (! $cita->contactado_at) {
                $cita->contactado_at = now();
                $cita->save();
                app(CitaSolicitudService::class)->registrarEvento(
                    $cita,
                    'contactado',
                    'clinica',
                    $staffUserId,
                    'La clínica abrió chat con el paciente'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat listo',
                'data' => [
                    'conversacion_id' => $conv->id,
                    'paciente_user_id' => $pacienteUserId,
                    'contactado_at' => $cita->contactado_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al abrir chat del paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca que la clínica ya contactó al paciente por una solicitud.
     */
    public function marcarContactado(Request $request, $id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            if ($cita->clinica_id !== $user->clinica_efectiva_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita',
                ], 403);
            }

            if (! $cita->contactado_at) {
                $cita->contactado_at = now();
                $cita->save();
                app(CitaSolicitudService::class)->registrarEvento(
                    $cita,
                    'contactado',
                    'clinica',
                    $user->id,
                    $request->input('mensaje') ?: 'La clínica marcó la solicitud como contactada'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud marcada como contactada',
                'data' => $cita->fresh(['paciente', 'user']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar contacto: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Contador y listado corto de solicitudes pendientes del portal.
     */
    public function solicitudesPendientes(Request $request)
    {
        try {
            $user = Auth::user();
            $clinicaId = $user->clinica_efectiva_id;
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $query = Cita::query()
                ->with(['paciente:id,nombre,apellidoPat,apellidoMat,telefono,email', 'sucursal:id,nombre'])
                ->where('clinica_id', $clinicaId)
                ->where('estado', 'pendiente')
                ->where(function ($q) {
                    $q->where('requiere_confirmacion', true)
                        ->orWhere(function ($inner) {
                            $inner->whereNull('user_id')->where('origen', 'portal');
                        })
                        ->orWhereNotNull('especialidad_solicitada');
                })
                ->orderBy('fecha')
                ->orderBy('hora');

            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            $rows = $query->limit(30)->get()->map(function (Cita $cita) {
                return [
                    'id' => $cita->id,
                    'fecha' => optional($cita->fecha)->format('Y-m-d'),
                    'hora' => $cita->hora instanceof \DateTimeInterface
                        ? $cita->hora->format('H:i')
                        : substr((string) $cita->hora, 0, 5),
                    'especialidad_solicitada' => $cita->especialidad_solicitada,
                    'contactado_at' => $cita->contactado_at?->toIso8601String(),
                    'paciente' => $cita->paciente ? [
                        'id' => $cita->paciente->id,
                        'nombre' => trim(($cita->paciente->nombre ?? '').' '.($cita->paciente->apellidoPat ?? '')),
                        'telefono' => $cita->paciente->telefono,
                        'email' => $cita->paciente->email,
                    ] : null,
                    'sucursal' => $cita->sucursal ? [
                        'id' => $cita->sucursal->id,
                        'nombre' => $cita->sucursal->nombre,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $rows->count(),
                'data' => $rows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitudes: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear múltiples citas para un mismo paciente
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMultiple(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'paciente_id' => 'required|exists:pacientes,id',
                'sillon_id' => 'nullable|exists:sillones,id',
                'citas' => 'required|array|min:1',
                'citas.*.fecha' => 'required|date',
                'citas.*.hora' => 'required|date_format:H:i',
                'citas.*.estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada',
                'citas.*.notas' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $paciente = Paciente::findOrFail($request->paciente_id);

            // Verificar que el paciente pertenece a la misma clínica
            if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este paciente'
                ], 403);
            }

            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $clinica = Clinica::find($user->clinica_efectiva_id);
            $availability = app(CitaAvailabilityService::class);
            $estadoDefault = $availability->estadoInicial($clinica);
            $sillonId = $this->normalizeSillonId($request->input('sillon_id'));

            $citasCreadas = [];
            $citasSkipped = [];
            $primeraVez = true; // Solo la primera cita es "primera vez"

            foreach ($request->citas as $citaData) {
                $doctorId = $citaData['user_id'] ?? $paciente->user_id ?? null;
                $check = $availability->canBook(
                    $clinica,
                    $citaData['fecha'],
                    $citaData['hora'],
                    $sucursalId ? (int) $sucursalId : null,
                    $doctorId ? (int) $doctorId : null,
                    $paciente->id
                );

                if (! $check['ok']) {
                    $citasSkipped[] = [
                        'fecha' => $citaData['fecha'],
                        'hora' => $citaData['hora'],
                        'motivo' => $check['message'] ?? 'Horario no disponible',
                    ];
                    continue;
                }

                $sillonError = $this->assertSillonDisponible(
                    $sillonId,
                    (string) $citaData['fecha'],
                    (string) $citaData['hora'],
                    $sucursalId ? (int) $sucursalId : null
                );
                if ($sillonError) {
                    $citasSkipped[] = [
                        'fecha' => $citaData['fecha'],
                        'hora' => $citaData['hora'],
                        'motivo' => $sillonError,
                    ];
                    continue;
                }

                $cita = Cita::create([
                    'paciente_id' => $paciente->id,
                    'admin_id' => $user->id,
                    'user_id' => $doctorId,
                    'sillon_id' => $sillonId,
                    'clinica_id' => $user->clinica_efectiva_id,
                    'sucursal_id' => $sucursalId,
                    'fecha' => $citaData['fecha'],
                    'hora' => $citaData['hora'],
                    'estado' => $citaData['estado'] ?? $estadoDefault,
                    'primera_vez' => $primeraVez,
                    'notas' => $citaData['notas'] ?? null
                ]);

                $cita->load(['paciente', 'admin', 'sillon']);
                $citasCreadas[] = $cita;
                $primeraVez = false; // Las siguientes ya no son primera vez
            }

            // Enviar correo de notificación (una sola vez con todas las citas)
            // Las series NO envían WhatsApp por cada cita; usarán recordatorios diarios.
            if (!empty($citasCreadas)) {
                $this->sendMultipleCitasNotificationEmail($citasCreadas, $paciente, $user);
            }

            $message = count($citasCreadas) . ' cita(s) creada(s) exitosamente';
            if (!empty($citasSkipped)) {
                $message .= '. ' . count($citasSkipped) . ' cita(s) omitida(s)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $citasCreadas,
                'total' => count($citasCreadas),
                'skipped' => count($citasSkipped),
                'skipped_details' => $citasSkipped,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear las citas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar correo de notificación para múltiples citas
     */
    private function sendMultipleCitasNotificationEmail($citas, $paciente, $admin)
    {
        if (! $paciente->email) {
            return;
        }

        try {
            $clinica   = $admin->clinica;
            $fromEmail = config('mail.from.address', 'contacto@lynkamed.mx');
            $appName   = config('app.name', 'LynkaMed');

            Mail::send('emails.cita-patient-notification', [
                'cita'     => $citas[0],
                'citas'    => $citas,
                'paciente' => $paciente,
                'multiple' => count($citas) > 1,
                'clinica'  => $clinica,
            ], function ($message) use ($paciente, $citas, $clinica, $fromEmail, $appName) {
                $clinicaNombre = $clinica ? $clinica->nombre : 'Clínica Médica';
                $subject = count($citas) > 1
                    ? 'Confirmación de ' . count($citas) . ' Citas - ' . $clinicaNombre
                    : 'Confirmación de Cita - ' . $clinicaNombre;

                $message->to($paciente->email)->subject($subject);

                $sym = $message->getSymfonyMessage();
                $sym->getHeaders()
                    ->addTextHeader('List-Unsubscribe', "<mailto:{$fromEmail}?subject=unsubscribe>")
                    ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click')
                    ->addTextHeader('X-Mailer', "{$appName} Mailer 1.0")
                    ->addTextHeader('Precedence', 'transactional')
                    ->addTextHeader('X-Priority', '3 (Normal)')
                    ->addTextHeader('Importance', 'Normal')
                    ->addTextHeader('Auto-Submitted', 'auto-generated');
            });
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '550') || str_contains($msg, '551') ||
                str_contains($msg, 'mailbox') || str_contains($msg, 'not found') ||
                str_contains($msg, 'unavailable')) {
                $paciente->email_invalido    = true;
                $paciente->email_invalido_at = now();
                $paciente->save();
            }
            \Log::error('Error sending cita notification email: ' . $msg);
        } catch (\Exception $e) {
            \Log::error('Error sending multiple citas notification email: ' . $e->getMessage());
        }
    }
    /**
     * Enviar invitación de calendario al doctor y al paciente
     * 
     * @param Cita $cita
     * @param string $action 'create', 'update', or 'cancel'
     */
    private function sendCalendarInvitation($cita, $action = 'create')
    {
        try {
            // Cargar relaciones necesarias
            $cita->load(['user', 'paciente']);
            
            $emailsSent = [];
            $emailsFailed = [];
            
            // SIEMPRE enviar al paciente si tiene email válido (por defecto)
            if ($cita->paciente && $cita->paciente->email) {
                $patientEmail = trim($cita->paciente->email);
                
                // Validar formato de email
                if (filter_var($patientEmail, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $cita->paciente->notify(new CitaInvitationMail($cita, $action));
                        $emailsSent[] = "paciente: {$patientEmail}";
                        \Log::info("Calendar invitation sent to patient: {$patientEmail} for cita {$cita->id} (action: {$action})");
                    } catch (\Exception $e) {
                        $emailsFailed[] = "paciente: {$patientEmail} - {$e->getMessage()}";
                        \Log::error("Failed to send to patient {$patientEmail}: " . $e->getMessage());
                    }
                } else {
                    \Log::warning("Invalid patient email format for cita {$cita->id}: {$patientEmail}");
                }
            } else {
                \Log::info("No patient email for cita {$cita->id}");
            }
            
            // Enviar al doctor O correo personalizado SOLO si está asignado explícitamente
            if ($cita->custom_email) {
                // Si hay un correo personalizado, enviar a ese correo
                $customEmail = trim($cita->custom_email);
                
                if (filter_var($customEmail, FILTER_VALIDATE_EMAIL)) {
                    try {
                        \Notification::route('mail', $customEmail)
                            ->notify(new CitaInvitationMail($cita, $action));
                        $emailsSent[] = "correo personalizado: {$customEmail}";
                        \Log::info("Calendar invitation sent to custom email: {$customEmail} for cita {$cita->id} (action: {$action})");
                    } catch (\Exception $e) {
                        $emailsFailed[] = "correo personalizado: {$customEmail} - {$e->getMessage()}";
                        \Log::error("Failed to send to custom email {$customEmail}: " . $e->getMessage());
                    }
                } else {
                    \Log::warning("Invalid custom email format for cita {$cita->id}: {$customEmail}");
                }
            } elseif ($cita->user_id && $cita->user && $cita->user->email) {
                // Si no hay correo personalizado, enviar al doctor asignado
                try {
                    $cita->user->notify(new CitaInvitationMail($cita, $action));
                    $emailsSent[] = "doctor: {$cita->user->email}";
                    \Log::info("Calendar invitation sent to doctor: {$cita->user->email} for cita {$cita->id} (action: {$action})");
                } catch (\Exception $e) {
                    $emailsFailed[] = "doctor: {$cita->user->email} - {$e->getMessage()}";
                    \Log::error("Failed to send to doctor {$cita->user->email}: " . $e->getMessage());
                }
            } else {
                \Log::info("No doctor or custom email assigned for cita {$cita->id}, skipping notification");
            }
            
            // Resumen de envíos
            if (count($emailsSent) > 0) {
                \Log::info("Cita {$cita->id} notifications sent to: " . implode(', ', $emailsSent));
            }
            if (count($emailsFailed) > 0) {
                \Log::warning("Cita {$cita->id} notifications failed: " . implode(', ', $emailsFailed));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the cita operation
            \Log::error('Error sending calendar invitation: ' . $e->getMessage());
        }
    }

    /**
     * Normaliza sillon_id: null/''/'null'/0 → null (evita FK con 0 por (int) null).
     */
    private function normalizeSillonId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'null' || $value === false) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    /**
     * Valida sillón de la clínica y evita doble booking en mismo horario.
     */
    private function assertSillonDisponible(
        ?int $sillonId,
        string $fecha,
        string $hora,
        ?int $sucursalId = null,
        ?int $excludeCitaId = null
    ): ?string {
        if (! $sillonId) {
            return null;
        }

        $user = Auth::user();
        $sillon = Sillon::where('clinica_id', $user->clinica_efectiva_id)
            ->where('id', $sillonId)
            ->where('activo', true)
            ->first();

        if (! $sillon) {
            return 'El sillón seleccionado no es válido';
        }

        if ($sucursalId && $sillon->sucursal_id && (int) $sillon->sucursal_id !== (int) $sucursalId) {
            return 'El sillón no pertenece a esta sucursal';
        }

        $query = Cita::where('sillon_id', $sillonId)
            ->whereDate('fecha', $fecha)
            ->whereTime('hora', $hora)
            ->where('estado', '!=', 'cancelada');

        if ($excludeCitaId) {
            $query->where('id', '!=', $excludeCitaId);
        }

        if ($query->exists()) {
            return 'El sillón ya tiene una cita en ese horario';
        }

        return null;
    }
}
