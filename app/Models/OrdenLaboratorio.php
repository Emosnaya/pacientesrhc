<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdenLaboratorio extends Model
{
    protected $table = 'ordenes_laboratorio';

    protected $fillable = [
        'clinica_id',
        'paciente_id',
        'user_id',
        'laboratorio_id',
        'folio',
        'estudios',
        'indicaciones',
        'diagnostico_clinico',
        'notas_laboratorio',
        'modelo_dental',
        'email_laboratorio',
        'correo_enviado',
        'correo_enviado_at',
        'status',
        'fecha_recoleccion',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
    ];

    protected $casts = [
        'correo_enviado'        => 'boolean',
        'correo_enviado_at'     => 'datetime',
        'fecha_recoleccion'     => 'date',
        'fecha_entrega_estimada'=> 'date',
        'fecha_entrega_real'    => 'date',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function clinica(): BelongsTo  { return $this->belongsTo(Clinica::class); }
    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function laboratorio(): BelongsTo { return $this->belongsTo(Laboratorio::class); }
    public function portalToken(): HasOne { return $this->hasOne(OrdenLaboratorioToken::class, 'orden_id'); }

    // ─── Badge legible del status ──────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente'   => 'Pendiente',
            'recibida'    => 'Recibida',
            'en_proceso'  => 'En proceso',
            'lista'       => 'Lista para entregar',
            'entregada'   => 'Entregada',
            'cancelada'   => 'Cancelada',
            default       => ucfirst($this->status),
        };
    }
}
