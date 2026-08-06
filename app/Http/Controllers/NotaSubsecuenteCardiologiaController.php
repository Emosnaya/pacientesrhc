<?php

namespace App\Http\Controllers;

use App\Models\NotaSubsecuenteCardiologia;
use App\Traits\ClinicaScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use App\Support\FormValue;

class NotaSubsecuenteCardiologiaController extends Controller
{
    use ClinicaScope;

    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $notas = NotaSubsecuenteCardiologia::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with([
                    'user:id,nombre,apellidoPat,apellidoMat',
                    'ordenLaboratorio:id,nota_subsecuente_id,folio,estudios,laboratorio_id,email_laboratorio,correo_enviado,indicaciones,diagnostico_clinico',
                    'ordenLaboratorio.laboratorio:id,nombre,email',
                    'receta:id,nota_subsecuente_id,folio,fecha,diagnostico_principal,indicaciones_generales',
                    'receta.medicamentos',
                ])
                ->orderBy('fecha_consulta', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $notas,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando notas subsecuentes cardiología: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notas de subsecuente',
            ], 500);
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
                'fecha_consulta' => 'required|date',
                'hora' => 'nullable|date_format:H:i',
            ]);

            $data = array_merge(FormValue::fromRequest($request), [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 39,
            ]);

            $nota = NotaSubsecuenteCardiologia::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Nota de subsecuente creada exitosamente',
                'data' => $nota->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando nota subsecuente cardiología: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nota de subsecuente: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $nota = NotaSubsecuenteCardiologia::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with([
                    'user:id,nombre,apellidoPat,apellidoMat,cedula_especialista',
                    'paciente', 'clinica', 'sucursal',
                    'ordenLaboratorio:id,nota_subsecuente_id,folio,estudios,laboratorio_id,email_laboratorio,correo_enviado,indicaciones,diagnostico_clinico',
                    'ordenLaboratorio.laboratorio:id,nombre,email',
                    'receta:id,nota_subsecuente_id,folio,fecha,diagnostico_principal,indicaciones_generales',
                    'receta.medicamentos',
                ])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $nota,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo nota subsecuente cardiología: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Nota de subsecuente no encontrada',
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $nota = NotaSubsecuenteCardiologia::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $nota->update(FormValue::fromRequest($request));

            return response()->json([
                'success' => true,
                'message' => 'Nota de subsecuente actualizada exitosamente',
                'data' => $nota->fresh()->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando nota subsecuente cardiología: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nota de subsecuente',
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para eliminar expedientes',
                ], 403);
            }

            $nota = NotaSubsecuenteCardiologia::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $nota->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nota de subsecuente eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando nota subsecuente cardiología: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar nota de subsecuente',
            ], 500);
        }
    }

    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $nota = NotaSubsecuenteCardiologia::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $nota->user;

            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath = public_path('storage/' . $firmaUser->firma_digital);
                $imageData = file_get_contents($imagePath);
                $imageType = mime_content_type($imagePath);
                $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            }

            $clinicaLogo = null;
            $clinicaObj = $nota->clinica;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath = public_path('storage/' . $clinicaObj->logo);
                $logoData = file_get_contents($logoPath);
                $logoType = mime_content_type($logoPath);
                $clinicaLogo = 'data:' . $logoType . ';base64,' . base64_encode($logoData);
            }

            $pdf = Pdf::loadView('pdfs.nota-subsecuente-cardiologia', [
                'nota' => $nota,
                'paciente' => $nota->paciente,
                'clinica' => $clinicaObj,
                'user' => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = "nota_subsecuente_{$nota->paciente->registro}_{$nota->fecha_consulta->format('Y-m-d')}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF nota subsecuente cardiología: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }
}
