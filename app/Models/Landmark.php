<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'ciudad',
        'alcaldia',
        'estado',
        'direccion',
        'latitud',
        'longitud',
        'activo',
        'orden',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeEnCiudad($query, ?string $ciudad)
    {
        if (! $ciudad) {
            return $query;
        }

        return $query->where('ciudad', 'like', '%'.$ciudad.'%');
    }
}
