<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Paciente extends Model
{
    use HasFactory, Auditable, Notifiable;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
        'archivo_muerto',
        'archivo_muerto_at',
        'archivo_muerto_motivo'
    ];

    protected $appends = ['domicilio_formateado'];

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
        'email' => 'encrypted',
        'domicilio' => 'encrypted',
        'calle' => 'encrypted',
        'colonia' => 'encrypted',
        'diagnostico' => 'encrypted',
        'medicamentos' => 'encrypted',
        'motivo_consulta' => 'encrypted',
        'alergias' => 'encrypted',
        'fechaNacimiento' => 'date',
        'aviso_privacidad_aceptado_at' => 'datetime',
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
