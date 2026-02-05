<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PruebaEsfuerzoPulmonar extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'fecha_realizacion',
        'diagnosticos',
        'oxigeno_suplementario',
        'oxigeno_litros',
        'peso',
        'talla',
        'predicho_vo2',
        'predicho_mets',
        'fcmax_100',
        'fcmax_65',
        'fcmax_75',
        'fcmax_85',
        'basal_fc',
        'basal_tas',
        'basal_tad',
        'basal_saturacion',
        'basal_borg_disnea',
        'basal_borg_fatiga',
        'max_fc_pico',
        'max_tas',
        'max_tad',
        'max_saturacion',
        'max_borg_disnea',
        'max_borg_fatiga',
        'max_velocidad_kmh',
        'max_inclinacion',
        'max_mets',
        'motivo_detencion',
        'rec1_fc',
        'rec1_tas',
        'rec1_tad',
        'rec1_saturacion',
        'rec1_borg_disnea',
        'rec1_borg_fatiga',
        'rec3_fc',
        'rec3_tas',
        'rec3_tad',
        'rec3_saturacion',
        'rec3_borg_disnea',
        'rec3_borg_fatiga',
        'rec5_fc',
        'rec5_tas',
        'rec5_tad',
        'rec5_saturacion',
        'rec5_borg_disnea',
        'rec5_borg_fatiga',
        'etapa_maxima',
        'vel_etapa',
        'inclinacion_etapa',
        'mets_equivalentes',
        'vo2_equivalente',
        'fc_pico_alcanzado',
        'saturacion_minima',
        'borg_max_disnea',
        'borg_max_fatiga',
        'porcentaje_fcmax',
        'porcentaje_mets',
        'porcentaje_vo2',
        'clase_funcional',
        'interpretacion',
        'plan_sesiones',
        'plan_tiempo',
        'plan_oxigeno_litros',
        'plan_borg_modificado',
        'plan_fc_inicial_min',
        'plan_fc_inicial_max',
        'plan_fc_no_rebasar',
        'plan_banda_sin_fin',
        'plan_ergometro_brazos',
        'plan_bicicleta_estatica',
        'plan_banda_velocidad',
        'plan_banda_tiempo',
        'plan_banda_resistencia',
        'plan_ergometro_velocidad',
        'plan_ergometro_resistencia',
        'plan_bicicleta_velocidad',
        'plan_bicicleta_resistencia',
        'plan_manejo_complementario',
    ];

    protected $casts = [
        'fecha_realizacion' => 'date',
        'oxigeno_suplementario' => 'boolean',
        'plan_banda_sin_fin' => 'boolean',
        'plan_ergometro_brazos' => 'boolean',
        'plan_bicicleta_estatica' => 'boolean',
        // Campos sensibles cifrados
        'interpretacion' => 'encrypted',
        'plan_manejo_complementario' => 'encrypted',
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
