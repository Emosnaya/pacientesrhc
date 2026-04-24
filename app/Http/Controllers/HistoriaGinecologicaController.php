<?php

namespace App\Http\Controllers;

use App\Models\HistoriaGinecologica;
use App\Models\Paciente;
use App\Traits\ClinicaScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoriaGinecologicaController extends Controller
{
    use ClinicaScope;

    /**
     * Listar historias ginecológicas de un paciente.
     */
    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historias = HistoriaGinecologica::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_consulta', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $historias,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando historias ginecológicas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historias ginecológicas',
            ], 500);
        }
    }

    /**
     * Crear nueva historia ginecológica.
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
                'tipo_exp' => 33,
            ]);

            $historia = HistoriaGinecologica::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Historia ginecológica creada exitosamente',
                'data' => $historia->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando historia ginecológica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear historia ginecológica: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar una historia ginecológica específica.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaGinecologica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $historia,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo historia ginecológica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Historia ginecológica no encontrada',
            ], 404);
        }
    }

    /**
     * Actualizar historia ginecológica.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaGinecologica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $historia->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Historia ginecológica actualizada exitosamente',
                'data' => $historia->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando historia ginecológica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar historia ginecológica',
            ], 500);
        }
    }

    /**
     * Eliminar historia ginecológica.
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

            $historia = HistoriaGinecologica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $historia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Historia ginecológica eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando historia ginecológica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar historia ginecológica',
            ], 500);
        }
    }

    /**
     * Generar PDF de la historia ginecológica.
     */
    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaGinecologica::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $historia->user;
            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath = public_path('storage/' . $firmaUser->firma_digital);
                $imageData = file_get_contents($imagePath);
                $imageType = mime_content_type($imagePath);
                $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            }

            $clinicaObj = $historia->clinica;
            $clinicaLogo = null;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath = public_path('storage/' . $clinicaObj->logo);
                $logoData = file_get_contents($logoPath);
                $logoType = mime_content_type($logoPath);
                $clinicaLogo = 'data:' . $logoType . ';base64,' . base64_encode($logoData);
            }

            $pdf = Pdf::loadView('pdfs.historia-ginecologica', [
                'historia' => $historia,
                'paciente' => $historia->paciente,
                'clinica' => $clinicaObj,
                'user' => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = 'historia_ginecologica_' . $historia->paciente->id . '_' . $historia->fecha_consulta->format('Y-m-d') . '.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de historia ginecológica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }
}
