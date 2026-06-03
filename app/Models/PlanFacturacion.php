<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFacturacion extends Model
{
    protected $table = 'planes_facturacion';

    protected $fillable = [
        'nombre',
        'clave',
        'cantidad_facturas_min',
        'cantidad_facturas_max',
        'precio_mensual',
        'stripe_price_id',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'activo' => 'boolean',
    ];

    const CLAVE_BASICO = 'basico';
    const CLAVE_PRO = 'pro';
    const CLAVE_ENTERPRISE = 'enterprise';
}
