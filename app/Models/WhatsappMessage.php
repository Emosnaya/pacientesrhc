<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'cita_id',
        'paciente_id',
        'clinica_id',
        'tipo',
        'direccion',
        'estado',
        'twilio_sid',
        'telefono_to',
        'body',
        'error',
        'accionable',
    ];

    protected $casts = [
        'accionable' => 'boolean',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }
}
