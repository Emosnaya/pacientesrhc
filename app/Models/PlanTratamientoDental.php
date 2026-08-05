<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanTratamientoDental extends Model
{
    use SoftDeletes;

    protected $table = 'planes_tratamiento_dental';

    protected $fillable = [
        'clinica_id',
        'sucursal_id',
        'paciente_id',
        'user_id',
        'odontograma_id',
        'presupuesto_id',
        'titulo',
        'estado',
        'fecha',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(PlanTratamientoDentalItem::class, 'plan_tratamiento_dental_id')
            ->orderBy('fase')
            ->orderBy('orden')
            ->orderBy('id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function odontograma()
    {
        return $this->belongsTo(Odontograma::class);
    }

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
