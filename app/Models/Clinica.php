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
        'trial_ends_at',
        'plan_type',
        'billing_cycle',
        'next_billing_date',
        'stripe_subscription_id',
        'stripe_customer_id',
        'billing_cycle',
        'activa',
        'permite_multiples_sucursales',
        'max_sucursales',
        'max_usuarios',
        'max_pacientes',
        'receta_pdf_config',
        'es_consultorio_privado',
        'propietario_user_id',
        'facturacion_addon_activo',
        'facturapi_organization_id',
        'facturapi_configured',
        'facturapi_api_key_test',
        'facturapi_api_key_live',
        'facturapi_mode',
        'facturacion_iva_incluido',
        'facturacion_tasa_iva',
        'facturacion_serie',
        'color_principal',
        'portal_permite_multiples_citas_mismo_horario',
        'portal_max_reagendas_paciente',
        'portal_bloqueo_dias_post_cancelacion',
        'cita_estado_inicial',
        'citas_solapamiento_modo',
        'whatsapp_notificaciones_activas',
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'activa' => 'boolean',
        'permite_multiples_sucursales' => 'boolean',
        'es_consultorio_privado' => 'boolean',
        'facturacion_addon_activo' => 'boolean',
        'facturapi_configured'     => 'boolean',
        'facturacion_iva_incluido' => 'boolean',
        'facturacion_tasa_iva'     => 'float',
        'portal_permite_multiples_citas_mismo_horario' => 'boolean',
        'portal_max_reagendas_paciente' => 'integer',
        'portal_bloqueo_dias_post_cancelacion' => 'integer',
        'whatsapp_notificaciones_activas' => 'boolean',
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
     * En consultorio privado: el equipo usa la suscripción del espacio (propietario / vencimiento del consultorio).
     */
    public function suscripcionConsultorioCompartidaActiva(): bool
    {
        if (! $this->es_consultorio_privado) {
            return false;
        }

        if ($this->fecha_vencimiento && \App\Services\SubscriptionStatusService::fechaVencimientoVigente($this->fecha_vencimiento)) {
            return true;
        }

        if ($this->trial_ends_at && now()->lessThanOrEqualTo($this->trial_ends_at)) {
            return true;
        }

        $propietario = $this->propietario;
        if ($propietario && $propietario->tieneSuscripcionConsultorioActiva()) {
            return true;
        }

        return (bool) ($this->pagado && $this->activa);
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

    /**
     * Fin efectivo del trial (clínica, propietario o fecha de vencimiento en consultorio sin pago).
     */
    public function trialEndsAtEfectivo(): ?\Carbon\Carbon
    {
        $now = now();

        if ($this->trial_ends_at && $now->lessThanOrEqualTo($this->trial_ends_at)) {
            return $this->trial_ends_at->copy();
        }

        $propietario = $this->relationLoaded('propietario') ? $this->propietario : $this->propietario()->first();
        if ($propietario?->trial_ends_at && $now->lessThan($propietario->trial_ends_at)) {
            return $propietario->trial_ends_at->copy();
        }

        if (
            $this->es_consultorio_privado
            && ! $this->pagado
            && $this->fecha_vencimiento
            && \App\Services\SubscriptionStatusService::fechaVencimientoVigente($this->fecha_vencimiento->copy())
            && ! $this->stripe_subscription_id
        ) {
            return $this->fecha_vencimiento->copy()->endOfDay();
        }

        return null;
    }

    public function estaEnTrial(): bool
    {
        return $this->trialEndsAtEfectivo() !== null && ! $this->pagado;
    }

    /**
     * Persiste trial_ends_at (y fecha_vencimiento si falta) desde propietario o vencimiento vigente.
     */
    public function sincronizarFechasTrial(): bool
    {
        $trialEnd = $this->trialEndsAtEfectivo();
        if (! $trialEnd || now()->greaterThan($trialEnd)) {
            return false;
        }

        $attrs = [];
        if (! $this->trial_ends_at || ! $this->trial_ends_at->equalTo($trialEnd)) {
            $attrs['trial_ends_at'] = $trialEnd;
        }
        if (! $this->fecha_vencimiento) {
            $attrs['fecha_vencimiento'] = $trialEnd->toDateString();
        }
        if (! $this->activa) {
            $attrs['activa'] = true;
        }

        if ($attrs === []) {
            return false;
        }

        $this->update($attrs);

        return true;
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        if (! $this->activa) {
            return false;
        }

        $fechaVigente = \App\Services\SubscriptionStatusService::fechaVencimientoVigente(
            $this->fecha_vencimiento ? $this->fecha_vencimiento->copy() : null
        );

        if ($this->pagado) {
            return $fechaVigente;
        }

        if ($this->trial_ends_at && now()->lessThanOrEqualTo($this->trial_ends_at)) {
            return true;
        }

        if ($this->fecha_vencimiento && $fechaVigente) {
            return true;
        }

        return false;
    }

    public function isExpired(): bool
    {
        if (! $this->fecha_vencimiento) {
            return false;
        }

        return ! \App\Services\SubscriptionStatusService::fechaVencimientoVigente($this->fecha_vencimiento->copy());
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
