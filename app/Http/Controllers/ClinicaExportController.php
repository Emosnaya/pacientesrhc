<?php

namespace App\Http\Controllers;

use App\Jobs\BuildClinicaExpedienteExport;
use App\Models\Clinica;
use App\Models\ClinicaExport;
use App\Services\ClinicalAuditService;
use App\Services\ExpedienteClinicExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClinicaExportController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $clinica = $this->resolveClinica($user);
        if (! $clinica) {
            return response()->json(['success' => false, 'message' => 'No hay clínica activa'], 403);
        }

        if (! ExpedienteClinicExportService::userIsClinicaOwner($user, $clinica)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el propietario de la clínica puede solicitar esta exportación.',
            ], 403);
        }

        $pending = ClinicaExport::where('clinica_id', $clinica->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [ClinicaExport::STATUS_PENDING, ClinicaExport::STATUS_PROCESSING])
            ->latest('id')
            ->first();

        if ($pending) {
            return response()->json([
                'success' => true,
                'message' => 'Ya hay una exportación en proceso.',
                'data' => $this->payload($pending),
            ]);
        }

        $export = ClinicaExport::create([
            'clinica_id' => $clinica->id,
            'user_id' => $user->id,
            'sucursal_id' => $user->sucursal_id,
            'status' => ClinicaExport::STATUS_PENDING,
            'scope' => [
                'include_pacientes' => true,
                'include_expedientes' => true,
                'include_archivos' => true,
                'exclude' => ['citas', 'recetas', 'finanzas'],
            ],
        ]);

        ClinicalAuditService::logAccess(
            $user,
            ClinicaExport::class,
            (int) $export->id,
            'Solicitud de exportación de pacientes y expedientes clínicos',
            'exported'
        );

        BuildClinicaExpedienteExport::dispatch($export->id);

        return response()->json([
            'success' => true,
            'message' => 'Exportación iniciada. Te avisaremos cuando esté lista.',
            'data' => $this->payload($export),
        ], 202);
    }

    public function show(Request $request, int $id)
    {
        $user = Auth::user();
        $export = $this->authorizeExport($user, $id);
        if ($export instanceof \Illuminate\Http\JsonResponse) {
            return $export;
        }

        return response()->json([
            'success' => true,
            'data' => $this->payload($export),
        ]);
    }

    public function latest(Request $request)
    {
        $user = Auth::user();
        $clinica = $this->resolveClinica($user);
        if (! $clinica) {
            return response()->json(['success' => false, 'message' => 'No hay clínica activa'], 403);
        }

        if (! ExpedienteClinicExportService::userIsClinicaOwner($user, $clinica)) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $export = ClinicaExport::where('clinica_id', $clinica->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $export ? $this->payload($export) : null,
        ]);
    }

    public function download(Request $request, int $id)
    {
        $user = Auth::user();
        $export = $this->authorizeExport($user, $id);
        if ($export instanceof \Illuminate\Http\JsonResponse) {
            return $export;
        }

        if (! $export->isDownloadable()) {
            $msg = $export->isExpired()
                ? 'La exportación expiró. Solicita una nueva.'
                : 'La exportación aún no está lista.';

            return response()->json(['success' => false, 'message' => $msg], 409);
        }

        if (! Storage::disk('private')->exists($export->ruta_zip)) {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado'], 404);
        }

        ClinicalAuditService::logAccess(
            $user,
            ClinicaExport::class,
            (int) $export->id,
            'Descarga de exportación de pacientes y expedientes',
            'downloaded'
        );

        $filename = 'lynkamed_pacientes_expedientes_'.$export->clinica_id.'_'.$export->id.'.zip';

        return Storage::disk('private')->download($export->ruta_zip, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    protected function resolveClinica($user): ?Clinica
    {
        $clinicaId = $user->clinica_efectiva_id ?? $user->clinica_id;
        if (! $clinicaId) {
            return null;
        }

        return Clinica::find($clinicaId);
    }

    protected function authorizeExport($user, int $id)
    {
        $export = ClinicaExport::find($id);
        if (! $export) {
            return response()->json(['success' => false, 'message' => 'Exportación no encontrada'], 404);
        }

        $clinica = Clinica::find($export->clinica_id);
        if (! $clinica || ! ExpedienteClinicExportService::userIsClinicaOwner($user, $clinica)) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $clinicaActiva = (int) ($user->clinica_efectiva_id ?? $user->clinica_id);
        if ((int) $export->clinica_id !== $clinicaActiva) {
            return response()->json(['success' => false, 'message' => 'No autorizado para esta clínica'], 403);
        }

        if ((int) $export->user_id !== (int) $user->id && ! ExpedienteClinicExportService::userIsClinicaOwner($user, $clinica)) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        return $export;
    }

    protected function payload(ClinicaExport $export): array
    {
        return [
            'id' => $export->id,
            'status' => $export->status,
            'pacientes_total' => $export->pacientes_total,
            'pacientes_done' => $export->pacientes_done,
            'expedientes_total' => $export->expedientes_total,
            'archivos_total' => $export->archivos_total,
            'progress' => $export->progressPercent(),
            'tamanio_bytes' => $export->tamanio_bytes,
            'error_message' => $export->error_message,
            'expires_at' => optional($export->expires_at)?->toIso8601String(),
            'completed_at' => optional($export->completed_at)?->toIso8601String(),
            'downloadable' => $export->isDownloadable(),
            'created_at' => optional($export->created_at)?->toIso8601String(),
        ];
    }
}
