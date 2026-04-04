<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ecocardiograma extends Model
{
    use HasFactory;

    protected $table = 'ecocardiogramas';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_estudio',
        'hora',
        'tipo_estudio',
        'indicacion',
        'calidad_imagen',
        // JSON fields
        'ventriculo_izquierdo',
        'motilidad_regional',
        'funcion_diastolica',
        'ventriculo_derecho',
        'auriculas',
        'valvula_mitral',
        'valvula_aortica',
        'valvula_tricuspide',
        'valvula_pulmonar',
        'aorta',
        'pericardio',
        'hallazgos_adicionales',
        // Text fields
        'conclusiones',
        'recomendaciones',
        'medico_realiza',
        'cedula_medico',
    ];

    protected $casts = [
        'fecha_estudio' => 'date',
        'ventriculo_izquierdo' => 'array',
        'motilidad_regional' => 'array',
        'funcion_diastolica' => 'array',
        'ventriculo_derecho' => 'array',
        'auriculas' => 'array',
        'valvula_mitral' => 'array',
        'valvula_aortica' => 'array',
        'valvula_tricuspide' => 'array',
        'valvula_pulmonar' => 'array',
        'aorta' => 'array',
        'pericardio' => 'array',
        'hallazgos_adicionales' => 'array',
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
     * Estructura vacía de VI
     */
    public static function getEmptyVI(): array
    {
        return [
            'diametro_diastolico' => '',
            'diametro_sistolico' => '',
            'volumen_diastolico' => '',
            'volumen_sistolico' => '',
            'septum' => '',
            'pared_posterior' => '',
            'masa' => '',
            'indice_masa' => '',
            'grosor_relativo' => '',
            'fevi' => '',
            'fevi_metodo' => '',
            'fraccion_acortamiento' => '',
            'gls' => '',
        ];
    }

    /**
     * Estructura vacía de motilidad regional
     */
    public static function getEmptyMotilidad(): array
    {
        return [
            'descripcion' => '',
            'hipoquinesia' => ['tiene' => false, 'segmentos' => ''],
            'aquinesia' => ['tiene' => false, 'segmentos' => ''],
            'disquinesia' => ['tiene' => false, 'segmentos' => ''],
        ];
    }

    /**
     * Estructura vacía de función diastólica
     */
    public static function getEmptyFuncionDiastolica(): array
    {
        return [
            'e_mitral' => '',
            'a_mitral' => '',
            'relacion_ea' => '',
            'e_prima_septal' => '',
            'e_prima_lateral' => '',
            'relacion_e_e_prima' => '',
            'tiempo_desaceleracion' => '',
            'triv' => '',
            'patron' => '',
            'grado_disfuncion' => '',
        ];
    }

    /**
     * Estructura vacía de VD
     */
    public static function getEmptyVD(): array
    {
        return [
            'diametro_basal' => '',
            'diametro_medio' => '',
            'longitud' => '',
            'tapse' => '',
            'onda_s_tricuspide' => '',
            'fac' => '',
            'funcion' => '',
        ];
    }

    /**
     * Estructura vacía de aurículas
     */
    public static function getEmptyAuriculas(): array
    {
        return [
            'ai' => [
                'diametro' => '',
                'area' => '',
                'volumen' => '',
                'volumen_indexado' => '',
                'dilatacion' => false,
            ],
            'ad' => [
                'area' => '',
                'dilatacion' => false,
            ],
        ];
    }

    /**
     * Estructura vacía de válvula mitral
     */
    public static function getEmptyValvulaMitral(): array
    {
        return [
            'morfologia' => '',
            'area' => '',
            'gradiente_medio' => '',
            'gradiente_pico' => '',
            'estenosis' => ['tiene' => false, 'grado' => ''],
            'insuficiencia' => [
                'tiene' => false,
                'grado' => '',
                'mecanismo' => '',
                'vena_contracta' => '',
                'ore' => '',
                'volumen_regurgitante' => '',
            ],
            'prolapso' => ['tiene' => false, 'valva' => ''],
        ];
    }

    /**
     * Estructura vacía de válvula aórtica
     */
    public static function getEmptyValvulaAortica(): array
    {
        return [
            'morfologia' => '',
            'area' => '',
            'velocidad_pico' => '',
            'gradiente_pico' => '',
            'gradiente_medio' => '',
            'estenosis' => ['tiene' => false, 'grado' => ''],
            'insuficiencia' => [
                'tiene' => false,
                'grado' => '',
                'vena_contracta' => '',
                'tiempo_hemipresion' => '',
            ],
        ];
    }

    /**
     * Estructura vacía de válvula tricúspide
     */
    public static function getEmptyValvulaTricuspide(): array
    {
        return [
            'morfologia' => '',
            'insuficiencia' => [
                'tiene' => false,
                'grado' => '',
                'velocidad_pico' => '',
            ],
            'psap' => '',
            'presion_ad_estimada' => '',
        ];
    }

    /**
     * Estructura vacía de válvula pulmonar
     */
    public static function getEmptyValvulaPulmonar(): array
    {
        return [
            'morfologia' => '',
            'insuficiencia' => ['tiene' => false, 'grado' => ''],
            'velocidad_pico' => '',
            'gradiente' => '',
        ];
    }

    /**
     * Estructura vacía de aorta
     */
    public static function getEmptyAorta(): array
    {
        return [
            'raiz' => '',
            'ascendente' => '',
            'arco' => '',
            'descendente' => '',
            'aneurisma' => false,
            'coartacion' => false,
            'diseccion' => false,
        ];
    }

    /**
     * Estructura vacía de pericardio
     */
    public static function getEmptyPericardio(): array
    {
        return [
            'aspecto' => '',
            'derrame' => ['tiene' => false, 'cantidad' => '', 'localizacion' => ''],
            'taponamiento' => false,
            'constriccion' => false,
        ];
    }

    /**
     * Estructura vacía de hallazgos adicionales
     */
    public static function getEmptyHallazgos(): array
    {
        return [
            'trombo' => ['tiene' => false, 'localizacion' => ''],
            'masa' => ['tiene' => false, 'descripcion' => ''],
            'fop' => false,
            'cia' => false,
            'civ' => false,
            'otros' => '',
        ];
    }
}
