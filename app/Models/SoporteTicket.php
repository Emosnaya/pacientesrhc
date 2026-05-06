<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoporteTicket extends Model
{
    use HasFactory;

    protected $table = 'soporte_tickets';

    protected $fillable = [
        'numero_ticket',
        'categoria',
        'asunto',
        'mensaje',
        'user_id',
        'clinica_id',
        'usuario_nombre',
        'usuario_email',
        'clinica_nombre',
        'status',
        'prioridad',
        'respuesta',
        'resuelto_por',
        'resuelto_at',
        'origen',
    ];

    protected $casts = [
        'resuelto_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot del modelo para auto-generar numero_ticket
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->numero_ticket)) {
                $ticket->numero_ticket = self::generarNumeroTicket();
            }
        });
    }

    /**
     * Genera un número de ticket único: TKT-YYYY-XXXXX
     */
    public static function generarNumeroTicket(): string
    {
        $year = date('Y');
        $prefix = "TKT-{$year}-";
        
        // Obtener el último ticket del año actual
        $ultimoTicket = self::where('numero_ticket', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($ultimoTicket) {
            // Extraer el número secuencial
            $ultimoNumero = intval(substr($ultimoTicket->numero_ticket, -5));
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return $prefix . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Relación con el usuario que creó el ticket
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la clínica
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Mensajes de chat en vivo de este ticket
     */
    public function mensajes()
    {
        return $this->hasMany(SoporteMensaje::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    /**
     * Relación con el admin que resolvió el ticket
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para tickets nuevos (sin atender)
     */
    public function scopeNuevos($query)
    {
        return $query->where('status', 'nuevo');
    }

    /**
     * Scope para tickets en proceso
     */
    public function scopeEnProceso($query)
    {
        return $query->where('status', 'en_proceso');
    }

    /**
     * Scope para tickets resueltos
     */
    public function scopeResueltos($query)
    {
        return $query->where('status', 'resuelto');
    }

    /**
     * Marcar como en proceso
     */
    public function marcarEnProceso()
    {
        $this->update(['status' => 'en_proceso']);
    }

    /**
     * Marcar como resuelto
     */
    public function resolver($respuesta, $adminId)
    {
        $this->update([
            'status' => 'resuelto',
            'respuesta' => $respuesta,
            'resuelto_por' => $adminId,
            'resuelto_at' => now(),
        ]);
    }

    /**
     * Cerrar ticket
     */
    public function cerrar()
    {
        $this->update(['status' => 'cerrado']);
    }

    /**
     * Obtener el color del badge según status
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'nuevo' => 'blue',
            'en_proceso' => 'yellow',
            'resuelto' => 'green',
            'cerrado' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Obtener el color del badge según prioridad
     */
    public function getPrioridadColorAttribute(): string
    {
        return match($this->prioridad) {
            'baja' => 'gray',
            'media' => 'blue',
            'alta' => 'orange',
            'urgente' => 'red',
            default => 'gray',
        };
    }
}
