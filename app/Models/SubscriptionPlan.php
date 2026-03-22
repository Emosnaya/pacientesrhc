<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'max_pacientes',
        'max_usuarios',
        'max_sucursales',
        'features',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    /**
     * Obtener el descuento anual en porcentaje
     */
    public function getAnnualDiscountPercentageAttribute(): int
    {
        if ($this->price_monthly == 0) return 0;
        
        $monthlyYearly = $this->price_monthly * 12;
        $discount = (($monthlyYearly - $this->price_yearly) / $monthlyYearly) * 100;
        
        return round($discount);
    }

    /**
     * Obtener el ahorro anual en pesos
     */
    public function getAnnualSavingsAttribute(): float
    {
        return ($this->price_monthly * 12) - $this->price_yearly;
    }
}
