<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioItem extends Model
{
    protected $table = 'inventario_items';

    protected $fillable = [
        'clinica_id',
        'sucursal_id',
        'nombre',
        'descripcion',
        'categoria',
        'unidad',
        'cantidad',
        'cantidad_minima',
        'precio_costo',
        'activo',
    ];

    protected $casts = [
        'cantidad'         => 'float',
        'cantidad_minima'  => 'float',
        'precio_costo'     => 'float',
        'activo'           => 'boolean',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'item_id')->latest();
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /** True cuando el stock está por debajo o igual al mínimo configurado */
    public function getStockBajoAttribute(): bool
    {
        return $this->cantidad_minima > 0 && $this->cantidad <= $this->cantidad_minima;
    }
}
