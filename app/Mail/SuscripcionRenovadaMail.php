<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Clinica;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuscripcionRenovadaMail extends BaseMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public User    $user,
        public Clinica $clinica,
        public string  $billingCycle,   // 'mensual' | 'anual'
        public float   $monto,
        public string  $nuevaFechaVencimiento, // Y-m-d
        public ?string $stripeSessionId = null,
    ) {}

    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Lynkamed');

        return new Envelope(
            subject: '✅ Tu suscripción ha sido renovada — ' . $this->clinica->nombre,
            replyTo: [
                new Address(
                    config('mail.from.address', 'contacto@lynkamed.mx'),
                    $appName,
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.suscripcion-renovada',
        );
    }
}
