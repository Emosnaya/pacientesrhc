<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PacienteLinkCompartido extends Model
{
    protected $table = 'paciente_links_compartidos';

    protected $fillable = [
        'paciente_id',
        'tipo',
        'referencia_id',
        'referencia_nombre',
        'token',
        'expires_at',
        'vistas',
        'max_vistas',
        'nota',
        'created_by_user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function isValido(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_vistas && $this->vistas >= $this->max_vistas) return false;
        return true;
    }

    public function incrementarVistas(): void
    {
        $this->increment('vistas');
    }
}
