<?php

namespace App\Mail;

use App\Models\Paciente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PacientePortalAccesoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Paciente $paciente,
        public string $accesoUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Accede a tu portal de paciente — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.paciente-portal-acceso',
            with: [
                'nombrePaciente' => trim(($this->paciente->nombre ?? '').' '.($this->paciente->apellidoPat ?? '')),
                'accesoUrl' => $this->accesoUrl,
            ],
        );
    }
}
