<?php

namespace App\Mail;

use App\Models\Paciente;
use App\Models\Clinica;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PacienteOtpMail extends BaseMail
{
    use Queueable, SerializesModels;

    public Paciente $paciente;
    public string $otpCode;
    public Clinica $clinica;

    public function __construct(Paciente $paciente, string $otpCode, Clinica $clinica)
    {
        $this->paciente = $paciente;
        $this->otpCode = $otpCode;
        $this->clinica = $clinica;
    }

    public function envelope(): Envelope
    {
        $clinica = trim((string) ($this->clinica->nombre ?? ''));

        return new Envelope(
            subject: $clinica !== ''
                ? 'Código de verificación — '.$clinica
                : 'Código de verificación',
            replyTo: [
                new Address(
                    config('mail.from.address', 'contacto@lynkamed.mx'),
                    config('app.name', 'LynkaMed'),
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.paciente-otp',
        );
    }
}
