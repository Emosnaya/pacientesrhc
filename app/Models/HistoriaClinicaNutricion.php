<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinicaNutricion extends Model
{
    use HasFactory;

    protected $table = 'historia_clinica_nutricion';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_elaboracion',
        'numero_expediente',
        'ocupacion',
        'tutor_nombre',
        'motivo_consulta',
        // Antecedentes heredofamiliares (JSON)
        'antecedentes_heredofamiliares',
        // Antecedentes personales patológicos (JSON)
        'antecedentes_personales_patologicos',
        // Sustancias bioactivas (JSON)
        'sustancias_bioactivas',
        // Antecedentes gineco-obstétricos (JSON)
        'antecedentes_gineco_obstetricos',
        // Padecimiento actual y terapéutica (JSON)
        'padecimiento_terapeutica',
        // Uso de medicamentos (JSON)
        'uso_medicamentos',
        // Valoración cardiovascular TA/FC (JSON)
        'valoracion_cardiovascular',
        // Antropometría escalares
        'peso_habitual',
        'peso_maximo',
        'peso_minimo',
        'fecha_evaluacion_antrop',
        'edad_anos',
        'peso_actual',
        'talla_cm',
        // Pliegues cutáneos (JSON)
        'pliegues_cutaneos',
        // Perímetros (JSON)
        'perimetros',
        // Diámetros (JSON)
        'diametros',
        // Longitudes (JSON)
        'longitudes',
        // Índices (JSON)
        'indices',
        // Actividad física actual (JSON)
        'actividad_fisica',
        // Actividad física total
        'total_minutos_semana',
        'costo_energetico_total_act',
        'cumple_acsm',
        // Recomendación actividad física (JSON)
        'recomendacion_actividad_fisica',
        'total_minutos_semana_rec',
        'costo_energetico_total_rec',
        'observaciones_actividad',
        'no_cumple_acsm',
    ];

    protected $casts = [
        'fecha_elaboracion'              => 'date',
        'fecha_evaluacion_antrop'        => 'date',
        'antecedentes_heredofamiliares'  => 'array',
        'antecedentes_personales_patologicos' => 'array',
        'sustancias_bioactivas'          => 'array',
        'antecedentes_gineco_obstetricos'=> 'array',
        'padecimiento_terapeutica'       => 'array',
        'uso_medicamentos'               => 'array',
        'valoracion_cardiovascular'      => 'array',
        'pliegues_cutaneos'              => 'array',
        'perimetros'                     => 'array',
        'diametros'                      => 'array',
        'longitudes'                     => 'array',
        'indices'                        => 'array',
        'actividad_fisica'               => 'array',
        'recomendacion_actividad_fisica' => 'array',
        'cumple_acsm'                    => 'boolean',
        'no_cumple_acsm'                 => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
