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
        $brand = trim((string) config('mail.from.name', ''));
        $skip = ['', 'Example', 'Laravel'];
        $suffix = ! in_array($brand, $skip, true) ? ' — '.$brand : '';

        return new Envelope(
            subject: 'Accede a tu portal de paciente'.$suffix,
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
