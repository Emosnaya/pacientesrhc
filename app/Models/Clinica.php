<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clinica extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo_clinica',
        'modulos_habilitados',
        'email',
        'logo',
        'telefono',
        'direccion',
        'plan',
        'duration',
        'pagado',
        'fecha_vencimiento',
        'activa',
        'permite_multiples_sucursales',
        'max_sucursales',
        'max_usuarios',
        'max_pacientes',
        'receta_pdf_config',
        'es_consultorio_privado',
        'propietario_user_id',
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'activa' => 'boolean',
        'permite_multiples_sucursales' => 'boolean',
        'es_consultorio_privado' => 'boolean',
        'fecha_vencimiento' => 'date',
        'modulos_habilitados' => 'array',
        'receta_pdf_config' => 'array'
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

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }
    
    /**
     * Obtiene la sucursal principal de la clínica
     */
    public function sucursalPrincipal()
    {
        return $this->hasOne(Sucursal::class)->where('es_principal', true);
    }

    /**
     * Propietario del consultorio privado
     */
    public function propietario()
    {
        return $this->belongsTo(User::class, 'propietario_user_id');
    }

    /**
     * Todos los usuarios que tienen acceso a esta clínica/consultorio (pivot enriquecido)
     */
    public function miembros()
    {
        return $this->belongsToMany(User::class, 'user_clinicas')
                    ->using(UserClinica::class)
                    ->withPivot(['rol_en_clinica', 'activa', 'invitado_por'])
                    ->withTimestamps();
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
    
    /**
     * Verifica si la clínica puede tener múltiples sucursales
     */
    public function puedeCrearMasSucursales(): bool
    {
        // Si no permite múltiples sucursales y ya tiene una, no puede crear más
        if (!$this->permite_multiples_sucursales) {
            return $this->sucursales()->count() === 0;
        }
        
        return true;
    }
    
    /**
     * Verifica si debe mostrar el selector de sucursales
     */
    public function mostrarSelectorSucursales(): bool
    {
        return $this->permite_multiples_sucursales && $this->sucursales()->count() > 1;
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
