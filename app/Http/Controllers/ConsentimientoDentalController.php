<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteArchivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConsentimientoDentalController extends Controller
{
    public function plantillas(): JsonResponse
    {
        $list = collect(config('consentimientos_dentales.plantillas', []))
            ->values()
            ->map(fn ($p) => [
                'clave' => $p['clave'],
                'titulo' => $p['titulo'],
                'descripcion' => $p['descripcion'],
                'procedimiento_default' => $p['procedimiento_default'],
            ]);

        return response()->json(['data' => $list]);
    }

    public function store(Request $request, int $pacienteId): JsonResponse
    {
        $user = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        if (! $paciente->belongsToClinicaWorkspace((int) ($user->clinica_efectiva_id ?? 0))) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $claves = array_keys(config('consentimientos_dentales.plantillas', []));

        $data = $request->validate([
            'plantilla' => 'required|string|in:'.implode(',', $claves),
            'procedimiento' => 'required|string|max:500',
            'diagnostico' => 'nullable|string|max:500',
            'notas_adicionales' => 'nullable|string|max:2000',
            'firma_paciente' => ['required', 'string', 'regex:/^data:image\/(png|jpg|jpeg);base64,/'],
            'nombre_firmante' => 'nullable|string|max:255',
            'parentesco_firmante' => 'nullable|string|max:100',
            'es_tutor' => 'sometimes|boolean',
        ]);

        $plantilla = config('consentimientos_dentales.plantillas.'.$data['plantilla']);
        if (! $plantilla) {
            return response()->json(['message' => 'Plantilla no encontrada'], 422);
        }

        $pdfController = app(PDFController::class);
        $clinica = $pdfController->getClinicaInfo($user);
        $clinicaLogo = $pdfController->getClinicaLogoBase64($user);

        $firmaMedico = null;
        if (! empty($user->firma_digital)) {
            $path = public_path('storage/'.$user->firma_digital);
            if (! file_exists($path)) {
                $path = storage_path('app/public/'.$user->firma_digital);
            }
            if (file_exists($path)) {
                $firmaMedico = 'data:'.mime_content_type($path).';base64,'.base64_encode(file_get_contents($path));
            }
        }

        $nombrePaciente = trim(($paciente->nombre ?? '').' '.($paciente->apellidoPat ?? '').' '.($paciente->apellidoMat ?? ''));
        $nombreFirmante = trim($data['nombre_firmante'] ?? '') ?: $nombrePaciente;
        $esTutor = (bool) ($data['es_tutor'] ?? false);

        $payload = [
            'paciente' => $paciente,
            'user' => $user,
            'clinica' => $clinica,
            'clinicaLogo' => $clinicaLogo,
            'plantilla' => $plantilla,
            'procedimiento' => $data['procedimiento'],
            'diagnostico' => $data['diagnostico'] ?? null,
            'notas_adicionales' => $data['notas_adicionales'] ?? null,
            'firmaPaciente' => $data['firma_paciente'],
            'firmaMedico' => $firmaMedico,
            'nombrePaciente' => $nombrePaciente,
            'nombreFirmante' => $nombreFirmante,
            'parentescoFirmante' => $data['parentesco_firmante'] ?? null,
            'esTutor' => $esTutor,
            'fecha' => now()->timezone(config('app.timezone', 'America/Mexico_City')),
            'lugar' => $clinica->direccion ?: ($clinica->nombre ?? 'Consultorio dental'),
        ];

        $pdf = Pdf::loadView('dental.consentimiento_informado', $payload)
            ->setPaper('letter');

        $binary = $pdf->output();
        $slug = Str::slug($plantilla['clave'].'-'.$nombrePaciente);
        $filename = 'CI_'.$slug.'_'.now()->format('Ymd_His').'.pdf';
        $storagePath = "pacientes/{$pacienteId}/archivos/".$filename;

        Storage::disk('private')->put($storagePath, $binary);

        $archivo = PacienteArchivo::create([
            'paciente_id' => $pacienteId,
            'clinica_id' => $user->clinica_efectiva_id,
            'nombre_original' => $filename,
            'ruta' => $storagePath,
            'mime_type' => 'application/pdf',
            'tamanio' => strlen($binary),
            'descripcion' => 'Consentimiento informado — '.$plantilla['titulo'],
            'subido_por_paciente' => false,
            'visible_en_portal' => false,
            'subido_por_user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Consentimiento firmado y guardado en documentos del paciente',
            'data' => [
                'id' => $archivo->id,
                'nombre_original' => $archivo->nombre_original,
                'descripcion' => $archivo->descripcion,
                'mime_type' => $archivo->mime_type,
                'tamanio' => $archivo->tamanio,
                'created_at' => $archivo->created_at?->format('d/m/Y H:i'),
            ],
        ], 201);
    }
}
