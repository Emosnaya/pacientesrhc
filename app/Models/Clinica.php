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
        'facturacion_addon_activo',
        'color_principal',
        'portal_permite_multiples_citas_mismo_horario',
        'portal_max_reagendas_paciente',
        'portal_bloqueo_dias_post_cancelacion',
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'activa' => 'boolean',
        'permite_multiples_sucursales' => 'boolean',
        'es_consultorio_privado' => 'boolean',
        'facturacion_addon_activo' => 'boolean',
        'portal_permite_multiples_citas_mismo_horario' => 'boolean',
        'portal_max_reagendas_paciente' => 'integer',
        'portal_bloqueo_dias_post_cancelacion' => 'integer',
        'fecha_vencimiento' => 'date',
        'trial_ends_at' => 'datetime',
        'modulos_habilitados' => 'array',
        'receta_pdf_config' => 'array'
    ];

    protected $appends = [
        'logo_url',
        'modulos_efectivos',
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

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class);
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
        if (!$this->activa) return false;

        // Pagada y no vencida
        if ($this->pagado) {
            return !$this->fecha_vencimiento || $this->fecha_vencimiento >= now();
        }

        // Trial activo (sin pago pero con trial_ends_at vigente)
        if ($this->trial_ends_at && $this->trial_ends_at >= now()) {
            return true;
        }

        // fecha_vencimiento vigente aunque no esté marcada como pagada
        if ($this->fecha_vencimiento && $this->fecha_vencimiento >= now()) {
            return true;
        }

        return false;
    }

    public function isExpired(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now();
    }
    
    /**
     * Verifica si la clínica puede crear más sucursales según su cuota.
     * La sucursal principal siempre está incluida en el plan base.
     * Las sucursales adicionales requieren haber comprado slots.
     */
    public function puedeCrearMasSucursales(): bool
    {
        $max = (int) ($this->max_sucursales ?? 1);
        $actual = $this->sucursales()->count();
        return $actual < $max;
    }

    /**
     * Devuelve cuántos slots de sucursal aún están disponibles.
     */
    public function slotsSucursalesDisponibles(): int
    {
        $max = (int) ($this->max_sucursales ?? 1);
        $actual = $this->sucursales()->count();
        return max(0, $max - $actual);
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

    /**
     * Verifica si un módulo está habilitado en esta clínica.
     * Si no hay módulos configurados, se considera acceso total (legacy).
     * Para clínicas que recién se registran, los módulos se derivan del tipo.
     */
    public function hasModulo(string $key): bool
    {
        $modulos = $this->modulos_habilitados ?? [];

        // Sin módulos explícitos → todos permitidos (clínicas legacy / sin configurar)
        if (empty($modulos)) {
            return true;
        }

        return in_array($key, $modulos, true);
    }

    /**
     * Devuelve los módulos efectivos (claves seleccionables).
     * - Si tiene modulos_habilitados configurados, los retorna.
     * - Si no, infiere módulos a partir del tipo de clínica usando el mapeo
     *   entre tipos y módulos seleccionables.
     */
    public function getModulosEfectivosAttribute(): array
    {
        $modulos = $this->modulos_habilitados ?? [];
        if (!empty($modulos)) {
            return $modulos;
        }

        // Mapeo tipo_clinica → módulos seleccionables por defecto
        $defaults = [
            'rehabilitacion_cardiopulmonar' => ['cardiaco', 'pulmonar', 'fisioterapia', 'nutricion', 'psicologia'],
            'fisioterapia'  => ['fisioterapia'],
            'nutricion'     => ['nutricion'],
            'psicologia'    => ['psicologia'],
            'dental'        => [],
            'cardiologia'   => [],
            'ginecologia'   => [],
            // Las demás especialidades no usan módulos seleccionables
        ];

        return $defaults[$this->tipo_clinica] ?? [];
    }
}
