<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Evento;
use Carbon\Carbon;

class CitaAvailabilityService
{
    public const MODO_PERMITIR = 'permitir';
    public const MODO_PROFESIONAL = 'profesional';
    public const MODO_CLINICA = 'clinica';

    public const ESTADOS_ACTIVOS = ['pendiente', 'confirmada'];

    /**
     * Estado inicial configurado en la clínica (pendiente|confirmada).
     */
    public function estadoInicial(Clinica $clinica): string
    {
        $estado = $clinica->cita_estado_inicial ?? 'confirmada';

        return in_array($estado, ['pendiente', 'confirmada'], true) ? $estado : 'confirmada';
    }

    /**
     * Modo de solapamiento unificado. Mantiene compatibilidad con el flag legacy del portal.
     */
    public function modoSolapamiento(Clinica $clinica): string
    {
        $modo = $clinica->citas_solapamiento_modo ?? null;

        if (in_array($modo, [self::MODO_PERMITIR, self::MODO_PROFESIONAL, self::MODO_CLINICA], true)) {
            return $modo;
        }

        // Compatibilidad: portal_permite_multiples_citas_mismo_horario = false → clínica
        if (array_key_exists('portal_permite_multiples_citas_mismo_horario', $clinica->getAttributes())
            && ! (bool) ($clinica->portal_permite_multiples_citas_mismo_horario ?? true)
        ) {
            return self::MODO_CLINICA;
        }

        return self::MODO_PERMITIR;
    }

    /**
     * Verifica bloqueos + reglas de solapamiento.
     *
     * @return array{ok: bool, message: ?string}
     */
    public function canBook(
        Clinica $clinica,
        string $fecha,
        string $hora,
        ?int $sucursalId = null,
        ?int $doctorId = null,
        ?int $pacienteId = null,
        ?int $excludeCitaId = null
    ): array {
        $hora = substr($hora, 0, 5);

        $bloqueo = $this->checkBloqueo($clinica->id, $fecha, $hora, $sucursalId);
        if (! $bloqueo['ok']) {
            return $bloqueo;
        }

        // Un paciente no puede tener dos citas activas en el mismo horario
        if ($pacienteId) {
            $duplicada = $this->activeCitasQuery($clinica->id, $fecha, $hora, $excludeCitaId)
                ->where('paciente_id', $pacienteId)
                ->exists();

            if ($duplicada) {
                return ['ok' => false, 'message' => 'El paciente ya tiene una cita en ese horario'];
            }
        }

        $modo = $this->modoSolapamiento($clinica);

        if ($modo === self::MODO_CLINICA) {
            $ocupadas = $this->activeCitasQuery($clinica->id, $fecha, $hora, $excludeCitaId)->exists();
            if ($ocupadas) {
                return ['ok' => false, 'message' => 'Ese horario ya está ocupado en la clínica'];
            }
        }

        if ($modo === self::MODO_PROFESIONAL) {
            // Solicitud sin profesional: no bloquea a todos los doctores.
            // El cupo se valida al asignar doctor en la confirmación.
            if (! $doctorId) {
                return ['ok' => true, 'message' => null];
            }

            $doctorOcupado = $this->activeCitasQuery($clinica->id, $fecha, $hora, $excludeCitaId)
                ->where('user_id', $doctorId)
                ->exists();

            if ($doctorOcupado) {
                return ['ok' => false, 'message' => 'El profesional ya tiene una cita en ese horario'];
            }
        }

        // En modo clinica, las pendientes (con o sin doctor) ya bloquean vía ESTADOS_ACTIVOS.
        // En modo permitir, no hay bloqueo adicional.

        return ['ok' => true, 'message' => null];
    }

    /**
     * @return array{ok: bool, message: ?string}
     */
    public function checkBloqueo(int $clinicaId, string $fecha, string $hora, ?int $sucursalId = null): array
    {
        $hora = substr($hora, 0, 5);

        $query = Evento::query()
            ->where('tipo', 'bloqueo')
            ->where('clinica_id', $clinicaId)
            ->whereDate('fecha', $fecha);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        foreach ($query->get() as $bloqueo) {
            if ($bloqueo->todo_el_dia) {
                return [
                    'ok' => false,
                    'message' => 'No se pueden agendar citas en esta fecha. Motivo: ' . ($bloqueo->titulo ?: 'Día bloqueado'),
                ];
            }

            $hInicio = $bloqueo->hora ? substr((string) $bloqueo->hora, 0, 5) : null;
            $hFin = $bloqueo->hora_fin ? substr((string) $bloqueo->hora_fin, 0, 5) : null;

            if ($hInicio && $hFin && $hora >= $hInicio && $hora < $hFin) {
                return [
                    'ok' => false,
                    'message' => "Este horario está bloqueado ({$hInicio} - {$hFin}). Motivo: " . ($bloqueo->titulo ?: 'Bloqueo'),
                ];
            }

            if ($hInicio && ! $hFin && $hora === $hInicio) {
                return [
                    'ok' => false,
                    'message' => 'Este horario está bloqueado. Motivo: ' . ($bloqueo->titulo ?: 'Bloqueo'),
                ];
            }
        }

        return ['ok' => true, 'message' => null];
    }

    protected function activeCitasQuery(int $clinicaId, string $fecha, string $hora, ?int $excludeCitaId = null)
    {
        $query = Cita::query()
            ->where('clinica_id', $clinicaId)
            ->whereDate('fecha', $fecha)
            ->where('hora', $hora)
            ->whereIn('estado', self::ESTADOS_ACTIVOS);

        if ($excludeCitaId) {
            $query->where('id', '!=', $excludeCitaId);
        }

        return $query;
    }

    /**
     * Helper para fechas futuras (portal).
     */
    public function isPastSlot(string $fecha, string $hora): bool
    {
        $hora = substr($hora, 0, 5);
        if (Carbon::parse($fecha)->startOfDay()->lt(now()->startOfDay())) {
            return true;
        }

        return $fecha === now()->toDateString() && $hora <= now()->format('H:i');
    }
}
