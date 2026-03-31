<?php

namespace App\Services;

use App\Mail\PacienteConsentimientoInvitacion;
use App\Models\Clinica;
use App\Models\Paciente;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PacienteConsentimientoService
{
    public const CONTEXTO_REGISTRO = 'registro';

    public const CONTEXTO_NUEVA_VINCULACION = 'nueva_vinculacion';

    /**
     * Genera token, guarda hash y envía correo si el paciente tiene email.
     *
     * @param  string  $contexto  self::CONTEXTO_* (afecta asunto y cuerpo del correo / pantalla de aceptación).
     */
    public function enviarInvitacion(Paciente $paciente, ?Clinica $clinica = null, string $contexto = self::CONTEXTO_REGISTRO): bool
    {
        $email = $paciente->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $contexto = in_array($contexto, [self::CONTEXTO_REGISTRO, self::CONTEXTO_NUEVA_VINCULACION], true)
            ? $contexto
            : self::CONTEXTO_REGISTRO;

        $plain = Str::random(64);
        $paciente->consentimiento_token_hash = hash('sha256', $plain);
        $paciente->consentimiento_token_expires_at = now()->addDays(
            (int) config('legal.consentimiento_enlace_dias', 14)
        );
        $paciente->consentimiento_email_enviado_at = now();
        $paciente->consentimiento_invitacion_contexto = $contexto;
        $paciente->save();

        $clinica = $clinica ?? $paciente->clinica;
        $nombreClinica = $clinica?->nombre ?? config('app.name');

        try {
            Mail::to($email)->send(new PacienteConsentimientoInvitacion(
                paciente: $paciente,
                clinicaNombre: $nombreClinica,
                plainToken: $plain,
                contexto: $contexto
            ));
        } catch (\Throwable $e) {
            \Log::error('PacienteConsentimiento: envío de correo fallido', [
                'paciente_id' => $paciente->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Marca aceptación de aviso y términos; invalida el token.
     */
    public function aceptarConToken(string $plainToken): array
    {
        $hash = hash('sha256', $plainToken);

        $paciente = Paciente::query()
            ->where('consentimiento_token_hash', $hash)
            ->where(function ($q) {
                $q->whereNull('consentimiento_token_expires_at')
                    ->orWhere('consentimiento_token_expires_at', '>', now());
            })
            ->first();

        if (! $paciente) {
            return ['ok' => false, 'message' => 'Enlace inválido o vencido.'];
        }

        if ($paciente->aviso_privacidad_aceptado_at) {
            $this->limpiarToken($paciente);

            return [
                'ok' => true,
                'message' => 'Tu consentimiento ya estaba registrado.',
                'paciente_id' => $paciente->id,
                'portal_email' => 'skipped',
                'portal_email_detalle' => 'ya_aceptado_sin_reenvio',
            ];
        }

        $vAviso = (string) config('legal.version_aviso_privacidad', '1');
        $vTerm = (string) config('legal.version_terminos', '1');
        $paciente->version_aviso = 'aviso:'.$vAviso.'|terminos:'.$vTerm;
        $paciente->aviso_privacidad_aceptado_at = now();
        $this->limpiarToken($paciente);
        $paciente->save();

        $portal = app(PacientePortalUserService::class)->provisionAfterConsent($paciente->fresh());

        return array_merge([
            'ok' => true,
            'message' => 'Gracias. Tu aceptación del aviso de privacidad y términos ha quedado registrada.',
            'paciente_id' => $paciente->id,
        ], $portal);
    }

    public function verificarToken(string $plainToken): ?Paciente
    {
        $hash = hash('sha256', $plainToken);

        return Paciente::query()
            ->where('consentimiento_token_hash', $hash)
            ->where(function ($q) {
                $q->whereNull('consentimiento_token_expires_at')
                    ->orWhere('consentimiento_token_expires_at', '>', now());
            })
            ->first();
    }

    private function limpiarToken(Paciente $paciente): void
    {
        $paciente->consentimiento_token_hash = null;
        $paciente->consentimiento_token_expires_at = null;
        $paciente->consentimiento_invitacion_contexto = null;
    }
}
