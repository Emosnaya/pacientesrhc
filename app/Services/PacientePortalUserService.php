<?php

namespace App\Services;

use App\Mail\PacientePortalAccesoMail;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PacientePortalUserService
{
    /**
     * Tras aceptar consentimiento: cuenta de portal sin contraseña y correo de invitación.
     *
     * @return array{portal_email: string, portal_email_detalle?: string}
     *         portal_email: sent | failed | skipped
     */
    public function provisionAfterConsent(Paciente $paciente): array
    {
        $email = $paciente->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'portal_email' => 'skipped',
                'portal_email_detalle' => 'sin_email_valido',
            ];
        }

        $emailNorm = strtolower(trim($email));

        $existing = User::query()->where('email', $emailNorm)->first();

        if ($existing) {
            if ($existing->paciente_id === null && ($existing->clinica_id || $existing->clinica_activa_id)) {
                \Log::warning('PacientePortal: email ya usado por cuenta de personal', [
                    'paciente_id' => $paciente->id,
                    'user_id' => $existing->id,
                ]);

                return [
                    'portal_email' => 'skipped',
                    'portal_email_detalle' => 'email_usado_por_personal',
                ];
            }

            if ($existing->paciente_id !== null && (int) $existing->paciente_id !== (int) $paciente->id) {
                \Log::warning('PacientePortal: email asociado a otro expediente', [
                    'paciente_id' => $paciente->id,
                    'user_id' => $existing->id,
                ]);

                return [
                    'portal_email' => 'skipped',
                    'portal_email_detalle' => 'email_otro_expediente',
                ];
            }

            if ((int) $existing->paciente_id === (int) $paciente->id) {
                $existing->nombre = $paciente->nombre ?? $existing->nombre;
                $existing->apellidoPat = $paciente->apellidoPat ?? $existing->apellidoPat;
                $existing->apellidoMat = $paciente->apellidoMat ?? $existing->apellidoMat;
                $existing->rol = 'paciente';
                $existing->save();

                return $this->resultadoEnvioCorreo($paciente, $this->sendPortalEmail($paciente));
            }
        }

        $user = new User;
        $user->nombre = $paciente->nombre ?? '';
        $user->apellidoPat = $paciente->apellidoPat ?? '';
        $user->apellidoMat = $paciente->apellidoMat ?? '';
        $user->email = $emailNorm;
        $user->cedula = null;
        $user->paciente_id = $paciente->id;
        $user->rol = 'paciente';
        $user->password = null;
        $user->password_set_at = null;
        $user->email_verified = true;
        $user->clinica_id = null;
        $user->clinica_activa_id = null;
        $user->sucursal_id = null;
        $user->isAdmin = false;
        $user->isSuperAdmin = false;
        $user->save();

        return $this->resultadoEnvioCorreo($paciente, $this->sendPortalEmail($paciente));
    }

    /**
     * @return array{portal_email: string, portal_email_detalle?: string}
     */
    private function resultadoEnvioCorreo(Paciente $paciente, bool $enviado): array
    {
        if ($enviado) {
            return ['portal_email' => 'sent'];
        }

        return [
            'portal_email' => 'failed',
            'portal_email_detalle' => 'revisa_logs_y_mail_env',
        ];
    }

    public function sendPortalEmail(Paciente $paciente): bool
    {
        $email = $paciente->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $url = rtrim((string) config('app.frontend_url'), '/').'/portal/acceso';

        try {
            Mail::to($email)->send(new PacientePortalAccesoMail(
                paciente: $paciente,
                accesoUrl: $url
            ));
            \Log::info('PacientePortal: correo de acceso al portal enviado', [
                'paciente_id' => $paciente->id,
                'to' => $email,
                'mailer' => config('mail.default'),
            ]);

            return true;
        } catch (\Throwable $e) {
            \Log::error('PacientePortal: email de acceso fallido', [
                'paciente_id' => $paciente->id,
                'to' => $email,
                'mailer' => config('mail.default'),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
