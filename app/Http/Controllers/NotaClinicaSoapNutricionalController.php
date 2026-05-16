<?php

namespace App\Http\Controllers;

use App\Models\NotaClinicaSoapNutricional;
use App\Traits\ClinicaScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotaClinicaSoapNutricionalController extends Controller
{
    use ClinicaScope;

    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $registros = NotaClinicaSoapNutricional::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_elaboracion', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $registros]);
        } catch (\Exception $e) {
            Log::error('Error listando SOAP nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener notas SOAP'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);
            $sucursalId = $request->header('X-Sucursal-ID') ?? $user->sucursal_id;

            $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'fecha_elaboracion' => 'required|date',
            ]);

            $nota = NotaClinicaSoapNutricional::create(array_merge($request->all(), [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 38,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Nota clínica SOAP creada exitosamente',
                'data' => $nota->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando SOAP nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear nota SOAP: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $nota = NotaClinicaSoapNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json(['success' => true, 'data' => $nota]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo SOAP nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Nota SOAP no encontrada'], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $nota = NotaClinicaSoapNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $nota->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Nota SOAP actualizada exitosamente',
                'data' => $nota->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando SOAP nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar nota SOAP'], 500);
        }
    }

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
                return response()->json(['success' => false, 'message' => 'No tiene permisos para eliminar expedientes'], 403);
            }

            $nota = NotaClinicaSoapNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $nota->delete();

            return response()->json(['success' => true, 'message' => 'Nota SOAP eliminada exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error eliminando SOAP nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar nota SOAP'], 500);
        }
    }

    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $nota = NotaClinicaSoapNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $nota->user && $nota->user->isFirmante()
                ? $nota->user
                : null;
            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath = public_path('storage/' . $firmaUser->firma_digital);
                $firmaBase64 = 'data:' . mime_content_type($imagePath) . ';base64,' . base64_encode(file_get_contents($imagePath));
            }

            $clinicaLogo = null;
            $clinicaObj = $nota->clinica;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath = public_path('storage/' . $clinicaObj->logo);
                $clinicaLogo = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = Pdf::loadView('pdfs.nota-clinica-soap-nutricional', [
                'nota' => $nota,
                'paciente' => $nota->paciente,
                'clinica' => $clinicaObj,
                'sucursal' => $nota->sucursal,
                'user' => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ])->setPaper('letter', 'portrait');

            $fecha = $nota->fecha_elaboracion?->format('Y-m-d') ?? now()->format('Y-m-d');
            $registro = $nota->paciente->registro ?? $nota->paciente_id;
            $filename = "soap_nutricional_{$registro}_{$fecha}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF SOAP nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar PDF'], 500);
        }
    }
}
