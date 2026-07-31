<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicaExport extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'clinica_id',
        'user_id',
        'sucursal_id',
        'status',
        'scope',
        'pacientes_total',
        'pacientes_done',
        'expedientes_total',
        'archivos_total',
        'ruta_zip',
        'tamanio_bytes',
        'error_message',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'scope' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isDownloadable(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && ! empty($this->ruta_zip)
            && ! $this->isExpired();
    }

    public function progressPercent(): int
    {
        if ($this->pacientes_total <= 0) {
            return $this->status === self::STATUS_COMPLETED ? 100 : 0;
        }

        return (int) min(100, round(($this->pacientes_done / $this->pacientes_total) * 100));
    }
}
