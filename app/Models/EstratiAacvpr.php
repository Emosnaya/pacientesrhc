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
        // Hallazgos
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
        // Numéricos
        'semanas' => 'integer',
        'sesiones' => 'integer',
        'borg' => 'integer',
        'fc_diana' => 'decimal:2',
        'fc_diana_manual' => 'decimal:2',
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
