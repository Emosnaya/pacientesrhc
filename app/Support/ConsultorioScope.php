<?php

namespace App\Support;

use App\Models\Clinica;
use App\Models\SolicitudFactura;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Alcance de datos por médico en consultorios privados compartidos.
 * La suscripción LynkaMed y el add-on CFDI son del consultorio (Clinica);
 * caja, egresos y facturas se filtran por usuario.
 */
class ConsultorioScope
{
    public static function clinica(User $user): ?Clinica
    {
        $id = $user->clinica_efectiva_id;

        return $id ? Clinica::query()->find($id) : null;
    }

    public static function esConsultorioPrivado(User $user): bool
    {
        return (bool) self::clinica($user)?->es_consultorio_privado;
    }

    public static function emisorTipoDefault(User $user): string
    {
        return self::esConsultorioPrivado($user)
            ? SolicitudFactura::EMISOR_DOCTOR
            : SolicitudFactura::EMISOR_CLINICA;
    }

    /** @param Builder $query */
    public static function scopePagos($query, User $user)
    {
        if (self::esConsultorioPrivado($user)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /** @param Builder $query */
    public static function scopeEgresos($query, User $user)
    {
        if (self::esConsultorioPrivado($user)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /** @param Builder $query */
    public static function scopeSolicitudes($query, User $user)
    {
        if (self::esConsultorioPrivado($user)) {
            $query->where('solicitada_por', $user->id);
        }

        return $query;
    }

    public static function puedeAccederRecursoMedico(User $user, ?int $ownerUserId): bool
    {
        if (! self::esConsultorioPrivado($user)) {
            return true;
        }

        return $ownerUserId === null || (int) $ownerUserId === (int) $user->id;
    }
}
