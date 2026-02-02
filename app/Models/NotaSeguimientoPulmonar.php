<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaSeguimientoPulmonar extends Model
{
    use HasFactory;

    protected $table = 'nota_seguimiento_pulmonar';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'tipo_exp',
        'expediente_pulmonar_id',
        'fecha_consulta',
        'hora_consulta',
        'ficha_identificacion',
        'diagnosticos',
        's_subjetivo',
        'o_objetivo',
        'a_apreciacion',
        'p_plan',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'hora_consulta' => 'datetime:H:i',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function expedientePulmonar()
    {
        return $this->belongsTo(ExpedientePulmonar::class);
    }
}
