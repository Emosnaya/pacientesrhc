<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'clinica_id',
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'estado',
        'codigo_postal',
        'es_principal',
        'activa',
        'notas'
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activa' => 'boolean'
    ];

    /**
     * Relación con Clínica
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Relación con Usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con Pacientes
     */
    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }

    /**
     * Relación con Citas
     */
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * Scope para sucursales activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para sucursal principal
     */
    public function scopePrincipal($query)
    {
        return $query->where('es_principal', true);
    }

    /**
     * Scope para filtrar por clínica
     */
    public function scopeDeClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * Generar código único si no existe
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sucursal) {
            if (empty($sucursal->codigo)) {
                $sucursal->codigo = static::generarCodigo($sucursal->clinica_id);
            }
        });
    }

    /**
     * Generar código único para la sucursal
     */
    private static function generarCodigo($clinicaId)
    {
        $count = static::where('clinica_id', $clinicaId)->count();
        return 'SUC-' . str_pad($clinicaId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
