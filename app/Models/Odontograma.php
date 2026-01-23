<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odontograma extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paciente_id',
        'sucursal_id',
        'historia_clinica_dental_id',
        'fecha',
        'dientes',
        // Análisis Periodontal
        'ap_calculo_supragingival',
        'ap_calculo_infragingival',
        'ap_movilidad_dental',
        'ap_bolsas_periodontales',
        'ap_pseudobolsas',
        'ap_indice_placa',
        'ap_calculo_supragingival_dientes',
        'ap_calculo_infragingival_dientes',
        'ap_movilidad_dental_dientes',
        'ap_bolsas_periodontales_dientes',
        'ap_pseudobolsas_dientes',
        'ap_indice_placa_dientes',
        // Análisis Endodóntico
        'ae_endo_defectuosa',
        'ae_necrosis_pulpar',
        'ae_pulpitis_irreversible',
        'ae_lesiones_periapicales',
        'ae_endo_defectuosa_dientes',
        'ae_necrosis_pulpar_dientes',
        'ae_pulpitis_irreversible_dientes',
        'ae_lesiones_periapicales_dientes',
        // Diagnóstico y observaciones
        'diagnostico',
        'pronostico',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'dientes' => 'array',
        'ap_calculo_supragingival' => 'boolean',
        'ap_calculo_infragingival' => 'boolean',
        'ap_movilidad_dental' => 'boolean',
        'ap_bolsas_periodontales' => 'boolean',
        'ap_pseudobolsas' => 'boolean',
        'ap_indice_placa' => 'boolean',
        'ae_endo_defectuosa' => 'boolean',
        'ae_necrosis_pulpar' => 'boolean',
        'ae_pulpitis_irreversible' => 'boolean',
        'ae_lesiones_periapicales' => 'boolean',
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
     * Relación con Historia Clínica Dental
     */
    public function historiaClinicaDental()
    {
        return $this->belongsTo(HistoriaClinicaDental::class);
    }

    /**
     * Inicializar odontograma vacío con todos los dientes
     */
    public static function inicializarDientes()
    {
        $dientes = [];
        
        // Sistema de numeración FDI (1-52)
        // Cuadrante 1: 11-18 (superior derecho)
        // Cuadrante 2: 21-28 (superior izquierdo)
        // Cuadrante 3: 31-38 (inferior izquierdo)
        // Cuadrante 4: 41-48 (inferior derecho)
        // Cuadrante 5: 51-55 (deciduos superior derecho)
        // Cuadrante 6: 61-65 (deciduos superior izquierdo)
        // Cuadrante 7: 71-75 (deciduos inferior izquierdo)
        // Cuadrante 8: 81-85 (deciduos inferior derecho)
        
        $numeracion = [
            // Permanentes
            18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28,
            48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38,
            // Deciduos
            55, 54, 53, 52, 51, 61, 62, 63, 64, 65,
            85, 84, 83, 82, 81, 71, 72, 73, 74, 75
        ];
        
        foreach ($numeracion as $numero) {
            $dientes[$numero] = [
                'numero' => $numero,
                'estado' => 'sano', // sano, caries, obturado, ausente, fracturado, corona, implante, extraccion_indicada
                'caras_afectadas' => [], // mesial, distal, oclusal, vestibular, lingual/palatina
                'notas' => '',
            ];
        }
        
        return $dientes;
    }
}
