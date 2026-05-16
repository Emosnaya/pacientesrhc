<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteNutricionPlan extends Model
{
    use HasFactory;

    protected $table = 'paciente_nutricion_planes';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'user_id',
        'titulo',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
        'kcal_objetivo',
        'macros',
        'plan_alimenticio',
        'plan_ejercicio',
        'notas',
        'estado',
        'version',
        'publicado_en_portal',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'kcal_objetivo' => 'decimal:2',
        'macros' => 'array',
        'plan_alimenticio' => 'array',
        'plan_ejercicio' => 'array',
        'publicado_en_portal' => 'boolean',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
