<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstratiAacvpr extends Model
{
    use HasFactory;

    protected $table = 'estrati_aacvprs';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        // Fechas
        'fecha_estratificacion',
        'primeravez_rhc',
        'pe_fecha',
        // Datos de Prueba de Esfuerzo
        'fc_basal',
        'fc_maxima',
        'fc_borg_12',
        'dp_borg_12',
        'mets_borg_12',
        'carga_maxima',
        'tolerancia_esfuerzo',
        // Riesgo Alto
        'alto_fevi_disminuida',
        'alto_sintomas_reposo',
        'alto_isquemia_baja_intensidad',
        'alto_arritmias_ventriculares',
        'alto_im_complicado',
        'alto_capacidad_menor_5mets',
        'alto_hemodinamica_anormal',
        'alto_paro_cardiaco',
        'alto_enfermedad_compleja',
        // Riesgo Moderado
        'moderado_fevi_moderada',
        'moderado_sintomas_moderados',
        'moderado_isquemia_moderada',
        'moderado_capacidad_5_7mets',
        'moderado_sin_automonitoreo',
        // Riesgo Bajo
        'bajo_fevi_preservada',
        'bajo_sin_sintomas',
        'bajo_sin_isquemia',
        'bajo_capacidad_mayor_7mets',
        'bajo_sin_arritmias',
        'bajo_im_no_complicado',
        'bajo_hemodinamica_normal',
        'bajo_automonitoreo_adecuado',
        // Hoja AACVPR (criterios detallados)
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
        // Hallazgos (legacy texto, opcional)
        'hallazgo_fevi',
        'hallazgo_mets',
        'hallazgo_sintomas',
        'hallazgo_isquemia',
        'hallazgo_arritmias',
        'hallazgo_hemodinamica',
        'hallazgo_procedimiento',
        'hallazgo_coronaria',
        // Riesgo Global
        'riesgo_global',
        // Parámetros Iniciales
        'grupo',
        'semanas',
        'sesiones',
        'borg',
        'fc_diana_metodo',
        'fc_diana_str',
        'fc_diana',
        'fc_diana_manual',
        'karvonen',
        'blackburn',
        'narita',
        'dp_diana',
        'carga_inicial',
        'comentarios'
    ];

    protected $casts = [
        'fecha_estratificacion' => 'date',
        'primeravez_rhc' => 'date',
        'pe_fecha' => 'date',
        // Riesgo Alto
        'alto_fevi_disminuida' => 'boolean',
        'alto_sintomas_reposo' => 'boolean',
        'alto_isquemia_baja_intensidad' => 'boolean',
        'alto_arritmias_ventriculares' => 'boolean',
        'alto_im_complicado' => 'boolean',
        'alto_capacidad_menor_5mets' => 'boolean',
        'alto_hemodinamica_anormal' => 'boolean',
        'alto_paro_cardiaco' => 'boolean',
        'alto_enfermedad_compleja' => 'boolean',
        // Riesgo Moderado
        'moderado_fevi_moderada' => 'boolean',
        'moderado_sintomas_moderados' => 'boolean',
        'moderado_isquemia_moderada' => 'boolean',
        'moderado_capacidad_5_7mets' => 'boolean',
        'moderado_sin_automonitoreo' => 'boolean',
        // Riesgo Bajo
        'bajo_fevi_preservada' => 'boolean',
        'bajo_sin_sintomas' => 'boolean',
        'bajo_sin_isquemia' => 'boolean',
        'bajo_capacidad_mayor_7mets' => 'boolean',
        'bajo_sin_arritmias' => 'boolean',
        'bajo_im_no_complicado' => 'boolean',
        'bajo_hemodinamica_normal' => 'boolean',
        'bajo_automonitoreo_adecuado' => 'boolean',
        'aacvpr_alto_arritmias_vent_complejas_pe' => 'boolean',
        'aacvpr_alto_angina_sintomas_menos_5mets' => 'boolean',
        'aacvpr_alto_isquemia_st_ge_2mm' => 'boolean',
        'aacvpr_alto_alteraciones_hemodinamicas_pe' => 'boolean',
        'aacvpr_alto_capacidad_3_4_mets' => 'boolean',
        'aacvpr_alto_hc_fevi_lt_35' => 'boolean',
        'aacvpr_alto_hc_choque_cardiogenico' => 'boolean',
        'aacvpr_alto_hc_arritmias_complejas_reposo' => 'boolean',
        'aacvpr_alto_hc_im_complicado_revasc_incompleta' => 'boolean',
        'aacvpr_alto_hc_falla_cardiaca_nyha_iii_iv' => 'boolean',
        'aacvpr_alto_hc_alta_temprana_post_agudo' => 'boolean',
        'aacvpr_alto_hc_muerte_subita' => 'boolean',
        'aacvpr_alto_hc_trasplante_cardiaco' => 'boolean',
        'aacvpr_alto_hc_isquemia_post_evento' => 'boolean',
        'aacvpr_alto_hc_desfibrilador_implantado' => 'boolean',
        'aacvpr_alto_hc_complicaciones_hospitalizacion' => 'boolean',
        'aacvpr_alto_hc_inestabilidad_post_agudo' => 'boolean',
        'aacvpr_alto_hc_comorbilidades_graves_rcv' => 'boolean',
        'aacvpr_alto_hc_depresion_clinica' => 'boolean',
        'aacvpr_alto_hc_aislamiento_social' => 'boolean',
        'aacvpr_alto_hc_bajos_ingresos' => 'boolean',
        'aacvpr_mod_angina_estable_mas_7mets' => 'boolean',
        'aacvpr_mod_isquemia_st_lt_2mm' => 'boolean',
        'aacvpr_mod_capacidad_lt_5mets' => 'boolean',
        'aacvpr_mod_hc_fevi_35_49' => 'boolean',
        'aacvpr_mod_hc_hta_no_controlada' => 'boolean',
        'aacvpr_bajo_sin_arritmia_compleja_pe' => 'boolean',
        'aacvpr_bajo_sin_angina_significativa_pe' => 'boolean',
        'aacvpr_bajo_hemodinamica_normal_pe' => 'boolean',
        'aacvpr_bajo_capacidad_6_7_mets' => 'boolean',
        'aacvpr_bajo_hc_fevi_gt_50' => 'boolean',
        'aacvpr_bajo_hc_im_ok_revasc_completa' => 'boolean',
        'aacvpr_bajo_hc_sin_arritmias_complejas_reposo' => 'boolean',
        'aacvpr_bajo_hc_sin_falla_cardiaca' => 'boolean',
        'aacvpr_bajo_hc_sin_isquemia_post' => 'boolean',
        'aacvpr_bajo_hc_hta_controlada' => 'boolean',
        'aacvpr_bajo_hc_sin_depresion' => 'boolean',
        'aacvpr_bajo_hc_sin_comorbilidades' => 'boolean',
        'aacvpr_bajo_hc_autonomia_sin_riesgo_psicosocial' => 'boolean',
        'aacvpr_bajo_hc_sin_dispositivos_implantados' => 'boolean',
        // Numéricos
        'semanas' => 'integer',
        'sesiones' => 'integer',
        'borg' => 'integer',
        'fc_diana' => 'decimal:2',
        'fc_diana_manual' => 'decimal:2',
        'karvonen' => 'decimal:2',
        'blackburn' => 'decimal:2',
        'narita' => 'decimal:2',
        'dp_diana' => 'decimal:2',
        'carga_inicial' => 'decimal:2',
    ];

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con los permisos otorgados sobre este reporte
     */
    public function permissions()
    {
        return $this->morphMany(UserPermission::class, 'permissionable');
    }
}
