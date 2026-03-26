<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PacientePortalOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombre,
        public string $code
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de verificación — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.paciente-portal-otp',
            with: [
                'nombre' => $this->nombre,
                'code' => $this->code,
            ],
        );
    }
}
