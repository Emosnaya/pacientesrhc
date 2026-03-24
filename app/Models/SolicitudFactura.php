<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudFactura extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_factura';

    protected $fillable = [
        'clinica_id',
        'paciente_id',
        'datos_fiscales_id',
        'pago_id',
        'emisor_tipo',
        'doctor_id',
        'concepto',
        'clave_prod_serv',
        'clave_unidad',
        'subtotal',
        'iva',
        'total',
        'estado',
        'uuid',
        'folio_fiscal',
        'cadena_original_sat',
        'sello_sat',
        'sello_cfdi',
        'fecha_timbrado',
        'serie',
        'folio',
        'xml_path',
        'pdf_path',
        'enviada_at',
        'enviada_a',
        'cancelada_at',
        'motivo_cancelacion',
        'uuid_sustitucion',
        'solicitada_por',
        'procesada_por',
        'notas',
        'error_mensaje',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_timbrado' => 'datetime',
        'enviada_at' => 'datetime',
        'cancelada_at' => 'datetime',
    ];

    // =====================================================
    // CONSTANTES
    // =====================================================

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_FACTURADA = 'facturada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_ERROR = 'error';

    const EMISOR_CLINICA = 'clinica';
    const EMISOR_DOCTOR = 'doctor';

    // =====================================================
    // RELACIONES
    // =====================================================

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function datosFiscales(): BelongsTo
    {
        return $this->belongsTo(DatosFiscales::class, 'datos_fiscales_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function solicitadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitada_por');
    }

    public function procesadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesada_por');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getEstaFacturadaAttribute(): bool
    {
        return $this->estado === self::ESTADO_FACTURADA && !empty($this->uuid);
    }

    public function getEstaCanceladaAttribute(): bool
    {
        return $this->estado === self::ESTADO_CANCELADA;
    }

    public function getEstaEnProcesoAttribute(): bool
    {
        return $this->estado === self::ESTADO_EN_PROCESO;
    }

    public function getEstaPendienteAttribute(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function getTieneErrorAttribute(): bool
    {
        return $this->estado === self::ESTADO_ERROR;
    }

    public function getPuedeReintentarAttribute(): bool
    {
        return in_array($this->estado, [self::ESTADO_ERROR, self::ESTADO_PENDIENTE]);
    }

    public function getPuedeCancelarAttribute(): bool
    {
        return $this->estado === self::ESTADO_FACTURADA && !empty($this->uuid);
    }

    /**
     * Obtener el nombre del emisor (clínica o doctor)
     */
    public function getNombreEmisorAttribute(): string
    {
        if ($this->emisor_tipo === self::EMISOR_DOCTOR && $this->doctor) {
            return $this->doctor->name;
        }
        return $this->clinica?->nombre ?? 'Clínica';
    }

    /**
     * Etiqueta de estado formateada
     */
    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EN_PROCESO => 'En proceso',
            self::ESTADO_FACTURADA => 'Facturada',
            self::ESTADO_CANCELADA => 'Cancelada',
            self::ESTADO_ERROR => 'Error',
            default => $this->estado,
        };
    }

    /**
     * Color del badge según estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'amber',
            self::ESTADO_EN_PROCESO => 'blue',
            self::ESTADO_FACTURADA => 'emerald',
            self::ESTADO_CANCELADA => 'gray',
            self::ESTADO_ERROR => 'red',
            default => 'gray',
        };
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeFacturadas($query)
    {
        return $query->where('estado', self::ESTADO_FACTURADA);
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', self::ESTADO_CANCELADA);
    }

    public function scopeConErrores($query)
    {
        return $query->where('estado', self::ESTADO_ERROR);
    }

    public function scopeDeClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    public function scopeDePaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeDeDoctor($query, $doctorId)
    {
        return $query->where('emisor_tipo', self::EMISOR_DOCTOR)
                     ->where('doctor_id', $doctorId);
    }

    // =====================================================
    // MÉTODOS
    // =====================================================

    /**
     * Marcar como en proceso de timbrado
     */
    public function marcarEnProceso(): void
    {
        $this->update(['estado' => self::ESTADO_EN_PROCESO]);
    }

    /**
     * Marcar como facturada con los datos del CFDI
     */
    public function marcarFacturada(array $datosCfdi, int $procesadaPor): void
    {
        $this->update([
            'estado' => self::ESTADO_FACTURADA,
            'uuid' => $datosCfdi['uuid'] ?? null,
            'folio_fiscal' => $datosCfdi['folio_fiscal'] ?? null,
            'cadena_original_sat' => $datosCfdi['cadena_original_sat'] ?? null,
            'sello_sat' => $datosCfdi['sello_sat'] ?? null,
            'sello_cfdi' => $datosCfdi['sello_cfdi'] ?? null,
            'fecha_timbrado' => $datosCfdi['fecha_timbrado'] ?? now(),
            'serie' => $datosCfdi['serie'] ?? null,
            'folio' => $datosCfdi['folio'] ?? null,
            'xml_path' => $datosCfdi['xml_path'] ?? null,
            'pdf_path' => $datosCfdi['pdf_path'] ?? null,
            'procesada_por' => $procesadaPor,
        ]);
    }

    /**
     * Marcar como error
     */
    public function marcarError(string $mensaje): void
    {
        $this->update([
            'estado' => self::ESTADO_ERROR,
            'error_mensaje' => $mensaje,
        ]);
    }

    /**
     * Marcar como cancelada
     */
    public function marcarCancelada(string $motivo, ?string $uuidSustitucion = null): void
    {
        $this->update([
            'estado' => self::ESTADO_CANCELADA,
            'cancelada_at' => now(),
            'motivo_cancelacion' => $motivo,
            'uuid_sustitucion' => $uuidSustitucion,
        ]);
    }

    /**
     * Registrar envío de factura
     */
    public function registrarEnvio(string $email): void
    {
        $this->update([
            'enviada_at' => now(),
            'enviada_a' => $email,
        ]);
    }

    /**
     * Reintentar facturación
     */
    public function reintentar(): void
    {
        $this->update([
            'estado' => self::ESTADO_PENDIENTE,
            'error_mensaje' => null,
        ]);
    }
}
