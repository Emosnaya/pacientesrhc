<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sillon extends Model
{
    protected $table = 'sillones';

    protected $fillable = [
        'clinica_id',
        'sucursal_id',
        'nombre',
        'color',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'sillon_id');
    }
}
