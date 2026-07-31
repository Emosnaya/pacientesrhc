<?php

namespace App\Services;

class PhoneHashService
{
    /**
     * Normaliza a dígitos MX (+52XXXXXXXXXX) o null si no es válido.
     */
    public function normalize(?string $telefono): ?string
    {
        if (! $telefono) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $telefono);
        if (! $digits) {
            return null;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '52')) {
            return '+' . $digits;
        }

        if (strlen($digits) === 10) {
            return '+52' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+52' . substr($digits, 1);
        }

        return null;
    }

    /**
     * Hash HMAC estable para buscar teléfonos cifrados.
     */
    public function hash(?string $telefono): ?string
    {
        $normalized = $this->normalize($telefono);
        if (! $normalized) {
            return null;
        }

        $key = config('services.search.index_key')
            ?: config('app.key')
            ?: 'dev-search-key';

        return hash_hmac('sha256', $normalized, $key);
    }

    /**
     * Extrae últimos 10 dígitos de un From de Twilio (whatsapp:+52...).
     */
    public function fromTwilio(?string $from): ?string
    {
        if (! $from) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', str_replace('whatsapp:', '', $from));
        if (! $digits) {
            return null;
        }

        if (strlen($digits) >= 10) {
            return '+52' . substr($digits, -10);
        }

        return $this->normalize($digits);
    }
}
