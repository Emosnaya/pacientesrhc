<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use DateTimeZone;
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
     * Zona horaria de México (donde operan las clínicas)
     */
    private const TIMEZONE = 'America/Mexico_City';

    /**
     * Genera el archivo ICS
     */
    public function generateIcs(Cita $cita, string $action = 'create'): string
    {
        $cita->load(['paciente', 'user', 'user.clinica', 'sucursal.clinica', 'clinica']);

        $event = $this->createEvent($cita, $action);

        $calendar = new Calendar([$event]);
        $calendar->setProductIdentifier('-//LynkaMed//Appointments//ES');

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

        // Fecha y hora en zona horaria de México
        $fechaSolo = Carbon::parse($cita->fecha)->format('Y-m-d');
        $horaSolo = Carbon::parse($cita->hora)->format('H:i:s');
        
        // Crear el datetime en la zona horaria correcta de México
        $timezone = new DateTimeZone(self::TIMEZONE);
        $inicioCarbon = Carbon::createFromFormat('Y-m-d H:i:s', "{$fechaSolo} {$horaSolo}", self::TIMEZONE);
        $finCarbon = $inicioCarbon->copy()->addHour();

        // Crear DateTimeImmutable CON la zona horaria de México
        $inicioDateTime = new \DateTimeImmutable($inicioCarbon->format('Y-m-d H:i:s'), $timezone);
        $finDateTime = new \DateTimeImmutable($finCarbon->format('Y-m-d H:i:s'), $timezone);

        // Obtener ubicación de la clínica/sucursal
        $ubicacion = $this->getUbicacion($cita);

        // Crear evento con UID único
        // Usar true en DateTime para incluir la zona horaria (TZID) en el ICS
        $event = (new Event(new UniqueIdentifier("cita-{$cita->id}@lynkamed.mx")))
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($inicioDateTime, true), // true = incluir TZID
                    new DateTime($finDateTime, true)
                )
            )
            ->setSummary($this->getSummary($cita))
            ->setDescription($this->getDescription($cita));

        // Agregar ubicación si existe
        if ($ubicacion) {
            $event->setLocation(new Location($ubicacion));
        }

        // Organizador
        $organizerEmail = $doctor?->email ?? config('mail.from.address');
        $organizerName = $this->getClinicaName($cita);
        
        if ($organizerEmail) {
            $event->setOrganizer(
                new Organizer(
                    new EmailAddress($organizerEmail),
                    $organizerName
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

    /**
     * Obtiene la ubicación de la cita (sucursal o clínica)
     */
    private function getUbicacion(Cita $cita): ?string
    {
        // Intentar obtener dirección de la sucursal
        if ($cita->sucursal && $cita->sucursal->direccion) {
            return $cita->sucursal->direccion;
        }

        // Si no, intentar de la clínica
        if ($cita->clinica && $cita->clinica->direccion) {
            return $cita->clinica->direccion;
        }

        // Si tiene user con clínica
        if ($cita->user && $cita->user->clinica && $cita->user->clinica->direccion) {
            return $cita->user->clinica->direccion;
        }

        return null;
    }

    /**
     * Obtiene el nombre de la clínica para mostrar
     */
    private function getClinicaName(Cita $cita): string
    {
        if ($cita->sucursal && $cita->sucursal->nombre) {
            return $cita->sucursal->nombre;
        }

        if ($cita->clinica && $cita->clinica->nombre) {
            return $cita->clinica->nombre;
        }

        if ($cita->user && $cita->user->clinica && $cita->user->clinica->nombre) {
            return $cita->user->clinica->nombre;
        }

        return 'LynkaMed';
    }

    private function getSummary(Cita $cita): string
    {
        $tipo = $cita->primera_vez ? 'Primera consulta' : 'Consulta';
        $clinicaName = $this->getClinicaName($cita);
        return "{$tipo} - {$clinicaName}";
    }

    private function getDescription(Cita $cita): string
    {
        $p = $cita->paciente;
        $clinicaName = $this->getClinicaName($cita);

        return implode("\n", array_filter([
            "Cita en: {$clinicaName}",
            "Paciente: {$p->nombre} {$p->apellidoPat} {$p->apellidoMat}",
            $p->telefono ? "Teléfono: {$p->telefono}" : null,
            $cita->notas ? "Notas: {$cita->notas}" : null,
            $cita->primera_vez ? "Primera vez: Sí" : null,
        ]));
    }
}
