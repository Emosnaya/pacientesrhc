<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinicaFisioterapia extends Model
{
    use HasFactory, Auditable;

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
        // Cifrado de campos sensibles
        'motivo_consulta' => 'encrypted',
        'padecimiento_actual' => 'encrypted',
        'antecedentes_heredofamiliares' => 'encrypted',
        'antecedentes_personales_patologicos' => 'encrypted',
        'antecedentes_personales_no_patologicos' => 'encrypted',
        'antecedentes_quirurgicos_traumaticos' => 'encrypted',
        'diagnostico_medico' => 'encrypted',
        'diagnostico_fisioterapeutico' => 'encrypted',
        'objetivos_tratamiento' => 'encrypted',
        'pronostico' => 'encrypted',
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
