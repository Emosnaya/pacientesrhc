<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
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
            $query = Cita::with(['paciente', 'admin']);

            // Filtrar por clínica del usuario autenticado
            $query->forClinica($user->clinica_id);
            
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
                if ($paciente->clinica_id !== $user->clinica_id) {
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
                    'nuevo_paciente.telefono' => 'required|string|max:20',
                    'nuevo_paciente.email' => 'nullable|email|max:255',
                    'nuevo_paciente.fechaNacimiento' => 'required|date',
                    'nuevo_paciente.genero' => 'required|in:masculino,femenino',
                    'nuevo_paciente.registro' => 'required|string|max:50|unique:pacientes,registro',
                    'nuevo_paciente.tipo_paciente' => 'required|in:cardiaca,pulmonar,ambos,fisioterapia',
                    'nuevo_paciente.user_id' => 'nullable|exists:users,id',
                    'user_id' => 'nullable|exists:users,id',
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
                $peso = $pacienteData['peso'] ?? 0;
                $talla = $pacienteData['talla'] ?? 0;
                $imc = $talla > 0 ? ($peso / ($talla * $talla)) : 0;
                $fechaNacimiento = $pacienteData['fechaNacimiento'];
                $edad = Carbon::parse($fechaNacimiento)->age;

                $nuevoPaciente = new Paciente();
                $nuevoPaciente->registro = $pacienteData['registro'];
                $nuevoPaciente->nombre = $pacienteData['nombre'];
                $nuevoPaciente->apellidoPat = $pacienteData['apellidoPat'];
                $nuevoPaciente->apellidoMat = $pacienteData['apellidoMat'];
                $nuevoPaciente->telefono = $pacienteData['telefono'];
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
                $nuevoPaciente->tipo_paciente = $pacienteData['tipo_paciente'] ?? 'cardiaca';
                
                // Asignar el paciente al doctor seleccionado o al usuario actual
                if (!empty($pacienteData['user_id'])) {
                    // Si se proporcionó un doctor, asignar a ese doctor
                    $doctorId = $pacienteData['user_id'];
                    // Verificar que el doctor existe y pertenece a la misma clínica
                    $doctor = User::where('id', $doctorId)
                        ->where('clinica_id', $user->clinica_id)
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
                
                $nuevoPaciente->clinica_id = $user->clinica_id;
                $nuevoPaciente->sucursal_id = $user->sucursal_id; // Asignar sucursal del usuario
                $nuevoPaciente->save();
                
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

            // Verificar si ya existe una cita con el mismo paciente, fecha y hora
            $citaQuery = Cita::where('paciente_id', $paciente->id)
                ->where('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->where('clinica_id', $user->clinica_id)
                ->whereIn('estado', ['pendiente', 'confirmada']); // Solo verificar citas activas
            
            // Si el usuario tiene sucursal, validar solo en esa sucursal
            if ($user->sucursal_id) {
                $citaQuery->where('sucursal_id', $user->sucursal_id);
            }
            
            $citaExistente = $citaQuery->first();

            if ($citaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una cita para este paciente en la misma fecha y hora'
                ], 422);
            }

            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            // Crear la cita (se permiten múltiples citas al mismo tiempo)
            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'admin_id' => $user->id,
                'user_id' => $userId,
                'clinica_id' => $user->clinica_id,
                'sucursal_id' => $sucursalId,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'estado' => $request->estado ?? 'pendiente',
                'primera_vez' => $request->primera_vez ?? true,
                'notas' => $request->notas,
                'custom_email' => $request->custom_email ?? null
            ]);

            $cita->load(['paciente', 'admin']);

            // Enviar correos de notificación
            $this->sendCitaNotificationEmails($cita, $user);

            // Enviar invitación de calendario al doctor del paciente
            $this->sendCalendarInvitation($cita, 'create');

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
            $cita = Cita::with(['paciente', 'admin'])->findOrFail($id);
            $user = Auth::user();

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_id) {
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
            if ($cita->clinica_id !== $user->clinica_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $cita->update($request->only(['user_id', 'custom_email', 'fecha', 'hora', 'estado', 'primera_vez', 'notas']));
            $cita->load(['paciente', 'admin']);

            // Enviar actualización de calendario si cambió fecha u hora
            if ($request->has('fecha') || $request->has('hora')) {
                $this->sendCalendarInvitation($cita, 'update');
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
            if ($cita->clinica_id !== $user->clinica_id) {
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
            if ($cita->clinica_id !== $user->clinica_id) {
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

            $query = Cita::with(['paciente', 'admin'])
                        ->forClinica($user->clinica_id)
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
            if ($cita->clinica_id !== $user->clinica_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $cita->update(['estado' => $request->estado]);
            $cita->load(['paciente', 'admin']);

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
            if ($paciente->clinica_id !== $user->clinica_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este paciente'
                ], 403);
            }

            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $citasCreadas = [];
            $citasSkipped = [];
            $primeraVez = true; // Solo la primera cita es "primera vez"

            foreach ($request->citas as $citaData) {
                // Verificar si ya existe una cita con el mismo paciente, fecha y hora
                $citaExistente = Cita::where('paciente_id', $paciente->id)
                    ->where('fecha', $citaData['fecha'])
                    ->where('hora', $citaData['hora'])
                    ->where('clinica_id', $user->clinica_id)
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->first();

                if ($citaExistente) {
                    // Saltar esta cita porque ya existe
                    $citasSkipped[] = [
                        'fecha' => $citaData['fecha'],
                        'hora' => $citaData['hora']
                    ];
                    continue;
                }

                $cita = Cita::create([
                    'paciente_id' => $paciente->id,
                    'admin_id' => $user->id,
                    'clinica_id' => $user->clinica_id,
                    'sucursal_id' => $sucursalId,
                    'fecha' => $citaData['fecha'],
                    'hora' => $citaData['hora'],
                    'estado' => $citaData['estado'] ?? 'confirmada',
                    'primera_vez' => $primeraVez,
                    'notas' => $citaData['notas'] ?? null
                ]);

                $cita->load(['paciente', 'admin']);
                $citasCreadas[] = $cita;
                $primeraVez = false; // Las siguientes ya no son primera vez
            }

            // Enviar correo de notificación (una sola vez con todas las citas)
            if (!empty($citasCreadas)) {
                $this->sendMultipleCitasNotificationEmail($citasCreadas, $paciente, $user);
            }

            $message = count($citasCreadas) . ' cita(s) creada(s) exitosamente';
            if (!empty($citasSkipped)) {
                $message .= '. ' . count($citasSkipped) . ' cita(s) omitida(s) por duplicado';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $citasCreadas,
                'total' => count($citasCreadas),
                'skipped' => count($citasSkipped)
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
        try {
            if ($paciente->email) {
                Mail::send('emails.cita-patient-notification', [
                    'cita' => $citas[0], // Primera cita para compatibilidad
                    'citas' => $citas,   // Todas las citas
                    'paciente' => $paciente,
                    'multiple' => count($citas) > 1
                ], function ($message) use ($paciente, $citas) {
                    $subject = count($citas) > 1 
                        ? 'Confirmación de ' . count($citas) . ' Citas - CERCAP'
                        : 'Confirmación de Cita - CERCAP';
                    $message->to($paciente->email)->subject($subject);
                });
            }
        } catch (\Exception $e) {
            \Log::error('Error sending multiple citas notification email: ' . $e->getMessage());
        }
    }

    /**
     * Enviar correos de notificación de cita
     */
    private function sendCitaNotificationEmails($cita, $admin)
    {
        try {
            // Enviar correo solo al paciente si tiene email
            if ($cita->paciente->email) {
                // Usar Mailable personalizado para enviar con ICS incluido
                \Mail::to($cita->paciente->email)
                     ->send(new \App\Mail\CitaNotificationMail($cita));
                
                \Log::info('Cita notification email sent to patient: ' . $cita->paciente->email);
            }

        } catch (\Exception $e) {
            // Log error but don't fail the cita creation
            \Log::error('Error sending cita notification emails: ' . $e->getMessage());
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
}
