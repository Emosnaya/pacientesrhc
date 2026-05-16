<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteNutricionSeguimiento extends Model
{
    use HasFactory;

    protected $table = 'paciente_nutricion_seguimientos';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'plan_id',
        'user_id',
        'fecha',
        'comidas',
        'agua_ml',
        'ejercicio',
        'habitos',
        'cumplio_plan',
        'energia_nivel',
        'hambre_nivel',
        'notas_paciente',
        'notas_clinica',
        'completado',
        'capturado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'comidas' => 'array',
        'ejercicio' => 'array',
        'habitos' => 'array',
        'cumplio_plan' => 'boolean',
        'completado' => 'boolean',
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

    public function plan()
    {
        return $this->belongsTo(PacienteNutricionPlan::class, 'plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
