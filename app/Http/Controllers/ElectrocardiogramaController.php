<?php

namespace App\Http\Controllers;

use App\Models\Electrocardiograma;
use App\Models\Paciente;
use App\Traits\ClinicaScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ElectrocardiogramaController extends Controller
{
    use ClinicaScope;

    /**
     * Listar electrocardiogramas de un paciente.
     */
    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $ecgs = Electrocardiograma::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_estudio', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $ecgs,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando electrocardiogramas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener electrocardiogramas',
            ], 500);
        }
    }

    /**
     * Crear nuevo electrocardiograma.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);
            $sucursalId = $request->header('X-Sucursal-ID') ?? $user->sucursal_id;

            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'fecha_estudio' => 'required|date',
                'imagen_ecg' => 'nullable|image|max:5120', // 5MB max
            ]);

            $data = $request->except('imagen_ecg');
            $data['user_id'] = $user->id;
            $data['clinica_id'] = $clinicaId;
            $data['sucursal_id'] = $sucursalId;
            $data['tipo_exp'] = 32;

            // Subir imagen del ECG si existe
            if ($request->hasFile('imagen_ecg')) {
                $path = $request->file('imagen_ecg')->store("clinicas/{$clinicaId}/ecg", 'public');
                $data['imagen_ecg'] = $path;
            }

            $ecg = Electrocardiograma::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Electrocardiograma creado exitosamente',
                'data' => $ecg->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando electrocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear electrocardiograma: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un electrocardiograma específico.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $ecg = Electrocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $ecg,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo electrocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Electrocardiograma no encontrado',
            ], 404);
        }
    }

    /**
     * Actualizar electrocardiograma.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $ecg = Electrocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $data = $request->except('imagen_ecg');

            // Actualizar imagen si se proporciona una nueva
            if ($request->hasFile('imagen_ecg')) {
                // Eliminar imagen anterior
                if ($ecg->imagen_ecg) {
                    Storage::disk('public')->delete($ecg->imagen_ecg);
                }
                $path = $request->file('imagen_ecg')->store("clinicas/{$clinicaId}/ecg", 'public');
                $data['imagen_ecg'] = $path;
            }

            $ecg->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Electrocardiograma actualizado exitosamente',
                'data' => $ecg->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando electrocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar electrocardiograma',
            ], 500);
        }
    }

    /**
     * Eliminar electrocardiograma.
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

            $ecg = Electrocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            // Eliminar imagen si existe
            if ($ecg->imagen_ecg) {
                Storage::disk('public')->delete($ecg->imagen_ecg);
            }

            $ecg->delete();

            return response()->json([
                'success' => true,
                'message' => 'Electrocardiograma eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando electrocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar electrocardiograma',
            ], 500);
        }
    }

    /**
     * Generar PDF del electrocardiograma.
     */
    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $ecg = Electrocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $pdf = Pdf::loadView('pdfs.electrocardiograma', [
                'ecg' => $ecg,
                'paciente' => $ecg->paciente,
                'clinica' => $ecg->clinica,
                'user' => $ecg->user,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = "ecg_{$ecg->paciente->registro}_{$ecg->fecha->format('Y-m-d')}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF electrocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }

    /**
     * Comparar dos electrocardiogramas del mismo paciente.
     */
    public function compare(Request $request, $id1, $id2): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $ecg1 = Electrocardiograma::where('id', $id1)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat', 'paciente'])
                ->firstOrFail();

            $ecg2 = Electrocardiograma::where('id', $id2)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat'])
                ->firstOrFail();

            if ($ecg1->paciente_id !== $ecg2->paciente_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los electrocardiogramas deben ser del mismo paciente',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'ecg1' => $ecg1,
                    'ecg2' => $ecg2,
                    'paciente' => $ecg1->paciente,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error comparando electrocardiogramas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al comparar electrocardiogramas',
            ], 500);
        }
    }
}
