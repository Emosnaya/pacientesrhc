<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NotaSubsecuenteCardiologia extends Model
{
    use HasFactory;

    protected $table = 'nota_subsecuente_cardiologias';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_consulta',
        'hora',
        'motivo_consulta',
        'sintomas',
        'exploracion_fisica',
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
        'diagnostico_principal',
        'diagnostico_cie10',
        'diagnosticos_secundarios',
        'estudios_solicitados',
        'laboratorios',
        'medicamentos_receta',
        'indicaciones_receta',
        'proxima_cita',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'proxima_cita' => 'date',
        'laboratorios' => 'array',
        'medicamentos_receta' => 'array',
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

    public function ordenLaboratorio(): HasOne
    {
        return $this->hasOne(OrdenLaboratorio::class, 'nota_subsecuente_id');
    }

    public function receta(): HasOne
    {
        return $this->hasOne(Receta::class, 'nota_subsecuente_id');
    }
}
