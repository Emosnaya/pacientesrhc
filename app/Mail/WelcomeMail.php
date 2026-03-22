<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Clinica;
use App\Models\SubscriptionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $consultorio;
    public $plan;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Clinica $consultorio, SubscriptionPlan $plan)
    {
        $this->user = $user;
        $this->consultorio = $consultorio;
        $this->plan = $plan;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('¡Bienvenido a ' . config('app.name', 'PacientesRHC') . '!')
                    ->view('emails.welcome');
    }
}
