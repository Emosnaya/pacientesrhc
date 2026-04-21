<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Paciente extends Model
{
    use HasFactory, Auditable, Notifiable;

    /**
     * Boot the model - auto-generate UUID on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paciente) {
            if (empty($paciente->uuid_publico)) {
                $paciente->uuid_publico = Str::uuid()->toString();
            }
        });
    }

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid_publico',
        'registro',
        'nombre',
        'apellidoPat',
        'apellidoMat',
        'telefono',
        'email',
        'fechaNacimiento',
        'edad',
        'genero',
        'estadoCivil',
        'profesion',
        'domicilio',
        'calle',
        'num_ext',
        'num_int',
        'colonia',
        'codigo_postal',
        'ciudad',
        'estado_dir',
        'talla',
        'peso',
        'cintura',
        'imc',
        'diagnostico',
        'medicamentos',
        'motivo_consulta',
        'alergias',
        'envio',
        'tipo_paciente',
        'categoria_pago',
        'aseguradora',
        'color',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'aviso_privacidad_aceptado_at',
        'version_aviso',
        'consentimiento_token_hash',
        'consentimiento_token_expires_at',
        'consentimiento_email_enviado_at',
        'consentimiento_invitacion_contexto',
        'archivo_muerto',
        'archivo_muerto_at',
        'archivo_muerto_motivo'
    ];

    protected $appends = ['domicilio_formateado'];

    /**
     * No exponer hash de invitación en JSON de API.
     */
    protected $hidden = [
        'consentimiento_token_hash',
        'consentimiento_invitacion_contexto',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'encrypted',
        'apellidoPat' => 'encrypted',
        'apellidoMat' => 'encrypted',
        'telefono' => 'encrypted',
        'domicilio' => 'encrypted',
        'calle' => 'encrypted',
        'colonia' => 'encrypted',
        'diagnostico' => 'encrypted',
        'medicamentos' => 'encrypted',
        'motivo_consulta' => 'encrypted',
        'alergias' => 'encrypted',
        'fechaNacimiento' => 'date',
        'aviso_privacidad_aceptado_at' => 'datetime',
        'consentimiento_token_expires_at' => 'datetime',
        'consentimiento_email_enviado_at' => 'datetime',
        'archivo_muerto' => 'boolean',
        'archivo_muerto_at' => 'datetime',
    ];

    /**
     * Dirección formateada: usa campos nuevos si existen, sino cae al domicilio legacy.
     */
    public function getDomicilioFormateadoAttribute(): string
    {
        if (!empty($this->calle)) {
            $calle = trim($this->calle);
            if (!empty($this->num_ext)) $calle .= ' #' . $this->num_ext;
            if (!empty($this->num_int)) $calle .= ' Int. ' . $this->num_int;
            $parts = [$calle];
            if (!empty($this->colonia)) $parts[] = 'Col. ' . $this->colonia;
            if (!empty($this->ciudad)) {
                $ciudad = $this->ciudad;
                if (!empty($this->estado_dir)) $ciudad .= ', ' . $this->estado_dir;
                $parts[] = $ciudad;
            }
            if (!empty($this->codigo_postal)) $parts[] = 'C.P. ' . $this->codigo_postal;
            return implode(', ', $parts);
        }
        return $this->domicilio ?? '';
    }

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la clínica
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Relación con la sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Relación muchos a muchos con clínicas (para pasaporte de salud)
     */
    public function clinicas()
    {
        return $this->belongsToMany(Clinica::class, 'clinica_paciente')
            ->using(ClinicaPaciente::class)
            ->withPivot(
                'sucursal_id',
                'user_id',
                'vinculado_at',
                'portal_visible_citas',
                'portal_visible_datos_basicos',
                'portal_visible_expediente_resumen',
                'motivo_consulta',
                'tipo_paciente',
                'numero_expediente'
            )
            ->withTimestamps();
    }

    /**
     * Tras cargar la relación clinicas, expone motivo_consulta, tipo_paciente y numero_expediente 
     * del vínculo con la clínica indicada (fallback a columnas en pacientes para datos legacy).
     */
    public function mergeClinicaPivotAttributes(?int $clinicaId): void
    {
        if (! $clinicaId || ! $this->relationLoaded('clinicas')) {
            return;
        }
        $motivoLegacy = $this->getAttribute('motivo_consulta');
        $tipoLegacy = $this->getAttribute('tipo_paciente');
        $registroGlobal = $this->getAttribute('registro');

        $row = $this->clinicas->firstWhere('id', $clinicaId);
        $pivot = $row?->pivot;

        $motivoPivot = $pivot?->motivo_consulta;
        $usarMotivo = $motivoPivot !== null && trim((string) $motivoPivot) !== '';
        $this->setAttribute('motivo_consulta', $usarMotivo ? $motivoPivot : $motivoLegacy);

        $tipoPivot = $pivot?->tipo_paciente;
        $usarTipo = $tipoPivot !== null && trim((string) $tipoPivot) !== '';
        $this->setAttribute('tipo_paciente', $usarTipo ? $tipoPivot : $tipoLegacy);

        // numero_expediente local de la clínica (fallback al registro global)
        $expedientePivot = $pivot?->numero_expediente;
        $usarExpediente = $expedientePivot !== null && trim((string) $expedientePivot) !== '';
        $this->setAttribute('numero_expediente', $usarExpediente ? $expedientePivot : $registroGlobal);
    }

    /**
     * El paciente está vinculado al workspace solo si existe fila en clinica_paciente (no se usa pacientes.clinica_id).
     */
    public function belongsToClinicaWorkspace(?int $clinicaId): bool
    {
        if (! $clinicaId) {
            return false;
        }

        return $this->clinicas()->where('clinicas.id', $clinicaId)->exists();
    }

    /**
     * Pacientes con vínculo activo en clinica_paciente para la clínica indicada.
     *
     * @param  int|null  $pivotSucursalId  Si se indica, el vínculo pivot debe tener esa sucursal_id.
     */
    public function scopeForClinicaWorkspace($query, ?int $clinicaId, $pivotSucursalId = null)
    {
        if (! $clinicaId) {
            return $query->whereRaw('1 = 0');
        }
        $table = $query->getModel()->getTable();

        return $query->whereExists(function ($sub) use ($clinicaId, $pivotSucursalId, $table) {
            $sub->selectRaw('1')
                ->from('clinica_paciente')
                ->whereColumn('clinica_paciente.paciente_id', $table.'.id')
                ->where('clinica_paciente.clinica_id', $clinicaId);
            if ($pivotSucursalId !== null && $pivotSucursalId !== '') {
                $sub->where('clinica_paciente.sucursal_id', (int) $pivotSucursalId);
            }
        });
    }

    /**
     * Relación con los permisos otorgados sobre este paciente
     */
    public function permissions()
    {
        return $this->morphMany(UserPermission::class, 'permissionable');
    }

    /**
     * Relación con los expedientes del paciente
     */
    public function expedientes()
    {
        return $this->hasMany(ReporteFinal::class);
    }

    /**
     * Relación con los reportes clínicos
     */
    public function clinicos()
    {
        return $this->hasMany(Clinico::class);
    }

    /**
     * Relación con los reportes de esfuerzo
     */
    public function esfuerzos()
    {
        return $this->hasMany(Esfuerzo::class);
    }

    /**
     * Relación con los reportes de estratificación
     */
    public function estratificaciones()
    {
        return $this->hasMany(Estratificacion::class);
    }

    /**
     * Relación con los reportes nutricionales
     */
    public function reporteNutris()
    {
        return $this->hasMany(ReporteNutri::class);
    }

    /**
     * Relación con los reportes psicológicos
     */
    public function reportePsicos()
    {
        return $this->hasMany(ReportePsico::class);
    }

    /**
     * Relación con los reportes de fisioterapia
     */
    public function reporteFisios()
    {
        return $this->hasMany(ReporteFisio::class);
    }

    /**
     * Relación con las citas del paciente
     */
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * Relación con los expedientes pulmonares
     */
    public function expedientesPulmonares()
    {
        return $this->hasMany(ExpedientePulmonar::class);
    }

    /**
     * Relación con los pagos del paciente
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Obtener el total de pagos realizados por el paciente
     */
    public function getTotalPagosAttribute()
    {
        return $this->pagos->sum(function($pago) {
            return (float) $pago->monto;
        });
    }

    /**
     * Obtener el saldo pendiente del paciente
     * Por ahora retorna 0, pero puede calcularse basado en tratamientos/servicios
     */
    public function getSaldoPendienteAttribute()
    {
        // TODO: Implementar lógica cuando se tenga sistema de costos de tratamientos
        // Por ahora solo retornamos el total pagado como referencia
        return 0;
    }

    /**
     * Obtener el último pago realizado
     */
    public function getUltimoPagoAttribute()
    {
        return $this->pagos()->latest()->first();
    }

    /**
     * Verificar si el paciente tiene pagos registrados
     */
    public function tienePagos(): bool
    {
        return $this->pagos()->exists();
    }

    /**
     * Verificar si el paciente es de tipo cardíaco
     */
    public function isCardiaco(): bool
    {
        return $this->tipo_paciente === 'cardiaca' || $this->tipo_paciente === 'ambos';
    }

    /**
     * Verificar si el paciente es de tipo pulmonar
     */
    public function isPulmonar(): bool
    {
        return $this->tipo_paciente === 'pulmonar' || $this->tipo_paciente === 'ambos';
    }

    /**
     * Verificar si el paciente es de tipo fisioterapia
     */
    public function isFisioterapia(): bool
    {
        return $this->tipo_paciente === 'fisioterapia';
    }

    /**
     * Obtener el tipo de paciente formateado
     */
    public function getTipoPacienteFormattedAttribute(): string
    {
        return match($this->tipo_paciente) {
            'cardiaca' => 'Rehabilitación Cardíaca',
            'pulmonar' => 'Rehabilitación Pulmonar',
            'fisioterapia' => 'Fisioterapia',
            'ambos' => 'Ambos Tipos',
            default => 'No especificado'
        };
    }

}
