<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaEvolucionFisioterapia extends Model
{
    use HasFactory, Auditable;

    protected $table = 'nota_evolucion_fisioterapia';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'fecha',
        'hora',
        'diagnostico_fisioterapeutico',
        'observaciones_subjetivas',
        'dolor_eva',
        'funcionalidad',
        'observaciones_objetivas',
        'tecnicas_modalidades_aplicadas',
        'ejercicio_terapeutico',
        'respuesta_tratamiento',
        'plan',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'dolor_eva' => 'integer',
        // Cifrado de campos sensibles
        'diagnostico_fisioterapeutico' => 'encrypted',
        'observaciones_subjetivas' => 'encrypted',
        'observaciones_objetivas' => 'encrypted',
        'tecnicas_modalidades_aplicadas' => 'encrypted',
        'ejercicio_terapeutico' => 'encrypted',
        'respuesta_tratamiento' => 'encrypted',
        'plan' => 'encrypted',
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
