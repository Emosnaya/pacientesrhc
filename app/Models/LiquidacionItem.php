<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidacionItem extends Model
{
    protected $fillable = [
        'liquidacion_id',
        'user_id',
        'compensation_profile_id',
        'sueldo_fijo',
        'base_comisionable',
        'comision_pct',
        'monto_comision',
        'total',
        'cantidad_pagos',
        'detalle_json',
    ];

    protected $casts = [
        'sueldo_fijo' => 'float',
        'base_comisionable' => 'float',
        'comision_pct' => 'float',
        'monto_comision' => 'float',
        'total' => 'float',
        'cantidad_pagos' => 'integer',
        'detalle_json' => 'array',
    ];

    public function liquidacion()
    {
        return $this->belongsTo(Liquidacion::class, 'liquidacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(CompensationProfile::class, 'compensation_profile_id');
    }

    public function pagosPivot()
    {
        return $this->hasMany(LiquidacionItemPago::class, 'liquidacion_item_id');
    }
}
