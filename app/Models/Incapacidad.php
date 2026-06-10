<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incapacidad extends Model
{
    protected $table = 'incapacidades';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'folio',
        'tipo_incapacidad',
        'fecha_inicio',
        'fecha_termino',
        'diagnostico',
        'comentarios',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
        'folio' => 'integer',
        'tipo_exp' => 'integer',
    ];

    public const TIPOS = [
        'escolar' => 'Escolar',
        'laboral' => 'Laboral',
        'deportiva' => 'Deportiva',
        'transporte' => 'Transporte',
        'otra' => 'Otra',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function getTipoIncapacidadLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo_incapacidad] ?? $this->tipo_incapacidad;
    }

    public function getDiasAttribute(): int
    {
        if (!$this->fecha_inicio || !$this->fecha_termino) {
            return 0;
        }

        return $this->fecha_inicio->diffInDays($this->fecha_termino) + 1;
    }
}
