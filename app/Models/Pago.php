<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'user_id',
        'cita_id',
        'monto',
        'metodo_pago',
        'referencia',
        'concepto',
        'notas',
        'firma_paciente',
    ];

    /**
     * Cifrado de datos sensibles
     */
    protected $casts = [
        'monto' => 'encrypted',
        'referencia' => 'encrypted',
        'concepto' => 'encrypted',
        'notas' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
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
     * Relación con el usuario que recibió el pago
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con la cita asociada
     */
    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    /**
     * Scope para filtrar pagos por fecha
     */
    public function scopeByDate($query, $fecha)
    {
        return $query->whereDate('created_at', $fecha);
    }

    /**
     * Scope para filtrar pagos por rango de fechas
     */
    public function scopeBetweenDates($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para filtrar pagos por método de pago
     */
    public function scopeByMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    /**
     * Scope para filtrar pagos por clínica
     */
    public function scopeByClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * Scope para filtrar pagos por sucursal
     */
    public function scopeBySucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Scope para filtrar pagos del día actual
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para filtrar pagos del mes actual
     */
    public function scopeMesActual($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Obtener el monto formateado (ya desencriptado)
     */
    public function getMontoFormateadoAttribute()
    {
        return '$' . number_format($this->monto, 2);
    }
}
