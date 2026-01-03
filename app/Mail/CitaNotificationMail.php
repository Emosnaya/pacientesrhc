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

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
        $this->paciente = $cita->paciente;
    }

    public function build()
    {
        // Generar ICS
        $calendarService = new CalendarService();
        $icsContent = $calendarService->generateIcs($this->cita, 'create');
        
        return $this->view('emails.cita-patient-notification')
                    ->subject('Confirmación de Cita - ' . $this->paciente->nombre . ' ' . $this->paciente->apellidoPat . ' - CERCAP')
                    // Adjuntar ICS con MIME type correcto
                    ->attachData($icsContent, 'event.ics', [
                        'mime' => 'text/calendar; method=REQUEST; charset=UTF-8'
                    ]);
    }
}
