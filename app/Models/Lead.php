<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Prospecto comercial: profesional o establecimiento que aún no usa LynkaMed.
 * Se genera, entre otros, al escanear un Pasaporte de Salud sin suscripción activa.
 */
class Lead extends Model
{
    protected $table = 'leads';

    public const ORIGEN_PASAPORTE = 'pasaporte_qr';

    protected $fillable = [
        'origen',
        'nombre',
        'email',
        'telefono',
        'establecimiento',
        'especialidad',
        'mensaje',
        'paciente_uuid',
        'estado',
        'notas_internas',
        'contactado_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'contactado_at' => 'datetime',
    ];

    public function scopeNuevos($query)
    {
        return $query->where('estado', 'nuevo');
    }
}
