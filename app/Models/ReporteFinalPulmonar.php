<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteFinalPulmonar extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'tipo_exp',
        'fecha_inicio',
        'fecha_termino',
        'diagnostico',
        'descripcion_programa',
        'pe_inicial_rubro',
        'pe_inicial_fc_basal',
        'pe_inicial_spo2',
        'pe_inicial_litros_oxigeno',
        'pe_inicial_carga_maxima',
        'pe_inicial_vo2_pico',
        'pe_inicial_vo2_pico_ml',
        'pe_inicial_fc_pico',
        'pe_inicial_porcentaje_fcmax',
        'pe_inicial_spo2_minima',
        'pe_inicial_borg_disnea',
        'pe_inicial_borg_fatiga',
        'pe_inicial_dinamometria',
        'pe_inicial_sit_to_stand_30seg',
        'pe_final_rubro',
        'pe_final_fc_basal',
        'pe_final_spo2',
        'pe_final_litros_oxigeno',
        'pe_final_carga_maxima',
        'pe_final_vo2_pico',
        'pe_final_vo2_pico_ml',
        'pe_final_fc_pico',
        'pe_final_porcentaje_fcmax',
        'pe_final_spo2_minima',
        'pe_final_borg_disnea',
        'pe_final_borg_fatiga',
        'pe_final_dinamometria',
        'pe_final_sit_to_stand_30seg',
        'resultados_clinicos',
        'plan'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date'
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
}
