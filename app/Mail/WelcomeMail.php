<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Clinica;
use App\Models\SubscriptionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends BaseMail
{
    use Queueable, SerializesModels;

    public User $user;
    public Clinica $consultorio;
    public SubscriptionPlan $plan;

    public function __construct(User $user, Clinica $consultorio, SubscriptionPlan $plan)
    {
        $this->user        = $user;
        $this->consultorio = $consultorio;
        $this->plan        = $plan;
    }

    public function envelope(): Envelope
    {
        $appName = config('app.name', 'LynkaMed');

        return new Envelope(
            subject: '¡Bienvenido a '.$appName.'!',
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
            html: 'emails.welcome',
        );
    }
}
