<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaMedicamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'receta_id',
        'medicamento',
        'presentacion',
        'dosis',
        'frecuencia',
        'duracion',
        'indicaciones_especificas',
        'orden',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
}
