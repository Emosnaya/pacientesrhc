<?php

namespace App\Http\Controllers;

use App\Models\ControlPrenatal;
use App\Models\HistoriaObstetrica;
use App\Models\Paciente;
use App\Traits\ClinicaScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ControlPrenatalController extends Controller
{
    use ClinicaScope;

    /**
     * Listar controles prenatales de un paciente.
     */
    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $controles = ControlPrenatal::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat', 'historiaObstetrica'])
                ->orderBy('fecha_control', 'desc')
                ->orderBy('numero_control', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $controles,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando controles prenatales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener controles prenatales',
            ], 500);
        }
    }

    /**
     * Crear nuevo control prenatal.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);
            $sucursalId = $request->header('X-Sucursal-ID') ?? $user->sucursal_id;

            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'fecha_control' => 'required|date',
            ]);

            // Calcular número de control automáticamente
            $ultimoControl = ControlPrenatal::where('paciente_id', $request->paciente_id)
                ->where('clinica_id', $clinicaId)
                ->max('numero_control');

            $data = array_merge($request->all(), [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 35,
                'numero_control' => ($ultimoControl ?? 0) + 1,
            ]);

            $control = ControlPrenatal::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Control prenatal creado exitosamente',
                'data' => $control->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando control prenatal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear control prenatal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un control prenatal específico.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $control = ControlPrenatal::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal', 'historiaObstetrica'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $control,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo control prenatal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Control prenatal no encontrado',
            ], 404);
        }
    }

    /**
     * Actualizar control prenatal.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $control = ControlPrenatal::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $control->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Control prenatal actualizado exitosamente',
                'data' => $control->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando control prenatal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar control prenatal',
            ], 500);
        }
    }

    /**
     * Eliminar control prenatal.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $isSuperAdmin = DB::table('user_clinicas')
                ->where('user_id', $user->id)
                ->where('clinica_id', $clinicaId)
                ->where('isSuperAdmin', true)
                ->exists();

            if (!$isSuperAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para eliminar expedientes',
                ], 403);
            }

            $control = ControlPrenatal::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $control->delete();

            return response()->json([
                'success' => true,
                'message' => 'Control prenatal eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando control prenatal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar control prenatal',
            ], 500);
        }
    }

    /**
     * Generar PDF del control prenatal.
     */
    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $control = ControlPrenatal::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal', 'historiaObstetrica'])
                ->firstOrFail();

            $pdf = Pdf::loadView('pdfs.control-prenatal', [
                'control' => $control,
                'paciente' => $control->paciente,
                'clinica' => $control->clinica,
                'user' => $control->user,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = 'control_prenatal_' . $control->numero_control . '_' . $control->paciente->id . '_' . $control->fecha_control->format('Y-m-d') . '.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de control prenatal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }

    /**
     * Obtener resumen de controles de un embarazo.
     */
    public function resumenEmbarazo(Request $request, $historiaObstetricaId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $controles = ControlPrenatal::where('historia_obstetrica_id', $historiaObstetricaId)
                ->where('clinica_id', $clinicaId)
                ->orderBy('numero_control', 'asc')
                ->get();

            // Generar resumen con curva de peso y otros datos
            $resumen = [
                'total_controles' => $controles->count(),
                'controles' => $controles->map(function ($control) {
                    return [
                        'numero' => $control->numero_control,
                        'fecha' => $control->fecha_control,
                        'semanas' => $control->semanas_gestacion,
                        'peso' => $control->signos_vitales['peso'] ?? null,
                        'pa' => ($control->signos_vitales['presion_arterial_sistolica'] ?? '') . '/' . ($control->signos_vitales['presion_arterial_diastolica'] ?? ''),
                        'fcf' => $control->exploracion_obstetrica['frecuencia_cardiaca_fetal'] ?? null,
                        'au' => $control->exploracion_obstetrica['altura_uterina'] ?? null,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $resumen,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen de embarazo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen',
            ], 500);
        }
    }
}
