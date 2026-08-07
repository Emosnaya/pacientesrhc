<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\CitaEvento;
use App\Models\Clinica;
use App\Models\PacienteNotificacion;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CitaSolicitudService
{
    public function registrarEvento(
        Cita $cita,
        string $tipo,
        string $actor = 'sistema',
        ?int $actorUserId = null,
        ?string $mensaje = null,
        ?array $meta = null
    ): CitaEvento {
        $evento = CitaEvento::create([
            'cita_id' => $cita->id,
            'tipo' => $tipo,
            'actor' => $actor,
            'actor_user_id' => $actorUserId,
            'mensaje' => $mensaje,
            'meta' => $meta,
        ]);

        $this->notificarPacienteEventoCita($cita, $tipo, $mensaje);

        return $evento;
    }

    private function notificarPacienteEventoCita(Cita $cita, string $tipo, ?string $mensaje = null): void
    {
        if (! $cita->paciente_id) {
            return;
        }

        $map = [
            'solicitado' => ['Solicitud de cita enviada', 'La clínica recibirá tu solicitud y te confirmará o contactará.'],
            'contactado' => ['La clínica te contactó', 'Revisa tu chat o WhatsApp; la clínica se puso en contacto por tu cita.'],
            'confirmado' => ['Cita confirmada', 'Tu cita fue confirmada. Revísala en Mis citas.'],
            'agendada' => ['Nueva cita agendada', $mensaje ?: 'Tu clínica te agendó una cita. Revísala en Mis citas.'],
            'pendiente_confirmacion' => ['Confirma tu asistencia', $mensaje ?: 'Tu clínica te agendó una cita. Confirma tu asistencia en Mis citas.'],
            'modificado' => ['Cita modificada', 'La clínica actualizó el horario o detalles de tu cita.'],
            'cancelado' => ['Cita cancelada', $mensaje ?: 'Tu cita fue cancelada.'],
        ];

        if (! isset($map[$tipo])) {
            return;
        }

        [$titulo, $cuerpo] = $map[$tipo];

        try {
            PacienteNotificacion::notify(
                (int) $cita->paciente_id,
                'cita_'.$tipo,
                $titulo,
                $cuerpo,
                ['cita_id' => $cita->id, 'clinica_id' => $cita->clinica_id]
            );
        } catch (\Throwable $e) {
            Log::warning('No se pudo crear notificación de cita para paciente', [
                'cita_id' => $cita->id,
                'tipo' => $tipo,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Avisa a admins/propietario de la clínica sobre una solicitud del portal.
     */
    public function notificarClinicaNuevaSolicitud(Cita $cita): void
    {
        $cita->loadMissing(['paciente', 'clinica', 'sucursal']);
        $clinica = $cita->clinica;
        if (! $clinica) {
            return;
        }

        $emails = User::query()
            ->where('clinica_id', $clinica->id)
            ->where(function ($q) {
                $q->where('isAdmin', 1)->orWhere('isSuperAdmin', 1);
            })
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        if ($clinica->email) {
            $emails = $emails->push($clinica->email)->unique()->values();
        }

        if ($emails->isEmpty()) {
            return;
        }

        $paciente = $cita->paciente;
        $pacienteNombre = $paciente
            ? trim(($paciente->nombre ?? '').' '.($paciente->apellidoPat ?? '').' '.($paciente->apellidoMat ?? ''))
            : 'Paciente';
        $fecha = optional($cita->fecha)->format('d/m/Y');
        $hora = $cita->hora instanceof \DateTimeInterface
            ? $cita->hora->format('H:i')
            : substr((string) $cita->hora, 0, 5);
        $especialidad = $cita->especialidad_solicitada
            ? str_replace('_', ' ', $cita->especialidad_solicitada)
            : 'General';

        $subject = "Nueva solicitud de cita · {$clinica->nombre}";
        $body = "Hola,\n\n"
            ."{$pacienteNombre} solicitó una cita desde la app LynkaMed.\n\n"
            ."Fecha: {$fecha}\n"
            ."Hora: {$hora}\n"
            ."Especialidad: {$especialidad}\n"
            .'Sucursal: '.($cita->sucursal?->nombre ?: 'Principal')."\n"
            .'Teléfono: '.($paciente?->telefono ?: 'No registrado')."\n"
            .'Email: '.($paciente?->email ?: 'No registrado')."\n\n"
            ."Entra a Agenda para confirmar, contactar o modificar el horario.\n\n"
            ."LynkaMed";

        foreach ($emails as $email) {
            try {
                Mail::raw($body, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::warning('No se pudo notificar solicitud de cita a clínica', [
                    'email' => $email,
                    'cita_id' => $cita->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function contarSolicitudesPendientes(int $clinicaId, ?int $sucursalId = null): int
    {
        $query = Cita::query()
            ->where('clinica_id', $clinicaId)
            ->where('estado', 'pendiente')
            ->where(function ($q) {
                $q->where('requiere_confirmacion', true)
                    ->orWhere(function ($inner) {
                        $inner->whereNull('user_id')->where('origen', 'portal');
                    });
            });

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        return $query->count();
    }
}
