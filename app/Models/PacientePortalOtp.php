<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PacientePortalOtp extends Model
{
    protected $table = 'paciente_portal_otps';

    protected $fillable = [
        'paciente_id',
        'otp_hash',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public static function hashCode(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public function matches(string $plain): bool
    {
        return hash_equals($this->otp_hash, self::hashCode($plain));
    }

    public function isUsable(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }

    public function markConsumed(): void
    {
        $this->update(['consumed_at' => now()]);
    }
}
