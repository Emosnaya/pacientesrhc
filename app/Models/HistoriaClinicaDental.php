<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriaClinicaDental extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'historia_clinica_dental';

    protected $fillable = [
        'paciente_id',
        'sucursal_id',
        'user_id',
        'fecha',
        'nombre_doctor',
        'cedula_profesional',
        'alergias',
        'medicamentos_actuales',
        'alergico_anestesicos',
        'anestesicos_detalle',
        'alergico_medicamentos',
        'medicamentos_alergicos_detalle',
        'embarazada',
        'toma_anticonceptivos',
        'mal_aliento',
        'hipersensibilidad_dental',
        'respira_boca',
        'muerde_unas',
        'muerde_labios',
        'aprieta_dientes',
        'veces_cepilla_dia',
        'higienizacion_metodo',
        'ultima_visita_odontologo',
        'historia_enfermedad',
        'motivo_consulta',
        // Antecedentes Familiares
        'af_diabetes',
        'af_hipertension',
        'af_cancer',
        'af_cardiacas',
        'af_vih',
        'af_epilepsia',
        // Información Patológica
        'ip_diabetes',
        'ip_hipertension',
        'ip_cancer',
        'ip_cardiacas',
        'ip_veneras',
        'ip_epilepsia',
        'ip_asma',
        'ip_gastricas',
        'ip_cicatriz',
        'ip_presion_alta_baja',
        // Antecedentes Toxicológicos
        'at_fuma',
        'at_fuma_detalle',
        'at_drogas',
        'at_drogas_detalle',
        'at_toma',
        'at_toma_detalle',
        // Antecedentes Ginecoobstétricos
        'ag_menarca',
        'ag_menarca_edad',
        'ag_menopausia',
        'ag_menopausia_edad',
        'ag_embarazo',
        // Antecedentes Odontológicos
        'ao_limpieza_6meses',
        'ao_sangrado',
        'ao_dolor_abrir',
        'ao_tratamiento_ortodoncia',
        'ao_morder_labios',
        'ao_dieta_dulces',
        'ao_cepilla_dientes',
        'ao_trauma_cara',
        'ao_dolor_masticar',
        // Examen Tejidos Blandos
        'etb_carrillos',
        'etb_encias',
        'etb_lengua',
        'etb_paladar',
        'etb_atm',
        'etb_labios',
        // Signos Vitales
        'sv_ta',
        'sv_pulso',
        'sv_fc',
        'sv_peso',
        'sv_altura',
    ];

    protected $casts = [
        'fecha' => 'date',
        'ultima_visita_odontologo' => 'date',
        'medicamentos_actuales' => 'array',
        'alergico_anestesicos' => 'boolean',
        'alergico_medicamentos' => 'boolean',
        'embarazada' => 'boolean',
        'toma_anticonceptivos' => 'boolean',
        'mal_aliento' => 'boolean',
        'hipersensibilidad_dental' => 'boolean',
        'respira_boca' => 'boolean',
        'muerde_unas' => 'boolean',
        'muerde_labios' => 'boolean',
        'aprieta_dientes' => 'boolean',
        'af_diabetes' => 'boolean',
        'af_hipertension' => 'boolean',
        'af_cancer' => 'boolean',
        'af_cardiacas' => 'boolean',
        'af_vih' => 'boolean',
        'af_epilepsia' => 'boolean',
        'ip_diabetes' => 'boolean',
        'ip_hipertension' => 'boolean',
        'ip_cancer' => 'boolean',
        'ip_cardiacas' => 'boolean',
        'ip_veneras' => 'boolean',
        'ip_epilepsia' => 'boolean',
        'ip_asma' => 'boolean',
        'ip_gastricas' => 'boolean',
        'ip_cicatriz' => 'boolean',
        'ip_presion_alta_baja' => 'boolean',
        'at_fuma' => 'boolean',
        'at_drogas' => 'boolean',
        'at_toma' => 'boolean',
        'ag_menarca' => 'boolean',
        'ag_menopausia' => 'boolean',
        'ag_embarazo' => 'boolean',
        'ao_limpieza_6meses' => 'boolean',
        'ao_sangrado' => 'boolean',
        'ao_dolor_abrir' => 'boolean',
        'ao_tratamiento_ortodoncia' => 'boolean',
        'ao_morder_labios' => 'boolean',
        'ao_dieta_dulces' => 'boolean',
        'ao_cepilla_dientes' => 'boolean',
        'ao_trauma_cara' => 'boolean',
        'ao_dolor_masticar' => 'boolean',
        // Cifrado de campos sensibles
        'alergias' => 'encrypted',
        'anestesicos_detalle' => 'encrypted',
        'medicamentos_alergicos_detalle' => 'encrypted',
        'historia_enfermedad' => 'encrypted',
        'motivo_consulta' => 'encrypted',
        'at_fuma_detalle' => 'encrypted',
        'at_drogas_detalle' => 'encrypted',
        'at_toma_detalle' => 'encrypted',
    ];

    /**
     * Relación con Paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con Sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Relación con Usuario (Doctor)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con Odontogramas
     */
    public function odontogramas()
    {
        return $this->hasMany(Odontograma::class);
    }
}
