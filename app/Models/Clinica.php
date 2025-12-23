<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinica extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'email',
        'logo',
        'telefono',
        'direccion',
        'plan',
        'pagado',
        'fecha_vencimiento',
        'activa'
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'activa' => 'boolean',
        'fecha_vencimiento' => 'date'
    ];

    protected $appends = [
        'logo_url'
    ];

    // Relaciones
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->activa && $this->pagado;
    }

    public function isExpired(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        
        // Construir URL completa usando el dominio de la API
        $baseUrl = config('app.url');
        return $baseUrl . '/storage/' . $this->logo;
    }
}
