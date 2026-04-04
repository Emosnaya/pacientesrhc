<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoriaObstetrica extends Model
{
    use HasFactory;

    protected $table = 'historia_obstetrica';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_consulta',
        'hora',
        'motivo_consulta',
        'embarazo_actual',
        'antecedentes_obstetricos',
        'antecedentes_personales',
        'antecedentes_familiares',
        'signos_vitales',
        'exploracion_obstetrica',
        'laboratorios',
        'ultrasonidos',
        'riesgo_obstetrico',
        'plan_manejo',
        'padecimiento_actual',
        'notas_evolucion',
        'observaciones',
        'medico_nombre',
        'medico_cedula',
        'medico_especialidad',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'embarazo_actual' => 'array',
        'antecedentes_obstetricos' => 'array',
        'antecedentes_personales' => 'array',
        'antecedentes_familiares' => 'array',
        'signos_vitales' => 'array',
        'exploracion_obstetrica' => 'array',
        'laboratorios' => 'array',
        'ultrasonidos' => 'array',
        'riesgo_obstetrico' => 'array',
        'plan_manejo' => 'array',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Controles prenatales asociados
     */
    public function controlesPrenatales(): HasMany
    {
        return $this->hasMany(ControlPrenatal::class);
    }

    /**
     * Estructura vacía de embarazo actual
     */
    public static function getEmptyEmbarazoActual(): array
    {
        return [
            'fum' => '',
            'fpp' => '',
            'semanas_gestacion' => '',
            'trimestre' => '',
            'embarazo_planeado' => null,
            'embarazo_deseado' => null,
            'control_prenatal_previo' => null,
            'num_controles_previos' => '',
            'fecha_primer_control' => '',
            'metodo_calculo_fpp' => '',
        ];
    }

    /**
     * Estructura vacía de antecedentes obstétricos
     */
    public static function getEmptyAntecedentesObstetricos(): array
    {
        return [
            'gestas' => '',
            'partos' => '',
            'cesareas' => '',
            'abortos' => '',
            'ectopicos' => '',
            'molas' => '',
            'hijos_vivos' => '',
            'hijos_muertos' => '',
            'embarazos_previos' => [],
            'periodo_intergenesico' => '',
            'fecha_ultimo_evento' => '',
        ];
    }

    /**
     * Estructura vacía de antecedentes personales
     */
    public static function getEmptyAntecedentesPersonales(): array
    {
        return [
            'enfermedades_cronicas' => [],
            'alergias' => [],
            'cirugias_previas' => [],
            'medicamentos_habituales' => [],
            'transfusiones' => null,
            'grupo_sanguineo' => '',
            'factor_rh' => '',
        ];
    }

    /**
     * Estructura vacía de antecedentes familiares
     */
    public static function getEmptyAntecedentesFamiliares(): array
    {
        return [
            'diabetes' => null,
            'hipertension' => null,
            'preeclampsia' => null,
            'gemelar' => null,
            'malformaciones' => null,
            'enfermedades_geneticas' => '',
            'otros' => '',
        ];
    }

    /**
     * Estructura vacía de signos vitales
     */
    public static function getEmptySignosVitales(): array
    {
        return [
            'peso' => '',
            'talla' => '',
            'imc' => '',
            'peso_pregestacional' => '',
            'ganancia_peso' => '',
            'presion_arterial' => '',
            'frecuencia_cardiaca' => '',
            'temperatura' => '',
        ];
    }

    /**
     * Estructura vacía de exploración obstétrica
     */
    public static function getEmptyExploracionObstetrica(): array
    {
        return [
            'altura_uterina' => '',
            'presentacion' => '',
            'situacion' => '',
            'posicion' => '',
            'frecuencia_cardiaca_fetal' => '',
            'movimientos_fetales' => '',
            'edema' => '',
            'varices' => '',
            'reflejos_osteotendinosos' => '',
            'mamas' => [
                'preparacion_lactancia' => '',
                'anomalias' => '',
            ],
            'cervix' => [
                'dilatacion' => '',
                'borramiento' => '',
                'consistencia' => '',
                'posicion' => '',
                'altura_presentacion' => '',
            ],
        ];
    }

    /**
     * Estructura vacía de laboratorios
     */
    public static function getEmptyLaboratorios(): array
    {
        return [
            'hemoglobina' => '',
            'hematocrito' => '',
            'glucosa' => '',
            'urea' => '',
            'creatinina' => '',
            'acido_urico' => '',
            'examen_orina' => '',
            'urocultivo' => '',
            'grupo_rh' => '',
            'coombs_indirecto' => '',
            'vdrl' => '',
            'vih' => '',
            'hepatitis_b' => '',
            'toxoplasma' => '',
            'rubeola' => '',
            'citomegalovirus' => '',
            'herpes' => '',
            'perfil_tiroideo' => '',
            'otros' => [],
        ];
    }

    /**
     * Estructura vacía de riesgo obstétrico
     */
    public static function getEmptyRiesgoObstetrico(): array
    {
        return [
            'clasificacion' => '',
            'factores' => [],
            'puntuacion' => '',
        ];
    }

    /**
     * Estructura vacía de plan de manejo
     */
    public static function getEmptyPlanManejo(): array
    {
        return [
            'suplementos' => [],
            'vacunas' => [],
            'recomendaciones' => [],
            'signos_alarma' => [],
            'fecha_proxima_cita' => '',
            'lugar_atencion_parto' => '',
        ];
    }
}
