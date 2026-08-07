<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedimientoClinica extends Model
{
    protected $table = 'procedimientos_clinica';

    protected $fillable = [
        'clinica_id',
        'nombre',
        'descripcion',
        'categoria',
        'codigo',
        'precio',
        'activo',
        'orden',
    ];

    protected $casts = [
        'precio' => 'float',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function scopeForClinica($query, int $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
