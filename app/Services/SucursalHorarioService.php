<?php

namespace App\Services;

use App\Models\Sucursal;
use Carbon\Carbon;

class SucursalHorarioService
{
    public const DIAS = ['lun', 'mar', 'mie', 'jue', 'vie', 'sab', 'dom'];

    /**
     * Horario por defecto: Lun–Vie 09:00–18:00, Sáb 09:00–14:00, Dom cerrado.
     *
     * @return array<string, array{abierto:bool,inicio:?string,fin:?string}>
     */
    public function defaultHorarios(): array
    {
        $horario = [];
        foreach (self::DIAS as $dia) {
            if (in_array($dia, ['sab'], true)) {
                $horario[$dia] = ['abierto' => true, 'inicio' => '09:00', 'fin' => '14:00'];
            } elseif ($dia === 'dom') {
                $horario[$dia] = ['abierto' => false, 'inicio' => null, 'fin' => null];
            } else {
                $horario[$dia] = ['abierto' => true, 'inicio' => '09:00', 'fin' => '18:00'];
            }
        }

        return $horario;
    }

    /**
     * Normaliza el JSON de horarios y rellena faltantes.
     *
     * @param  array<string, mixed>|null  $input
     * @return array<string, array{abierto:bool,inicio:?string,fin:?string}>
     */
    public function normalize(?array $input): array
    {
        $base = $this->defaultHorarios();
        if (! is_array($input)) {
            return $base;
        }

        foreach (self::DIAS as $dia) {
            if (! isset($input[$dia]) || ! is_array($input[$dia])) {
                continue;
            }
            $row = $input[$dia];
            $abierto = (bool) ($row['abierto'] ?? false);
            $inicio = $this->normalizeHora($row['inicio'] ?? null);
            $fin = $this->normalizeHora($row['fin'] ?? null);

            if ($abierto && (! $inicio || ! $fin || $inicio >= $fin)) {
                $abierto = false;
                $inicio = null;
                $fin = null;
            }

            $base[$dia] = [
                'abierto' => $abierto,
                'inicio' => $abierto ? $inicio : null,
                'fin' => $abierto ? $fin : null,
            ];
        }

        return $base;
    }

    public function diaKeyFromDate(string $fecha): string
    {
        $map = [1 => 'lun', 2 => 'mar', 3 => 'mie', 4 => 'jue', 5 => 'vie', 6 => 'sab', 0 => 'dom'];
        $dow = (int) Carbon::parse($fecha)->dayOfWeek;

        return $map[$dow] ?? 'lun';
    }

    /**
     * Genera slots de atención a partir del horario semanal.
     *
     * @return array<int, string> horas HH:MM
     */
    public function slotsParaFecha(Sucursal $sucursal, string $fecha, int $intervaloMinutos = 30): array
    {
        $horarios = $this->normalize($sucursal->horarios_atencion);
        $dia = $this->diaKeyFromDate($fecha);
        $row = $horarios[$dia] ?? null;

        if (! $row || ! ($row['abierto'] ?? false) || empty($row['inicio']) || empty($row['fin'])) {
            return [];
        }

        $inicio = Carbon::parse($fecha.' '.$row['inicio']);
        $fin = Carbon::parse($fecha.' '.$row['fin']);
        $slots = [];

        while ($inicio->lt($fin)) {
            $slots[] = $inicio->format('H:i');
            $inicio->addMinutes(max(15, $intervaloMinutos));
        }

        return $slots;
    }

    private function normalizeHora($value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{2}:\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }
}
