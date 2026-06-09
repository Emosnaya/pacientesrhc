<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionStatusService
{
    /**
     * La fecha de vencimiento an cubre hoy? (vlido hasta el da anterior al vencimiento).
     * Si vence el 15-jun, el 14 tiene acceso; el 15 ya vence.
     */
    public static function fechaVencimientoVigente(?Carbon $fechaVencimiento): bool
    {
        if (! $fechaVencimiento) {
            return true;
        }

        return now()->startOfDay()->lessThan($fechaVencimiento->copy()->startOfDay());
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
                'No se ha registrado ningn pago para esta cuenta.',
                $esConsultorio,
                null
            );
        }

        if (! $fechaVencimiento && $clinica->pagado && $clinica->activa) {
            return self::activePayload('perpetuo', null, $esConsultorio);
        }

        $diasRestantes = $fechaVencimiento
            ? (int) $now->copy()->startOfDay()->diffInDays($fechaVencimiento->copy()->startOfDay(), false)
            : null;

        if ($fechaVencimiento && ! self::fechaVencimientoVigente($fechaVencimiento)) {
            $diasVencido = abs($diasRestantes ?? 0);

            return self::inactivePayload(
                $esConsultorio ? 'consultorio_vencido' : 'clinica_vencida',
                $esConsultorio
                    ? "Tu suscripcin venci hace {$diasVencido} das. Renueva para continuar usando el sistema."
                    : 'La suscripcin de tu clnica ha vencido. Renueva para continuar.',
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
                    'Tu suscripcin de consultorio ha vencido. Renueva para continuar.',
                    true,
                    0
                );
            }

            if (! $esPropietario && ! $suscripcionCompartida) {
                return self::inactivePayload(
                    'consultorio_vencido',
                    'El consultorio no tiene suscripcin activa. El propietario debe renovar el plan para que el equipo pueda entrar.',
                    true,
                    0
                );
            }
        }

        if (! $clinica->activa) {
            return self::inactivePayload(
                'clinica_inactiva',
                'Tu espacio de trabajo est desactivado. Contacta al administrador.',
                $esConsultorio,
                null
            );
        }

        if (! $clinica->pagado && ! ($clinica->trial_ends_at && $now->lessThanOrEqualTo($clinica->trial_ends_at))
            && ! ($fechaVencimiento && self::fechaVencimientoVigente($fechaVencimiento))) {
            return self::inactivePayload(
                $esConsultorio ? 'consultorio_sin_pago' : 'clinica_sin_pago',
                'No se ha registrado ningn pago para esta cuenta.',
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
            'anual' => ['precio' => $anio['total'], 'ciclo' => 'anual', 'ahorro' => $anio['ahorro'] ?? 0, 'etiqueta' => 'Precio de lanzamiento'],
        ];
    }

    private static function activePayload(string $tipo, ?int $diasRestantes, bool $esConsultorio): array
    {
        return [
            'active' => true,
            'tipo' => $tipo,
            'message' => null,
            'dias_vencido' => null,
            'dias_restantes' => $diasRestantes,
            'puede_renovar_online' => true,
            'contacto_url' => null,
            'renovacion_url' => $esConsultorio ? '/suscripcion' : null,
        ];
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
            'dias_restantes' => null,
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
