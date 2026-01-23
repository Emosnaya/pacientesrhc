<?php

namespace App\Mail;

use App\Models\Cita;
use App\Services\CalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CitaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cita;
    public $paciente;
    public $clinica;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
        $this->paciente = $cita->paciente;
        // Obtener clínica del usuario asignado a la cita
        $this->clinica = $cita->user ? $cita->user->clinica : null;
    }

    public function build()
    {
        // Generar ICS
        $calendarService = new CalendarService();
        $icsContent = $calendarService->generateIcs($this->cita, 'create');
        
        $clinicaNombre = $this->clinica ? $this->clinica->nombre : 'Clínica Médica';
        
        return $this->view('emails.cita-patient-notification')
                    ->with(['clinica' => $this->clinica])
                    ->subject('Confirmación de Cita - ' . $this->paciente->nombre . ' ' . $this->paciente->apellidoPat . ' - ' . $clinicaNombre)
                    // Adjuntar ICS con MIME type correcto
                    ->attachData($icsContent, 'event.ics', [
                        'mime' => 'text/calendar; method=REQUEST; charset=UTF-8'
                    ]);
    }
}
