<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalExpedienteCompartido extends Model
{
    protected $table = 'portal_expediente_compartidos';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'tipo_exp',
        'expediente_id',
        'tipo_nombre_snapshot',
        'fecha_snapshot',
    ];

    protected $casts = [
        'fecha_snapshot' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }
}
