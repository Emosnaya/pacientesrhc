<?php

namespace App\Http\Controllers;

use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\EstratiAacvpr;
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
     * Quien firma el PDF. Solo profesionales de salud con cédula Y firma digital pueden firmar.
     * Por seguridad NUNCA se acepta doctor_firma_id del request.
     * 
     * Requisitos para firmar:
     * 1. Usuario autenticado
     * 2. Rol de profesional de salud (definido en config/roles.php -> roles_firmantes)
     * 3. Cédula profesional cargada
     * 4. Firma digital cargada
     * 
     * Si no cumple todos los requisitos → null (PDF sin firma).
     */
    private function getDoctorParaFirma(Request $request, $creadorUserId)
    {
        $usuarioActual = Auth::user();
        if (!$usuarioActual) {
            // Si no hay usuario autenticado, devolver creador solo para datos (sin firma)
            return null;
        }

        // Verificar los 3 requisitos para firmar:
        // 1. Debe ser rol firmante (profesional de salud)
        if (!$usuarioActual->isFirmante()) {
            return null;
        }

        // 2. Debe tener cédula profesional cargada
        if (empty($usuarioActual->cedula) || trim($usuarioActual->cedula) === '') {
            return null;
        }

        // 3. Debe tener firma digital cargada
        if (empty($usuarioActual->firma_digital) || trim($usuarioActual->firma_digital) === '') {
            return null;
        }

        // Cumple todos los requisitos: puede firmar con su propia firma
        return $usuarioActual;
    }

    /**
     * Resolver usuario para PDF: quien firma (puede ser null si es administrativo) y usuario para clinica/logo.
     */
    private function resolveUsuarioPdf(Request $request, $creadorUserId): array
    {
        $userFirma = $this->getDoctorParaFirma($request, $creadorUserId);
        
        // Cuenta portal del paciente (paciente_id): el PDF debe reflejar la clínica del profesional que creó el expediente
        $usuarioActual = Auth::user();
        if ($usuarioActual && $usuarioActual->paciente_id) {
            $user = User::with(['sucursal', 'clinica', 'clinicaActiva'])->find($creadorUserId);
        } elseif ($usuarioActual) {
            $user = $usuarioActual;
            if (!$user->relationLoaded('sucursal') && $user->sucursal_id) {
                $user->load('sucursal');
            }
        } else {
            $user = User::with('sucursal')->find($creadorUserId);
        }
        
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
    public function getClinicaLogoBase64($user, $targetHeight = 36)
    {
        try {
            // Validar que el usuario exista
            if (!$user) {
                return null;
            }

            $user->loadMissing(['clinicaActiva', 'clinica']);
            $clinica = $user->clinicaActiva ?? $user->clinica;
            $logoPath = null;
            
            if ($clinica && $clinica->logo) {
                $logoPath = public_path('storage/' . $clinica->logo);
                if (!file_exists($logoPath)) {
                    $logoPath = storage_path('app/public/' . $clinica->logo);
                }
            }

            if (!$logoPath || !file_exists($logoPath)) {
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
     * Obtener logo de universidad en base64
     */
    private function getUniversidadLogoBase64($user, $targetHeight = 36)
    {
        try {
            if (!$user || !$user->logo_universidad) {
                return null;
            }
            
            $logoPath = storage_path('app/public/' . $user->logo_universidad);
            
            // Si no existe el logo, retornar null
            if (!file_exists($logoPath)) {
                \Log::warning('Logo de universidad no encontrado: ' . $logoPath);
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
            
            \Log::info('Logo de universidad redimensionado exitosamente', [
                'original' => $originalWidth . 'x' . $originalHeight,
                'nuevo' => $newWidth . 'x' . $newHeight
            ]);
            
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            
        } catch (\Exception $e) {
            \Log::error('Error al procesar logo de universidad: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener la información completa de la clínica para las vistas
     */
    public function getClinicaInfo($user)
    {
        // Validar que el usuario exista
        if (!$user) {
            return (object)[
                'nombre' => 'Clínica Médica',
                'telefono' => '',
                'email' => '',
                'direccion' => '',
                'logo' => null,
                'logo_url' => null,
            ];
        }

        $user->loadMissing(['clinicaActiva', 'clinica', 'sucursal']);
        $clinica = $user->clinicaActiva ?? $user->clinica;
        
        if (!$clinica) {
            // Datos por defecto si no hay clínica
            return (object)[
                'nombre' => 'Clínica Médica',
                'telefono' => '',
                'email' => '',
                'direccion' => '',
                'logo' => null,
                'logo_url' => null,
            ];
        }
        
        // Si el usuario tiene una sucursal asignada, usar los datos de la sucursal
        if ($user->sucursal_id && $user->sucursal) {
            $sucursal = $user->sucursal;
            
            return (object)[
                'nombre' => $clinica->nombre,
                'telefono' => $sucursal->telefono ?? $clinica->telefono,
                'email' => $sucursal->email ?? $clinica->email,
                'direccion' => $sucursal->direccion ?? $clinica->direccion,
                'logo' => $clinica->logo,
                'logo_url' => $clinica->logo ? asset('storage/' . $clinica->logo) : null,
            ];
        }
        
        // Agregar logo_url al objeto clínica (solo si hay logo; sin imagen por defecto)
        $clinica->logo_url = $clinica->logo
            ? asset('storage/' . $clinica->logo)
            : null;
        
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

    public function estratiAacvprPdf(Request $request)
    {
        $data = EstratiAacvpr::find($request->id);
        $paciente = Paciente::find($data->paciente_id);
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        // Obtener información de la clínica
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);

        $firmaBase64 = $this->getFirmaBase64($userFirma);

        $pdf = Pdf::loadView('cardiaca.estrati_aacvpr', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
        return $pdf->stream('Estratificacion_AACVPR_EAPC.pdf'); 
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
        
        // Usar la misma lógica que los expedientes: usuario actual para firma y datos
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);
        if (!$user) {
            abort(404, 'No se pudo determinar el médico');
        }
        
        // Asegurar que la relación sucursal esté cargada
        if (!$user->relationLoaded('sucursal') && $user->sucursal_id) {
            $user->load('sucursal');
        }

        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);
        $universidadLogo = $this->getUniversidadLogoBase64($user, 44);
        
        // Obtener sucursal del usuario
        $sucursal = $user->sucursal;

        $firmaBase64 = $this->getFirmaBase64($userFirma);
        
        // Datos de e.firma si la receta está firmada electrónicamente
        $efirmaData = null;
        if ($data->firma_digital && $data->firmada_at) {
            // Obtener datos de la e.firma del usuario que firmó
            $efirma = \App\Models\Efirma::where('user_id', $data->user_id)
                ->where('tipo', 'personal')
                ->first();
            
            $efirmaData = [
                'firmada_at' => $data->firmada_at,
                'numero_serie' => $data->numero_serie_certificado,
                'rfc' => $efirma->rfc ?? null,
                'nombre_titular' => $efirma->nombre_titular ?? $user->nombre_completo,
                // Sello digital completo (requerido por COFEPRIS)
                'sello_digital' => $data->firma_digital,
                // Cadena original completa (requerido por COFEPRIS)
                'cadena_original' => $data->cadena_original,
            ];
        }

        $pdf = Pdf::loadView('receta', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica', 'universidadLogo', 'sucursal', 'efirmaData'));
        return $pdf->stream('Receta_Medica.pdf');
    }

    public function odontogramaPdf(Request $request)
    {
        $data = Odontograma::find($request->id);
        
        if (!$data) {
            return response()->json(['error' => 'Odontograma no encontrado'], 404);
        }

        $paciente = Paciente::find($data->paciente_id);
        
        [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id);

        if (!$user) {
            $user = User::with('sucursal')->find($data->user_id);
        }
        
        $clinica = $this->getClinicaInfo($user);
        $clinicaLogo = $this->getClinicaLogoBase64($user);
        
        // Obtener firma base64 usando el método centralizado
        $firmaBase64 = $this->getFirmaBase64($userFirma);

        // Decodificar los dientes (están guardados como JSON)
        $dientes = is_string($data->dientes) ? json_decode($data->dientes, true) : $data->dientes;

        $pdf = Pdf::loadView('dental.odontograma', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64', 'dientes'));
        return $pdf->stream('Odontograma_' . ($paciente->nombre ?? 'Paciente') . '_' . ($paciente->apellidoPat ?? '') . '.pdf');
    }

    /**
     * Enviar expediente por correo
     */
    public function sendExpedienteByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expediente_id' => 'required|integer',
            'expediente_type' => 'required|string|in:esfuerzo,estratificacion,estrati_aacvpr,clinico,reporte_final,reporte_psico,reporte_nutri,reporte_fisio,expediente_pulmonar,cualidad_fisica,reporte_final_pulmonar,historia_dental,odontograma',
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
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id ?? $paciente->user_id);
                    $pdfFileName = 'Estratificacion.pdf';
                    break;
                case 'estrati_aacvpr':
                    $data = EstratiAacvpr::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id ?? $paciente->user_id);
                    $pdfFileName = 'Estratificacion_AACVPR_EAPC.pdf';
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
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id ?? $paciente->user_id);
                    $pdfFileName = 'Expediente_Pulmonar.pdf';
                    break;
                case 'cualidad_fisica':
                    $data = CualidadFisica::find($expedienteId);
                    $paciente = Paciente::find($data->paciente_id);
                    [$user, $userFirma] = $this->resolveUsuarioPdf($request, $data->user_id ?? $paciente->user_id);
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

            if (!$data || !$paciente) {
                return response()->json(['error' => 'Expediente no encontrado'], 404);
            }

            if (!$user && Auth::check()) {
                $authUser = Auth::user();
                if ($authUser && empty($authUser->paciente_id)) {
                    $authUser->loadMissing(['sucursal', 'clinica', 'clinicaActiva']);
                    $user = $authUser;
                }
            }

            if (!$user) {
                return response()->json(['error' => 'No se pudo determinar el usuario para el envío'], 404);
            }

            $clinica = $this->getClinicaInfo($user);
            $clinicaLogo = $this->getClinicaLogoBase64($user);
            $firmaBase64 = $this->getFirmaBase64($userFirma);

            // Generar asunto automático con tipo de expediente y nombre del paciente
            $tipoExpedienteNombres = [
                'esfuerzo' => 'Prueba de Esfuerzo',
                'estratificacion' => 'Estratificación',
                'estrati_aacvpr' => 'Estratificación AACVPR/EAPC',
                'clinico' => 'Reporte Clínico',
                'reporte_final' => 'Reporte Final',
                'reporte_psico' => 'Reporte Psicológico',
                'reporte_nutri' => 'Reporte Nutricional',
                'reporte_fisio' => 'Reporte de Fisioterapia',
                'expediente_pulmonar' => 'Expediente Pulmonar',
                'cualidad_fisica' => 'Cualidades Físicas No Aeróbicas',
                'reporte_final_pulmonar' => 'Reporte Final Pulmonar',
                'historia_dental' => 'Historia Clínica Dental',
                'odontograma' => 'Odontograma'
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

            // Generar PDF según el tipo (mismas variables que los métodos stream/descarga)
            $pdf = null;

            switch ($expedienteType) {
                case 'esfuerzo':
                    $pdf = Pdf::loadView('cardiaca.esfuerzo', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
                    break;
                case 'estratificacion':
                    $pdf = Pdf::loadView('cardiaca.estrati', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
                    break;
                case 'estrati_aacvpr':
                    $pdf = Pdf::loadView('cardiaca.estrati_aacvpr', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
                    break;
                case 'clinico':
                    $pdf = Pdf::loadView('cardiaca.clinico', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64'));
                    break;
                case 'reporte_final':
                    $esfuerzoUno = Esfuerzo::find($data->pe_1);
                    $esfuerzoDos = Esfuerzo::find($data->pe_2);
                    $estrati = Estratificacion::where('paciente_id', $paciente->id)->get();

                    $pdf = Pdf::loadView('cardiaca.reporte', compact('data', 'paciente', 'user', 'estrati', 'esfuerzoUno', 'esfuerzoDos', 'firmaBase64', 'clinicaLogo', 'clinica'));
                    break;
                case 'reporte_psico':
                    $pdf = Pdf::loadView('psico', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica'));
                    break;
                case 'reporte_nutri':
                    $pdf = Pdf::loadView('nutri', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica'));
                    break;
                case 'reporte_fisio':
                    // Mismo criterio que ReporteFisioController::imprimir: adjunto subido a storage público
                    $pdfContent = null;
                    $resolvedPath = null;
                    if (! empty($data->archivo)) {
                        $candidates = [
                            storage_path('app/public/' . $data->archivo),
                            public_path('storage/' . $data->archivo),
                        ];
                        foreach ($candidates as $tryPath) {
                            if (is_readable($tryPath)) {
                                $resolvedPath = $tryPath;
                                $pdfContent = file_get_contents($tryPath);
                                break;
                            }
                        }
                    }
                    $debugInfo = [
                        'archivo_field' => $data->archivo ?? null,
                        'resolved_path' => $resolvedPath,
                    ];
                    if ($pdfContent === false || $pdfContent === null) {
                        return response()->json([
                            'error' => 'Archivo de fisioterapia no encontrado. Los reportes tipo 7 solo se envían si tienen un archivo adjunto (PDF/imagen/doc) subido; el texto en el formulario no genera PDF automático.',
                            'debug' => $debugInfo,
                        ], 404);
                    }
                    break;
                case 'expediente_pulmonar':
                    $pdf = Pdf::loadView('pulmonar.pulmonar', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
                    break;
                case 'cualidad_fisica':
                    $pdf = Pdf::loadView('cardiaca.cualidadesfisicas', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
                    break;
                case 'reporte_final_pulmonar':
                    $pdf = Pdf::loadView('pulmonar.reportefinalpulmonar', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
                    break;
                case 'historia_dental':
                    $pdf = Pdf::loadView('dental.historia_dental', compact('data', 'paciente', 'user', 'firmaBase64', 'clinicaLogo', 'clinica'));
                    break;
                case 'odontograma':
                    $dientes = is_string($data->dientes) ? json_decode($data->dientes, true) : $data->dientes;
                    $pdf = Pdf::loadView('dental.odontograma', compact('data', 'paciente', 'user', 'clinicaLogo', 'clinica', 'firmaBase64', 'dientes'));
                    break;
            }

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
