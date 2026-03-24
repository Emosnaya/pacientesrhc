<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatosFiscales extends Model
{
    use HasFactory;

    protected $table = 'datos_fiscales';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'rfc',
        'razon_social',
        'codigo_postal',
        'regimen_fiscal',
        'uso_cfdi',
        'email_facturacion',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'localidad',
        'municipio',
        'estado',
        'pais',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function solicitudesFactura(): HasMany
    {
        return $this->hasMany(SolicitudFactura::class, 'datos_fiscales_id');
    }

    public function regimenFiscalInfo(): BelongsTo
    {
        return $this->belongsTo(CatRegimenFiscal::class, 'regimen_fiscal', 'clave');
    }

    public function usoCfdiInfo(): BelongsTo
    {
        return $this->belongsTo(CatUsoCfdi::class, 'uso_cfdi', 'clave');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * RFC formateado (con guiones si aplica)
     */
    public function getRfcFormateadoAttribute(): string
    {
        $rfc = strtoupper($this->rfc);
        if (strlen($rfc) === 13) {
            // Persona física: AAAA000000XXX -> AAAA-000000-XXX
            return substr($rfc, 0, 4) . '-' . substr($rfc, 4, 6) . '-' . substr($rfc, 10);
        } elseif (strlen($rfc) === 12) {
            // Persona moral: AAA000000XXX -> AAA-000000-XXX
            return substr($rfc, 0, 3) . '-' . substr($rfc, 3, 6) . '-' . substr($rfc, 9);
        }
        return $rfc;
    }

    /**
     * Determinar si es persona física o moral por RFC
     */
    public function getEsPersonaFisicaAttribute(): bool
    {
        return strlen($this->rfc) === 13;
    }

    /**
     * Dirección fiscal completa formateada
     */
    public function getDireccionCompletaAttribute(): string
    {
        $partes = [];
        
        if ($this->calle) {
            $direccion = $this->calle;
            if ($this->numero_exterior) $direccion .= ' #' . $this->numero_exterior;
            if ($this->numero_interior) $direccion .= ' Int. ' . $this->numero_interior;
            $partes[] = $direccion;
        }
        
        if ($this->colonia) $partes[] = 'Col. ' . $this->colonia;
        if ($this->municipio) $partes[] = $this->municipio;
        if ($this->estado) $partes[] = $this->estado;
        if ($this->codigo_postal) $partes[] = 'C.P. ' . $this->codigo_postal;
        
        return implode(', ', $partes);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDeClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    public function scopeDePaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    // =====================================================
    // MÉTODOS
    // =====================================================

    /**
     * Validar RFC según reglas del SAT
     */
    public static function validarRfc(string $rfc): bool
    {
        $rfc = strtoupper(trim($rfc));
        
        // Patrón para persona física (13 caracteres)
        $patronFisica = '/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/';
        
        // Patrón para persona moral (12 caracteres)
        $patronMoral = '/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/';
        
        return preg_match($patronFisica, $rfc) || preg_match($patronMoral, $rfc);
    }

    /**
     * Preparar datos para el receptor del CFDI
     */
    public function toCfdiReceptor(): array
    {
        return [
            'Rfc' => strtoupper($this->rfc),
            'Nombre' => strtoupper($this->razon_social),
            'DomicilioFiscalReceptor' => $this->codigo_postal,
            'RegimenFiscalReceptor' => $this->regimen_fiscal,
            'UsoCFDI' => $this->uso_cfdi,
        ];
    }
}
