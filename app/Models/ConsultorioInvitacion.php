<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Invitación para unirse a un consultorio/clínica como colaborador.
 * El token se genera automáticamente; el invitado lo acepta via GET /api/consultorio/invitacion/{token}.
 */
class ConsultorioInvitacion extends Model
{
    protected $table = 'consultorio_invitaciones';

    protected $fillable = [
        'clinica_id',
        'email',
        'token',
        'rol',
        'invitado_por',
        'estado',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // ──────────────────────────────── Boot ──────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $invitacion) {
            if (empty($invitacion->token)) {
                $invitacion->token = Str::random(64);
            }
            if (empty($invitacion->expires_at)) {
                $invitacion->expires_at = now()->addDays(7);
            }
        });
    }

    // ──────────────────────────────── Relaciones ────────────────────────────────

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function invitante()
    {
        return $this->belongsTo(User::class, 'invitado_por');
    }

    // ──────────────────────────────── Helpers ───────────────────────────────────

    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function estaVigente(): bool
    {
        return $this->esPendiente() && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Marcar como expirada si la fecha ya pasó
     */
    public function checkExpiry(): void
    {
        if ($this->esPendiente() && $this->expires_at !== null && $this->expires_at->isPast()) {
            $this->update(['estado' => 'expirada']);
        }
    }
}
