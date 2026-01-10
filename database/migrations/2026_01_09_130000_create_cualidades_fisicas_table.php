<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cualidades_fisicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('cascade');
            
            // Datos generales
            $table->date('fecha_prueba_inicial')->nullable();
            $table->date('fecha_prueba_final')->nullable();
            
            // Signos vitales prueba inicial
            $table->string('ta_inicial', 10)->nullable(); // Tensión arterial
            $table->string('fc_inicial', 10)->nullable(); // Frecuencia cardiaca
            $table->string('sato2_inicial', 10)->nullable(); // Saturación O2
            $table->string('talla_inicial', 10)->nullable();
            $table->string('peso_inicial', 10)->nullable();
            $table->string('perimetria_abdominal_inicial', 10)->nullable();
            
            // Signos vitales prueba final
            $table->string('ta_final', 10)->nullable();
            $table->string('fc_final', 10)->nullable();
            $table->string('sato2_final', 10)->nullable();
            $table->string('talla_final', 10)->nullable();
            $table->string('peso_final', 10)->nullable();
            $table->string('perimetria_abdominal_final', 10)->nullable();
            
            // Antecedentes musculo esqueléticos
            $table->text('antecedentes_musculo_esqueleticos')->nullable();
            
            // DINAMOMETRÍA - Prueba inicial
            $table->string('dinamometria_mano_derecha_toma1_inicial', 10)->nullable();
            $table->string('dinamometria_mano_derecha_toma2_inicial', 10)->nullable();
            $table->string('dinamometria_mano_derecha_toma3_inicial', 10)->nullable();
            $table->string('dinamometria_mano_derecha_promedio_inicial', 10)->nullable();
            
            $table->string('dinamometria_mano_izquierda_toma1_inicial', 10)->nullable();
            $table->string('dinamometria_mano_izquierda_toma2_inicial', 10)->nullable();
            $table->string('dinamometria_mano_izquierda_toma3_inicial', 10)->nullable();
            $table->string('dinamometria_mano_izquierda_promedio_inicial', 10)->nullable();
            $table->text('observaciones_dinamometria_inicial')->nullable();
            
            // DINAMOMETRÍA - Prueba final
            $table->string('dinamometria_mano_derecha_toma1_final', 10)->nullable();
            $table->string('dinamometria_mano_derecha_toma2_final', 10)->nullable();
            $table->string('dinamometria_mano_derecha_toma3_final', 10)->nullable();
            $table->string('dinamometria_mano_derecha_promedio_final', 10)->nullable();
            
            $table->string('dinamometria_mano_izquierda_toma1_final', 10)->nullable();
            $table->string('dinamometria_mano_izquierda_toma2_final', 10)->nullable();
            $table->string('dinamometria_mano_izquierda_toma3_final', 10)->nullable();
            $table->string('dinamometria_mano_izquierda_promedio_final', 10)->nullable();
            $table->text('observaciones_dinamometria_final')->nullable();
            
            // FUERZA GLOBAL (MRC) - Prueba inicial
            $table->tinyInteger('abd_hombro_derecho_inicial')->nullable();
            $table->tinyInteger('abd_hombro_izquierdo_inicial')->nullable();
            $table->tinyInteger('flexion_codo_derecho_inicial')->nullable();
            $table->tinyInteger('flexion_codo_izquierdo_inicial')->nullable();
            $table->tinyInteger('extension_muneca_derecho_inicial')->nullable();
            $table->tinyInteger('extension_muneca_izquierdo_inicial')->nullable();
            $table->tinyInteger('extension_cadera_derecho_inicial')->nullable();
            $table->tinyInteger('extension_cadera_izquierdo_inicial')->nullable();
            $table->tinyInteger('extension_rodilla_derecho_inicial')->nullable();
            $table->tinyInteger('extension_rodilla_izquierdo_inicial')->nullable();
            $table->tinyInteger('dorsiflexion_derecho_inicial')->nullable();
            $table->tinyInteger('dorsiflexion_izquierdo_inicial')->nullable();
            $table->tinyInteger('puntaje_final_mrc_inicial')->nullable();
            $table->text('observaciones_mrc_inicial')->nullable();
            
            // FUERZA GLOBAL (MRC) - Prueba final
            $table->tinyInteger('abd_hombro_derecho_final')->nullable();
            $table->tinyInteger('abd_hombro_izquierdo_final')->nullable();
            $table->tinyInteger('flexion_codo_derecho_final')->nullable();
            $table->tinyInteger('flexion_codo_izquierdo_final')->nullable();
            $table->tinyInteger('extension_muneca_derecho_final')->nullable();
            $table->tinyInteger('extension_muneca_izquierdo_final')->nullable();
            $table->tinyInteger('extension_cadera_derecho_final')->nullable();
            $table->tinyInteger('extension_cadera_izquierdo_final')->nullable();
            $table->tinyInteger('extension_rodilla_derecho_final')->nullable();
            $table->tinyInteger('extension_rodilla_izquierdo_final')->nullable();
            $table->tinyInteger('dorsiflexion_derecho_final')->nullable();
            $table->tinyInteger('dorsiflexion_izquierdo_final')->nullable();
            $table->tinyInteger('puntaje_final_mrc_final')->nullable();
            $table->text('observaciones_mrc_final')->nullable();
            
            // PRUEBA DE BALANCE - Prueba inicial
            $table->string('balance_romberg_derecha_oa_inicial', 10)->nullable();
            $table->string('balance_romberg_derecha_oc_inicial', 10)->nullable();
            $table->string('balance_romberg_izquierda_oa_inicial', 10)->nullable();
            $table->string('balance_romberg_izquierda_oc_inicial', 10)->nullable();
            
            $table->string('balance_semitandem_derecha_oa_inicial', 10)->nullable();
            $table->string('balance_semitandem_derecha_oc_inicial', 10)->nullable();
            $table->string('balance_semitandem_izquierda_oa_inicial', 10)->nullable();
            $table->string('balance_semitandem_izquierda_oc_inicial', 10)->nullable();
            
            $table->string('balance_tandem_derecha_oa_inicial', 10)->nullable();
            $table->string('balance_tandem_derecha_oc_inicial', 10)->nullable();
            $table->string('balance_tandem_izquierda_oa_inicial', 10)->nullable();
            $table->string('balance_tandem_izquierda_oc_inicial', 10)->nullable();
            
            $table->string('balance_monopedal_derecha_oa_inicial', 10)->nullable();
            $table->string('balance_monopedal_derecha_oc_inicial', 10)->nullable();
            $table->string('balance_monopedal_izquierda_oa_inicial', 10)->nullable();
            $table->string('balance_monopedal_izquierda_oc_inicial', 10)->nullable();
            $table->text('observaciones_balance_inicial')->nullable();
            
            // PRUEBA DE BALANCE - Prueba final
            $table->string('balance_romberg_derecha_oa_final', 10)->nullable();
            $table->string('balance_romberg_derecha_oc_final', 10)->nullable();
            $table->string('balance_romberg_izquierda_oa_final', 10)->nullable();
            $table->string('balance_romberg_izquierda_oc_final', 10)->nullable();
            
            $table->string('balance_semitandem_derecha_oa_final', 10)->nullable();
            $table->string('balance_semitandem_derecha_oc_final', 10)->nullable();
            $table->string('balance_semitandem_izquierda_oa_final', 10)->nullable();
            $table->string('balance_semitandem_izquierda_oc_final', 10)->nullable();
            
            $table->string('balance_tandem_derecha_oa_final', 10)->nullable();
            $table->string('balance_tandem_derecha_oc_final', 10)->nullable();
            $table->string('balance_tandem_izquierda_oa_final', 10)->nullable();
            $table->string('balance_tandem_izquierda_oc_final', 10)->nullable();
            
            $table->string('balance_monopedal_derecha_oa_final', 10)->nullable();
            $table->string('balance_monopedal_derecha_oc_final', 10)->nullable();
            $table->string('balance_monopedal_izquierda_oa_final', 10)->nullable();
            $table->string('balance_monopedal_izquierda_oc_final', 10)->nullable();
            $table->text('observaciones_balance_final')->nullable();
            
            // SIT TO STAND TEST 80°
            $table->integer('sentadillas_realizadas_inicial')->nullable();
            $table->string('tiempo_5_sentadillas_inicial', 10)->nullable();
            $table->integer('sentadillas_realizadas_final')->nullable();
            $table->string('tiempo_5_sentadillas_final', 10)->nullable();
            
            // TEST DE LAGARTIJAS
            $table->enum('tipo_lagartija_inicial', ['respaldo_pared', 'media_lagartija', 'lagartija_completa'])->nullable();
            $table->integer('lagartijas_realizadas_a_inicial')->nullable();
            $table->integer('lagartijas_realizadas_b_inicial')->nullable();
            $table->integer('lagartijas_realizadas_c_inicial')->nullable();
            $table->text('observaciones_lagartijas_inicial')->nullable();
            
            $table->enum('tipo_lagartija_final', ['respaldo_pared', 'media_lagartija', 'lagartija_completa'])->nullable();
            $table->integer('lagartijas_realizadas_a_final')->nullable();
            $table->integer('lagartijas_realizadas_b_final')->nullable();
            $table->integer('lagartijas_realizadas_c_final')->nullable();
            $table->text('observaciones_lagartijas_final')->nullable();
            
            // TEST DE ABDOMINALES
            $table->integer('abdominales_realizadas_inicial')->nullable();
            $table->text('observaciones_abdominales_inicial')->nullable();
            $table->integer('abdominales_realizadas_final')->nullable();
            $table->text('observaciones_abdominales_final')->nullable();
            
            // SIT AND REACH
            $table->string('sit_reach_toma1_inicial', 10)->nullable();
            $table->string('sit_reach_toma2_inicial', 10)->nullable();
            $table->string('sit_reach_toma3_inicial', 10)->nullable();
            $table->string('sit_reach_promedio_inicial', 10)->nullable();
            $table->text('observaciones_sit_and_reach_inicial')->nullable();
            
            $table->string('sit_reach_toma1_final', 10)->nullable();
            $table->string('sit_reach_toma2_final', 10)->nullable();
            $table->string('sit_reach_toma3_final', 10)->nullable();
            $table->string('sit_reach_promedio_final', 10)->nullable();
            $table->text('observaciones_sit_and_reach_final')->nullable();
            
            // BACK SCRATCH (Prueba de rascado)
            $table->string('back_scratch_lado_derecho_inicial', 10)->nullable();
            $table->string('back_scratch_lado_izquierdo_inicial', 10)->nullable();
            $table->text('observaciones_back_scratch_inicial')->nullable();
            $table->string('back_scratch_lado_derecho_final', 10)->nullable();
            $table->string('back_scratch_lado_izquierdo_final', 10)->nullable();
            $table->text('observaciones_back_scratch_final')->nullable();
            
            // ============ ESCALA DE TINETTI ============
            // 22 campos específicos por tiempo (inicial/final) + totales
            // Prueba inicial
            $table->tinyInteger('tinetti_iniciacion_vacilaciones_inicial')->nullable();
            $table->tinyInteger('tinetti_iniciacion_no_vacila_inicial')->nullable();
            $table->tinyInteger('tinetti_long_der_no_sobrepasa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_der_sobrepasa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_der_no_separa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_der_separa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_izq_no_sobrepasa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_izq_sobrepasa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_izq_no_separa_inicial')->nullable();
            $table->tinyInteger('tinetti_long_izq_separa_inicial')->nullable();
            $table->tinyInteger('tinetti_simetria_no_igual_inicial')->nullable();
            $table->tinyInteger('tinetti_simetria_igual_inicial')->nullable();
            $table->tinyInteger('tinetti_fluidez_paradas_inicial')->nullable();
            $table->tinyInteger('tinetti_fluidez_continuos_inicial')->nullable();
            $table->tinyInteger('tinetti_trayectoria_grave_inicial')->nullable();
            $table->tinyInteger('tinetti_trayectoria_leve_inicial')->nullable();
            $table->tinyInteger('tinetti_trayectoria_sin_inicial')->nullable();
            $table->tinyInteger('tinetti_tronco_balanceo_inicial')->nullable();
            $table->tinyInteger('tinetti_tronco_flexiona_inicial')->nullable();
            $table->tinyInteger('tinetti_tronco_no_balancea_inicial')->nullable();
            $table->tinyInteger('tinetti_postura_separados_inicial')->nullable();
            $table->tinyInteger('tinetti_postura_juntos_inicial')->nullable();
            $table->tinyInteger('tinetti_total_marcha_inicial')->nullable();
            
            // Prueba final
            $table->tinyInteger('tinetti_iniciacion_vacilaciones_final')->nullable();
            $table->tinyInteger('tinetti_iniciacion_no_vacila_final')->nullable();
            $table->tinyInteger('tinetti_long_der_no_sobrepasa_final')->nullable();
            $table->tinyInteger('tinetti_long_der_sobrepasa_final')->nullable();
            $table->tinyInteger('tinetti_long_der_no_separa_final')->nullable();
            $table->tinyInteger('tinetti_long_der_separa_final')->nullable();
            $table->tinyInteger('tinetti_long_izq_no_sobrepasa_final')->nullable();
            $table->tinyInteger('tinetti_long_izq_sobrepasa_final')->nullable();
            $table->tinyInteger('tinetti_long_izq_no_separa_final')->nullable();
            $table->tinyInteger('tinetti_long_izq_separa_final')->nullable();
            $table->tinyInteger('tinetti_simetria_no_igual_final')->nullable();
            $table->tinyInteger('tinetti_simetria_igual_final')->nullable();
            $table->tinyInteger('tinetti_fluidez_paradas_final')->nullable();
            $table->tinyInteger('tinetti_fluidez_continuos_final')->nullable();
            $table->tinyInteger('tinetti_trayectoria_grave_final')->nullable();
            $table->tinyInteger('tinetti_trayectoria_leve_final')->nullable();
            $table->tinyInteger('tinetti_trayectoria_sin_final')->nullable();
            $table->tinyInteger('tinetti_tronco_balanceo_final')->nullable();
            $table->tinyInteger('tinetti_tronco_flexiona_final')->nullable();
            $table->tinyInteger('tinetti_tronco_no_balancea_final')->nullable();
            $table->tinyInteger('tinetti_postura_separados_final')->nullable();
            $table->tinyInteger('tinetti_postura_juntos_final')->nullable();
            $table->tinyInteger('tinetti_total_marcha_final')->nullable();
            
            // ============ EVALUACIÓN DE EQUILIBRIO ============
            // 26 campos específicos por tiempo (inicial/final) + totales
            // Prueba inicial
            $table->tinyInteger('equilibrio_sentado_inclina_inicial')->nullable();
            $table->tinyInteger('equilibrio_sentado_seguro_inicial')->nullable();
            $table->tinyInteger('equilibrio_levantarse_imposible_inicial')->nullable();
            $table->tinyInteger('equilibrio_levantarse_brazos_inicial')->nullable();
            $table->tinyInteger('equilibrio_levantarse_solo_inicial')->nullable();
            $table->tinyInteger('equilibrio_intentos_incapaz_inicial')->nullable();
            $table->tinyInteger('equilibrio_intentos_mas_uno_inicial')->nullable();
            $table->tinyInteger('equilibrio_intentos_un_solo_inicial')->nullable();
            $table->tinyInteger('equilibrio_bipe_inm_inestable_inicial')->nullable();
            $table->tinyInteger('equilibrio_bipe_inm_andador_inicial')->nullable();
            $table->tinyInteger('equilibrio_bipe_inm_estable_inicial')->nullable();
            $table->tinyInteger('equilibrio_bipe_inestable_inicial')->nullable();
            $table->tinyInteger('equilibrio_bipe_apoyo_amplio_inicial')->nullable();
            $table->tinyInteger('equilibrio_bipe_apoyo_estrecho_inicial')->nullable();
            $table->tinyInteger('equilibrio_empujar_caerse_inicial')->nullable();
            $table->tinyInteger('equilibrio_empujar_tambalea_inicial')->nullable();
            $table->tinyInteger('equilibrio_empujar_estable_inicial')->nullable();
            $table->tinyInteger('equilibrio_ojos_inestable_inicial')->nullable();
            $table->tinyInteger('equilibrio_ojos_estable_inicial')->nullable();
            $table->tinyInteger('equilibrio_vuelta_discontinuos_inicial')->nullable();
            $table->tinyInteger('equilibrio_vuelta_continuos_inicial')->nullable();
            $table->tinyInteger('equilibrio_vuelta_inestable_inicial')->nullable();
            $table->tinyInteger('equilibrio_vuelta_estable_inicial')->nullable();
            $table->tinyInteger('equilibrio_sentarse_inseguro_inicial')->nullable();
            $table->tinyInteger('equilibrio_sentarse_brazos_inicial')->nullable();
            $table->tinyInteger('equilibrio_sentarse_seguro_inicial')->nullable();
            $table->tinyInteger('eval_eq_total_equilibrio_inicial')->nullable();
            $table->tinyInteger('eval_eq_total_puntos_escala_inicial')->nullable();
            
            // Prueba final
            $table->tinyInteger('equilibrio_sentado_inclina_final')->nullable();
            $table->tinyInteger('equilibrio_sentado_seguro_final')->nullable();
            $table->tinyInteger('equilibrio_levantarse_imposible_final')->nullable();
            $table->tinyInteger('equilibrio_levantarse_brazos_final')->nullable();
            $table->tinyInteger('equilibrio_levantarse_solo_final')->nullable();
            $table->tinyInteger('equilibrio_intentos_incapaz_final')->nullable();
            $table->tinyInteger('equilibrio_intentos_mas_uno_final')->nullable();
            $table->tinyInteger('equilibrio_intentos_un_solo_final')->nullable();
            $table->tinyInteger('equilibrio_bipe_inm_inestable_final')->nullable();
            $table->tinyInteger('equilibrio_bipe_inm_andador_final')->nullable();
            $table->tinyInteger('equilibrio_bipe_inm_estable_final')->nullable();
            $table->tinyInteger('equilibrio_bipe_inestable_final')->nullable();
            $table->tinyInteger('equilibrio_bipe_apoyo_amplio_final')->nullable();
            $table->tinyInteger('equilibrio_bipe_apoyo_estrecho_final')->nullable();
            $table->tinyInteger('equilibrio_empujar_caerse_final')->nullable();
            $table->tinyInteger('equilibrio_empujar_tambalea_final')->nullable();
            $table->tinyInteger('equilibrio_empujar_estable_final')->nullable();
            $table->tinyInteger('equilibrio_ojos_inestable_final')->nullable();
            $table->tinyInteger('equilibrio_ojos_estable_final')->nullable();
            $table->tinyInteger('equilibrio_vuelta_discontinuos_final')->nullable();
            $table->tinyInteger('equilibrio_vuelta_continuos_final')->nullable();
            $table->tinyInteger('equilibrio_vuelta_inestable_final')->nullable();
            $table->tinyInteger('equilibrio_vuelta_estable_final')->nullable();
            $table->tinyInteger('equilibrio_sentarse_inseguro_final')->nullable();
            $table->tinyInteger('equilibrio_sentarse_brazos_final')->nullable();
            $table->tinyInteger('equilibrio_sentarse_seguro_final')->nullable();
            $table->tinyInteger('eval_eq_total_equilibrio_final')->nullable();
            $table->tinyInteger('eval_eq_total_puntos_escala_final')->nullable();
            
            // Observaciones generales
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cualidades_fisicas');
    }
};
