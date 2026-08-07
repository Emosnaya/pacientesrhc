<?php

namespace App\Models;

use App\Services\ExpoPushService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

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
        $row = self::create([
            'paciente_id' => $pacienteId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'cuerpo' => $cuerpo,
            'data' => $data,
        ]);

        try {
            app(ExpoPushService::class)->sendToPaciente(
                $pacienteId,
                $titulo,
                $cuerpo,
                array_merge(['tipo' => $tipo, 'notif_id' => $row->id], $data ?? [])
            );
        } catch (\Throwable $e) {
            Log::warning('Push tras notificación falló: '.$e->getMessage());
        }

        return $row;
    }
}
