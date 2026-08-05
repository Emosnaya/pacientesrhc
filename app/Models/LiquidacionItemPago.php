<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidacionItemPago extends Model
{
    protected $table = 'liquidacion_item_pagos';

    protected $fillable = [
        'liquidacion_item_id',
        'pago_id',
        'monto_atribuido',
    ];

    protected $casts = [
        'monto_atribuido' => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(LiquidacionItem::class, 'liquidacion_item_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }
}
