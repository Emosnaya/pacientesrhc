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
        'tipo_ecg',
        'indicacion',
        'contexto_clinico',
        'velocidad_papel',
        'calibracion',
        'ritmo_frecuencia',
        'intervalos',
        'eje_electrico',
        'onda_p',
        'complejo_qrs',
        'segmento_st',
        'onda_t',
        'arritmias',
        'marcapasos',
        'imagen_path',
        'interpretacion',
        'comparacion_previo',
        'diagnostico_electrocardiografico',
        'conclusiones',
        'recomendaciones',
        'urgente',
        'comparado_previo',
        'cambios_vs_previo',
        'medico_interpreta',
        'medico_realiza',
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

    public function getImagenStoragePathAttribute(): ?string
    {
        return $this->imagen_path ?: ($this->attributes['imagen_ecg'] ?? null);
    }

    public static function getEmptyRitmoFrecuencia(): array
    {
        return [
            'tipo_ritmo' => '',
            'frecuencia_cardiaca' => '',
            'origen' => '',
            'conduccion_av' => '',
        ];
    }

    public static function getEmptyIntervalos(): array
    {
        return [
            'pr' => '',
            'qrs' => '',
            'qt' => '',
            'qtc' => '',
            'formula_qtc' => 'bazett',
        ];
    }

    public static function getEmptyEje(): array
    {
        return [
            'aqrs' => '',
            'ap' => '',
            'at' => '',
            'desviacion' => '',
        ];
    }

    public static function getEmptyOndaP(): array
    {
        return [
            'morfologia' => '',
            'duracion' => '',
            'amplitud' => '',
            'p_mitrale' => false,
            'p_pulmonale' => false,
        ];
    }

    public static function getEmptyQRS(): array
    {
        return [
            'duracion' => '',
            'morfologia' => '',
            'amplitud_max' => '',
            'transicion' => '',
            'ondas_q' => ['tiene' => false, 'localizacion' => ''],
            'bajo_voltaje' => false,
            'alto_voltaje_vi' => false,
            'alto_voltaje_vd' => false,
            'bloqueo_rama' => ['tiene' => false, 'tipo' => '', 'grado' => ''],
            'bloqueo_rama_izquierda' => ['tiene' => false, 'grado' => ''],
            'bloqueo_fasciculo_anterior' => false,
            'bloqueo_fasciculo_posterior' => false,
            'bloqueo_bifascicular' => false,
            'bloqueo_trifascicular' => false,
        ];
    }

    public static function getEmptyST(): array
    {
        return [
            'normal' => true,
            'elevacion' => ['tiene' => false, 'derivaciones' => '', 'magnitud' => ''],
            'depresion' => ['tiene' => false, 'derivaciones' => '', 'magnitud' => ''],
        ];
    }

    public static function getEmptyOndaT(): array
    {
        return [
            'morfologia' => '',
            'inversion' => ['tiene' => false, 'derivaciones' => ''],
            'aplanamiento' => ['tiene' => false, 'derivaciones' => ''],
            'hiperagudas' => false,
        ];
    }

    public static function getEmptyArritmias(): array
    {
        return [
            'extrasistoles_supraventriculares' => ['tiene' => false, 'frecuencia' => ''],
            'extrasistoles_ventriculares' => ['tiene' => false, 'frecuencia' => ''],
            'taquicardia_supraventricular' => ['tiene' => false, 'ciclo_fc' => ''],
            'taquicardia_auricular_ectopica' => false,
            'taquicardia_ventricular' => ['tiene' => false, 'tipo' => ''],
            'fibrilacion_ventricular' => false,
            'fibrilacion_auricular' => false,
            'flutter_auricular' => ['tiene' => false, 'conduccion' => ''],
            'otras' => '',
        ];
    }

    public static function getEmptyMarcapasos(): array
    {
        return [
            'tiene' => false,
            'tipo' => '',
            'modo' => '',
            'captura' => '',
            'sensado' => '',
        ];
    }
}
