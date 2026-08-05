<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FichaEndodoncia extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'fichas_endodoncia';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'user_id',
        'fecha',
        'pieza',
        'diagnostico_pulpar',
        'diagnostico_periapical',
        'dolor',
        'pruebas',
        'hallazgos_rx',
        'etapa',
        'tecnica',
        'material_obturacion',
        'conductos',
        'tratamiento_realizado',
        'plan_tratamiento',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'pieza' => 'integer',
        'conductos' => 'integer',
        'pruebas' => 'array',
        'hallazgos_rx' => 'encrypted',
        'tratamiento_realizado' => 'encrypted',
        'plan_tratamiento' => 'encrypted',
        'observaciones' => 'encrypted',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
