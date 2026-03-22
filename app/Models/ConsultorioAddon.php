<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultorioAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clinica_id',
        'price_monthly',
        'price_yearly',
        'billing_cycle',
        'is_active',
        'stripe_subscription_id',
        'started_at',
        'expires_at',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el consultorio adicional
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Verificar si el addon está activo
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Precio según ciclo de facturación
     */
    public function getPriceAttribute(): float
    {
        return $this->billing_cycle === 'anual' 
            ? $this->price_yearly 
            : $this->price_monthly;
    }
}
