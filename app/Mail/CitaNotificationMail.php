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
    public $sucursal;
    public $clinicaDisplayName;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
        $this->paciente = $cita->paciente;
        // Obtener clínica del usuario asignado a la cita
        $this->clinica = $cita->user ? $cita->user->clinica : null;
        $this->sucursal = $cita->sucursal;
        $tipoClinica = $this->clinica->tipo_clinica
            ?? ($this->sucursal && $this->sucursal->clinica ? $this->sucursal->clinica->tipo_clinica : null)
            ?? 'rehabilitacion_cardiopulmonar';
        $this->clinicaDisplayName = $this->sucursal->nombre
            ?? ($this->clinica ? $this->clinica->nombre : null)
            ?? (config('clinica_tipos.tipos.' . $tipoClinica . '.nombre'))
            ?? 'Clínica Médica';
    }

    public function build()
    {
        // Generar ICS
        $calendarService = new CalendarService();
        $icsContent = $calendarService->generateIcs($this->cita, 'create');
        
        $clinicaNombre = $this->clinicaDisplayName;
        
        return $this->view('emails.cita-patient-notification')
                    ->with([
                        'clinica' => $this->clinica,
                        'sucursal' => $this->sucursal,
                        'clinicaDisplayName' => $this->clinicaDisplayName
                    ])
                    ->subject('Confirmación de Cita - ' . $this->paciente->nombre . ' ' . $this->paciente->apellidoPat . ' - ' . $clinicaNombre)
                    // Adjuntar ICS con MIME type correcto
                    ->attachData($icsContent, 'event.ics', [
                        'mime' => 'text/calendar; method=REQUEST; charset=UTF-8'
                    ]);
    }
}
