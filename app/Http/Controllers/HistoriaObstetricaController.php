<?php

namespace App\Http\Controllers;

use App\Models\HistoriaObstetrica;
use App\Models\Paciente;
use App\Traits\ClinicaScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoriaObstetricaController extends Controller
{
    use ClinicaScope;

    /**
     * Listar historias obstétricas de un paciente.
     */
    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historias = HistoriaObstetrica::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_consulta', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $historias,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando historias obstétricas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historias obstétricas',
            ], 500);
        }
    }

    /**
     * Crear nueva historia obstétrica.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);
            $sucursalId = $request->header('X-Sucursal-ID') ?? $user->sucursal_id;

            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'fecha_consulta' => 'required|date',
            ]);

            $data = array_merge($request->all(), [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 34,
            ]);

            $historia = HistoriaObstetrica::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Historia obstétrica creada exitosamente',
                'data' => $historia->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando historia obstétrica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear historia obstétrica: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar una historia obstétrica específica.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaObstetrica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal', 'controlesPrenatales'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $historia,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo historia obstétrica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Historia obstétrica no encontrada',
            ], 404);
        }
    }

    /**
     * Actualizar historia obstétrica.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaObstetrica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $historia->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Historia obstétrica actualizada exitosamente',
                'data' => $historia->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando historia obstétrica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar historia obstétrica',
            ], 500);
        }
    }

    /**
     * Eliminar historia obstétrica.
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

            $historia = HistoriaObstetrica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $historia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Historia obstétrica eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando historia obstétrica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar historia obstétrica',
            ], 500);
        }
    }

    /**
     * Generar PDF de la historia obstétrica.
     */
    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaObstetrica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal', 'controlesPrenatales'])
                ->firstOrFail();

            $pdf = Pdf::loadView('pdfs.historia-obstetrica', [
                'historia' => $historia,
                'paciente' => $historia->paciente,
                'clinica' => $historia->clinica,
                'user' => $historia->user,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = 'historia_obstetrica_' . $historia->paciente->id . '_' . $historia->fecha_consulta->format('Y-m-d') . '.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de historia obstétrica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }
}
