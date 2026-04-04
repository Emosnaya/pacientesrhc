<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Electrocardiograma extends Model
{
    use HasFactory;

    protected $table = 'electrocardiogramas';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_estudio',
        'hora',
        'indicacion',
        'contexto_clinico',
        'velocidad_papel',
        'calibracion',
        // JSON fields
        'ritmo_frecuencia',
        'intervalos',
        'eje_electrico',
        'onda_p',
        'complejo_qrs',
        'segmento_st',
        'onda_t',
        'arritmias',
        'marcapasos',
        // Others
        'imagen_path',
        'interpretacion',
        'conclusiones',
        'recomendaciones',
        'urgente',
        'comparado_previo',
        'cambios_vs_previo',
        'medico_interpreta',
        'cedula_medico',
    ];

    protected $casts = [
        'fecha_estudio' => 'date',
        'urgente' => 'boolean',
        'comparado_previo' => 'boolean',
        'ritmo_frecuencia' => 'array',
        'intervalos' => 'array',
        'eje_electrico' => 'array',
        'onda_p' => 'array',
        'complejo_qrs' => 'array',
        'segmento_st' => 'array',
        'onda_t' => 'array',
        'arritmias' => 'array',
        'marcapasos' => 'array',
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
     * Estructura vacía de ritmo y frecuencia
     */
    public static function getEmptyRitmoFrecuencia(): array
    {
        return [
            'ritmo' => '',
            'fc' => '',
            'regularidad' => '',
            'origen' => '',
        ];
    }

    /**
     * Estructura vacía de intervalos
     */
    public static function getEmptyIntervalos(): array
    {
        return [
            'pr' => '',
            'qrs' => '',
            'qt' => '',
            'qtc' => '',
            'formula_qtc' => 'Bazett',
        ];
    }

    /**
     * Estructura vacía de eje eléctrico
     */
    public static function getEmptyEje(): array
    {
        return [
            'eje_qrs' => '',
            'eje_p' => '',
            'eje_t' => '',
        ];
    }

    /**
     * Estructura vacía de onda P
     */
    public static function getEmptyOndaP(): array
    {
        return [
            'morfologia' => '',
            'duracion' => '',
            'amplitud' => '',
            'crecimiento_ai' => false,
            'crecimiento_ad' => false,
        ];
    }

    /**
     * Estructura vacía de complejo QRS
     */
    public static function getEmptyQRS(): array
    {
        return [
            'morfologia' => '',
            'duracion' => '',
            'amplitud_max' => '',
            'bajo_voltaje' => false,
            'bloqueo_rama' => ['tiene' => false, 'tipo' => ''],
            'hemibloqueo' => ['tiene' => false, 'tipo' => ''],
            'hipertrofia_vi' => ['tiene' => false, 'criterios' => ''],
            'hipertrofia_vd' => ['tiene' => false, 'criterios' => ''],
            'ondas_q' => ['tiene' => false, 'localizacion' => '', 'patologicas' => false],
        ];
    }

    /**
     * Estructura vacía de segmento ST
     */
    public static function getEmptyST(): array
    {
        return [
            'normal' => true,
            'elevacion' => ['tiene' => false, 'derivaciones' => '', 'magnitud' => ''],
            'depresion' => ['tiene' => false, 'derivaciones' => '', 'magnitud' => '', 'tipo' => ''],
        ];
    }

    /**
     * Estructura vacía de onda T
     */
    public static function getEmptyOndaT(): array
    {
        return [
            'morfologia' => '',
            'inversiones' => ['tiene' => false, 'derivaciones' => ''],
            'aplanamiento' => false,
            'picudas' => false,
        ];
    }

    /**
     * Estructura vacía de arritmias
     */
    public static function getEmptyArritmias(): array
    {
        return [
            'extrasistoles_sv' => false,
            'extrasistoles_v' => false,
            'fa' => false,
            'flutter' => false,
            'taquicardia_sv' => false,
            'taquicardia_v' => false,
            'bradicardia' => false,
            'bloqueo_av' => ['tiene' => false, 'grado' => ''],
            'pausa_sinusal' => false,
        ];
    }

    /**
     * Estructura vacía de marcapasos
     */
    public static function getEmptyMarcapasos(): array
    {
        return [
            'presente' => false,
            'tipo_estimulacion' => '',
            'espigas_visibles' => false,
            'captura' => '',
            'sensado' => '',
        ];
    }
}
