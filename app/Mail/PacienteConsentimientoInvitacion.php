<?php

namespace App\Mail;

use App\Models\Paciente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PacienteConsentimientoInvitacion extends BaseMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public Paciente $paciente,
        public string $clinicaNombre,
        public string $plainToken,
        public string $contexto = 'registro'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->contexto === 'nueva_vinculacion'
            ? 'Confirma el uso compartido de tu expediente — '.$this->clinicaNombre
            : 'Aceptación de aviso de privacidad y términos — '.$this->clinicaNombre;

        return new Envelope(
            subject: $subject,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(
                    config('mail.from.address', 'contacto@lynkamed.mx'),
                    config('app.name', 'LynkaMed'),
                ),
            ],
        );
    }

    public function content(): Content
    {
        $base = rtrim((string) config('app.frontend_url'), '/');
        $url = $base.'/aceptar-consentimiento?token='.urlencode($this->plainToken);

        return new Content(
            html: 'emails.paciente-consentimiento-invitacion',
            with: [
                'nombrePaciente' => trim(($this->paciente->nombre ?? '').' '.($this->paciente->apellidoPat ?? '')),
                'clinicaNombre' => $this->clinicaNombre,
                'urlAceptacion' => $url,
                'diasValidez' => config('legal.consentimiento_enlace_dias', 14),
                'urlAviso' => config('legal.url_aviso_privacidad'),
                'urlTerminos' => config('legal.url_terminos'),
                'contexto' => $this->contexto,
            ],
        );
    }
}
