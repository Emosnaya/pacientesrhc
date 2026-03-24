<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'folio',
        'paciente_id',
        'user_id',
        'sucursal_id',
        'clinica_id',
        'fecha',
        'diagnostico_principal',
        'indicaciones_generales',
        // Campos de firma electrónica
        'firma_digital',
        'cadena_original',
        'firmada_at',
        'numero_serie_certificado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'firmada_at' => 'datetime',
        'diagnostico_principal' => 'encrypted',
        'indicaciones_generales' => 'encrypted',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function medicamentos()
    {
        return $this->hasMany(RecetaMedicamento::class)->orderBy('orden');
    }
}
