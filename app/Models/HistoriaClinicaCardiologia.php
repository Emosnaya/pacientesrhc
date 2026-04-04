<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriaClinicaCardiologia extends Model
{
    use HasFactory;

    protected $table = 'historia_clinica_cardiologias';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_consulta',
        'hora',
        'motivo_consulta',
        'padecimiento_actual',
        // JSON fields
        'antecedentes_cardiovasculares',
        'factores_riesgo',
        'antecedentes_familiares',
        // Medicación
        'medicacion_cardiovascular',
        'medicacion_otros',
        'alergias',
        // Síntomas JSON
        'sintomas',
        // Signos vitales
        'ta_sistolica',
        'ta_diastolica',
        'fc',
        'fr',
        'spo2',
        'temperatura',
        'peso',
        'talla',
        'imc',
        'perimetro_abdominal',
        // Exploración JSON
        'exploracion_cardiovascular',
        'pulsos_perifericos',
        // Estudios JSON
        'estudios_previos',
        'laboratorios',
        // Diagnósticos
        'diagnostico_principal',
        'diagnostico_cie10',
        'diagnosticos_secundarios',
        'clasificacion_riesgo',
        // Plan
        'plan_farmacologico',
        'plan_no_farmacologico',
        'estudios_solicitados',
        'interconsultas',
        'indicaciones',
        'pronostico',
        'proxima_cita',
        'notas_adicionales',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'proxima_cita' => 'date',
        'antecedentes_cardiovasculares' => 'array',
        'factores_riesgo' => 'array',
        'antecedentes_familiares' => 'array',
        'sintomas' => 'array',
        'exploracion_cardiovascular' => 'array',
        'pulsos_perifericos' => 'array',
        'estudios_previos' => 'array',
        'laboratorios' => 'array',
    ];

    /**
     * Relación con el paciente
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con el usuario (médico)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la clínica
     */
    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Relación con la sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Obtener estructura vacía de antecedentes cardiovasculares
     */
    public static function getEmptyAntecedentesCV(): array
    {
        return [
            'iam' => ['tiene' => false, 'detalle' => ''],
            'angina' => ['tiene' => false, 'detalle' => ''],
            'arritmias' => ['tiene' => false, 'tipo' => ''],
            'ic' => ['tiene' => false, 'clase_nyha' => ''],
            'valvulopatia' => ['tiene' => false, 'detalle' => ''],
            'cardiopatia_congenita' => ['tiene' => false, 'detalle' => ''],
            'dispositivo' => ['tiene' => false, 'tipo' => '', 'fecha' => null],
            'cirugia_cardiaca' => ['tiene' => false, 'detalle' => ''],
            'cateterismo' => ['tiene' => false, 'detalle' => ''],
            'angioplastia' => ['tiene' => false, 'detalle' => ''],
            'otros' => '',
        ];
    }

    /**
     * Obtener estructura vacía de factores de riesgo
     */
    public static function getEmptyFactoresRiesgo(): array
    {
        return [
            'hta' => ['tiene' => false, 'tiempo' => '', 'tratamiento' => ''],
            'dm' => ['tiene' => false, 'tipo' => '', 'tiempo' => '', 'tratamiento' => ''],
            'dislipidemia' => ['tiene' => false, 'detalle' => ''],
            'tabaquismo' => ['tiene' => false, 'estado' => '', 'cigarros_dia' => '', 'anios' => ''],
            'obesidad' => false,
            'sedentarismo' => false,
            'estres' => false,
            'apnea' => false,
            'erc' => ['tiene' => false, 'estadio' => ''],
            'otros' => '',
        ];
    }

    /**
     * Obtener estructura vacía de antecedentes familiares
     */
    public static function getEmptyAntecedentesFam(): array
    {
        return [
            'cardiopatia_isquemica' => ['tiene' => false, 'parentesco' => ''],
            'muerte_subita' => ['tiene' => false, 'parentesco' => ''],
            'hta' => false,
            'dm' => false,
            'dislipidemia' => false,
            'miocardiopatia' => false,
            'otros' => '',
        ];
    }

    /**
     * Obtener estructura vacía de síntomas
     */
    public static function getEmptySintomas(): array
    {
        return [
            'dolor_toracico' => [
                'tiene' => false, 
                'tipo' => '', 
                'localizacion' => '', 
                'irradiacion' => '', 
                'duracion' => '', 
                'desencadenante' => '', 
                'alivio' => ''
            ],
            'disnea' => ['tiene' => false, 'clase_nyha' => ''],
            'ortopnea' => false,
            'dpn' => false,
            'palpitaciones' => ['tiene' => false, 'tipo' => ''],
            'sincope' => ['tiene' => false, 'detalle' => ''],
            'presincope' => false,
            'edema' => ['tiene' => false, 'localizacion' => ''],
            'fatiga' => false,
            'claudicacion' => false,
            'otros' => '',
        ];
    }

    /**
     * Obtener estructura vacía de exploración cardiovascular
     */
    public static function getEmptyExploracionCV(): array
    {
        return [
            'estado_general' => '',
            'cuello' => '',
            'iy_cm' => '',
            'torax' => '',
            'apex' => '',
            'ritmo' => '',
            'r1' => '',
            'r2' => '',
            'r3' => false,
            'r4' => false,
            'soplo' => ['tiene' => false, 'foco' => '', 'grado' => '', 'tipo' => '', 'irradiacion' => ''],
            'frote_pericardico' => false,
            'auscultacion_pulmonar' => '',
            'estertores' => ['tiene' => false, 'localizacion' => ''],
            'otros' => '',
        ];
    }

    /**
     * Obtener estructura vacía de pulsos periféricos
     */
    public static function getEmptyPulsos(): array
    {
        return [
            'carotideo_der' => '', 'carotideo_izq' => '',
            'radial_der' => '', 'radial_izq' => '',
            'femoral_der' => '', 'femoral_izq' => '',
            'popliteo_der' => '', 'popliteo_izq' => '',
            'tibial_der' => '', 'tibial_izq' => '',
            'pedio_der' => '', 'pedio_izq' => '',
            'edema_mmii' => ['tiene' => false, 'grado' => ''],
            'varices' => false,
        ];
    }

    /**
     * Obtener estructura vacía de estudios previos
     */
    public static function getEmptyEstudios(): array
    {
        return [
            'ecg' => '',
            'ecocardiograma' => '',
            'prueba_esfuerzo' => '',
            'holter' => '',
            'mapa' => '',
            'cateterismo' => '',
            'angiotac' => '',
            'rmn_cardiaca' => '',
            'otros' => '',
        ];
    }

    /**
     * Obtener estructura vacía de laboratorios
     */
    public static function getEmptyLaboratorios(): array
    {
        return [
            'glucosa' => '',
            'hba1c' => '',
            'creatinina' => '',
            'tfg' => '',
            'colesterol_total' => '',
            'ldl' => '',
            'hdl' => '',
            'trigliceridos' => '',
            'hemoglobina' => '',
            'bnp' => '',
            'troponinas' => '',
            'dimero_d' => '',
            'otros' => '',
        ];
    }
}
