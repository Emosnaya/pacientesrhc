<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedientePulmonar extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'fecha_consulta',
        'hora_consulta',
        'antecedentes_heredo_familiares',
        'covid19_si_no',
        'covid19_numero_dosis',
        'covid19_fecha_ultima_dosis',
        'influenza_si_no',
        'influenza_ano',
        'neumococo_si_no',
        'neumococo_ano',
        'actividad_fisica_si_no',
        'actividad_fisica_tipo',
        'actividad_fisica_dias_semana',
        'actividad_fisica_tiempo_dia',
        'antecedentes_alergicos',
        'antecedentes_quirurgicos',
        'antecedentes_traumaticos',
        'tabaquismo_boolean',
        'tabaquismo_detalle',
        'alcoholismo_boolean',
        'alcoholismo_detalle',
        'toxicomanias_boolean',
        'toxicomanias_detalle',
        'enfermedades_cronicas',
        'medicamentos_actuales',
        'medico_envia',
        'motivo_envio',
        'disnea_boolean',
        'disnea_detalle',
        'fatiga_boolean',
        'fatiga_detalle',
        'tos_boolean',
        'tos_detalle',
        'dolor_boolean',
        'dolor_detalle',
        'independencia_avd',
        'sueno_boolean',
        'sueno_detalle',
        'estado_emocional',
        'fc',
        'ta',
        'sat_aa',
        'sat_fio2',
        'fio2',
        'sat_inicial',
        'fc_inicial',
        'cabeza_cuello',
        'torax',
        'extremidades',
        'equilibrio',
        'marcha',
        'sit_to_stand_5rep',
        'sit_to_stand_30seg',
        'sit_to_stand_60seg',
        'dinamometria_derecha',
        'dinamometria_izquierda',
        'otros_exploracion',
        'sit_to_stand_5rep_final',
        'sit_to_stand_30seg_final',
        'sit_to_stand_60seg_final',
        'dinamometria_derecha_final',
        'dinamometria_izquierda_final',
        'sat_final',
        'fc_final',
        'nadir',
        'fc_pico',
        'diagnosticos_finales',
        'plan_tratamiento'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_consulta' => 'date',
        'hora_consulta' => 'datetime:H:i',
        'covid19_si_no' => 'boolean',
        'covid19_fecha_ultima_dosis' => 'date',
        'influenza_si_no' => 'boolean',
        'neumococo_si_no' => 'boolean',
        'actividad_fisica_si_no' => 'boolean',
        'enfermedades_cronicas' => 'array',
        'medicamentos_actuales' => 'array',
        'dinamometria_derecha' => 'decimal:2',
        'dinamometria_izquierda' => 'decimal:2'
    ];

    /**
     * Relación con el paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con el usuario que creó el expediente
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los permisos otorgados sobre este expediente
     */
    public function permissions()
    {
        return $this->morphMany(UserPermission::class, 'permissionable');
    }

    /**
     * Obtener peso del paciente
     */
    public function getPesoAttribute()
    {
        return $this->paciente ? $this->paciente->peso : null;
    }

    /**
     * Obtener talla del paciente
     */
    public function getTallaAttribute()
    {
        return $this->paciente ? $this->paciente->talla : null;
    }

    /**
     * Obtener IMC del paciente
     */
    public function getImcAttribute()
    {
        return $this->paciente ? $this->paciente->imc : null;
    }

    /**
     * Obtener el nombre completo del paciente
     */
    public function getNombreCompletoPacienteAttribute(): string
    {
        return $this->paciente ? 
            $this->paciente->nombre . ' ' . $this->paciente->apellidoPat . ' ' . $this->paciente->apellidoMat : 
            'Paciente no encontrado';
    }

    /**
     * Obtener la fecha y hora de consulta formateada
     */
    public function getFechaHoraConsultaAttribute(): string
    {
        return $this->fecha_consulta->format('d/m/Y') . ' ' . $this->hora_consulta->format('H:i');
    }
}
