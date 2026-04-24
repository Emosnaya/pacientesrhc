<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    protected $fillable = [
        'clinica_id', 'nombre', 'email', 'telefono', 'contacto', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(OrdenLaboratorio::class, 'laboratorio_id');
    }
}
