<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinica_id',
        'sucursal_id',
        'paciente_id',
        'user_id',
        'titulo',
        'descripcion',
        'estado',
        'fecha_emision',
        'fecha_vigencia',
        'monto_total',
        'notas',
        'condiciones_pago',
        'paciente_aceptado_at',
        'paciente_rechazado_at',
        'paciente_aceptacion_tipo',
        'firma_paciente',
        'paciente_aceptacion_ip',
        'paciente_aceptacion_user_agent',
    ];

    protected $casts = [
        'monto_total' => 'float',
        'fecha_emision' => 'date',
        'fecha_vigencia' => 'date',
        'paciente_aceptado_at' => 'datetime',
        'paciente_rechazado_at' => 'datetime',
    ];

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PresupuestoItem::class)->orderBy('orden')->orderBy('id');
    }
}
