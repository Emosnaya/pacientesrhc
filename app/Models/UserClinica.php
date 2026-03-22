<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot enriquecido para la relación user ↔ clinica.
 * Almacena el rol del usuario dentro de esa clínica y quién lo invitó.
 */
class UserClinica extends Pivot
{
    protected $table = 'user_clinicas';

    public $incrementing = true; // tiene id propio para poder actualizarlo

    protected $fillable = [
        'user_id',
        'clinica_id',
        'rol_en_clinica',   // propietario | colaborador | visor
        'activa',
        'invitado_por',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // ──────────────────────────────── Relaciones ────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function invitadoPor()
    {
        return $this->belongsTo(User::class, 'invitado_por');
    }

    // ──────────────────────────────── Helpers ───────────────────────────────────

    public function esPropietario(): bool
    {
        return $this->rol_en_clinica === 'propietario';
    }

    public function esColaborador(): bool
    {
        return $this->rol_en_clinica === 'colaborador';
    }
}
