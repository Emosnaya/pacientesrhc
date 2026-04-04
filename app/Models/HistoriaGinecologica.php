<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoriaGinecologica extends Model
{
    use HasFactory;

    protected $table = 'historia_ginecologica';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_consulta',
        'hora',
        'motivo_consulta',
        'antecedentes_ginecologicos',
        'antecedentes_obstetricos',
        'signos_vitales',
        'exploracion_fisica',
        'estudios_solicitados',
        'diagnosticos',
        'tratamiento',
        'padecimiento_actual',
        'notas_adicionales',
        'observaciones',
        'medico_nombre',
        'medico_cedula',
        'medico_especialidad',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'antecedentes_ginecologicos' => 'array',
        'antecedentes_obstetricos' => 'array',
        'signos_vitales' => 'array',
        'exploracion_fisica' => 'array',
        'estudios_solicitados' => 'array',
        'diagnosticos' => 'array',
        'tratamiento' => 'array',
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
     * Estructura vacía de antecedentes ginecológicos
     */
    public static function getEmptyAntecedentesGinecologicos(): array
    {
        return [
            'menarca' => '',
            'ritmo_menstrual' => '',
            'duracion_menstruacion' => '',
            'fum' => '',
            'ciclos_regulares' => null,
            'dismenorrea' => null,
            'vida_sexual_activa' => null,
            'edad_ivsa' => '',
            'num_parejas_sexuales' => '',
            'metodo_anticonceptivo' => '',
            'fecha_ultimo_pap' => '',
            'resultado_pap' => '',
            'fecha_ultima_mamografia' => '',
            'resultado_mamografia' => '',
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
            'fecha_ultimo_parto' => '',
            'tipo_ultimo_parto' => '',
            'complicaciones_previas' => '',
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
            'presion_arterial' => '',
            'frecuencia_cardiaca' => '',
            'temperatura' => '',
        ];
    }

    /**
     * Estructura vacía de exploración física
     */
    public static function getEmptyExploracionFisica(): array
    {
        return [
            'mamas' => [
                'inspeccion' => '',
                'palpacion' => '',
                'axila' => '',
                'hallazgos' => '',
            ],
            'abdomen' => [
                'inspeccion' => '',
                'palpacion' => '',
                'hallazgos' => '',
            ],
            'genitales_externos' => [
                'inspeccion' => '',
                'hallazgos' => '',
            ],
            'especuloscopia' => [
                'cuello' => '',
                'vagina' => '',
                'secrecion' => '',
                'hallazgos' => '',
            ],
            'tacto_vaginal' => [
                'utero_tamano' => '',
                'utero_posicion' => '',
                'anexos' => '',
                'fondos_saco' => '',
                'hallazgos' => '',
            ],
        ];
    }

    /**
     * Estructura vacía de estudios solicitados
     */
    public static function getEmptyEstudiosSolicitados(): array
    {
        return [
            'laboratorio' => [],
            'gabinete' => [],
            'otros' => [],
        ];
    }

    /**
     * Estructura vacía de tratamiento
     */
    public static function getEmptyTratamiento(): array
    {
        return [
            'medicamentos' => [],
            'indicaciones_generales' => '',
            'fecha_proxima_cita' => '',
        ];
    }
}
