<?php

namespace App\Http\Controllers;

use App\Models\Ecocardiograma;
use App\Models\Paciente;
use App\Traits\ClinicaScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class EcocardiogramaController extends Controller
{
    use ClinicaScope;

    /**
     * Listar ecocardiogramas de un paciente.
     */
    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $ecos = Ecocardiograma::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_estudio', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $ecos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando ecocardiogramas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ecocardiogramas',
            ], 500);
        }
    }

    /**
     * Crear nuevo ecocardiograma.
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
                'tipo_estudio' => 'nullable|string|max:100',
            ]);

            $data = array_merge($request->all(), [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 31,
            ]);

            $eco = Ecocardiograma::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Ecocardiograma creado exitosamente',
                'data' => $eco->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando ecocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear ecocardiograma: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un ecocardiograma específico.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $eco = Ecocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $eco,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo ecocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ecocardiograma no encontrado',
            ], 404);
        }
    }

    /**
     * Actualizar ecocardiograma.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $eco = Ecocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $eco->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ecocardiograma actualizado exitosamente',
                'data' => $eco->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando ecocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar ecocardiograma',
            ], 500);
        }
    }

    /**
     * Eliminar ecocardiograma.
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

            $eco = Ecocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $eco->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ecocardiograma eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando ecocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar ecocardiograma',
            ], 500);
        }
    }

    /**
     * Generar PDF del ecocardiograma.
     */
    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $eco = Ecocardiograma::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $eco->user;
            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath = public_path('storage/' . $firmaUser->firma_digital);
                $imageData = file_get_contents($imagePath);
                $imageType = mime_content_type($imagePath);
                $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            }

            $clinicaObj = $eco->clinica;
            $clinicaLogo = null;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath = public_path('storage/' . $clinicaObj->logo);
                $logoData = file_get_contents($logoPath);
                $logoType = mime_content_type($logoPath);
                $clinicaLogo = 'data:' . $logoType . ';base64,' . base64_encode($logoData);
            }

            $pdf = Pdf::loadView('pdfs.ecocardiograma', [
                'ecocardiograma' => $eco,
                'paciente' => $eco->paciente,
                'clinica' => $clinicaObj,
                'user' => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = "ecocardiograma_{$eco->paciente->registro}_{$eco->fecha_estudio->format('Y-m-d')}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF ecocardiograma: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }

    /**
     * Comparar dos ecocardiogramas del mismo paciente.
     */
    public function compare(Request $request, $id1, $id2): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $eco1 = Ecocardiograma::where('id', $id1)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat', 'paciente'])
                ->firstOrFail();

            $eco2 = Ecocardiograma::where('id', $id2)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat'])
                ->firstOrFail();

            if ($eco1->paciente_id !== $eco2->paciente_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los ecocardiogramas deben ser del mismo paciente',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'eco1' => $eco1,
                    'eco2' => $eco2,
                    'paciente' => $eco1->paciente,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error comparando ecocardiogramas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al comparar ecocardiogramas',
            ], 500);
        }
    }
}
