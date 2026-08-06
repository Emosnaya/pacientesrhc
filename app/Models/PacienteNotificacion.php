<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteNotificacion extends Model
{
    protected $table = 'paciente_notificaciones';

    protected $fillable = [
        'paciente_id',
        'tipo',
        'titulo',
        'cuerpo',
        'data',
        'leida_at',
    ];

    protected $casts = [
        'data' => 'array',
        'leida_at' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public static function notify(
        int $pacienteId,
        string $tipo,
        string $titulo,
        ?string $cuerpo = null,
        ?array $data = null
    ): self {
        return self::create([
            'paciente_id' => $pacienteId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'cuerpo' => $cuerpo,
            'data' => $data,
        ]);
    }
}
