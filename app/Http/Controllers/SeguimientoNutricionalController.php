<?php

namespace App\Http\Controllers;

use App\Models\SeguimientoNutricional;
use App\Traits\ClinicaScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeguimientoNutricionalController extends Controller
{
    use ClinicaScope;

    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $registros = SeguimientoNutricional::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_elaboracion', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $registros]);
        } catch (\Exception $e) {
            Log::error('Error listando seguimiento nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener seguimientos'], 500);
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

            $seguimiento = SeguimientoNutricional::create(array_merge($request->all(), [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 37,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento nutricional creado exitosamente',
                'data' => $seguimiento->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando seguimiento nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear seguimiento: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $seguimiento = SeguimientoNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat,cedula_especialista', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json(['success' => true, 'data' => $seguimiento]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo seguimiento nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Seguimiento no encontrado'], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $seguimiento = SeguimientoNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $seguimiento->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento nutricional actualizado exitosamente',
                'data' => $seguimiento->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando seguimiento nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar seguimiento'], 500);
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

            $seguimiento = SeguimientoNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $seguimiento->delete();

            return response()->json(['success' => true, 'message' => 'Seguimiento eliminado exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error eliminando seguimiento nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar seguimiento'], 500);
        }
    }

    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $seguimiento = SeguimientoNutricional::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $seguimiento->user && $seguimiento->user->isFirmante()
                ? $seguimiento->user
                : null;
            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath = public_path('storage/' . $firmaUser->firma_digital);
                $firmaBase64 = 'data:' . mime_content_type($imagePath) . ';base64,' . base64_encode(file_get_contents($imagePath));
            }

            $clinicaLogo = null;
            $clinicaObj = $seguimiento->clinica;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath = public_path('storage/' . $clinicaObj->logo);
                $clinicaLogo = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = Pdf::loadView('pdfs.seguimiento-nutricional', [
                'seguimiento' => $seguimiento,
                'paciente' => $seguimiento->paciente,
                'clinica' => $clinicaObj,
                'sucursal' => $seguimiento->sucursal,
                'user' => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ])->setPaper('letter', 'portrait');

            $fecha = $seguimiento->fecha_elaboracion?->format('Y-m-d') ?? now()->format('Y-m-d');
            $registro = $seguimiento->paciente->registro ?? $seguimiento->paciente_id;
            $filename = "seguimiento_nutricional_{$registro}_{$fecha}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF seguimiento nutricional: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar PDF'], 500);
        }
    }
}
