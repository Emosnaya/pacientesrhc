<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoComercialSolicitud extends Model
{
    use HasFactory;

    protected $table = 'contacto_comercial_solicitudes';

    protected $fillable = [
        'clinica_id',
        'user_id',
        'nombre_contacto',
        'email',
        'telefono',
        'cargo',
        'tipo_solicitud',
        'mensaje',
        'plan_actual',
        'plan_interes',
        'estado',
        'notas_internas',
        'fecha_contacto',
        'atendido_por',
        'metadata',
    ];

    protected $casts = [
        'fecha_contacto' => 'datetime',
        'metadata' => 'array',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeDeClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    // ─── Métodos ──────────────────────────────────────────────────────

    public function marcarContactado(?int $usuarioId = null): void
    {
        $this->update([
            'estado' => 'contactado',
            'fecha_contacto' => now(),
            'atendido_por' => $usuarioId,
        ]);
    }
}
