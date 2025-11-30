<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clinica_id',
        'tipo',
        'titulo',
        'descripcion',
        'fecha',
        'hora',
        'color',
        'completado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'string',
        'completado' => 'boolean',
    ];

    /**
     * Relación con el usuario
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
     * Scope para filtrar eventos por clínica
     */
    public function scopeForClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * Scope para filtrar eventos por fecha
     */
    public function scopeByDate($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para filtrar eventos por mes
     */
    public function scopeByMonth($query, $mes, $ano)
    {
        return $query->whereMonth('fecha', $mes)->whereYear('fecha', $ano);
    }

    /**
     * Scope para filtrar eventos por tipo
     */
    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
