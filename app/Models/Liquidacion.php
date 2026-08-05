<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidacion extends Model
{
    protected $table = 'liquidaciones';

    protected $fillable = [
        'clinica_id',
        'sucursal_id',
        'periodo_inicio',
        'periodo_fin',
        'estado',
        'generado_por',
        'pagado_at',
        'notas',
    ];

    protected $casts = [
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'pagado_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(LiquidacionItem::class, 'liquidacion_id');
    }

    public function generadoPor()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->estado, ['borrador', 'calculada'], true);
    }
}
