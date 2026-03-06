<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'clinica_id',
        'sucursal_id',
        'user_id',
        'monto',
        'tipo_egreso',
        'concepto',
        'proveedor',
        'factura',
        'notas',
    ];

    /**
     * Cifrado de datos sensibles
     */
    protected $casts = [
        'monto' => 'encrypted',
        'concepto' => 'encrypted',
        'proveedor' => 'encrypted',
        'factura' => 'encrypted',
        'notas' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
     * Relación con el usuario que registró el egreso
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para filtrar egresos por fecha
     */
    public function scopeByDate($query, $fecha)
    {
        return $query->whereDate('created_at', $fecha);
    }

    /**
     * Scope para filtrar egresos por rango de fechas
     */
    public function scopeBetweenDates($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para filtrar egresos por tipo
     */
    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo_egreso', $tipo);
    }

    /**
     * Scope para filtrar egresos por clínica
     */
    public function scopeByClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * Scope para filtrar egresos por sucursal
     */
    public function scopeBySucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Scope para filtrar egresos del día actual
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para filtrar egresos del mes actual
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

    /**
     * Obtener etiqueta del tipo de egreso
     */
    public function getTipoEgresoLabelAttribute()
    {
        $labels = [
            'compra_material' => 'Compra de Material',
            'servicio' => 'Servicio',
            'mantenimiento' => 'Mantenimiento',
            'nomina' => 'Nómina',
            'renta' => 'Renta',
            'servicios_publicos' => 'Servicios Públicos',
            'otro' => 'Otro',
        ];

        return $labels[$this->tipo_egreso] ?? 'Otro';
    }
}
