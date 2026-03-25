<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Criterios AACVPR según hoja de estratificación (riesgo alto / moderado / bajo).
     */
    public function up(): void
    {
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            // Riesgo alto — criterios generales (PE)
            $table->boolean('aacvpr_alto_arritmias_vent_complejas_pe')->default(false);
            $table->boolean('aacvpr_alto_angina_sintomas_menos_5mets')->default(false);
            $table->boolean('aacvpr_alto_isquemia_st_ge_2mm')->default(false);
            $table->boolean('aacvpr_alto_alteraciones_hemodinamicas_pe')->default(false);
            $table->boolean('aacvpr_alto_capacidad_3_4_mets')->default(false);

            // Riesgo alto — hallazgos clínicos
            $table->boolean('aacvpr_alto_hc_fevi_lt_35')->default(false);
            $table->boolean('aacvpr_alto_hc_choque_cardiogenico')->default(false);
            $table->boolean('aacvpr_alto_hc_arritmias_complejas_reposo')->default(false);
            $table->boolean('aacvpr_alto_hc_im_complicado_revasc_incompleta')->default(false);
            $table->boolean('aacvpr_alto_hc_falla_cardiaca_nyha_iii_iv')->default(false);
            $table->boolean('aacvpr_alto_hc_alta_temprana_post_agudo')->default(false);
            $table->boolean('aacvpr_alto_hc_muerte_subita')->default(false);
            $table->boolean('aacvpr_alto_hc_trasplante_cardiaco')->default(false);
            $table->boolean('aacvpr_alto_hc_isquemia_post_evento')->default(false);
            $table->boolean('aacvpr_alto_hc_desfibrilador_implantado')->default(false);
            $table->boolean('aacvpr_alto_hc_complicaciones_hospitalizacion')->default(false);
            $table->boolean('aacvpr_alto_hc_inestabilidad_post_agudo')->default(false);
            $table->boolean('aacvpr_alto_hc_comorbilidades_graves_rcv')->default(false);
            $table->boolean('aacvpr_alto_hc_depresion_clinica')->default(false);
            $table->boolean('aacvpr_alto_hc_aislamiento_social')->default(false);
            $table->boolean('aacvpr_alto_hc_bajos_ingresos')->default(false);

            // Riesgo moderado — generales
            $table->boolean('aacvpr_mod_angina_estable_mas_7mets')->default(false);
            $table->boolean('aacvpr_mod_isquemia_st_lt_2mm')->default(false);
            $table->boolean('aacvpr_mod_capacidad_lt_5mets')->default(false);

            // Riesgo moderado — hallazgos
            $table->boolean('aacvpr_mod_hc_fevi_35_49')->default(false);
            $table->boolean('aacvpr_mod_hc_hta_no_controlada')->default(false);

            // Riesgo bajo — generales
            $table->boolean('aacvpr_bajo_sin_arritmia_compleja_pe')->default(false);
            $table->boolean('aacvpr_bajo_sin_angina_significativa_pe')->default(false);
            $table->boolean('aacvpr_bajo_hemodinamica_normal_pe')->default(false);
            $table->boolean('aacvpr_bajo_capacidad_6_7_mets')->default(false);

            // Riesgo bajo — hallazgos
            $table->boolean('aacvpr_bajo_hc_fevi_gt_50')->default(false);
            $table->boolean('aacvpr_bajo_hc_im_ok_revasc_completa')->default(false);
            $table->boolean('aacvpr_bajo_hc_sin_arritmias_complejas_reposo')->default(false);
            $table->boolean('aacvpr_bajo_hc_sin_falla_cardiaca')->default(false);
            $table->boolean('aacvpr_bajo_hc_sin_isquemia_post')->default(false);
            $table->boolean('aacvpr_bajo_hc_hta_controlada')->default(false);
            $table->boolean('aacvpr_bajo_hc_sin_depresion')->default(false);
            $table->boolean('aacvpr_bajo_hc_sin_comorbilidades')->default(false);
            $table->boolean('aacvpr_bajo_hc_autonomia_sin_riesgo_psicosocial')->default(false);
            $table->boolean('aacvpr_bajo_hc_sin_dispositivos_implantados')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            $cols = [
                'aacvpr_alto_arritmias_vent_complejas_pe',
                'aacvpr_alto_angina_sintomas_menos_5mets',
                'aacvpr_alto_isquemia_st_ge_2mm',
                'aacvpr_alto_alteraciones_hemodinamicas_pe',
                'aacvpr_alto_capacidad_3_4_mets',
                'aacvpr_alto_hc_fevi_lt_35',
                'aacvpr_alto_hc_choque_cardiogenico',
                'aacvpr_alto_hc_arritmias_complejas_reposo',
                'aacvpr_alto_hc_im_complicado_revasc_incompleta',
                'aacvpr_alto_hc_falla_cardiaca_nyha_iii_iv',
                'aacvpr_alto_hc_alta_temprana_post_agudo',
                'aacvpr_alto_hc_muerte_subita',
                'aacvpr_alto_hc_trasplante_cardiaco',
                'aacvpr_alto_hc_isquemia_post_evento',
                'aacvpr_alto_hc_desfibrilador_implantado',
                'aacvpr_alto_hc_complicaciones_hospitalizacion',
                'aacvpr_alto_hc_inestabilidad_post_agudo',
                'aacvpr_alto_hc_comorbilidades_graves_rcv',
                'aacvpr_alto_hc_depresion_clinica',
                'aacvpr_alto_hc_aislamiento_social',
                'aacvpr_alto_hc_bajos_ingresos',
                'aacvpr_mod_angina_estable_mas_7mets',
                'aacvpr_mod_isquemia_st_lt_2mm',
                'aacvpr_mod_capacidad_lt_5mets',
                'aacvpr_mod_hc_fevi_35_49',
                'aacvpr_mod_hc_hta_no_controlada',
                'aacvpr_bajo_sin_arritmia_compleja_pe',
                'aacvpr_bajo_sin_angina_significativa_pe',
                'aacvpr_bajo_hemodinamica_normal_pe',
                'aacvpr_bajo_capacidad_6_7_mets',
                'aacvpr_bajo_hc_fevi_gt_50',
                'aacvpr_bajo_hc_im_ok_revasc_completa',
                'aacvpr_bajo_hc_sin_arritmias_complejas_reposo',
                'aacvpr_bajo_hc_sin_falla_cardiaca',
                'aacvpr_bajo_hc_sin_isquemia_post',
                'aacvpr_bajo_hc_hta_controlada',
                'aacvpr_bajo_hc_sin_depresion',
                'aacvpr_bajo_hc_sin_comorbilidades',
                'aacvpr_bajo_hc_autonomia_sin_riesgo_psicosocial',
                'aacvpr_bajo_hc_sin_dispositivos_implantados',
            ];
            $table->dropColumn($cols);
        });
    }
};
