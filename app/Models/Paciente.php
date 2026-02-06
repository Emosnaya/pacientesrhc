<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory, Auditable;

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
        'talla',
        'peso',
        'cintura',
        'imc',
        'diagnostico',
        'medicamentos',
        'envio',
        'tipo_paciente',
        'color',
        'user_id',
        'clinica_id',
        'sucursal_id'
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
        'email' => 'encrypted',
        'domicilio' => 'encrypted',
        'diagnostico' => 'encrypted',
        'medicamentos' => 'encrypted',
        'fechaNacimiento' => 'date',
    ];

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
