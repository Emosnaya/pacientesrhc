<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinicaFisioterapia extends Model
{
    use HasFactory;

    protected $table = 'historia_clinica_fisioterapia';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'fecha',
        'hora',
        'ocupacion',
        'motivo_consulta',
        'padecimiento_actual',
        'antecedentes_heredofamiliares',
        'antecedentes_personales_patologicos',
        'antecedentes_personales_no_patologicos',
        'antecedentes_quirurgicos_traumaticos',
        'signos_vitales',
        'inspeccion',
        'palpacion',
        'rango_movimiento',
        'fuerza_muscular',
        'pruebas_especiales',
        'diagnostico_medico',
        'diagnostico_fisioterapeutico',
        'objetivos_tratamiento',
        'modalidades_terapeuticas',
        'educacion_paciente',
        'pronostico',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
