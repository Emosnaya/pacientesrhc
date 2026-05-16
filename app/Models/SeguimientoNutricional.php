<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoNutricional extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_nutricional';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'tipo_exp',
        'fecha_elaboracion',
        'numero_seguimiento',
        'valoracion_bioquimica',
        'valoracion_dietetica',
        'recordatorio_24h',
        'analisis_dieta_habitual',
        'intervencion_nutricional',
        'observaciones',
    ];

    protected $casts = [
        'fecha_elaboracion' => 'date',
        'valoracion_bioquimica' => 'array',
        'valoracion_dietetica' => 'array',
        'recordatorio_24h' => 'array',
        'analisis_dieta_habitual' => 'array',
        'intervencion_nutricional' => 'array',
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
