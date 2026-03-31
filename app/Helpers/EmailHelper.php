<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * PNG optimizado para correo (no usar lynkamed-logo.png en HTML: hincha el mensaje y Gmail lo recorta).
     */
    public static function getLynkamedLogoPath(): ?string
    {
        $p = public_path('img/lynkamed-logo-email.png');

        return is_readable($p) ? $p : null;
    }

    public static function getLynkamedLogoPublicUrl(): ?string
    {
        if (! static::getLynkamedLogoPath()) {
            return null;
        }

        return url('img/lynkamed-logo-email.png');
    }

    /**
     * Último recurso: data URI del PNG pequeño (sigue siendo preferible embed o URL pública en correo).
     */
    public static function getLynkamedLogoBase64(): ?string
    {
        return static::getImageBase64('img/lynkamed-logo-email.png');
    }

    public static function clinicaLogoPath($clinica): ?string
    {
        if (! $clinica || empty($clinica->logo)) {
            return null;
        }
        $p = storage_path('app/public/'.$clinica->logo);

        return is_readable($p) ? $p : null;
    }

    /**
     * Imagen embebida (CID) para HTML de correo: compatible con Gmail y no infla el HTML.
     *
     * @param  \Illuminate\Mail\Message|\Symfony\Component\Mime\Email|null  $message
     */
    public static function embedMailImage($message, ?string $absolutePath): ?string
    {
        if (! $message || ! $absolutePath || ! is_readable($absolutePath)) {
            return null;
        }
        try {
            return $message->embed($absolutePath);
        } catch (\Throwable $e) {
            \Log::warning('embedMailImage: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Obtener imagen en formato base64 (PDFs, etc.; evitar logos grandes en correo HTML).
     */
    public static function getImageBase64($path)
    {
        try {
            $fullPath = public_path($path);

            if (! file_exists($fullPath)) {
                return null;
            }

            $imageData = file_get_contents($fullPath);
            $mimeType = mime_content_type($fullPath);

            return 'data:'.$mimeType.';base64,'.base64_encode($imageData);
        } catch (\Exception $e) {
            \Log::error('Error al convertir imagen a base64: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Logo de clínica en base64 (p. ej. vistas PDF).
     */
    public static function getClinicaLogoBase64($clinica)
    {
        try {
            if (! $clinica || ! $clinica->logo) {
                return null;
            }

            $logoPath = storage_path('app/public/'.$clinica->logo);

            if (! file_exists($logoPath)) {
                return null;
            }

            $imageData = file_get_contents($logoPath);
            $mimeType = mime_content_type($logoPath);

            return 'data:'.$mimeType.';base64,'.base64_encode($imageData);
        } catch (\Exception $e) {
            \Log::error('Error al convertir logo de clínica a base64: '.$e->getMessage());

            return null;
        }
    }
}
