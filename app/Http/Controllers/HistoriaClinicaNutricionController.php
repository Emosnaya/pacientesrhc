<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinicaNutricion;
use App\Traits\ClinicaScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoriaClinicaNutricionController extends Controller
{
    use ClinicaScope;

    /**
     * Listar historias clínicas nutriológicas de un paciente.
     */
    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historias = HistoriaClinicaNutricion::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_elaboracion', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $historias,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando historias nutrición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historias clínicas',
            ], 500);
        }
    }

    /**
     * Crear nueva historia clínica nutriológica.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);
            $sucursalId = $request->header('X-Sucursal-ID') ?? $user->sucursal_id;

            $request->validate([
                'paciente_id'       => 'required|exists:pacientes,id',
                'fecha_elaboracion' => 'required|date',
            ]);

            $data = array_merge($request->all(), [
                'user_id'    => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id'=> $sucursalId,
                'tipo_exp'   => 36,
            ]);

            $historia = HistoriaClinicaNutricion::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Historia clínica nutriológica creada exitosamente',
                'data'    => $historia->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando historia nutrición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear historia clínica: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar una historia clínica específica.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaClinicaNutricion::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $historia,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo historia nutrición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Historia clínica no encontrada',
            ], 404);
        }
    }

    /**
     * Actualizar historia clínica nutriológica.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaClinicaNutricion::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $historia->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Historia clínica actualizada exitosamente',
                'data'    => $historia->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando historia nutrición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar historia clínica',
            ], 500);
        }
    }

    /**
     * Eliminar historia clínica nutriológica (solo superadmin).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user      = Auth::user();
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

            $historia = HistoriaClinicaNutricion::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $historia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Historia clínica eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando historia nutrición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar historia clínica',
            ], 500);
        }
    }

    /**
     * Generar PDF de la historia clínica nutriológica.
     */
    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $historia = HistoriaClinicaNutricion::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $historia->user && $historia->user->isFirmante()
                ? $historia->user
                : null;

            // Firma digital
            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath   = public_path('storage/' . $firmaUser->firma_digital);
                $imageData   = file_get_contents($imagePath);
                $imageType   = mime_content_type($imagePath);
                $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            }

            // Logo de la clínica
            $clinicaLogo = null;
            $clinicaObj  = $historia->clinica;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath    = public_path('storage/' . $clinicaObj->logo);
                $logoData    = file_get_contents($logoPath);
                $logoType    = mime_content_type($logoPath);
                $clinicaLogo = 'data:' . $logoType . ';base64,' . base64_encode($logoData);
            }

            $pdf = Pdf::loadView('pdfs.historia-clinica-nutricion', [
                'historia'    => $historia,
                'paciente'    => $historia->paciente,
                'clinica'     => $clinicaObj,
                'sucursal'    => $historia->sucursal,
                'user'        => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $fecha    = $historia->fecha_elaboracion ? $historia->fecha_elaboracion->format('Y-m-d') : now()->format('Y-m-d');
            $registro = $historia->paciente->registro ?? $historia->paciente_id;
            $filename = "historia_nutricion_{$registro}_{$fecha}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF historia nutrición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }
}
