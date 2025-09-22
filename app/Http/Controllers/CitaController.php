<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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

            // Si no es admin, solo mostrar citas de pacientes accesibles
            if (!$user->isAdmin()) {
                $pacientesAccesibles = $user->getAccessiblePacientes();
                $pacienteIds = $pacientesAccesibles->pluck('id')->toArray();
                $query->whereIn('paciente_id', $pacienteIds);
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
            $validator = Validator::make($request->all(), [
                'paciente_id' => 'required|exists:pacientes,id',
                'fecha' => 'required|date|after_or_equal:today',
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

            $user = Auth::user();
            $paciente = Paciente::findOrFail($request->paciente_id);

            // Verificar permisos
            if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_write')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear citas para este paciente'
                ], 403);
            }

            // Verificar que no haya conflicto de horario (mínimo 1 hora de diferencia)
            $horaCita = Carbon::parse($request->fecha . ' ' . $request->hora);
            $horaInicio = $horaCita->copy()->subHour();
            $horaFin = $horaCita->copy()->addHour();

            $conflicto = Cita::where('fecha', $request->fecha)
                            ->where('estado', '!=', 'cancelada')
                            ->where(function($query) use ($horaInicio, $horaFin) {
                                $query->where('hora', '>', $horaInicio->format('H:i:s'))
                                      ->where('hora', '<', $horaFin->format('H:i:s'));
                            })
                            ->exists();

            if ($conflicto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una cita programada dentro de una hora de diferencia. Se requiere al menos 1 hora entre citas.'
                ], 409);
            }

            $cita = Cita::create([
                'paciente_id' => $request->paciente_id,
                'admin_id' => $user->id,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'estado' => $request->estado ?? 'pendiente',
                'primera_vez' => $request->primera_vez ?? true,
                'notas' => $request->notas
            ]);

            $cita->load(['paciente', 'admin']);

            return response()->json([
                'success' => true,
                'message' => 'Cita creada exitosamente',
                'data' => $cita
            ], 201);

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

            // Verificar permisos
            if (!$user->isAdmin() && !$user->hasPermissionOn($cita->paciente, 'can_read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver esta cita'
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
                'fecha' => 'sometimes|date|after_or_equal:today',
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

            // Verificar permisos
            if (!$user->isAdmin() && !$user->hasPermissionOn($cita->paciente, 'can_edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar esta cita'
                ], 403);
            }

            // Verificar conflicto de horario si se está cambiando fecha o hora (mínimo 1 hora de diferencia)
            if ($request->has('fecha') || $request->has('hora')) {
                $fecha = $request->fecha ?? $cita->fecha;
                $hora = $request->hora ?? $cita->hora;
                
                $horaCita = Carbon::parse($fecha . ' ' . $hora);
                $horaInicio = $horaCita->copy()->subHour();
                $horaFin = $horaCita->copy()->addHour();
                
                $conflicto = Cita::where('fecha', $fecha)
                                ->where('id', '!=', $id)
                                ->where('estado', '!=', 'cancelada')
                                ->where(function($query) use ($horaInicio, $horaFin) {
                                    $query->where('hora', '>', $horaInicio->format('H:i:s'))
                                          ->where('hora', '<', $horaFin->format('H:i:s'));
                                })
                                ->exists();

                if ($conflicto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una cita programada dentro de una hora de diferencia. Se requiere al menos 1 hora entre citas.'
                    ], 409);
                }
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

            // Verificar permisos
            if (!$user->isAdmin() && !$user->hasPermissionOn($cita->paciente, 'can_delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar esta cita'
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
                        ->byMonth($mes, $ano);

            // Si no es admin, solo mostrar citas de pacientes accesibles
            if (!$user->isAdmin()) {
                $pacientesAccesibles = $user->getAccessiblePacientes();
                $pacienteIds = $pacientesAccesibles->pluck('id')->toArray();
                $query->whereIn('paciente_id', $pacienteIds);
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

            // Verificar permisos
            if (!$user->isAdmin() && !$user->hasPermissionOn($cita->paciente, 'can_edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado de esta cita'
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
}
