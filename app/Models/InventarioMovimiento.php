<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    protected $fillable = [
        'item_id',
        'user_id',
        'tipo',
        'cantidad',
        'cantidad_anterior',
        'cantidad_nueva',
        'motivo',
    ];

    protected $casts = [
        'cantidad'          => 'float',
        'cantidad_anterior' => 'float',
        'cantidad_nueva'    => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventarioItem::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
