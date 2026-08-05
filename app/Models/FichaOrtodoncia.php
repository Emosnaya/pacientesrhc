<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FichaOrtodoncia extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'fichas_ortodoncia';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'user_id',
        'fecha',
        'clase_angle',
        'patron_esqueletal',
        'overjet_mm',
        'overbite_mm',
        'apinamiento',
        'habitos',
        'tipo_aparato',
        'fase',
        'proximo_control',
        'diagnostico',
        'plan_tratamiento',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'proximo_control' => 'date',
        'overjet_mm' => 'float',
        'overbite_mm' => 'float',
        'diagnostico' => 'encrypted',
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
