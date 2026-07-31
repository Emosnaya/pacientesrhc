<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionStatusService
{
    /**
     * Días calendario tras la fecha de vencimiento en los que aún hay acceso (sin 402).
     * Día 0 = día de vencimiento; día 1 = un día después. El bloqueo empieza en GRACE_DAYS.
     */
    public const GRACE_DAYS = 2;

    /**
     * ¿La fecha de vencimiento aún cubre hoy? (válido hasta el día anterior al vencimiento).
     * Si vence el 15-jun, el 14 tiene acceso; el 15 ya vence.
     */
    public static function fechaVencimientoVigente(?Carbon $fechaVencimiento): bool
    {
        if (! $fechaVencimiento) {
            return true;
        }

        return now()->startOfDay()->lessThan($fechaVencimiento->copy()->startOfDay());
    }

    /**
     * Días enteros respecto a la fecha de vencimiento (0 = vence hoy, negativo = ya pasó).
     */
    public static function diasRespectoVencimiento(?Carbon $fechaVencimiento): ?int
    {
        if (! $fechaVencimiento) {
            return null;
        }

        return (int) now()->copy()->startOfDay()->diffInDays($fechaVencimiento->copy()->startOfDay(), false);
    }

    public static function getStatus(Clinica $clinica, ?User $user = null): array
    {
        $now = now();
        $fechaVencimiento = $clinica->fecha_vencimiento;
        $esConsultorio = (bool) $clinica->es_consultorio_privado;
        $user ??= $clinica->propietario;

        if (! $fechaVencimiento && ! $clinica->pagado) {
            if ($clinica->trial_ends_at && $now->lessThanOrEqualTo($clinica->trial_ends_at)) {
                return self::activePayload('trial_activo', $now->diffInDays($clinica->trial_ends_at, false), $esConsultorio);
            }

            return self::inactivePayload(
                $esConsultorio ? 'consultorio_sin_pago' : 'clinica_sin_pago',
                'No se ha registrado ningún pago para esta cuenta.',
                $esConsultorio,
                null
            );
        }

        if (! $fechaVencimiento && $clinica->pagado && $clinica->activa) {
            return self::activePayload('perpetuo', null, $esConsultorio);
        }

        $diasRestantes = self::diasRespectoVencimiento($fechaVencimiento);

        if ($fechaVencimiento && ! self::fechaVencimientoVigente($fechaVencimiento)) {
            $diasVencido = abs($diasRestantes ?? 0);

            // Período de gracia: acceso completo los primeros GRACE_DAYS días (0 y 1).
            // Banner de vencida en frontend desde día 1; modal/402 desde día GRACE_DAYS (2).
            if ($diasVencido < self::GRACE_DAYS) {
                return self::activePayload(
                    $esConsultorio ? 'consultorio_gracia' : 'clinica_gracia',
                    $diasRestantes,
                    $esConsultorio,
                    [
                        'dias_vencido' => $diasVencido,
                        'en_gracia' => true,
                    ]
                );
            }

            return self::inactivePayload(
                $esConsultorio ? 'consultorio_vencido' : 'clinica_vencida',
                $esConsultorio
                    ? "Tu suscripción venció hace {$diasVencido} días. Renueva para continuar usando el sistema."
                    : 'La suscripción de tu clínica ha vencido. Renueva para continuar.',
                $esConsultorio,
                $diasVencido
            );
        }

        if ($esConsultorio && $user) {
            $suscripcionCompartida = $clinica->suscripcionConsultorioCompartidaActiva();
            $esPropietario = (int) $clinica->propietario_user_id === (int) $user->id;

            if ($esPropietario && ! $user->tieneSuscripcionConsultorioActiva() && ! $suscripcionCompartida) {
                return self::inactivePayload(
                    'consultorio_vencido',
                    'Tu suscripción de consultorio ha vencido. Renueva para continuar.',
                    true,
                    0
                );
            }

            if (! $esPropietario && ! $suscripcionCompartida) {
                return self::inactivePayload(
                    'consultorio_vencido',
                    'El consultorio no tiene suscripción activa. El propietario debe renovar el plan para que el equipo pueda entrar.',
                    true,
                    0
                );
            }
        }

        if (! $clinica->activa) {
            return self::inactivePayload(
                'clinica_inactiva',
                'Tu espacio de trabajo está desactivado. Contacta al administrador.',
                $esConsultorio,
                null
            );
        }

        if (! $clinica->pagado && ! ($clinica->trial_ends_at && $now->lessThanOrEqualTo($clinica->trial_ends_at))
            && ! ($fechaVencimiento && self::fechaVencimientoVigente($fechaVencimiento))) {
            return self::inactivePayload(
                $esConsultorio ? 'consultorio_sin_pago' : 'clinica_sin_pago',
                'No se ha registrado ningún pago para esta cuenta.',
                $esConsultorio,
                null
            );
        }

        return self::activePayload(
            $esConsultorio ? 'consultorio_activo' : 'clinica_activa',
            $diasRestantes,
            $esConsultorio
        );
    }

    public static function subscriptionInfoForResponse(Clinica $clinica, array $status): array
    {
        return [
            'tipo' => $status['tipo'],
            'clinica_id' => $clinica->id,
            'clinica_nombre' => $clinica->nombre,
            'es_consultorio' => (bool) $clinica->es_consultorio_privado,
            'fecha_vencimiento' => $clinica->fecha_vencimiento?->format('Y-m-d'),
            'dias_vencido' => $status['dias_vencido'],
            'en_gracia' => (bool) ($status['en_gracia'] ?? false),
            'puede_renovar_online' => $status['puede_renovar_online'],
            'contacto_comercial_url' => $status['contacto_url'],
            'renovacion_url' => $status['renovacion_url'],
            'planes_disponibles' => self::planesDisponibles($clinica),
        ];
    }

    public static function planesDisponibles(Clinica $clinica): array
    {
        $mes = PricingService::calcular(
            $clinica->tipo_clinica,
            $clinica->modulos_habilitados ?? [],
            'mensual',
            $clinica->es_consultorio_privado ?? false
        );
        $anio = PricingService::calcular(
            $clinica->tipo_clinica,
            $clinica->modulos_habilitados ?? [],
            'anual',
            $clinica->es_consultorio_privado ?? false
        );

        return [
            'mensual' => ['precio' => $mes['total'], 'ciclo' => 'mensual', 'etiqueta' => 'Precio de lanzamiento'],
            'anual' => [
                'precio' => $anio['total'],
                'ciclo' => 'anual',
                'ahorro' => $anio['ahorro_anual'] ?? 0,
                'meses_gratis' => $anio['meses_gratis'] ?? 1,
                'precio_sin_descuento' => $anio['precio_sin_descuento'] ?? (($mes['total'] ?? 0) * 12),
                'etiqueta' => '1 mes gratis',
            ],
        ];
    }

    private static function activePayload(string $tipo, ?int $diasRestantes, bool $esConsultorio, array $extra = []): array
    {
        return array_merge([
            'active' => true,
            'tipo' => $tipo,
            'message' => null,
            'dias_vencido' => null,
            'dias_restantes' => $diasRestantes,
            'en_gracia' => false,
            'puede_renovar_online' => true,
            'contacto_url' => null,
            'renovacion_url' => $esConsultorio ? '/suscripcion' : null,
        ], $extra);
    }

    private static function inactivePayload(
        string $tipo,
        string $message,
        bool $esConsultorio,
        ?int $diasVencido
    ): array {
        return [
            'active' => false,
            'tipo' => $tipo,
            'message' => $message,
            'dias_vencido' => $diasVencido,
            'dias_restantes' => $diasVencido !== null ? -1 * abs($diasVencido) : null,
            'en_gracia' => false,
            'puede_renovar_online' => $esConsultorio || in_array($tipo, [
                'clinica_vencida',
                'clinica_sin_pago',
                'consultorio_vencido',
                'consultorio_sin_pago',
            ], true),
            'contacto_url' => '/api/clinica-contacto-comercial',
            'renovacion_url' => $esConsultorio ? '/suscripcion' : null,
        ];
    }
}
