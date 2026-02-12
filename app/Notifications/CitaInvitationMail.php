<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Cita;
use App\Services\CalendarService;
use Carbon\Carbon;

class CitaInvitationMail extends Notification
{
    use Queueable;

    protected Cita $cita;
    protected string $action;
    protected CalendarService $calendarService;

    /**
     * @param Cita $cita
     * @param string $action create|update|cancel
     */
    public function __construct(Cita $cita, string $action = 'create')
    {
        $this->cita = $cita;
        $this->action = $action;
        $this->calendarService = new CalendarService();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->cita->load(['paciente', 'user.clinica', 'sucursal']);

        $paciente = $this->cita->paciente;
        $clinica  = optional($this->cita->user)->clinica;
        $sucursal = $this->cita->sucursal;
        $tipoClinica = $clinica->tipo_clinica
            ?? ($sucursal && $sucursal->clinica ? $sucursal->clinica->tipo_clinica : null)
            ?? 'rehabilitacion_cardiopulmonar';
        $clinicaDisplayName = $sucursal->nombre
            ?? ($clinica ? $clinica->nombre : null)
            ?? (config('clinica_tipos.tipos.' . $tipoClinica . '.nombre'))
            ?? 'Clínica Médica';

        $fechaFormateada = Carbon::parse($this->cita->fecha)
            ->locale('es')
            ->isoFormat('dddd D [de] MMMM [de] YYYY');

        $horaFormateada = Carbon::parse($this->cita->hora)->format('H:i');

        $isPatient = $notifiable instanceof \App\Models\Paciente;

        $subject = $this->getSubject($fechaFormateada, $horaFormateada, $isPatient, $clinicaDisplayName);

        // Generar ICS correcto
        $icsContent = $this->calendarService->generateIcs($this->cita, $this->action);

        // Determinar greeting e introLine según el tipo de destinatario
        $greeting = $isPatient 
            ? "Hola {$paciente->nombre}," 
            : "Hola Doctor/a,";

        $introLine = $this->getIntroLine($isPatient);
        
        // URL del calendario frontend
        $calendarUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/calendar';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.cita-invitation', [
                'cita' => $this->cita,
                'paciente' => $paciente,
                'clinica' => $clinica,
                'sucursal' => $sucursal,
                'clinicaDisplayName' => $clinicaDisplayName,
                'fechaFormateada' => $fechaFormateada,
                'horaFormateada' => $horaFormateada,
                'action' => $this->action,
                'isPatient' => $isPatient,
                'subject' => $subject,
                'greeting' => $greeting,
                'introLine' => $introLine,
                'calendarUrl' => $calendarUrl,
            ])
            ->attachData(
                $icsContent,
                'cita.ics',
                [
                    'mime' => 'text/calendar; method=' . ($this->action === 'cancel' ? 'CANCEL' : 'REQUEST') . '; charset=UTF-8',
                ]
            );
    }

    private function getIntroLine(bool $isPatient): string
    {
        return match ($this->action) {
            'update' => $isPatient 
                ? 'Tu cita ha sido actualizada. A continuación encontrarás los nuevos detalles.' 
                : 'Se ha actualizado una cita. Revisa los detalles a continuación.',
            'cancel' => $isPatient 
                ? 'Lamentamos informarte que tu cita ha sido cancelada.' 
                : 'Una cita ha sido cancelada. Revisa los detalles a continuación.',
            default => $isPatient 
                ? 'Te confirmamos tu próxima cita. Te esperamos en la fecha indicada.' 
                : 'Se ha programado una nueva cita. Revisa los detalles a continuación.',
        };
    }

    private function getSubject(string $fecha, string $hora, bool $isPatient, string $displayName = ''): string
    {
        $prefix = $displayName ? ($displayName . ' - ') : '';

        return match ($this->action) {
            'update' => "{$prefix}🔄 Cita actualizada - {$fecha} {$hora}",
            'cancel' => "{$prefix}❌ Cita cancelada - {$fecha} {$hora}",
            default  => "{$prefix}📅 Nueva cita - {$fecha} {$hora}",
        };
    }
}
