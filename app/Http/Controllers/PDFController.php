<?php

namespace App\Http\Controllers;

use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use App\Models\ReporteFisio;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\ExpedientePulmonar;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PDFController extends Controller
{
    /**
     * Obtener el doctor que firmará el documento
     * Prioridad:
     * 1. doctor_firma_id enviado en el request (si tiene firma digital)
     * 2. Usuario actual autenticado (si tiene firma digital)
     * 3. Primer doctor de la clínica con firma digital
     * 4. Usuario que creó el expediente (fallback)
     */
    private function getDoctorParaFirma(Request $request, $creadorUserId)
    {
        // 1. Si envían doctor_firma_id específico
        if ($request->has('doctor_firma_id') && $request->doctor_firma_id) {
            $doctorSeleccionado = User::where('id', $request->doctor_firma_id)
                ->whereNotNull('firma_digital')
                ->first();
            
            if ($doctorSeleccionado) {
                return $doctorSeleccionado;
            }
        }

        // 2. Usuario actual si tiene firma
        $usuarioActual = Auth::user();
        if ($usuarioActual && $usuarioActual->firma_digital) {
            return $usuarioActual;
        }

        // 3. Buscar cualquier doctor de la clínica con firma
        if ($usuarioActual) {
            $doctorConFirma = User::where('clinica_id', $usuarioActual->clinica_id)
                ->whereNotNull('firma_digital')
                ->first();
            
            if ($doctorConFirma) {
                return $doctorConFirma;
            }
        }

        // 4. Fallback: usuario que creó el expediente
        return User::find($creadorUserId);
    }

    public function esfuerzoPdf(Request $request)
    {
        $data = Esfuerzo::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);

        $pdf = Pdf::loadView('esfuerzo', compact('data', 'paciente','user')); // Cargar vista PDF con datos

        // Nombre del archivo según el tipo de esfuerzo
        $tipoEsfuerzo = $data->tipo_esfuerzo ?? 'cardiaco';
        $nombreArchivo = $tipoEsfuerzo === 'pulmonar' ? 'pe_Esfuerzo_Pulmonar.pdf' : 'pe_Esfuerzo_Cardiaca.pdf';

        return $pdf->stream($nombreArchivo); // Descargar el PDF
    }

    public function estratificacionPdf(Request $request)
    {
        $data = Estratificacion::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);

        $pdf = Pdf::loadView('estrati', compact('data', 'paciente','user'));
        return $pdf->stream('Estratificacion.pdf'); 
    }

    public function clinicoPdf(Request $request)
    {
        $data = Clinico::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);

        $pdf = Pdf::loadView('clinico', compact('data', 'paciente','user'));
        return $pdf->stream('Clinico.pdf'); 
    }
    public function reportePdf(Request $request)
    {
        $data = ReporteFinal::find($request->id);
        $esfuerzoUno = Esfuerzo::find($data->pe_1);
        $esfuerzoDos = Esfuerzo::find($data->pe_2);
        $paciente =  Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);
        $estrati = Estratificacion::where('paciente_id', $paciente->id)->get();

        // Preparar firma digital si existe
        $firmaBase64 = null;
        if ($user->firma_digital && file_exists(public_path('storage/' . $user->firma_digital))) {
            $imagePath = public_path('storage/' . $user->firma_digital);
            $imageData = file_get_contents($imagePath);
            $imageType = mime_content_type($imagePath);
            $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
        }

        $pdf = Pdf::loadView('reporte', compact('data', 'paciente','user', 'estrati', 'esfuerzoUno', 'esfuerzoDos', 'firmaBase64'));
        return $pdf->stream('Reporte_Final.pdf'); 
    }
    public function psicoPdf(Request $request)
    {
        $data = ReportePsico::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);

        $pdf = Pdf::loadView('psico', compact('data', 'paciente','user'));
        return $pdf->stream('Psico.pdf'); 
    }
    public function nutriPdf(Request $request)
    {
        $data = ReporteNutri::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);

        $pdf = Pdf::loadView('nutri', compact('data', 'paciente','user'));
        return $pdf->stream('Nutri.pdf'); 
    }

    public function pulmonarPdf(Request $request)
    {
        $data = ExpedientePulmonar::find($request->id);
        $paciente = Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);

        // Preparar firma digital si existe
        $firmaBase64 = null;
        if ($user->firma_digital && file_exists(public_path('storage/' . $user->firma_digital))) {
            $imagePath = public_path('storage/' . $user->firma_digital);
            $imageData = file_get_contents($imagePath);
            $imageType = mime_content_type($imagePath);
            $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
        }

        $pdf = Pdf::loadView('pulmonar', compact('data', 'paciente', 'user', 'firmaBase64'));
        return $pdf->stream('Expediente_Pulmonar.pdf'); 
    }

    /**
     * Enviar expediente por correo
     */
    public function sendExpedienteByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expediente_id' => 'required|integer',
            'expediente_type' => 'required|string|in:esfuerzo,estratificacion,clinico,reporte_final,reporte_psico,reporte_nutri,reporte_fisio,expediente_pulmonar',
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $expedienteId = $request->expediente_id;
            $expedienteType = $request->expediente_type;
            $email = $request->email;
            $message = $request->message ?? 'Adjunto encontrará el expediente médico solicitado.';

            // Obtener datos del expediente según el tipo
            $data = null;
            $paciente = null;
            $user = null;
            $pdfFileName = '';

            switch ($expedienteType) {
                case 'esfuerzo':
                    $data = Esfuerzo::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $tipoEsfuerzo = $data->tipo_esfuerzo ?? 'cardiaco';
                    $pdfFileName = $tipoEsfuerzo === 'pulmonar' ? 'Prueba_Esfuerzo_Pulmonar.pdf' : 'Prueba_Esfuerzo_Cardiaca.pdf';
                    break;
                case 'estratificacion':
                    $data = Estratificacion::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $pdfFileName = 'Estratificacion.pdf';
                    break;
                case 'clinico':
                    $data = Clinico::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $pdfFileName = 'Clinico.pdf';
                    break;
                case 'reporte_final':
                    $data = ReporteFinal::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $pdfFileName = 'Reporte_Final.pdf';
                    break;
                case 'reporte_psico':
                    $data = ReportePsico::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $pdfFileName = 'Reporte_Psico.pdf';
                    break;
                case 'reporte_nutri':
                    $data = ReporteNutri::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $pdfFileName = 'Reporte_Nutri.pdf';
                    break;
                case 'reporte_fisio':
                    $data = ReporteFisio::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id ?? $paciente->user_id);
                    $pdfFileName = 'Reporte_Fisio.pdf';
                    break;
                case 'expediente_pulmonar':
                    $data = ExpedientePulmonar::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    $user = $this->getDoctorParaFirma($request, $data->user_id);
                    $pdfFileName = 'Expediente_Pulmonar.pdf';
                    break;
            }

            if (!$data || !$paciente || !$user) {
                return response()->json(['error' => 'Expediente no encontrado', 'debug' => $debugInfo], 404);
            }

            // Generar asunto automático con tipo de expediente y nombre del paciente
            $tipoExpedienteNombres = [
                'esfuerzo' => 'Prueba de Esfuerzo',
                'estratificacion' => 'Estratificación',
                'clinico' => 'Reporte Clínico',
                'reporte_final' => 'Reporte Final',
                'reporte_psico' => 'Reporte Psicológico',
                'reporte_nutri' => 'Reporte Nutricional',
                'reporte_fisio' => 'Reporte de Fisioterapia',
                'expediente_pulmonar' => 'Expediente Pulmonar'
            ];

            $tipoExpedienteNombre = $tipoExpedienteNombres[$expedienteType] ?? 'Expediente Médico';
            
            // Si es esfuerzo, agregar el tipo específico (Cardíaca o Pulmonar)
            if ($expedienteType === 'esfuerzo' && isset($data->tipo_esfuerzo)) {
                $tipoEsfuerzo = $data->tipo_esfuerzo === 'pulmonar' ? 'Pulmonar' : 'Cardíaca';
                $tipoExpedienteNombre = 'Prueba de Esfuerzo ' . $tipoEsfuerzo;
            }
            $nombrePaciente = trim($paciente->nombre . ' ' . $paciente->apellidoPat . ' ' . $paciente->apellidoMat);
            $subject = $tipoExpedienteNombre . ' - ' . $nombrePaciente . ' - CERCAP';
            
            // Si el usuario proporciona un asunto personalizado, usarlo
            if ($request->has('subject') && !empty($request->subject)) {
                $subject = $request->subject;
            }

            // Generar PDF según el tipo
            $pdf = null;
            switch ($expedienteType) {
                case 'esfuerzo':
                    $pdf = Pdf::loadView('esfuerzo', compact('data', 'paciente', 'user'));
                    break;
                case 'estratificacion':
                    $pdf = Pdf::loadView('estrati', compact('data', 'paciente', 'user'));
                    break;
                case 'clinico':
                    $pdf = Pdf::loadView('clinico', compact('data', 'paciente', 'user'));
                    break;
                case 'reporte_final':
                    $esfuerzoUno = Esfuerzo::find($data->pe_1);
                    $esfuerzoDos = Esfuerzo::find($data->pe_2);
                    $estrati = Estratificacion::where('paciente_id', $paciente->id)->get();
                    
                    // Preparar firma digital si existe
                    $firmaBase64 = null;
                    if ($user->firma_digital && file_exists(public_path('storage/' . $user->firma_digital))) {
                        $imagePath = public_path('storage/' . $user->firma_digital);
                        $imageData = file_get_contents($imagePath);
                        $imageType = mime_content_type($imagePath);
                        $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
                    }
                    
                    $pdf = Pdf::loadView('reporte', compact('data', 'paciente', 'user', 'estrati', 'esfuerzoUno', 'esfuerzoDos', 'firmaBase64'));
                    break;
                case 'reporte_psico':
                    $pdf = Pdf::loadView('psico', compact('data', 'paciente', 'user'));
                    break;
                case 'reporte_nutri':
                    $pdf = Pdf::loadView('nutri', compact('data', 'paciente', 'user'));
                    break;
                case 'reporte_fisio':
                    // Para reportes de fisioterapia, usar el archivo existente si está disponible
                    $filePath = storage_path('app/public/' . $data->archivo);
                    $debugInfo = [
                        'archivo_field' => $data->archivo,
                        'full_path' => $filePath,
                        'file_exists' => file_exists($filePath),
                        'is_readable' => is_readable($filePath)
                    ];
                    
                    if ($data->archivo && file_exists($filePath)) {
                        $pdfContent = file_get_contents($filePath);
                    } else {
                        return response()->json(['error' => 'Archivo de fisioterapia no encontrado', 'debug' => $debugInfo], 404);
                    }
                    break;
                case 'expediente_pulmonar':
                    // Preparar firma digital si existe
                    $firmaBase64 = null;
                    if ($user->firma_digital && file_exists(public_path('storage/' . $user->firma_digital))) {
                        $imagePath = public_path('storage/' . $user->firma_digital);
                        $imageData = file_get_contents($imagePath);
                        $imageType = mime_content_type($imagePath);
                        $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
                    }
                    $pdf = Pdf::loadView('pulmonar', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
            }

            // Enviar correo
            if ($expedienteType === 'reporte_fisio') {
                // Para fisioterapia, usar el archivo existente
                Mail::send('emails.expediente', [
                    'paciente' => $paciente,
                    'expediente' => $data,
                    'mensaje' => $message,
                    'tipoExpedienteNombre' => $tipoExpedienteNombre
                ], function ($mail) use ($email, $subject, $pdfContent, $pdfFileName) {
                    $mail->to($email)
                         ->subject($subject)
                         ->attachData($pdfContent, $pdfFileName, ['mime' => 'application/pdf']);
                });
            } else {
                // Para otros tipos, generar PDF
                Mail::send('emails.expediente', [
                    'paciente' => $paciente,
                    'expediente' => $data,
                    'mensaje' => $message,
                    'tipoExpedienteNombre' => $tipoExpedienteNombre
                ], function ($mail) use ($email, $subject, $pdf, $pdfFileName) {
                    $mail->to($email)
                         ->subject($subject)
                         ->attachData($pdf->output(), $pdfFileName, ['mime' => 'application/pdf']);
                });
            }

            return response()->json([
                'message' => 'Expediente enviado por correo exitosamente',
                'email' => $email
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

}
