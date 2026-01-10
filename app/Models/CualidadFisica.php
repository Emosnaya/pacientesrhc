<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CualidadFisica extends Model
{
    use HasFactory;

    protected $table = 'cualidades_fisicas';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'fecha_prueba_inicial',
        'fecha_prueba_final',
        
        // Signos vitales inicial
        'ta_inicial',
        'fc_inicial',
        'sato2_inicial',
        'talla_inicial',
        'peso_inicial',
        'perimetria_abdominal_inicial',
        
        // Signos vitales final
        'ta_final',
        'fc_final',
        'sato2_final',
        'talla_final',
        'peso_final',
        'perimetria_abdominal_final',
        
        // Antecedentes
        'antecedentes_musculo_esqueleticos',
        
        // Dinamometría inicial
        'dinamometria_mano_derecha_toma1_inicial',
        'dinamometria_mano_derecha_toma2_inicial',
        'dinamometria_mano_derecha_toma3_inicial',
        'dinamometria_mano_derecha_promedio_inicial',
        'dinamometria_mano_izquierda_toma1_inicial',
        'dinamometria_mano_izquierda_toma2_inicial',
        'dinamometria_mano_izquierda_toma3_inicial',
        'dinamometria_mano_izquierda_promedio_inicial',
        'observaciones_dinamometria_inicial',
        
        // Dinamometría final
        'dinamometria_mano_derecha_toma1_final',
        'dinamometria_mano_derecha_toma2_final',
        'dinamometria_mano_derecha_toma3_final',
        'dinamometria_mano_derecha_promedio_final',
        'dinamometria_mano_izquierda_toma1_final',
        'dinamometria_mano_izquierda_toma2_final',
        'dinamometria_mano_izquierda_toma3_final',
        'dinamometria_mano_izquierda_promedio_final',
        'observaciones_dinamometria_final',
        
        // Fuerza MRC inicial
        'abd_hombro_derecho_inicial',
        'abd_hombro_izquierdo_inicial',
        'flexion_codo_derecho_inicial',
        'flexion_codo_izquierdo_inicial',
        'extension_muneca_derecho_inicial',
        'extension_muneca_izquierdo_inicial',
        'extension_cadera_derecho_inicial',
        'extension_cadera_izquierdo_inicial',
        'extension_rodilla_derecho_inicial',
        'extension_rodilla_izquierdo_inicial',
        'dorsiflexion_derecho_inicial',
        'dorsiflexion_izquierdo_inicial',
        'puntaje_final_mrc_inicial',
        'observaciones_mrc_inicial',
        
        // Fuerza MRC final
        'abd_hombro_derecho_final',
        'abd_hombro_izquierdo_final',
        'flexion_codo_derecho_final',
        'flexion_codo_izquierdo_final',
        'extension_muneca_derecho_final',
        'extension_muneca_izquierdo_final',
        'extension_cadera_derecho_final',
        'extension_cadera_izquierdo_final',
        'extension_rodilla_derecho_final',
        'extension_rodilla_izquierdo_final',
        'dorsiflexion_derecho_final',
        'dorsiflexion_izquierdo_final',
        'puntaje_final_mrc_final',
        'observaciones_mrc_final',
        
        // Balance inicial
        'balance_romberg_derecha_oa_inicial',
        'balance_romberg_derecha_oc_inicial',
        'balance_romberg_izquierda_oa_inicial',
        'balance_romberg_izquierda_oc_inicial',
        'balance_semitandem_derecha_oa_inicial',
        'balance_semitandem_derecha_oc_inicial',
        'balance_semitandem_izquierda_oa_inicial',
        'balance_semitandem_izquierda_oc_inicial',
        'balance_tandem_derecha_oa_inicial',
        'balance_tandem_derecha_oc_inicial',
        'balance_tandem_izquierda_oa_inicial',
        'balance_tandem_izquierda_oc_inicial',
        'balance_monopedal_derecha_oa_inicial',
        'balance_monopedal_derecha_oc_inicial',
        'balance_monopedal_izquierda_oa_inicial',
        'balance_monopedal_izquierda_oc_inicial',
        'observaciones_balance_inicial',
        
        // Balance final
        'balance_romberg_derecha_oa_final',
        'balance_romberg_derecha_oc_final',
        'balance_romberg_izquierda_oa_final',
        'balance_romberg_izquierda_oc_final',
        'balance_semitandem_derecha_oa_final',
        'balance_semitandem_derecha_oc_final',
        'balance_semitandem_izquierda_oa_final',
        'balance_semitandem_izquierda_oc_final',
        'balance_tandem_derecha_oa_final',
        'balance_tandem_derecha_oc_final',
        'balance_tandem_izquierda_oa_final',
        'balance_tandem_izquierda_oc_final',
        'balance_monopedal_derecha_oa_final',
        'balance_monopedal_derecha_oc_final',
        'balance_monopedal_izquierda_oa_final',
        'balance_monopedal_izquierda_oc_final',
        'observaciones_balance_final',
        
        // Tests
        'sentadillas_realizadas_inicial',
        'tiempo_5_sentadillas_inicial',
        'sentadillas_realizadas_final',
        'tiempo_5_sentadillas_final',
        
        'tipo_lagartija_inicial',
        'lagartijas_realizadas_a_inicial',
        'lagartijas_realizadas_b_inicial',
        'lagartijas_realizadas_c_inicial',
        'observaciones_lagartijas_inicial',
        'tipo_lagartija_final',
        'lagartijas_realizadas_a_final',
        'lagartijas_realizadas_b_final',
        'lagartijas_realizadas_c_final',
        'observaciones_lagartijas_final',
        
        'abdominales_realizadas_inicial',
        'observaciones_abdominales_inicial',
        'abdominales_realizadas_final',
        'observaciones_abdominales_final',
        
        'sit_reach_toma1_inicial',
        'sit_reach_toma2_inicial',
        'sit_reach_toma3_inicial',
        'sit_reach_promedio_inicial',
        'observaciones_sit_and_reach_inicial',
        'sit_reach_toma1_final',
        'sit_reach_toma2_final',
        'sit_reach_toma3_final',
        'sit_reach_promedio_final',
        'observaciones_sit_and_reach_final',
        
        'back_scratch_lado_derecho_inicial',
        'back_scratch_lado_izquierdo_inicial',
        'observaciones_back_scratch_inicial',
        'back_scratch_lado_derecho_final',
        'back_scratch_lado_izquierdo_final',
        'observaciones_back_scratch_final',
        
        // Escala de Tinetti (22 campos específicos inicial + 22 final)
        'tinetti_iniciacion_vacilaciones_inicial',
        'tinetti_iniciacion_no_vacila_inicial',
        'tinetti_long_der_no_sobrepasa_inicial',
        'tinetti_long_der_sobrepasa_inicial',
        'tinetti_long_der_no_separa_inicial',
        'tinetti_long_der_separa_inicial',
        'tinetti_long_izq_no_sobrepasa_inicial',
        'tinetti_long_izq_sobrepasa_inicial',
        'tinetti_long_izq_no_separa_inicial',
        'tinetti_long_izq_separa_inicial',
        'tinetti_simetria_no_igual_inicial',
        'tinetti_simetria_igual_inicial',
        'tinetti_fluidez_paradas_inicial',
        'tinetti_fluidez_continuos_inicial',
        'tinetti_trayectoria_grave_inicial',
        'tinetti_trayectoria_leve_inicial',
        'tinetti_trayectoria_sin_inicial',
        'tinetti_tronco_balanceo_inicial',
        'tinetti_tronco_flexiona_inicial',
        'tinetti_tronco_no_balancea_inicial',
        'tinetti_postura_separados_inicial',
        'tinetti_postura_juntos_inicial',
        'tinetti_total_marcha_inicial',
        
        'tinetti_iniciacion_vacilaciones_final',
        'tinetti_iniciacion_no_vacila_final',
        'tinetti_long_der_no_sobrepasa_final',
        'tinetti_long_der_sobrepasa_final',
        'tinetti_long_der_no_separa_final',
        'tinetti_long_der_separa_final',
        'tinetti_long_izq_no_sobrepasa_final',
        'tinetti_long_izq_sobrepasa_final',
        'tinetti_long_izq_no_separa_final',
        'tinetti_long_izq_separa_final',
        'tinetti_simetria_no_igual_final',
        'tinetti_simetria_igual_final',
        'tinetti_fluidez_paradas_final',
        'tinetti_fluidez_continuos_final',
        'tinetti_trayectoria_grave_final',
        'tinetti_trayectoria_leve_final',
        'tinetti_trayectoria_sin_final',
        'tinetti_tronco_balanceo_final',
        'tinetti_tronco_flexiona_final',
        'tinetti_tronco_no_balancea_final',
        'tinetti_postura_separados_final',
        'tinetti_postura_juntos_final',
        'tinetti_total_marcha_final',
        
        // Evaluación de equilibrio (26 campos específicos inicial + 26 final)
        'equilibrio_sentado_inclina_inicial',
        'equilibrio_sentado_seguro_inicial',
        'equilibrio_levantarse_imposible_inicial',
        'equilibrio_levantarse_brazos_inicial',
        'equilibrio_levantarse_solo_inicial',
        'equilibrio_intentos_incapaz_inicial',
        'equilibrio_intentos_mas_uno_inicial',
        'equilibrio_intentos_un_solo_inicial',
        'equilibrio_bipe_inm_inestable_inicial',
        'equilibrio_bipe_inm_andador_inicial',
        'equilibrio_bipe_inm_estable_inicial',
        'equilibrio_bipe_inestable_inicial',
        'equilibrio_bipe_apoyo_amplio_inicial',
        'equilibrio_bipe_apoyo_estrecho_inicial',
        'equilibrio_empujar_caerse_inicial',
        'equilibrio_empujar_tambalea_inicial',
        'equilibrio_empujar_estable_inicial',
        'equilibrio_ojos_inestable_inicial',
        'equilibrio_ojos_estable_inicial',
        'equilibrio_vuelta_discontinuos_inicial',
        'equilibrio_vuelta_continuos_inicial',
        'equilibrio_vuelta_inestable_inicial',
        'equilibrio_vuelta_estable_inicial',
        'equilibrio_sentarse_inseguro_inicial',
        'equilibrio_sentarse_brazos_inicial',
        'equilibrio_sentarse_seguro_inicial',
        'eval_eq_total_equilibrio_inicial',
        'eval_eq_total_puntos_escala_inicial',
        
        'equilibrio_sentado_inclina_final',
        'equilibrio_sentado_seguro_final',
        'equilibrio_levantarse_imposible_final',
        'equilibrio_levantarse_brazos_final',
        'equilibrio_levantarse_solo_final',
        'equilibrio_intentos_incapaz_final',
        'equilibrio_intentos_mas_uno_final',
        'equilibrio_intentos_un_solo_final',
        'equilibrio_bipe_inm_inestable_final',
        'equilibrio_bipe_inm_andador_final',
        'equilibrio_bipe_inm_estable_final',
        'equilibrio_bipe_inestable_final',
        'equilibrio_bipe_apoyo_amplio_final',
        'equilibrio_bipe_apoyo_estrecho_final',
        'equilibrio_empujar_caerse_final',
        'equilibrio_empujar_tambalea_final',
        'equilibrio_empujar_estable_final',
        'equilibrio_ojos_inestable_final',
        'equilibrio_ojos_estable_final',
        'equilibrio_vuelta_discontinuos_final',
        'equilibrio_vuelta_continuos_final',
        'equilibrio_vuelta_inestable_final',
        'equilibrio_vuelta_estable_final',
        'equilibrio_sentarse_inseguro_final',
        'equilibrio_sentarse_brazos_final',
        'equilibrio_sentarse_seguro_final',
        'eval_eq_total_equilibrio_final',
        'eval_eq_total_puntos_escala_final',
        
        'observaciones',
    ];

    protected $casts = [
        'fecha_prueba_inicial' => 'date',
        'fecha_prueba_final' => 'date',
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
