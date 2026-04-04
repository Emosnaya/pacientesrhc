<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlPrenatal extends Model
{
    use HasFactory;

    protected $table = 'control_prenatal';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'historia_obstetrica_id',
        'fecha_control',
        'hora',
        'numero_control',
        'semanas_gestacion',
        'trimestre',
        'signos_vitales',
        'exploracion_obstetrica',
        'laboratorios',
        'ultrasonido',
        'vacunas',
        'medicamentos',
        'signos_alarma_revisados',
        'evaluacion_riesgo',
        'indicaciones',
        'observaciones',
        'fecha_proxima_cita',
        'lugar_proxima_cita',
        'alertas',
        'medico_nombre',
        'medico_cedula',
    ];

    protected $casts = [
        'fecha_control' => 'date',
        'fecha_proxima_cita' => 'date',
        'signos_vitales' => 'array',
        'exploracion_obstetrica' => 'array',
        'laboratorios' => 'array',
        'ultrasonido' => 'array',
        'vacunas' => 'array',
        'medicamentos' => 'array',
        'signos_alarma_revisados' => 'array',
        'evaluacion_riesgo' => 'array',
        'alertas' => 'array',
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

    public function historiaObstetrica(): BelongsTo
    {
        return $this->belongsTo(HistoriaObstetrica::class);
    }

    /**
     * Estructura vacía de signos vitales
     */
    public static function getEmptySignosVitales(): array
    {
        return [
            'peso' => '',
            'presion_arterial_sistolica' => '',
            'presion_arterial_diastolica' => '',
            'frecuencia_cardiaca' => '',
            'temperatura' => '',
            'ganancia_peso_total' => '',
            'ganancia_peso_desde_ultimo' => '',
        ];
    }

    /**
     * Estructura vacía de exploración obstétrica
     */
    public static function getEmptyExploracionObstetrica(): array
    {
        return [
            'altura_uterina' => '',
            'frecuencia_cardiaca_fetal' => '',
            'movimientos_fetales' => '',
            'presentacion' => '',
            'situacion' => '',
            'posicion' => '',
            'edema' => '',
            'varices' => '',
            'actividad_uterina' => '',
        ];
    }

    /**
     * Estructura vacía de laboratorios
     */
    public static function getEmptyLaboratorios(): array
    {
        return [
            'hemoglobina' => '',
            'glucosa' => '',
            'proteinas_orina' => '',
            'glucosa_orina' => '',
            'bacterias_orina' => '',
            'otros' => [],
        ];
    }

    /**
     * Estructura vacía de ultrasonido
     */
    public static function getEmptyUltrasonido(): array
    {
        return [
            'realizado' => false,
            'semanas_eco' => '',
            'peso_fetal' => '',
            'liquido_amniotico' => '',
            'placenta' => '',
            'observaciones' => '',
        ];
    }

    /**
     * Estructura vacía de signos de alarma revisados
     */
    public static function getEmptySignosAlarma(): array
    {
        return [
            'sangrado' => false,
            'cefalea' => false,
            'vision_borrosa' => false,
            'edema_cara_manos' => false,
            'dolor_abdominal' => false,
            'fiebre' => false,
            'perdida_liquido' => false,
            'disminucion_movimientos' => false,
            'contracciones' => false,
            'observaciones' => '',
        ];
    }

    /**
     * Estructura vacía de evaluación de riesgo
     */
    public static function getEmptyEvaluacionRiesgo(): array
    {
        return [
            'clasificacion_actual' => '',
            'cambio_desde_ultimo' => false,
            'factores_nuevos' => [],
        ];
    }

    /**
     * Estructura vacía de alertas
     */
    public static function getEmptyAlertas(): array
    {
        return [
            'urgente' => false,
            'referencia_necesaria' => false,
            'motivo_referencia' => '',
            'hospital_referencia' => '',
        ];
    }
}
