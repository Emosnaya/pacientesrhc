<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaClinicaSoapNutricional extends Model
{
    use HasFactory;

    protected $table = 'nota_clinica_soap_nutricional';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_elaboracion',
        'numero_seguimiento',
        'nutriologo_evaluador',
        'encargado_turno',
        'subjetivo',
        'objetivo',
        'analisis',
        'plan',
    ];

    protected $casts = [
        'fecha_elaboracion' => 'date',
        'subjetivo' => 'array',
        'objetivo' => 'array',
        'analisis' => 'array',
        'plan' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
