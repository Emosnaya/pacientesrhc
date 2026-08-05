<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanTratamientoDentalItem extends Model
{
    protected $table = 'plan_tratamiento_dental_items';

    protected $fillable = [
        'plan_tratamiento_dental_id',
        'diente',
        'procedimiento',
        'fase',
        'estado',
        'precio_estimado',
        'notas',
        'orden',
        'completado_at',
    ];

    protected $casts = [
        'precio_estimado' => 'float',
        'fase' => 'integer',
        'orden' => 'integer',
        'completado_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(PlanTratamientoDental::class, 'plan_tratamiento_dental_id');
    }
}
