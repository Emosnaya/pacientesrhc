<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClinicaPaciente extends Pivot
{
    protected $table = 'clinica_paciente';

    protected $casts = [
        'motivo_consulta' => 'encrypted',
        'portal_visible_citas' => 'boolean',
        'portal_visible_datos_basicos' => 'boolean',
        'portal_visible_expediente_resumen' => 'boolean',
        'vinculado_at' => 'datetime',
    ];
}
