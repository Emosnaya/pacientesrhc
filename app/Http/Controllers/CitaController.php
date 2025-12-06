<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
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
                $nuevoPaciente->save();
                
                $paciente = $nuevoPaciente;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un paciente existente o crear uno nuevo'
                ], 422);
            }

            // Crear la cita (se permiten múltiples citas al mismo tiempo)
            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'admin_id' => $user->id,
                'clinica_id' => $user->clinica_id,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'estado' => $request->estado ?? 'pendiente',
                'primera_vez' => $request->primera_vez ?? true,
                'notas' => $request->notas
            ]);

            $cita->load(['paciente', 'admin']);

            // Enviar correos de notificación
            $this->sendCitaNotificationEmails($cita, $user);

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

            $cita->update($request->only(['fecha', 'hora', 'estado', 'primera_vez', 'notas']));
            $cita->load(['paciente', 'admin']);

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
    public function destroy($id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $user = Auth::user();

            // Solo los administradores pueden eliminar citas
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden eliminar citas'
                ], 403);
            }

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $cita->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cita eliminada exitosamente'
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

            // Verificar que la cita pertenece a la misma clínica del usuario
            if ($cita->clinica_id !== $user->clinica_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cita'
                ], 403);
            }

            $cita->update(['estado' => $request->estado]);
            $cita->load(['paciente', 'admin']);

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
     * Enviar correos de notificación de cita
     */
    private function sendCitaNotificationEmails($cita, $admin)
    {
        try {
            // Enviar correo solo al paciente si tiene email
            if ($cita->paciente->email) {
                Mail::send('emails.cita-patient-notification', [
                    'cita' => $cita,
                    'paciente' => $cita->paciente
                ], function ($message) use ($cita) {
                    $message->to($cita->paciente->email)
                            ->subject('Confirmación de Cita - ' . $cita->paciente->nombre . ' ' . $cita->paciente->apellidoPat . ' - CERCAP');
                });
            }

        } catch (\Exception $e) {
            // Log error but don't fail the cita creation
            \Log::error('Error sending cita notification emails: ' . $e->getMessage());
        }
    }
}
