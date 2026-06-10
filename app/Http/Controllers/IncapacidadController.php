<?php

namespace App\Http\Controllers;

use App\Models\Incapacidad;
use App\Traits\ClinicaScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IncapacidadController extends Controller
{
    use ClinicaScope;

    public function index(Request $request, $pacienteId): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $items = Incapacidad::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->with(['user:id,nombre,apellidoPat,apellidoMat'])
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando incapacidades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener incapacidades',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $clinicaId = $this->getClinicaIdFromRequest($request);
            $sucursalId = $request->header('X-Sucursal-ID') ?? $user->sucursal_id;

            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'tipo_incapacidad' => 'required|in:escolar,laboral,deportiva,transporte,otra',
                'fecha_inicio' => 'required|date',
                'fecha_termino' => 'required|date|after_or_equal:fecha_inicio',
                'diagnostico' => 'required|string',
                'comentarios' => 'nullable|string',
            ]);

            $data = array_merge($validated, [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursalId,
                'tipo_exp' => 40,
                'folio' => $this->generarFolio($clinicaId, $sucursalId),
            ]);

            $incapacidad = Incapacidad::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Incapacidad registrada exitosamente',
                'data' => $incapacidad->load('user:id,nombre,apellidoPat,apellidoMat'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando incapacidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar incapacidad: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $incapacidad = Incapacidad::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $incapacidad,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo incapacidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Incapacidad no encontrada',
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $incapacidad = Incapacidad::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $validated = $request->validate([
                'tipo_incapacidad' => 'sometimes|in:escolar,laboral,deportiva,transporte,otra',
                'fecha_inicio' => 'sometimes|date',
                'fecha_termino' => 'sometimes|date|after_or_equal:fecha_inicio',
                'diagnostico' => 'sometimes|string',
                'comentarios' => 'nullable|string',
            ]);

            $incapacidad->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Incapacidad actualizada exitosamente',
                'data' => $incapacidad->load('user:id,nombre,apellidoPat,apellidoMat'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando incapacidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar incapacidad',
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $incapacidad = Incapacidad::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->firstOrFail();

            $incapacidad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incapacidad eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando incapacidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar incapacidad',
            ], 500);
        }
    }

    public function pdf(Request $request, $id)
    {
        try {
            $clinicaId = $this->getClinicaIdFromRequest($request);

            $incapacidad = Incapacidad::where('id', $id)
                ->where('clinica_id', $clinicaId)
                ->with(['user', 'paciente', 'clinica', 'sucursal'])
                ->firstOrFail();

            $firmaUser = $incapacidad->user;

            $firmaBase64 = null;
            if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
                $imagePath = public_path('storage/' . $firmaUser->firma_digital);
                $imageData = file_get_contents($imagePath);
                $imageType = mime_content_type($imagePath);
                $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            }

            $clinicaLogo = null;
            $clinicaObj = $incapacidad->clinica;
            if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
                $logoPath = public_path('storage/' . $clinicaObj->logo);
                $logoData = file_get_contents($logoPath);
                $logoType = mime_content_type($logoPath);
                $clinicaLogo = 'data:' . $logoType . ';base64,' . base64_encode($logoData);
            }

            $pdf = Pdf::loadView('pdfs.incapacidad', [
                'data' => $incapacidad,
                'paciente' => $incapacidad->paciente,
                'clinica' => $clinicaObj,
                'sucursal' => $incapacidad->sucursal,
                'user' => $firmaUser,
                'firmaBase64' => $firmaBase64,
                'clinicaLogo' => $clinicaLogo,
            ]);

            $pdf->setPaper('letter', 'portrait');

            $filename = "incapacidad_{$incapacidad->paciente->registro}_{$incapacidad->fecha_inicio->format('Y-m-d')}.pdf";

            if ($request->query('download') === 'true') {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF incapacidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
            ], 500);
        }
    }

    private function generarFolio($clinicaId, $sucursalId): int
    {
        $ultimoFolio = Incapacidad::where('clinica_id', $clinicaId)
            ->where('sucursal_id', $sucursalId)
            ->max('folio');

        return $ultimoFolio ? ((int) $ultimoFolio + 1) : 1;
    }
}
