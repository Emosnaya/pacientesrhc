<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

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
        'user_id'
    ];

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

}
