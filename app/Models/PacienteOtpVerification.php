<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteOtpVerification extends Model
{
    protected $table = 'paciente_otp_verifications';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'user_id',
        'otp_code',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el OTP es válido y no ha expirado
     */
    public function isValid(string $code): bool
    {
        return $this->otp_code === $code
            && $this->expires_at->isFuture()
            && is_null($this->verified_at);
    }

    /**
     * Marcar como verificado
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }
}
