<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Cita;
use App\Models\Clinica;
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
        $this->cita->load(['paciente', 'user.clinica', 'sucursal.clinica', 'clinica', 'admin']);

        $paciente = $this->cita->paciente;
        $sucursal = $this->cita->sucursal;
        $clinica = $this->cita->clinica
            ?? optional($this->cita->user)->clinica
            ?? optional($this->cita->sucursal)->clinica
            ?? optional($this->cita->admin)->clinica;

        $tipoClinica = optional($clinica)->tipo_clinica
            ?? ($sucursal && $sucursal->clinica ? $sucursal->clinica->tipo_clinica : null)
            ?? 'rehabilitacion_cardiopulmonar';
        $clinicaDisplayName = $sucursal->nombre
            ?? (optional($clinica)->nombre)
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

        $greeting = $isPatient
            ? 'Hola '.($paciente->nombre ?? 'Paciente').','
            : 'Hola '.$this->doctorGreeting($notifiable).',';

        $introLine = $this->getIntroLine($isPatient);

        [$headerTitle, $headerSubtitle] = $this->getHeaderLines(
            $isPatient,
            $clinicaDisplayName,
            $fechaFormateada,
            $horaFormateada,
            $paciente
        );

        // URL del calendario frontend
        $calendarUrl = env('FRONTEND_URL', 'http://localhost:3000').'/calendar';

        $clinicaLogoModel = $this->cita->clinica_id
            ? Clinica::query()->find($this->cita->clinica_id)
            : null;
        if (! $clinicaLogoModel) {
            $clinicaLogoModel = $clinica;
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.cita-invitation', [
                'cita' => $this->cita,
                'paciente' => $paciente,
                'clinica' => $clinica,
                'sucursal' => $sucursal,
                'clinicaLogoModel' => $clinicaLogoModel,
                'clinicaDisplayName' => $clinicaDisplayName,
                'fechaFormateada' => $fechaFormateada,
                'horaFormateada' => $horaFormateada,
                'action' => $this->action,
                'isPatient' => $isPatient,
                'subject' => $subject,
                'headerTitle' => $headerTitle,
                'headerSubtitle' => $headerSubtitle,
                'greeting' => $greeting,
                'introLine' => $introLine,
                'calendarUrl' => $calendarUrl,
                'estadoLabel' => $this->estadoLabel($this->cita->estado),
                'doctorAsignadoNombre' => $this->userFullName($this->cita->user),
                'adminNombre' => $this->userFullName($this->cita->admin),
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
                ? 'Actualizamos los datos de tu cita. Verifica fecha, hora y ubicación a continuación.'
                : 'Se modificaron datos de una cita. Revisa los detalles y sincroniza tu calendario con el archivo adjunto si lo usas.',
            'cancel' => $isPatient
                ? 'Te informamos que esta cita ya no está vigente. Si necesitas otra fecha, puedes contactarnos.'
                : 'Esta cita fue cancelada en el sistema. Si el archivo adjunto (.ics) estaba en tu agenda, elimínalo o actualízalo.',
            default => $isPatient
                ? 'Tu cita quedó registrada. Conserva este correo; incluye la ubicación y un archivo para agregar el evento a tu calendario.'
                : 'Hay una nueva cita asignada en Lynkamed. Los datos clave están abajo; el adjunto .ics sirve para Outlook, Google Calendar o Apple Calendar.',
        };
    }

    private function getHeaderLines(
        bool $isPatient,
        string $clinicaDisplayName,
        string $fechaFormateada,
        string $horaFormateada,
        $paciente
    ): array {
        $meta = $clinicaDisplayName.' · '.$fechaFormateada.' · '.$horaFormateada;

        $title = match ($this->action) {
            'update' => $isPatient ? 'Cita actualizada' : 'Cita modificada',
            'cancel' => 'Cita cancelada',
            default => $isPatient ? 'Tu cita está confirmada' : 'Nueva cita en tu agenda',
        };

        $subtitle = $isPatient
            ? $meta
            : trim(($paciente->nombre ?? '').' '.($paciente->apellidoPat ?? '').' '.($paciente->apellidoMat ?? '')).' · '.$fechaFormateada.' · '.$horaFormateada;

        return [$title, $subtitle];
    }

    private function doctorGreeting($notifiable): string
    {
        if ($notifiable instanceof \App\Models\User && ($notifiable->nombre ?? null)) {
            return trim($notifiable->nombre.' '.($notifiable->apellidoPat ?? ''));
        }

        return 'Doctor/a';
    }

    private function userFullName(?\App\Models\User $user): ?string
    {
        if (! $user) {
            return null;
        }
        $n = trim(($user->nombre ?? '').' '.($user->apellidoPat ?? '').' '.($user->apellidoMat ?? ''));

        return $n !== '' ? $n : null;
    }

    private function estadoLabel(?string $estado): string
    {
        return match ($estado) {
            'confirmada' => 'Confirmada',
            'pendiente' => 'Pendiente de confirmación',
            'cancelada' => 'Cancelada',
            'completada' => 'Completada',
            default => $estado ? ucfirst($estado) : '—',
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
