<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'presupuesto_id',
        'concepto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'orden',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'precio_unitario' => 'float',
        'descuento' => 'float',
        'subtotal' => 'float',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
