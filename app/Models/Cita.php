<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cita extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'paciente_id',
        'admin_id',
        'user_id',
        'clinica_id',
        'fecha',
        'hora',
        'estado',
        'primera_vez',
        'notas',
        'custom_email',
        'motivo_cancelacion'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'primera_vez' => 'boolean',
    ];

    /**
     * Relación con el paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con el administrador que creó la cita
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relación con el doctor que atiende la cita
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con la clínica
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Scope para filtrar citas por fecha
     */
    public function scopeByDate($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para filtrar citas por mes
     */
    public function scopeByMonth($query, $mes, $ano)
    {
        return $query->whereMonth('fecha', $mes)->whereYear('fecha', $ano);
    }

    /**
     * Scope para filtrar citas por estado
     */
    public function scopeByStatus($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar citas de un paciente específico
     */
    public function scopeForPaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    /**
     * Scope para filtrar citas de un admin específico
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope para filtrar citas por clínica
     */
    public function scopeForClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * Obtener el nombre completo del paciente
     */
    public function getPacienteNombreCompletoAttribute()
    {
        return $this->paciente ?
            $this->paciente->nombre . ' ' . $this->paciente->apellidoPat . ' ' . $this->paciente->apellidoMat :
            'N/A';
    }

    /**
     * Obtener el nombre completo del admin
     */
    public function getAdminNombreCompletoAttribute()
    {
        return $this->admin ?
            $this->admin->nombre . ' ' . $this->admin->apellidoPat . ' ' . $this->admin->apellidoMat :
            'N/A';
    }

    /**
     * Obtener la fecha y hora formateada
     */
    public function getFechaHoraFormateadaAttribute()
    {
        $fecha = Carbon::parse($this->fecha)->format('d/m/Y');
        $hora = Carbon::parse($this->hora)->format('H:i');
        return $fecha . ' ' . $hora;
    }

    /**
     * Verificar si la cita es hoy
     */
    public function esHoy()
    {
        return $this->fecha->isToday();
    }

    /**
     * Verificar si la cita es en el futuro
     */
    public function esFutura()
    {
        return $this->fecha->isFuture() || ($this->fecha->isToday() && Carbon::parse($this->hora)->isFuture());
    }

    /**
     * Verificar si la cita es en el pasado
     */
    public function esPasada()
    {
        return !$this->esFutura();
    }
}
