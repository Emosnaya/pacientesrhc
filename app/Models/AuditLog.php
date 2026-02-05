<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'clinica_id',
        'sucursal_id',
        'evento',
        'modelo_afectado',
        'id_recurso',
        'ip_address',
        'user_agent',
        'datos_anteriores',
        'datos_nuevos',
        'descripcion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que realizó la acción
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
     * Relación con la sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeForModel($query, string $modelClass, int $modelId)
    {
        return $query->where('modelo_afectado', $modelClass)
                     ->where('id_recurso', $modelId);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por clínica
     */
    public function scopeByClinica($query, int $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * Scope para filtrar por evento
     */
    public function scopeByEvento($query, string $evento)
    {
        return $query->where('evento', $evento);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
