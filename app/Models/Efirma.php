<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

/**
 * Modelo para la e.firma (firma electrónica avanzada) del SAT.
 * Almacena el certificado (.cer) y la llave privada (.key) encriptada.
 */
class Efirma extends Model
{
    protected $table = 'efirmas';

    protected $fillable = [
        'user_id',
        'clinica_id',
        'tipo', // 'personal' o 'fiscal'
        'certificado_cer',
        'llave_key_encrypted',
        'numero_serie',
        'rfc',
        'nombre_titular',
        'curp',
        'vigencia_inicio',
        'vigencia_fin',
        'activa',
        'verificada',
        'ultima_verificacion',
        'subida_por',
        // Usos unificados
        'usar_para_recetas',
        'usar_para_facturacion',
        'facturapi_organization_id',
        'facturapi_api_key_test',
        'facturapi_api_key_live',
        'facturapi_configured',
        'facturacion_serie',
    ];

    protected $casts = [
        'vigencia_inicio' => 'datetime',
        'vigencia_fin' => 'datetime',
        'ultima_verificacion' => 'datetime',
        'activa' => 'boolean',
        'verificada' => 'boolean',
        'usar_para_recetas' => 'boolean',
        'usar_para_facturacion' => 'boolean',
        'facturapi_configured' => 'boolean',
    ];

    protected $hidden = [
        'llave_key_encrypted',
        'certificado_cer',
    ];

    // ─────────────────────────────── Relaciones ───────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Clinica::class);
    }

    public function subidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subida_por');
    }

    // ─────────────────────────────── Scopes ───────────────────────────────────

    /**
     * E.firmas personales (para recetas).
     */
    public function scopePersonal($query)
    {
        return $query->where('tipo', 'personal');
    }

    /**
     * E.firmas fiscales (para facturas).
     */
    public function scopeFiscal($query)
    {
        return $query->where('tipo', 'fiscal');
    }

    /**
     * E.firmas de un usuario específico.
     */
    public function scopeDeUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * E.firmas de una clínica específica.
     */
    public function scopeDeClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    /**
     * E.firma del médico para emitir CFDI (unificada personal o fiscal legacy).
     */
    public static function paraFacturacionUsuario(int $userId): ?self
    {
        $fiscal = static::where('user_id', $userId)->where('tipo', 'fiscal')->first();
        if ($fiscal) {
            return $fiscal;
        }

        // Compatibilidad: flujo unificado anterior (e.firma personal con toggle de facturación)
        $personal = static::where('user_id', $userId)->where('tipo', 'personal')->first();
        if ($personal && ($personal->usar_para_facturacion ?? false)) {
            return $personal;
        }

        return null;
    }

    public function listaParaFacturapi(): bool
    {
        return (bool) $this->facturapi_organization_id && ($this->facturapi_configured ?? false);
    }

    // ─────────────────────────────── Accessors ────────────────────────────────

    /**
     * Verifica si el certificado está vigente.
     */
    public function getEsVigenteAttribute(): bool
    {
        if (!$this->vigencia_fin) {
            return false;
        }
        return Carbon::now()->lt($this->vigencia_fin);
    }

    /**
     * Días restantes de vigencia.
     */
    public function getDiasRestantesAttribute(): int
    {
        if (!$this->vigencia_fin) {
            return 0;
        }
        return max(0, Carbon::now()->diffInDays($this->vigencia_fin, false));
    }

    /**
     * Estado legible de la e.firma.
     */
    public function getEstadoAttribute(): string
    {
        if (!$this->activa) {
            return 'inactiva';
        }
        if (!$this->verificada) {
            return 'pendiente_verificacion';
        }
        if (!$this->es_vigente) {
            return 'vencida';
        }
        if ($this->dias_restantes <= 30) {
            return 'por_vencer';
        }
        return 'vigente';
    }

    // ─────────────────────────────── Métodos ──────────────────────────────────

    /**
     * Guardar la llave privada encriptada.
     */
    public function setLlaveKey(string $keyContent): void
    {
        $this->llave_key_encrypted = Crypt::encryptString(base64_encode($keyContent));
    }

    /**
     * Obtener la llave privada desencriptada (en base64).
     */
    public function getLlaveKeyDecrypted(): ?string
    {
        if (!$this->llave_key_encrypted) {
            return null;
        }
        return base64_decode(Crypt::decryptString($this->llave_key_encrypted));
    }

    /**
     * Guardar el certificado.
     */
    public function setCertificado(string $cerContent): void
    {
        $this->certificado_cer = base64_encode($cerContent);
    }

    /**
     * Obtener el certificado decodificado.
     */
    public function getCertificadoDecoded(): ?string
    {
        if (!$this->certificado_cer) {
            return null;
        }
        return base64_decode($this->certificado_cer);
    }

    /**
     * Extraer información del certificado .cer
     */
    public function extraerInfoCertificado(): array
    {
        $cerContent = $this->getCertificadoDecoded();
        if (!$cerContent) {
            return [];
        }

        $certInfo = openssl_x509_parse($cerContent);
        if (!$certInfo) {
            // Intentar como DER (binario)
            $pem = "-----BEGIN CERTIFICATE-----\n" 
                 . chunk_split(base64_encode($cerContent), 64, "\n") 
                 . "-----END CERTIFICATE-----";
            $certInfo = openssl_x509_parse($pem);
        }

        if (!$certInfo) {
            return [];
        }

        // Extraer datos del subject
        $subject = $certInfo['subject'] ?? [];
        
        // El RFC en certificados SAT puede estar en varios lugares:
        // 1. x500UniqueIdentifier (más común en e.firma)
        // 2. serialNumber del subject
        // 3. OU (Organizational Unit)
        // 4. En el nombre (CN) separado por /
        $rfc = null;
        // Flag 'u' es crítico para que Ñ y & funcionen correctamente en UTF-8
        $rfcPattern     = '/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/iu';
        $rfcPatternFind = '/([A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3})/iu';

        // Helper: normaliza encoding a UTF-8 antes de aplicar regex
        $toUtf8 = function (string $s): string {
            if (mb_detect_encoding($s, 'UTF-8', true)) {
                return $s;
            }
            return mb_convert_encoding($s, 'UTF-8', mb_detect_encoding($s) ?: 'ISO-8859-1');
        };

        // 1. x500UniqueIdentifier (más común en CSD del SAT)
        if (!empty($subject['x500UniqueIdentifier'])) {
            $candidate = $toUtf8(trim($subject['x500UniqueIdentifier']));
            if (preg_match($rfcPattern, $candidate)) {
                $rfc = mb_strtoupper($candidate, 'UTF-8');
            }
        }

        // 2. serialNumber del subject
        if (!$rfc && !empty($subject['serialNumber'])) {
            $candidate = $toUtf8(trim($subject['serialNumber']));
            if (preg_match($rfcPattern, $candidate)) {
                $rfc = mb_strtoupper($candidate, 'UTF-8');
            }
        }

        // 3. OU (puede ser array)
        if (!$rfc && !empty($subject['OU'])) {
            $ous = is_array($subject['OU']) ? $subject['OU'] : [$subject['OU']];
            foreach ($ous as $ou) {
                $candidate = $toUtf8(trim($ou));
                if (preg_match($rfcPattern, $candidate)) {
                    $rfc = mb_strtoupper($candidate, 'UTF-8');
                    break;
                }
            }
        }

        // 4. CN: "NOMBRE / RFC" o "RFC / NOMBRE"
        if (!$rfc && !empty($subject['CN'])) {
            $cn = $toUtf8($subject['CN']);
            if (preg_match($rfcPatternFind, $cn, $matches)) {
                $rfc = mb_strtoupper($matches[1], 'UTF-8');
            }
        }

        // 5. Buscar en extensions (último recurso)
        if (!$rfc && isset($certInfo['extensions'])) {
            foreach ($certInfo['extensions'] as $ext) {
                if (is_string($ext)) {
                    $ext = $toUtf8($ext);
                    if (preg_match($rfcPatternFind, $ext, $matches)) {
                        $rfc = mb_strtoupper($matches[1], 'UTF-8');
                        break;
                    }
                }
            }
        }

        // Número de serie del certificado
        $numeroSerie = isset($certInfo['serialNumberHex']) 
            ? strtoupper($certInfo['serialNumberHex']) 
            : (isset($certInfo['serialNumber']) ? dechex($certInfo['serialNumber']) : null);

        return [
            'numero_serie' => $numeroSerie,
            'rfc' => $rfc,
            'nombre_titular' => $subject['CN'] ?? $subject['name'] ?? null,
            'curp' => null, // CURP no suele estar en el certificado
            'vigencia_inicio' => isset($certInfo['validFrom_time_t']) 
                ? Carbon::createFromTimestamp($certInfo['validFrom_time_t']) 
                : null,
            'vigencia_fin' => isset($certInfo['validTo_time_t']) 
                ? Carbon::createFromTimestamp($certInfo['validTo_time_t']) 
                : null,
        ];
    }

    /**
     * Verificar que la llave corresponde al certificado.
     */
    public function verificarPareja(string $keyPassword): bool
    {
        $cerContent = $this->getCertificadoDecoded();
        $keyContent = $this->getLlaveKeyDecrypted();

        if (!$cerContent || !$keyContent) {
            return false;
        }

        // Convertir .cer a PEM si es necesario
        $certPem = $cerContent;
        if (strpos($cerContent, '-----BEGIN') === false) {
            $certPem = "-----BEGIN CERTIFICATE-----\n" 
                     . chunk_split(base64_encode($cerContent), 64, "\n") 
                     . "-----END CERTIFICATE-----";
        }

        // Intentar cargar la llave privada con la contraseña
        $privateKey = openssl_pkey_get_private($keyContent, $keyPassword);
        
        if (!$privateKey) {
            // Intentar como DER
            $keyPem = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n" 
                    . chunk_split(base64_encode($keyContent), 64, "\n") 
                    . "-----END ENCRYPTED PRIVATE KEY-----";
            $privateKey = openssl_pkey_get_private($keyPem, $keyPassword);
        }

        if (!$privateKey) {
            return false;
        }

        // Verificar que la llave corresponde al certificado
        $certPublicKey = openssl_pkey_get_public($certPem);
        if (!$certPublicKey) {
            return false;
        }

        $certKeyDetails = openssl_pkey_get_details($certPublicKey);
        $privateKeyDetails = openssl_pkey_get_details($privateKey);

        // Comparar el módulo (n) de las llaves RSA
        return $certKeyDetails['rsa']['n'] === $privateKeyDetails['rsa']['n'];
    }

    /**
     * Firmar datos con la e.firma.
     */
    public function firmar(string $datos, string $keyPassword): ?array
    {
        if (!$this->es_vigente) {
            return null;
        }

        $keyContent = $this->getLlaveKeyDecrypted();
        if (!$keyContent) {
            return null;
        }

        // Cargar llave privada
        $privateKey = openssl_pkey_get_private($keyContent, $keyPassword);
        if (!$privateKey) {
            // Intentar como DER
            $keyPem = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n" 
                    . chunk_split(base64_encode($keyContent), 64, "\n") 
                    . "-----END ENCRYPTED PRIVATE KEY-----";
            $privateKey = openssl_pkey_get_private($keyPem, $keyPassword);
        }

        if (!$privateKey) {
            return null;
        }

        // Crear firma
        $firma = '';
        $success = openssl_sign($datos, $firma, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$success) {
            return null;
        }

        return [
            'firma' => base64_encode($firma),
            'cadena_original' => $datos,
            'algoritmo' => 'SHA256withRSA',
            'numero_serie' => $this->numero_serie,
            'fecha_firma' => now()->toIso8601String(),
        ];
    }
}
