<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\Efirma;
use App\Models\SolicitudFactura;
use App\Models\DatosFiscales;
use App\Support\SatFormaPago;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * FacturapiService
 *
 * Integración con Facturapi v2 (https://www.facturapi.io/v2).
 *
 * Modelo multi-organización:
 *  - $userApiKey (UserKey maestra del .env) → gestión de organizaciones (crear, subir CSD, obtener keys)
 *  - $orgApiKey (test key por organización, guardada en clinicas.facturapi_api_key_test) → timbrar, clientes, descargas
 */
class FacturapiService
{
    protected string $userApiKey;
    protected string $baseUrl = 'https://www.facturapi.io/v2';

    public function __construct()
    {
        $this->userApiKey = config('facturapi.user_key', '');
    }

    // ──────────────────────────────────────────────
    // ORGANIZACIONES (requiere UserKey maestra)
    // ──────────────────────────────────────────────

    /**
     * Crear una nueva organización en Facturapi para la clínica.
     * Pasos: crear → actualizar datos legales → subir CSD → obtener test key.
     *
     * @param Clinica $clinica
     * @param array $csdData {
     *   razon_social, rfc, codigo_postal, regimen_fiscal,
     *   cer_path (path al archivo .cer), key_path (path al archivo .key), password
     * }
     */
    public function crearOrganizacion(Clinica $clinica, array $csdData): array
    {
        try {
            // 1. Crear organización — usar razón social del CSD como nombre comercial
            //    (Facturapi asigna el RFC automáticamente del CSD al subirlo)
            $orgName = !empty($csdData['razon_social'])
                ? $csdData['razon_social']
                : $clinica->nombre;

            $createResponse = Http::withToken($this->userApiKey)
                ->post("{$this->baseUrl}/organizations", [
                    'name' => $orgName,
                ]);

            if (!$createResponse->successful()) {
                return [
                    'success' => false,
                    'error' => $createResponse->json()['message'] ?? 'Error al registrar el CSD para facturación',
                ];
            }

            $org = $createResponse->json();
            $organizationId = $org['id'];

            // 2. Actualizar datos legales (RFC, razón social, régimen, CP)
            Http::withToken($this->userApiKey)
                ->put("{$this->baseUrl}/organizations/{$organizationId}/legal", [
                    'legal_name' => strtoupper($csdData['razon_social'] ?? $clinica->nombre),
                    'tax_system'  => $csdData['regimen_fiscal'] ?? '612',
                    'address'     => [
                        'zip' => $csdData['codigo_postal'] ?? '00000',
                    ],
                ]);

            // 3. Subir CSD — Facturapi asigna RFC automáticamente del certificado
            $csdResult = $this->uploadCertificate($organizationId, $csdData);
            if (!$csdResult['success']) {
                Log::warning("Organización creada ({$organizationId}) pero fallo al subir CSD: " . $csdResult['error']);
            } else {
                // 4. Actualizar legal_name y nombre comercial con la razón social del CSD.
                //    Facturapi NO actualiza legal_name automáticamente, solo el tax_id (RFC).
                $legalName = mb_strtoupper($csdData['razon_social'] ?? $clinica->nombre, 'UTF-8');

                Http::withToken($this->userApiKey)
                    ->put("{$this->baseUrl}/organizations/{$organizationId}", [
                        'name' => $legalName,
                    ]);

                Http::withToken($this->userApiKey)
                    ->put("{$this->baseUrl}/organizations/{$organizationId}/legal", [
                        'legal_name' => $legalName,
                        'tax_system' => $csdData['regimen_fiscal'] ?? '601',
                        'address'    => ['zip' => $csdData['codigo_postal'] ?? '00000'],
                    ]);

                Log::info("Org Facturapi nombre y datos legales actualizados", [
                    'organization_id' => $organizationId,
                    'legal_name'      => $legalName,
                ]);
            }

            // 4. Obtener test API key de la organización
            $testKey = $this->obtenerTestApiKey($organizationId);

            Log::info("Organización Facturapi creada para clínica {$clinica->id}", [
                'organization_id' => $organizationId,
                'test_key_obtenida' => !empty($testKey),
            ]);

            return [
                'success'          => true,
                'organization_id'  => $organizationId,
                'api_key_test'     => $testKey,
            ];
        } catch (\Exception $e) {
            Log::error('Error creando organización en Facturapi', [
                'clinica_id' => $clinica->id,
                'error'      => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Actualizar nombre, datos legales y CSD de una organización existente.
     * Se usa cuando la clínica ya tiene org_id pero sube un nuevo CSD.
     */
    public function actualizarOrganizacion(string $organizationId, array $csdData): array
    {
        try {
            // El nombre fiscal siempre viene del CSD (nombre_titular del certificado).
            // Facturapi NO actualiza legal_name automáticamente al subir el CSD,
            // solo actualiza tax_id (RFC). Por eso debemos enviarlo explícitamente.
            $legalName = mb_strtoupper($csdData['razon_social'] ?? '', 'UTF-8');

            // 1. Actualizar nombre comercial y datos legales ANTES del CSD
            if ($legalName) {
                Http::withToken($this->userApiKey)
                    ->put("{$this->baseUrl}/organizations/{$organizationId}", [
                        'name' => $legalName,
                    ]);

                Http::withToken($this->userApiKey)
                    ->put("{$this->baseUrl}/organizations/{$organizationId}/legal", [
                        'legal_name' => $legalName,
                        'tax_system' => $csdData['regimen_fiscal'] ?? '601',
                        'address'    => ['zip' => $csdData['codigo_postal'] ?? '00000'],
                    ]);
            }

            // 2. Subir CSD — Facturapi asignará el RFC del certificado automáticamente
            $csdResult = $this->uploadCertificate($organizationId, $csdData);
            if (!$csdResult['success']) {
                return ['success' => false, 'error' => 'Error subiendo CSD: ' . ($csdResult['error'] ?? '')];
            }

            Log::info("Organización {$organizationId} actualizada en Facturapi", [
                'legal_name' => $legalName,
                'tax_system' => $csdData['regimen_fiscal'],
                'zip'        => $csdData['codigo_postal'],
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Error actualizando organización en Facturapi', [
                'organization_id' => $organizationId,
                'error'           => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Subir Certificado de Sello Digital (CSD) a Facturapi.
     * Usa PUT /v2/organizations/{org_id}/certificate con multipart/form-data.
     */
    public function uploadCertificate(string $organizationId, array $csdData): array
    {
        try {
            if (empty($csdData['cer_path']) || empty($csdData['key_path'])) {
                return ['success' => false, 'error' => 'Rutas de archivos CSD no proporcionadas'];
            }

            $cerContent = file_get_contents($csdData['cer_path']);
            $keyContent = file_get_contents($csdData['key_path']);

            if ($cerContent === false || $keyContent === false) {
                return ['success' => false, 'error' => 'No se pudieron leer los archivos CSD'];
            }

            // Facturapi requiere multipart/form-data donde:
            //   - 'cer' y 'key' son archivos binarios (con filename)
            //   - 'password' es un campo de TEXTO PLANO (sin filename)
            // Laravel Http::attach() siempre pone filename, por eso usamos Guzzle directamente.
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->put("{$this->baseUrl}/organizations/{$organizationId}/certificate", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->userApiKey,
                ],
                'multipart' => [
                    [
                        'name'     => 'cer',
                        'contents' => $cerContent,
                        'filename' => 'certificado.cer',
                        'headers'  => ['Content-Type' => 'application/octet-stream'],
                    ],
                    [
                        'name'     => 'key',
                        'contents' => $keyContent,
                        'filename' => 'llave.key',
                        'headers'  => ['Content-Type' => 'application/octet-stream'],
                    ],
                    [
                        'name'     => 'password',
                        'contents' => $csdData['password'] ?? '',
                    ],
                ],
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body       = json_decode($response->getBody()->getContents(), true);

            if ($statusCode < 200 || $statusCode >= 300) {
                $errorMsg = $body['message'] ?? "Error HTTP {$statusCode} subiendo CSD";
                Log::error('Facturapi uploadCertificate falló', [
                    'organization_id' => $organizationId,
                    'status'          => $statusCode,
                    'response'        => $body,
                ]);
                return ['success' => false, 'error' => $errorMsg];
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Error subiendo CSD a Facturapi', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtener la Test API Key de una organización (usando UserKey maestra).
     */
    public function obtenerTestApiKey(string $organizationId): ?string
    {
        try {
            $response = Http::withToken($this->userApiKey)
                ->get("{$this->baseUrl}/organizations/{$organizationId}/apikeys/test");

            if ($response->successful()) {
                $data = $response->json();
                return is_string($data) ? $data : ($data['key'] ?? $data['secret_key'] ?? null);
            }

            Log::warning("No se pudo obtener test API key para org {$organizationId}: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Error obteniendo test API key', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Intentar obtener la Live API Key de una organización.
     * Solo disponible si la org tiene activo el servicio de facturación en Facturapi.
     * Returns null si no está disponible aún (org en modo test o sin suscripción).
     */
    public function obtenerLiveApiKey(string $organizationId): ?string
    {
        try {
            $response = Http::withToken($this->userApiKey)
                ->get("{$this->baseUrl}/organizations/{$organizationId}/apikeys/live");

            if ($response->successful()) {
                $data = $response->json();
                // Puede ser array con múltiples keys live o una sola
                if (is_array($data) && isset($data[0])) {
                    return $data[0]['secret_key'] ?? $data[0]['key'] ?? null;
                }
                return is_string($data) ? $data : ($data['key'] ?? $data['secret_key'] ?? null);
            }

            return null; // No disponible todavía (no es error crítico)
        } catch (\Exception $e) {
            Log::error('Error obteniendo live API key', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sincronizar y guardar test + live API keys para una clínica.
     * Llamar después de subir CSD o cuando el admin refresca las keys.
     */
    public function sincronizarApiKeys(int $clinicaId, string $organizationId): array
    {
        $testKey = $this->obtenerTestApiKey($organizationId);
        $liveKey = $this->obtenerLiveApiKey($organizationId);

        $updates = [
            'facturapi_mode' => $liveKey ? 'live' : 'test',
        ];
        if ($testKey) {
            $updates['facturapi_api_key_test'] = $testKey;
        }
        if ($liveKey) {
            $updates['facturapi_api_key_live'] = $liveKey;
        }

        if (!empty($updates)) {
            \App\Models\Clinica::where('id', $clinicaId)->update($updates);
        }

        return [
            'test_key'  => $testKey ? substr($testKey, 0, 12) . '...' : null,
            'live_key'  => $liveKey ? substr($liveKey, 0, 12) . '...' : null,
            'modo'      => $liveKey ? 'live' : 'test',
        ];
    }

    /**
     * Sincronizar API keys de Facturapi para la e.firma fiscal personal de un médico.
     */
    public function sincronizarApiKeysEfirma(Efirma $efirma): array
    {
        if (! $efirma->facturapi_organization_id) {
            return ['test_key' => null, 'live_key' => null, 'modo' => 'test'];
        }

        $testKey = $this->obtenerTestApiKey($efirma->facturapi_organization_id);
        $liveKey = $this->obtenerLiveApiKey($efirma->facturapi_organization_id);

        $updates = ['facturapi_configured' => true];
        if ($testKey) {
            $updates['facturapi_api_key_test'] = $testKey;
        }
        if ($liveKey) {
            $updates['facturapi_api_key_live'] = $liveKey;
        }

        $efirma->update($updates);

        return [
            'test_key' => $testKey ? substr($testKey, 0, 12) . '...' : null,
            'live_key' => $liveKey ? substr($liveKey, 0, 12) . '...' : null,
            'modo'     => $liveKey ? 'live' : 'test',
        ];
    }

    /**
     * Crear organización Facturapi vinculada a la e.firma fiscal personal del médico.
     */
    public function crearOrganizacionParaEfirma(Efirma $efirma, array $csdData): array
    {
        $nombre = ! empty($csdData['razon_social'])
            ? $csdData['razon_social']
            : ($efirma->nombre_titular ?? 'Médico');

        $resultado = $this->crearOrganizacionGenerica($nombre, $csdData);
        if (! ($resultado['success'] ?? false)) {
            return $resultado;
        }

        $orgId = $resultado['organization_id'];
        $efirma->update([
            'facturapi_organization_id' => $orgId,
            'facturapi_api_key_test'    => $resultado['api_key_test'] ?? null,
            'facturapi_configured'      => true,
        ]);

        $this->sincronizarApiKeysEfirma($efirma->fresh());

        return [
            'success'         => true,
            'organization_id' => $orgId,
            'api_key_test'    => $resultado['api_key_test'] ?? null,
        ];
    }

    /**
     * Crear organización Facturapi (lógica compartida clínica / médico).
     */
    private function crearOrganizacionGenerica(string $orgName, array $csdData): array
    {
        try {
            $createResponse = Http::withToken($this->userApiKey)
                ->post("{$this->baseUrl}/organizations", [
                    'name' => $orgName,
                ]);

            if (! $createResponse->successful()) {
                return [
                    'success' => false,
                    'error'   => $createResponse->json()['message'] ?? 'Error al registrar el CSD para facturación',
                ];
            }

            $org = $createResponse->json();
            $organizationId = $org['id'];

            Http::withToken($this->userApiKey)
                ->put("{$this->baseUrl}/organizations/{$organizationId}/legal", [
                    'legal_name' => strtoupper($csdData['razon_social'] ?? $orgName),
                    'tax_system' => $csdData['regimen_fiscal'] ?? '612',
                    'address'    => [
                        'zip' => $csdData['codigo_postal'] ?? '00000',
                    ],
                ]);

            $csdResult = $this->uploadCertificate($organizationId, $csdData);
            if (! ($csdResult['success'] ?? false)) {
                Log::warning("Organización creada ({$organizationId}) pero fallo al subir CSD: " . ($csdResult['error'] ?? ''));
            } else {
                $legalName = mb_strtoupper($csdData['razon_social'] ?? $orgName, 'UTF-8');

                Http::withToken($this->userApiKey)
                    ->put("{$this->baseUrl}/organizations/{$organizationId}", [
                        'name' => $legalName,
                    ]);

                Http::withToken($this->userApiKey)
                    ->put("{$this->baseUrl}/organizations/{$organizationId}/legal", [
                        'legal_name' => $legalName,
                        'tax_system' => $csdData['regimen_fiscal'] ?? '601',
                        'address'    => ['zip' => $csdData['codigo_postal'] ?? '00000'],
                    ]);
            }

            $testKey = $this->obtenerTestApiKey($organizationId);

            return [
                'success'         => true,
                'organization_id' => $organizationId,
                'api_key_test'    => $testKey,
            ];
        } catch (\Exception $e) {
            Log::error('Error creando organización en Facturapi', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Contexto de emisión (API key + serie) según emisor de la solicitud.
     *
     * @return array{success: bool, api_key?: string, serie?: string, doctor_id?: int|null, error?: string}
     */
    public function resolverContextoEmision(SolicitudFactura $solicitud): array
    {
        $solicitud->loadMissing(['clinica']);
        $clinica = $solicitud->clinica;

        if ($solicitud->emisor_tipo === SolicitudFactura::EMISOR_DOCTOR && $solicitud->doctor_id) {
            $efirma = Efirma::paraFacturacionUsuario((int) $solicitud->doctor_id);

            if (! $efirma?->listaParaFacturapi()) {
                return [
                    'success' => false,
                    'error'   => 'El médico no tiene CSD configurado. Configúralo en Perfil → Facturación CFDI.',
                ];
            }

            $apiKey = $this->resolverApiKeyEmisionEfirma($efirma, $clinica);
            if (! $apiKey) {
                return [
                    'success' => false,
                    'error'   => 'No hay certificado activo para el médico emisor. Vuelve a subir tu CSD.',
                ];
            }

            return [
                'success'   => true,
                'api_key'   => $apiKey,
                'serie'     => $efirma->facturacion_serie ?? 'FAC',
                'doctor_id' => $solicitud->doctor_id,
            ];
        }

        if (! $clinica?->facturapi_organization_id) {
            return [
                'success' => false,
                'error'   => 'La clínica no tiene configurada la facturación (falta cargar el CSD)',
            ];
        }

        $apiKey = $this->resolverApiKeyEmision($clinica);
        if (! $apiKey) {
            return [
                'success' => false,
                'error'   => 'No hay certificado de facturación configurado. Vuelve a subir el CSD.',
            ];
        }

        return [
            'success'   => true,
            'api_key'   => $apiKey,
            'serie'     => $clinica->facturacion_serie ?? 'FAC',
            'doctor_id' => null,
        ];
    }

    private function resolverApiKeyEmisionEfirma(Efirma $efirma, ?Clinica $clinica): ?string
    {
        if ($this->usarModoLive() && ! empty($efirma->facturapi_api_key_live)) {
            return $efirma->facturapi_api_key_live;
        }

        return $efirma->facturapi_api_key_test ?: null;
    }

    /**
     * Resolver API key operativa para emitir/cancelar/descargar CFDI.
     */
    private function resolverApiKeyEmision(Clinica $clinica): ?string
    {
        if ($this->usarModoLive() && ! empty($clinica->facturapi_api_key_live)) {
            return $clinica->facturapi_api_key_live;
        }

        return $clinica->facturapi_api_key_test ?: null;
    }

    private function usarModoLive(): bool
    {
        return config('facturapi.environment', 'test') === 'live';
    }

    // ──────────────────────────────────────────────
    // TIMBRADO CFDI (requiere API key de la organización)
    // ──────────────────────────────────────────────

    /**
     * Timbrar factura CFDI 4.0 usando la API key de la organización (clínica).
     */
    public function timbrarFactura(SolicitudFactura $solicitud): array
    {
        $clinica = $solicitud->clinica;
        $contexto = $this->resolverContextoEmision($solicitud);

        if (! ($contexto['success'] ?? false)) {
            return [
                'success' => false,
                'error'   => $contexto['error'] ?? 'Error de configuración de facturación',
            ];
        }

        $orgApiKey = $contexto['api_key'];

        try {
            $solicitud->update(['estado' => SolicitudFactura::ESTADO_EN_PROCESO]);

            $datosFiscales = $solicitud->datosFiscales;
            if (!$datosFiscales) {
                return ['success' => false, 'error' => 'La solicitud no tiene datos fiscales del paciente'];
            }

            // Precio y configuración de IVA según ajuste de la clínica
            $ivaIncluido = (bool) ($clinica->facturacion_iva_incluido ?? false);
            $tasaIva     = (float) ($clinica->facturacion_tasa_iva ?? 16.00) / 100;

            $precio = $solicitud->subtotal > 0
                ? (float) $solicitud->subtotal
                : (float) $solicitud->total;

            [$serie, $folioNum] = SolicitudFactura::reservarFolio(
                $clinica->id,
                $contexto['serie'] ?? ($clinica->facturacion_serie ?? 'FAC'),
                $contexto['doctor_id'] ?? null
            );

            $body = [
                'customer' => [
                    'legal_name' => strtoupper($datosFiscales->razon_social),
                    'tax_id'     => strtoupper($datosFiscales->rfc),
                    'tax_system' => $datosFiscales->regimen_fiscal ?? '612',
                    'email'      => $datosFiscales->email_facturacion,
                    'address'    => [
                        'zip' => $datosFiscales->codigo_postal ?? '00000',
                    ],
                ],
                'items' => [[
                    'quantity' => (int) ($solicitud->cantidad ?? 1),
                    'product'  => [
                        'description'  => $solicitud->concepto,
                        'product_key'  => (int) ($solicitud->clave_prod_serv ?? 85121800),
                        'unit_key'     => $solicitud->clave_unidad ?? 'E48',
                        'price'        => $precio,
                        'tax_included' => $ivaIncluido,
                        'taxes'        => $tasaIva > 0
                            ? [['type' => 'IVA', 'rate' => $tasaIva, 'factor' => 'Tasa']]
                            : [],
                    ],
                ]],
                'payment_form'   => $this->resolverFormaPago($solicitud),
                'payment_method' => $solicitud->metodo_pago_cfdi ?? 'PUE',
                'use'            => $datosFiscales->uso_cfdi ?? 'D01',
                'series'         => $serie,
                'folio_number'   => $folioNum,
            ];

            $response = Http::withToken($orgApiKey)
                ->post("{$this->baseUrl}/invoices", $body);

            if (!$response->successful()) {
                $errorMsg = $response->json()['message'] ?? 'Error al timbrar la factura';
                $solicitud->update([
                    'estado'        => SolicitudFactura::ESTADO_ERROR,
                    'error_mensaje' => $errorMsg,
                ]);
                return ['success' => false, 'error' => $errorMsg];
            }

            $invoice = $response->json();

            $serieFinal = $invoice['series'] ?? $serie;
            $folioFinal = (int) ($invoice['folio_number'] ?? $folioNum);
            $etiqueta = $serieFinal . $folioFinal;

            $solicitud->update([
                'estado'              => SolicitudFactura::ESTADO_FACTURADA,
                'uuid'                => $invoice['uuid'] ?? null,
                'facturapi_invoice_id' => $invoice['id'],
                'facturapi_response'  => $invoice,
                'folio_fiscal'        => $etiqueta,
                'serie'               => $serieFinal,
                'folio'               => $folioFinal,
                'fecha_timbrado'      => now(),
                'procesada_por'       => $solicitud->solicitada_por,
            ]);

            Log::info("Factura timbrada correctamente #{$solicitud->id}", [
                'uuid'        => $invoice['uuid'] ?? null,
                'folio'       => $invoice['folio_number'] ?? null,
                'clinica_id'  => $solicitud->clinica_id,
            ]);

            return [
                'success' => true,
                'uuid'    => $invoice['uuid'] ?? null,
                'folio'   => $invoice['folio_number'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Excepción timbrando factura', [
                'solicitud_id' => $solicitud->id,
                'error'        => $e->getMessage(),
            ]);

            $solicitud->update([
                'estado'        => SolicitudFactura::ESTADO_ERROR,
                'error_mensaje' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cancelar un CFDI ya timbrado.
     */
    public function cancelarCfdi(SolicitudFactura $solicitud, string $motivo = '02'): array
    {
        $contexto = $this->resolverContextoEmision($solicitud);
        $orgApiKey = ($contexto['success'] ?? false) ? ($contexto['api_key'] ?? null) : null;
        if (!$orgApiKey || !$solicitud->facturapi_invoice_id) {
            return ['success' => false, 'error' => 'Organización o factura no configurada'];
        }

        try {
            $response = Http::withToken($orgApiKey)
                ->delete("{$this->baseUrl}/invoices/{$solicitud->facturapi_invoice_id}", [
                    'motive' => $motivo,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error'   => $response->json()['message'] ?? 'Error cancelando CFDI',
                ];
            }

            $solicitud->update([
                'estado'           => SolicitudFactura::ESTADO_CANCELADA,
                'fecha_cancelacion' => now(),
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Descargar XML del CFDI.
     */
    public function downloadXml(SolicitudFactura $solicitud): ?string
    {
        $contexto = $this->resolverContextoEmision($solicitud);
        $orgApiKey = ($contexto['success'] ?? false) ? ($contexto['api_key'] ?? null) : null;
        if (!$orgApiKey || !$solicitud->facturapi_invoice_id) {
            return null;
        }

        try {
            $response = Http::withToken($orgApiKey)
                ->get("{$this->baseUrl}/invoices/{$solicitud->facturapi_invoice_id}/xml");

            return $response->successful() ? $response->body() : null;
        } catch (\Exception $e) {
            Log::error('Error descargando XML', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Descargar PDF del CFDI.
     */
    public function downloadPdf(SolicitudFactura $solicitud): ?string
    {
        $contexto = $this->resolverContextoEmision($solicitud);
        $orgApiKey = ($contexto['success'] ?? false) ? ($contexto['api_key'] ?? null) : null;
        if (!$orgApiKey || !$solicitud->facturapi_invoice_id) {
            return null;
        }

        try {
            $response = Http::withToken($orgApiKey)
                ->get("{$this->baseUrl}/invoices/{$solicitud->facturapi_invoice_id}/pdf");

            return $response->successful() ? $response->body() : null;
        } catch (\Exception $e) {
            Log::error('Error descargando PDF', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Obtener estado de una factura desde Facturapi.
     */
    public function obtenerEstadoFactura(SolicitudFactura $solicitud): array
    {
        $contexto = $this->resolverContextoEmision($solicitud);
        $orgApiKey = ($contexto['success'] ?? false) ? ($contexto['api_key'] ?? null) : null;
        if (!$orgApiKey || !$solicitud->facturapi_invoice_id) {
            return ['success' => false, 'error' => 'Organización o factura no configurada'];
        }

        try {
            $response = Http::withToken($orgApiKey)
                ->get("{$this->baseUrl}/invoices/{$solicitud->facturapi_invoice_id}");

            if ($response->successful()) {
                $invoice = $response->json();
                return [
                    'success' => true,
                    'estado'  => $invoice['status'] ?? 'unknown',
                    'uuid'    => $invoice['uuid'] ?? null,
                    'datos'   => $invoice,
                ];
            }

            return ['success' => false, 'error' => 'No se pudo obtener el estado'];
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado de factura', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * c_FormaPago SAT para Facturapi (payment_form).
     */
    protected function resolverFormaPago(SolicitudFactura $solicitud): string
    {
        if (!empty($solicitud->forma_pago)) {
            return $solicitud->forma_pago;
        }

        $solicitud->loadMissing('pago');
        if ($solicitud->pago) {
            return SatFormaPago::desdeMetodoPago($solicitud->pago->metodo_pago);
        }

        return '01';
    }
}
