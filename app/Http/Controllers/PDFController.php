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
use App\Models\CualidadFisica;
use App\Models\ReporteFinalPulmonar;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaAltaFisioterapia;
use App\Models\HistoriaClinicaDental;
use App\Models\Odontograma;
use App\Models\NotaSeguimientoPulmonar;
use App\Models\Receta;
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
     * Quien firma el PDF. Por seguridad NUNCA se acepta doctor_firma_id del request:
     * solo la firma del usuario actual o sin firma.
     * - Usuario actual con firma digital cargada → se usa su firma.
     * - Sin firma cargada o no logueado → null (PDF sin apartado de doctor/firma).
     */
    private function getDoctorParaFirma(Request $request, $creadorUserId)
    {
        $usuarioActual = Auth::user();
        if (!$usuarioActual) {
            return User::find($creadorUserId);
        }

        // Si el usuario actual tiene firma digital cargada, firmar con su propia firma (sin depender del rol)
        if ($usuarioActual->firma_digital) {
            return $usuarioActual;
        }

        return null;
    }

    /**
     * Resolver usuario para PDF: quien firma (puede ser null si es administrativo) y usuario para clinica/logo.
     */
    private function resolveUsuarioPdf(Request $request, $creadorUserId): array
    {
        $userFirma = $this->getDoctorParaFirma($request, $creadorUserId);
        $user = $userFirma ?? User::find($creadorUserId) ?? Auth::user();
        return [$user, $userFirma];
    }

    /**
     * Obtener firma digital del usuario en base64 (data URL).
     * Prueba public/storage y storage/app/public por si no existe el symlink.
     */
    private function getFirmaBase64($user): ?string
    {
        if (!$user || !$user->firma_digital) {
            return null;
        }
        $path = public_path('storage/' . $user->firma_digital);
        if (!file_exists($path)) {
            $path = storage_path('app/public/' . $user->firma_digital);
        }
        if (!file_exists($path)) {
            return null;
        }
        $imageData = file_get_contents($path);
        $imageType = mime_content_type($path);
        return 'data:' . $imageType . ';base64,' . base64_encode($imageData);
    }

    /**
     * Obtener el logo de la clínica en formato base64 para PDF
     * Redimensiona la imagen a la altura especificada para garantizar el tamaño correcto en dompdf
     */
    private function getClinicaLogoBase64($user, $targetHeight = 36)
    {
        try {
            $clinica = $user->clinica;
            $logoPath = null;
            
            if ($clinica && $clinica->logo) {
                $logoPath = storage_path('app/public/' . $clinica->logo);
            }
            
            // Si no existe el logo de la clínica, usar el logo por defecto
            if (!$logoPath || !file_exists($logoPath)) {
                $logoPath = public_path('img/logo.png');
            }
            
            // Si tampoco existe el logo por defecto, retornar null
            if (!file_exists($logoPath)) {
                \Log::error('Logo no encontrado: ' . $logoPath);
                return null;
            }
            
            // Verificar si GD está disponible
            if (!function_exists('imagecreatefrompng')) {
                \Log::warning('GD no está disponible, usando imagen original');
                $imageData = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            
            // Redimensionar la imagen para que dompdf respete el tamaño
            $imageInfo = @getimagesize($logoPath);
            if (!$imageInfo) {
                \Log::error('No se pudo obtener información de la imagen: ' . $logoPath);
                $imageData = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];
            
            // Calcular nuevo ancho manteniendo proporción
            $ratio = $targetHeight / $originalHeight;
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)$targetHeight;
            
            // Crear imagen desde el archivo original
            $sourceImage = null;
            switch ($mimeType) {
                case 'image/jpeg':
                    $sourceImage = @imagecreatefromjpeg($logoPath);
                    break;
                case 'image/png':
                    $sourceImage = @imagecreatefrompng($logoPath);
                    break;
                case 'image/gif':
                    $sourceImage = @imagecreatefromgif($logoPath);
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = @imagecreatefromwebp($logoPath);
                    }
                    break;
            }
            
            if (!$sourceImage) {
                \Log::warning('No se pudo crear imagen desde: ' . $logoPath);
                $imageData = file_get_contents($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            
            // Crear nueva imagen redimensionada
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG y GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled(
                $resizedImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // Capturar output en buffer
            ob_start();
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($resizedImage, null, 90);
                    break;
                case 'image/png':
                    imagepng($resizedImage, null, 9);
                    break;
                case 'image/gif':
                    imagegif($resizedImage);
                    break;
                case 'image/webp':
                    if (function_exists('imagewebp')) {
                        imagewebp($resizedImage);
                    }
                    break;
                default:
                    imagepng($resizedImage);
                    $mimeType = 'image/png';
            }
            $imageData = ob_get_clean();
            
            // Liberar memoria
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);
            
            \Log::info('Logo redimensionado exitosamente', [
                'original' => $originalWidth . 'x' . $originalHeight,
                'nuevo' => $newWidth . 'x' . $newHeight
            ]);
            
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            
        } catch (\Exception $e) {
            \Log::error('Error al procesar logo: ' . $e->getMessage());
            // Fallback: devolver imagen original sin redimensionar
            if (isset($logoPath) && file_exists($logoPath)) {
                $imageData = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            return null;
        }
    }

    /**
     * Obtener la información completa de la clínica para las vistas
     */
    private function getClinicaInfo($user)
    {
        $clinica = $user->clinica;
        
        if (!$clinica) {
            // Datos por defecto si no hay clínica
            return (object)[
                'nombre' => 'Clínica Médica',
                'telefono' => '',
                'email' => '',
                'direccion' => '',
                'logo' => null,
                'logo_url' => asset('img/logo.png')
            ];
        }
        
        // Agregar logo_url al objeto clínica
        $clinica->logo_url = $clinica->logo 
            ? asset('storage/' . $clinica->logo) 
            : asset('img/logo.png');
        
        return $clinica;
    }

    public function esfuerzoPdf(Request $request)
    {
        $data = Esfuerzo::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('cardiaca.esfuerzo', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64')); // Cargar vista PDF con datos

        // Nombre del archivo según el tipo de esfuerzo
        $tipoEsfuerzo = $data->tipo_esfuerzo ?? 'cardiaco';
        $nombreArchivo = $tipoEsfuerzo === 'pulmonar' ? 'pe_Esfuerzo_Pulmonar.pdf' : 'pe_Esfuerzo_Cardiaca.pdf';

        return $pdf->stream($nombreArchivo); // Descargar el PDF
    }

    public function estratificacionPdf(Request $request)
    {
        $data = Estratificacion::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('cardiaca.estrati', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
        return $pdf->stream('Estratificacion.pdf'); 
    }

    public function clinicoPdf(Request $request)
    {
        $data = Clinico::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('cardiaca.clinico', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
        return $pdf->stream('Clinico.pdf'); 
    }
    public function reportePdf(Request $request)
    {
        $data = ReporteFinal::find($request->id);
        $esfuerzoUno = Esfuerzo::find($data->pe_1);
        $esfuerzoDos = Esfuerzo::find($data->pe_2);
        $paciente =  Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
        $estrati = Estratificacion::where('paciente_id', $paciente->id)->get();

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('cardiaca.reporte', compact('data', 'paciente', 'user', 'estrati', 'esfuerzoUno', 'esfuerzoDos', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Reporte_Final.pdf'); 
    }
    public function psicoPdf(Request $request)
    {
        $data = ReportePsico::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $pdf = Pdf::loadView('psico', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Psico.pdf'); 
    }
    public function nutriPdf(Request $request)
    {
        $data = ReporteNutri::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $pdf = Pdf::loadView('nutri', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Nutri.pdf'); 
    }

    public function pulmonarPdf(Request $request)
    {
        $data = ExpedientePulmonar::find($request->id);
        $paciente = Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('pulmonar.pulmonar', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Expediente_Pulmonar.pdf'); 
    }

    public function historiaFisioterapiaPdf(Request $request)
    {
        $data = HistoriaClinicaFisioterapia::find($request->id);
        $paciente = Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $paciente->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user, 60);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('fisioterapia.historia', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Historia_Clinica_Fisioterapia.pdf');
    }

    public function notaEvolucionFisioterapiaPdf(Request $request)
    {
        $data = NotaEvolucionFisioterapia::find($request->id);
        $paciente = Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $paciente->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user, 60);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('fisioterapia.evolucion', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Nota_Evolucion_Fisioterapia.pdf');
    }

    public function notaAltaFisioterapiaPdf(Request $request)
    {
        $data = NotaAltaFisioterapia::find($request->id);
        $paciente = Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $paciente->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user, 60);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('fisioterapia.alta', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Nota_Alta_Fisioterapia.pdf');
    }

    public function notaSeguimientoPulmonarPdf(Request $request)
    {
        $data = NotaSeguimientoPulmonar::find($request->id);
        if (!$data) {
            abort(404);
        }
        $paciente = Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user, 60);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('pulmonar.nota_seguimiento_pulmonar', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Nota_Seguimiento_Pulmonar.pdf');
    }

    public function historiaDentalPdf(Request $request)
    {
        $data = HistoriaClinicaDental::find($request->id);
        if (!$data) {
            abort(404, 'Historia clínica dental no encontrada');
        }
        $paciente = Paciente::find($data->paciente_id);
        if (!$paciente) {
            abort(404, 'Paciente no encontrado');
        }
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
        if (!$user) {
            abort(404, 'No se pudo determinar el médico para la firma');
        }

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('dental.historia_dental', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Historia_Clinica_Dental.pdf');
    }

    /**
     * PDF de Receta Médica
     */
    public function recetaPdf(Request $request, $id)
    {
        $data = Receta::with('medicamentos')->find($id);
        if (!$data) {
            abort(404, 'Receta no encontrada');
        }
        $paciente = Paciente::find($data->paciente_id);
        if (!$paciente) {
            abort(404, 'Paciente no encontrado');
        }
        $userFirma = $this->getDoctorParaFirma($request, $data->user_id);
        $user = $userFirma ?? User::find($data->user_id) ?? Auth::user();
        if (!$user) {
            abort(404, 'No se pudo determinar el médico para la firma');
        }

        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('receta', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
        return $pdf->stream('Receta_Medica.pdf');
    }

    public function odontogramaPdf(Request $request)
    {
        $data = Odontograma::find($request->id);
        
        if (!$data) {
            return response()->json(['error' => 'Odontograma no encontrado'], 404);
        }

        $paciente = Paciente::find($data->paciente_id);
        $user = $this->getDoctorParaFirma($request, $data->user_id);
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);
        
        // Obtener firma base64
        $firmaBase64 = null;
        if ($user->firma) {
            $firmaPath = storage_path('app/public/' . $user->firma);
            if (file_exists($firmaPath)) {
                $firmaBase64 = $this->getFirmaBase64($user);
            }
        }

        // Decodificar los dientes (están guardados como JSON)
        $dientes = is_string($data->dientes) ? json_decode($data->dientes, true) : $data->dientes;

        $pdf = Pdf::loadView('dental.odontograma', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64', 'dientes'));
        return $pdf->stream('Odontograma_' . $paciente->nombre . '_' . $paciente->apellido_paterno . '.pdf');
    }

    /**
     * Enviar expediente por correo
     */
    public function sendExpedienteByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expediente_id' => 'required|integer',
            'expediente_type' => 'required|string|in:esfuerzo,estratificacion,clinico,reporte_final,reporte_psico,reporte_nutri,reporte_fisio,expediente_pulmonar,cualidad_fisica,reporte_final_pulmonar,historia_dental,odontograma',
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
            $userFirma = null;
            $pdfFileName = '';

            switch ($expedienteType) {
                case 'esfuerzo':
                    $data = Esfuerzo::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $tipoEsfuerzo = $data->tipo_esfuerzo ?? 'cardiaco';
                    $pdfFileName = $tipoEsfuerzo === 'pulmonar' ? 'Prueba_Esfuerzo_Pulmonar.pdf' : 'Prueba_Esfuerzo_Cardiaca.pdf';
                    break;
                case 'estratificacion':
                    $data = Estratificacion::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Estratificacion.pdf';
                    break;
                case 'clinico':
                    $data = Clinico::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Clinico.pdf';
                    break;
                case 'reporte_final':
                    $data = ReporteFinal::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Reporte_Final.pdf';
                    break;
                case 'reporte_psico':
                    $data = ReportePsico::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Reporte_Psico.pdf';
                    break;
                case 'reporte_nutri':
                    $data = ReporteNutri::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Reporte_Nutri.pdf';
                    break;
                case 'reporte_fisio':
                    $data = ReporteFisio::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id ?? $paciente->user_id);
                    $pdfFileName = 'Reporte_Fisio.pdf';
                    break;
                case 'expediente_pulmonar':
                    $data = ExpedientePulmonar::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Expediente_Pulmonar.pdf';
                    break;
                case 'cualidad_fisica':
                    $data = CualidadFisica::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Cualidades_Fisicas.pdf';
                    break;
                case 'reporte_final_pulmonar':
                    $data = ReporteFinalPulmonar::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Reporte_Final_Pulmonar.pdf';
                    break;
                case 'historia_dental':
                    $data = HistoriaClinicaDental::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
                    $pdfFileName = 'Historia_Clinica_Dental.pdf';
                    break;
                case 'odontograma':
                    $data = Odontograma::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id ?? $paciente->user_id);
                    $pdfFileName = 'Odontograma.pdf';
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
                'expediente_pulmonar' => 'Expediente Pulmonar',
                'cualidad_fisica' => 'Cualidades Físicas No Aeróbicas',
                'reporte_final_pulmonar' => 'Reporte Final Pulmonar'
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
            $firmaBase64 = $this->getFirmaBase64($userFirma);

            switch ($expedienteType) {
                case 'esfuerzo':
                    $pdf = Pdf::loadView('cardiaca.esfuerzo', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
                case 'estratificacion':
                    $pdf = Pdf::loadView('cardiaca.estrati', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
                case 'clinico':
                    $pdf = Pdf::loadView('cardiaca.clinico', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
                case 'reporte_final':
                    $esfuerzoUno = Esfuerzo::find($data->pe_1);
                    $esfuerzoDos = Esfuerzo::find($data->pe_2);
                    $estrati = Estratificacion::where('paciente_id', $paciente->id)->get();
                    
                    $firmaBase64 = $this->getFirmaBase64($userFirma);

                    $pdf = Pdf::loadView('cardiaca.reporte', compact('data', 'paciente', 'user', 'estrati', 'esfuerzoUno', 'esfuerzoDos', 'firmaBase64'));
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
                    $firmaBase64 = $this->getFirmaBase64($userFirma);
                    $pdf = Pdf::loadView('pulmonar.pulmonar', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
                case 'cualidad_fisica':
                    $firmaBase64 = $this->getFirmaBase64($userFirma);
                    $pdf = Pdf::loadView('cardiaca.cualidadesfisicas', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
                case 'reporte_final_pulmonar':
                    $firmaBase64 = $this->getFirmaBase64($userFirma);
                    $pdf = Pdf::loadView('pulmonar.reportefinalpulmonar', compact('data', 'paciente', 'user', 'firmaBase64'));
                    break;
            }

            // Obtener clínica del usuario
            $clinica = $user->clinica;

            // Enviar correo
            if ($expedienteType === 'reporte_fisio') {
                // Para fisioterapia, usar el archivo existente
                Mail::send('emails.expediente', [
                    'paciente' => $paciente,
                    'expediente' => $data,
                    'mensaje' => $message,
                    'tipoExpedienteNombre' => $tipoExpedienteNombre,
                    'clinica' => $clinica
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
                    'tipoExpedienteNombre' => $tipoExpedienteNombre,
                    'clinica' => $clinica
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
