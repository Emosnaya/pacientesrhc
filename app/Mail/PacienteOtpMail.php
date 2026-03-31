<?php

namespace App\Mail;

use App\Models\Paciente;
use App\Models\Clinica;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PacienteOtpMail extends Mailable
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
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.paciente-otp',
        );
    }
}
