<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaAltaFisioterapia extends Model
{
    use HasFactory;

    protected $table = 'nota_alta_fisioterapia';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'fecha',
        'diagnostico_medico',
        'diagnostico_fisioterapeutico_inicial',
        'fecha_inicio_atencion',
        'fecha_termino',
        'numero_sesiones',
        'tratamiento_otorgado',
        'evolucion_resultados',
        'dolor_alta_eva',
        'mejoria_funcional',
        'objetivos_alcanzados',
        'estado_funcional_alta',
        'recomendaciones_seguimiento',
        'pronostico_funcional',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_inicio_atencion' => 'date',
        'fecha_termino' => 'date',
        'numero_sesiones' => 'integer',
        'dolor_alta_eva' => 'integer',
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
