<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Enum\EventStatus;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class CalendarService
{
    /**
     * Genera el archivo ICS
     */
    public function generateIcs(Cita $cita, string $action = 'create'): string
    {
        $cita->load(['paciente', 'user', 'user.clinica']);

        $event = $this->createEvent($cita, $action);

        $calendar = new Calendar([$event]);
        $calendar->setProductIdentifier('-//CERCAP//Appointments//ES');

        $factory = new CalendarFactory();
        $component = $factory->createCalendar($calendar);

        return $component->__toString();
    }

    /**
     * Crea el evento
     */
    private function createEvent(Cita $cita, string $action): Event
    {
        $paciente = $cita->paciente;
        $doctor   = $cita->user ?? $paciente->user;

        // Fecha y hora - extraer solo fecha y solo hora
        $fechaSolo = Carbon::parse($cita->fecha)->format('Y-m-d');
        $horaSolo = Carbon::parse($cita->hora)->format('H:i:s');
        $inicio = Carbon::parse("{$fechaSolo} {$horaSolo}")->utc();
        $fin    = $inicio->copy()->addHour();

        // Crear evento con UID único usando UniqueIdentifier
        $event = (new Event(new UniqueIdentifier("cita-{$cita->id}@cercap.mx")))
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($inicio->toDateTimeImmutable(), true),
                    new DateTime($fin->toDateTimeImmutable(), true)
                )
            )
            ->setSummary($this->getSummary($cita))
            ->setDescription($this->getDescription($cita))
            ->setLocation(
                new Location('Real de Mayorazgo 130, Local 3, Col. Xoco, Benito Juárez, CP 03330, CDMX')
            );

        // Organizador
        if ($doctor && $doctor->email) {
            $event->setOrganizer(
                new Organizer(
                    new EmailAddress($doctor->email),
                    'Centro de Rehabilitación Cardiopulmonar CERCAP'
                )
            );
        }

        // Estado
        if ($action === 'cancel') {
            $event->setStatus(EventStatus::CANCELLED());
        } else {
            $event->setStatus(EventStatus::CONFIRMED());
        }

        return $event;
    }

    private function getSummary(Cita $cita): string
    {
        $tipo = $cita->primera_vez ? 'Primera consulta' : 'Consulta';
        return "{$tipo} - {$cita->paciente->nombre} {$cita->paciente->apellidoPat}";
    }

    private function getDescription(Cita $cita): string
    {
        $p = $cita->paciente;

        return implode("\n", array_filter([
            "Paciente: {$p->nombre} {$p->apellidoPat} {$p->apellidoMat}",
            $p->telefono ? "Teléfono: {$p->telefono}" : null,
            $p->email ? "Email: {$p->email}" : null,
            $cita->observaciones ? "Notas: {$cita->observaciones}" : null,
            "Estado: {$cita->estado}",
            $cita->primera_vez ? "Primera vez: Sí" : null,
        ]));
    }
}
