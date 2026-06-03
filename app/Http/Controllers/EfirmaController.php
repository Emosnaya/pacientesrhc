<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Efirma;
use App\Models\Receta;
use App\Services\FacturapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EfirmaController extends Controller
{
    /**
     * Obtener la e.firma personal del usuario autenticado.
     */
    public function show()
    {
        $user = Auth::user();
        $efirma = Efirma::where('user_id', $user->id)
            ->where('tipo', 'personal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'tiene_efirma' => false,
                'mensaje' => 'No tienes una e.firma configurada'
            ]);
        }

        return response()->json([
            'tiene_efirma' => true,
            'efirma' => $this->formatEfirma($efirma)
        ]);
    }

    /**
     * Obtener la e.firma fiscal personal del usuario (para facturar individualmente).
     */
    public function showFiscalPersonal()
    {
        $user = Auth::user();
        $efirma = Efirma::where('user_id', $user->id)
            ->where('tipo', 'fiscal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'tiene_efirma' => false,
                'mensaje' => 'No tienes una e.firma fiscal configurada para facturación'
            ]);
        }

        return response()->json([
            'tiene_efirma' => true,
            'efirma' => $this->formatEfirma($efirma)
        ]);
    }

    /**
     * Obtener la e.firma fiscal de la clínica (solo admin/superadmin).
     */
    public function showClinica()
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        if (!$clinicaId) {
            return response()->json([
                'tiene_efirma' => false,
                'mensaje' => 'No estás asociado a ninguna clínica'
            ], 400);
        }

        // Verificar que sea admin o superadmin
        if (!$user->isSuperAdmin && !$user->isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo administradores pueden ver la e.firma fiscal'
            ], 403);
        }

        $efirma = Efirma::where('clinica_id', $clinicaId)
            ->where('tipo', 'fiscal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'tiene_efirma' => false,
                'mensaje' => 'La clínica no tiene e.firma fiscal configurada'
            ]);
        }

        return response()->json([
            'tiene_efirma' => true,
            'efirma' => $this->formatEfirma($efirma)
        ]);
    }

    /**
     * Formatear e.firma para respuesta JSON.
     */
    private function formatEfirma(Efirma $efirma): array
    {
        return [
            'id' => $efirma->id,
            'tipo' => $efirma->tipo,
            'rfc' => $efirma->rfc,
            'nombre_titular' => $efirma->nombre_titular,
            'numero_serie' => $efirma->numero_serie,
            'vigencia_inicio' => $efirma->vigencia_inicio?->format('Y-m-d'),
            'vigencia_fin' => $efirma->vigencia_fin?->format('Y-m-d'),
            'es_vigente' => $efirma->es_vigente,
            'dias_restantes' => $efirma->dias_restantes,
            'estado' => $efirma->estado,
            'verificada' => $efirma->verificada,
            'activa' => $efirma->activa,
            'usar_para_recetas' => $efirma->usar_para_recetas ?? true,
            'usar_para_facturacion' => $efirma->usar_para_facturacion ?? false,
            'facturapi_configured' => (bool) ($efirma->facturapi_configured ?? false),
            'facturacion_lista' => $efirma->listaParaFacturapi(),
            'updated_at' => $efirma->updated_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * Subir o actualizar la e.firma personal del usuario.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificado' => 'required|file|max:10240',
            'llave' => 'required|file|max:10240',
            'password' => 'required|string|min:1',
            'usar_para_recetas' => 'sometimes|in:0,1,true,false',
            'usar_para_facturacion' => 'sometimes|in:0,1,true,false',
        ], [
            'certificado.required' => 'El archivo del certificado (.cer) es requerido',
            'llave.required' => 'El archivo de la llave privada (.key) es requerido',
            'password.required' => 'La contraseña de la llave es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Opciones de uso
        $usos = [
            'usar_para_recetas' => filter_var($request->input('usar_para_recetas', true), FILTER_VALIDATE_BOOLEAN),
            'usar_para_facturacion' => filter_var($request->input('usar_para_facturacion', false), FILTER_VALIDATE_BOOLEAN),
        ];

        $result = $this->procesarEfirma(
            $request,
            ['user_id' => $user->id, 'tipo' => 'personal'],
            $user->id,
            $usos
        );

        $responseData = json_decode($result->getContent(), true);
        if (($responseData['success'] ?? false) && $usos['usar_para_facturacion']) {
            $responseData['facturapi'] = $this->sincronizarCsdConFacturapiPersonal($request, $user->id);
            if (! ($responseData['facturapi']['sincronizado'] ?? false)) {
                $responseData['message'] = ($responseData['message'] ?? 'E.firma guardada.')
                    . ' No se pudo activar la facturación: '
                    . ($responseData['facturapi']['error'] ?? 'vuelve a subir el CSD.');
            }

            return response()->json($responseData, $result->getStatusCode());
        }

        return $result;
    }

    /**
     * Activar CSD personal ya guardado para facturación (sin volver a subir archivos).
     */
    public function sincronizarFacturapiPersonal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:1',
            'codigo_postal' => 'nullable|string|size:5',
            'regimen_fiscal' => 'nullable|string|size:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $efirma = Efirma::paraFacturacionUsuario($user->id);

        if (! $efirma) {
            return response()->json([
                'success' => false,
                'message' => 'Sube tu e.firma y activa "Emitir facturas (CFDI)" primero.',
            ], 400);
        }

        if (! $efirma->verificarPareja($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña no coincide con tu llave privada.',
            ], 400);
        }

        if (empty(config('facturapi.user_key'))) {
            return response()->json([
                'success' => false,
                'message' => 'El servicio de timbrado no está configurado en el servidor.',
            ], 503);
        }

        $syncRequest = $this->requestConCsdDesdeEfirma($request, $efirma, $request->password);
        $facturapi = $this->sincronizarCsdConFacturapiPersonal($syncRequest, $user->id);

        if ($facturapi['sincronizado'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => 'CSD registrado correctamente. Ya puedes timbrar con tu RFC.',
                'facturapi' => $facturapi,
                'efirma' => $this->formatEfirma($efirma->fresh()),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $facturapi['error'] ?? 'No se pudo activar la facturación con el CSD',
            'facturapi' => $facturapi,
        ], 500);
    }

    /**
     * Actualizar solo los usos de una e.firma existente.
     */
    public function updateUsos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usar_para_recetas' => 'required|boolean',
            'usar_para_facturacion' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $efirma = Efirma::where('user_id', $user->id)
            ->where('tipo', 'personal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una e.firma configurada'
            ], 404);
        }

        // Al menos un uso debe estar activo
        if (!$request->usar_para_recetas && !$request->usar_para_facturacion) {
            return response()->json([
                'success' => false,
                'message' => 'Debes seleccionar al menos un uso para tu e.firma'
            ], 422);
        }

        $efirma->update([
            'usar_para_recetas' => $request->usar_para_recetas,
            'usar_para_facturacion' => $request->usar_para_facturacion,
        ]);

        $mensaje = 'Usos de e.firma actualizados correctamente';
        if ($request->usar_para_facturacion && ! $efirma->fresh()->listaParaFacturapi()) {
            $mensaje .= '. Para timbrar CFDI, vuelve a subir tu CSD en Facturación CFDI.';
        }

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'efirma' => $this->formatEfirma($efirma->fresh()),
        ]);
    }

    /**
     * Refrescar las API keys de Facturapi (test + live) para la clínica.
     * Útil cuando el admin completa el pago en Facturapi y quiere activar el modo live.
     */
    public function refrescarKeysFacturapi(Request $request)
    {
        $user      = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        if (!$user->isSuperAdmin && !$user->isAdmin) {
            return response()->json(['success' => false, 'message' => 'Sin permisos'], 403);
        }

        $clinica = Clinica::find($clinicaId);
        if (!$clinica || !$clinica->facturapi_organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'La clínica no tiene CSD configurado. Sube primero el certificado.',
            ], 400);
        }

        $facturapi = new FacturapiService();
        $keys      = $facturapi->sincronizarApiKeys($clinicaId, $clinica->facturapi_organization_id);

        $clinica->refresh();

        return response()->json([
            'success'    => true,
            'modo'       => $keys['modo'],
            'tiene_live' => (bool) $clinica->facturapi_api_key_live,
            'tiene_test' => (bool) $clinica->facturapi_api_key_test,
            'message'    => (bool) $clinica->facturapi_api_key_live
                ? (config('facturapi.environment', 'test') === 'live'
                    ? 'Facturación en modo producción (live) lista.'
                    : 'Key live sincronizada. Pon FACTURAPI_ENVIRONMENT=live en el servidor para timbrar CFDI reales.')
                : 'Keys de prueba listas. La key live estará disponible tras activar tu cuenta de timbrado.',
        ]);
    }

    /**
     * Sincronizar keys test + live del CSD personal del médico.
     * Llamar después de activar la cuenta de timbrado en producción.
     */
    public function refrescarKeysFacturapiPersonal(Request $request)
    {
        $user = Auth::user();
        $efirma = Efirma::paraFacturacionUsuario($user->id);

        if (! $efirma?->facturapi_organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Sube tu CSD en Facturación CFDI antes de sincronizar.',
            ], 400);
        }

        $facturapi = new FacturapiService();
        $keys = $facturapi->sincronizarApiKeysEfirma($efirma->fresh());

        $efirma->refresh();
        $modoLive = config('facturapi.environment', 'test') === 'live';

        return response()->json([
            'success' => true,
            'modo' => $keys['modo'],
            'tiene_live' => (bool) $efirma->facturapi_api_key_live,
            'tiene_test' => (bool) $efirma->facturapi_api_key_test,
            'timbrado_modo' => $modoLive && $efirma->facturapi_api_key_live ? 'live' : 'test',
            'message' => $efirma->facturapi_api_key_live
                ? ($modoLive
                    ? 'Keys sincronizadas. Timbrado en modo producción (live).'
                    : 'Key live disponible. Cambia FACTURAPI_ENVIRONMENT=live en el servidor para timbrar CFDI reales.')
                : 'Keys de prueba listas. La key live aparecerá cuando active tu cuenta de timbrado.',
            'efirma' => $this->formatEfirma($efirma),
        ]);
    }

    /**
     * Subir o actualizar la e.firma fiscal personal del usuario (para facturar individualmente).
     */
    public function storeFiscalPersonal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificado' => 'required|file|max:10240',
            'llave' => 'required|file|max:10240',
            'password' => 'required|string|min:1',
            'codigo_postal' => 'required|string|size:5',
            'regimen_fiscal' => 'nullable|string|size:3',
        ], [
            'certificado.required' => 'El archivo del certificado CSD (.cer) es requerido',
            'llave.required' => 'El archivo de la llave privada (.key) es requerido',
            'password.required' => 'La contraseña de la llave es requerida',
            'codigo_postal.required' => 'El código postal del SAT es requerido para facturar',
            'codigo_postal.size' => 'El código postal debe tener 5 dígitos',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $result = $this->procesarEfirma(
            $request,
            ['user_id' => $user->id, 'tipo' => 'fiscal'],
            $user->id,
            ['usar_para_facturacion' => true]
        );

        $responseData = json_decode($result->getContent(), true);
        if (($responseData['success'] ?? false)) {
            $facturapi = $this->sincronizarCsdConFacturapiPersonal($request, $user->id);
            $responseData['facturapi'] = $facturapi;
            return response()->json($responseData, $result->getStatusCode());
        }

        return $result;
    }

    /**
     * Subir o actualizar la e.firma fiscal (CSD) de la clínica.
     */
    public function storeClinica(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificado'    => 'required|file|max:10240',
            'llave'          => 'required|file|max:10240',
            'password'       => 'required|string|min:1',
            'codigo_postal'  => 'nullable|string|size:5',
            'regimen_fiscal' => 'nullable|string|size:3',
        ], [
            'certificado.required' => 'El archivo del certificado (.cer) es requerido',
            'llave.required'       => 'El archivo de la llave privada (.key) es requerido',
            'password.required'    => 'La contraseña de la llave es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        // Verificar permisos
        if (!$user->isSuperAdmin && !$user->isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo administradores pueden configurar la e.firma fiscal'
            ], 403);
        }

        if (!$clinicaId) {
            return response()->json([
                'success' => false,
                'message' => 'No estás asociado a ninguna clínica'
            ], 400);
        }

        $result = $this->procesarEfirma(
            $request,
            ['clinica_id' => $clinicaId, 'tipo' => 'fiscal'],
            $user->id
        );

        // Si se guardó correctamente, sincronizar con Facturapi y enriquecer respuesta
        $responseData = json_decode($result->getContent(), true);
        if (($responseData['success'] ?? false)) {
            $facturapi = $this->sincronizarCsdConFacturapi($request, $clinicaId);
            // Agregar estado del sync a la respuesta para feedback en el frontend
            $responseData['facturapi'] = $facturapi;
            return response()->json($responseData, $result->getStatusCode());
        }

        return $result;
    }

    /**
     * Crear o actualizar la organización en Facturapi con el CSD recién subido.
     */
    private function sincronizarCsdConFacturapi(Request $request, int $clinicaId): array
    {
        try {
            $clinica = Clinica::findOrFail($clinicaId);
            $efirma  = Efirma::where('clinica_id', $clinicaId)->where('tipo', 'fiscal')->first();

            if (!$efirma) {
                return ['sincronizado' => false, 'error' => 'No se encontró la e.firma guardada'];
            }

            $csdData = [
                'razon_social'   => $efirma->nombre_titular,
                'rfc'            => $efirma->rfc,
                'codigo_postal'  => $request->input('codigo_postal', '00000'),
                'regimen_fiscal' => $request->input('regimen_fiscal', '601'),
                'cer_path'       => $request->file('certificado')?->getRealPath(),
                'key_path'       => $request->file('llave')?->getRealPath(),
                'password'       => $request->input('password'),
            ];

            $facturapi = new FacturapiService();

            if ($clinica->facturapi_organization_id) {
                // Actualizar CSD + datos legales en organización existente
                $updateResult = $facturapi->actualizarOrganizacion(
                    $clinica->facturapi_organization_id,
                    $csdData
                );
                if ($updateResult['success']) {
                    // Sincronizar test + live keys (live disponible si ya pagaron suscripción)
                    $keys = $facturapi->sincronizarApiKeys($clinicaId, $clinica->facturapi_organization_id);
                    Log::info("CSD actualizado en Facturapi para clínica {$clinicaId}", $keys);
                    return ['sincronizado' => true, 'organization_id' => $clinica->facturapi_organization_id, 'keys' => $keys];
                }
                Log::warning("Error actualizando org en Facturapi: " . ($updateResult['error'] ?? ''));
                return ['sincronizado' => false, 'error' => $updateResult['error'] ?? 'Error actualizando organización'];
            }

            // Crear organización nueva
            $resultado = $facturapi->crearOrganizacion($clinica, $csdData);
            if ($resultado['success']) {
                $orgId = $resultado['organization_id'];
                // Obtener test + live keys
                $keys = $facturapi->sincronizarApiKeys($clinicaId, $orgId);
                Clinica::where('id', $clinicaId)->update([
                    'facturapi_organization_id' => $orgId,
                    'facturapi_configured'      => true,
                ]);
                Log::info("Organización Facturapi creada para clínica {$clinicaId}: {$orgId}", $keys);
                return ['sincronizado' => true, 'organization_id' => $orgId, 'keys' => $keys];
            }

            Log::warning("No se pudo crear organización en Facturapi: " . ($resultado['error'] ?? ''));
            return ['sincronizado' => false, 'error' => $resultado['error'] ?? 'Error creando organización'];

        } catch (\Exception $e) {
            Log::error("Error sincronizando CSD con Facturapi para clínica {$clinicaId}: " . $e->getMessage());
            return ['sincronizado' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Crear un Request con archivos temporales desde la e.firma almacenada.
     */
    private function requestConCsdDesdeEfirma(Request $baseRequest, Efirma $efirma, string $password): Request
    {
        $cerContent = $efirma->getCertificadoDecoded();
        $keyContent = $efirma->getLlaveKeyDecrypted();

        $cerTmp = tempnam(sys_get_temp_dir(), 'cer_');
        $keyTmp = tempnam(sys_get_temp_dir(), 'key_');
        file_put_contents($cerTmp, $cerContent);
        file_put_contents($keyTmp, $keyContent);

        $uploadedCer = new \Illuminate\Http\UploadedFile($cerTmp, 'certificado.cer', null, null, true);
        $uploadedKey = new \Illuminate\Http\UploadedFile($keyTmp, 'llave.key', null, null, true);

        $request = $baseRequest->duplicate(
            array_merge($baseRequest->all(), ['password' => $password]),
            $baseRequest->files->all()
        );
        $request->files->set('certificado', $uploadedCer);
        $request->files->set('llave', $uploadedKey);

        return $request;
    }

    /**
     * Crear o actualizar organización Facturapi para e.firma fiscal personal del médico.
     */
    private function sincronizarCsdConFacturapiPersonal(Request $request, int $userId): array
    {
        try {
            $efirma = Efirma::paraFacturacionUsuario($userId);

            if (! $efirma) {
                return ['sincronizado' => false, 'error' => 'No se encontró la e.firma guardada para facturación'];
            }

            if (empty(config('facturapi.user_key'))) {
                return ['sincronizado' => false, 'error' => 'FACTURAPI_USER_KEY no configurada en el servidor'];
            }

            $csdData = [
                'razon_social'   => $efirma->nombre_titular,
                'rfc'            => $efirma->rfc,
                'codigo_postal'  => $request->input('codigo_postal', '00000'),
                'regimen_fiscal' => $request->input('regimen_fiscal', '612'),
                'cer_path'       => $request->file('certificado')?->getRealPath(),
                'key_path'       => $request->file('llave')?->getRealPath(),
                'password'       => $request->input('password'),
            ];

            $facturapi = new FacturapiService();

            if ($efirma->facturapi_organization_id) {
                $updateResult = $facturapi->actualizarOrganizacion(
                    $efirma->facturapi_organization_id,
                    $csdData
                );

                if ($updateResult['success'] ?? false) {
                    $keys = $facturapi->sincronizarApiKeysEfirma($efirma->fresh());

                    return ['sincronizado' => true, 'organization_id' => $efirma->facturapi_organization_id, 'keys' => $keys];
                }

                return ['sincronizado' => false, 'error' => $updateResult['error'] ?? 'Error actualizando organización'];
            }

            $resultado = $facturapi->crearOrganizacionParaEfirma($efirma, $csdData);

            if ($resultado['success'] ?? false) {
                return [
                    'sincronizado'    => true,
                    'organization_id' => $resultado['organization_id'],
                    'keys'            => $facturapi->sincronizarApiKeysEfirma($efirma->fresh()),
                ];
            }

            return ['sincronizado' => false, 'error' => $resultado['error'] ?? 'Error creando organización'];
        } catch (\Exception $e) {
            Log::error("Error sincronizando CSD con Facturapi para médico {$userId}: " . $e->getMessage());

            return ['sincronizado' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Procesar y guardar la e.firma.
     */
    private function procesarEfirma(Request $request, array $filtros, int $subidaPorId, array $usos = [])
    {
        try {
            $cerContent = file_get_contents($request->file('certificado')->getRealPath());
            $keyContent = file_get_contents($request->file('llave')->getRealPath());
            $password = $request->password;

            // Crear o actualizar e.firma
            $efirma = Efirma::firstOrNew($filtros);
            $efirma->setCertificado($cerContent);
            $efirma->setLlaveKey($keyContent);
            $efirma->subida_por = $subidaPorId;

            // Guardar usos si se proporcionan
            if (!empty($usos)) {
                $efirma->usar_para_recetas = $usos['usar_para_recetas'] ?? true;
                $efirma->usar_para_facturacion = $usos['usar_para_facturacion'] ?? false;
            }

            // Extraer información del certificado
            $info = $efirma->extraerInfoCertificado();
            if (empty($info)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer el certificado. Verifica que sea un archivo .cer válido del SAT.'
                ], 400);
            }

            $efirma->fill($info);

            // Verificar que la llave corresponde al certificado
            if (!$efirma->verificarPareja($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña es incorrecta o la llave no corresponde al certificado.'
                ], 400);
            }

            // Verificar vigencia
            if (!$efirma->es_vigente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El certificado está vencido. Vigencia: ' . 
                        ($efirma->vigencia_fin ? $efirma->vigencia_fin->format('d/m/Y') : 'desconocida')
                ], 400);
            }

            $efirma->verificada = true;
            $efirma->activa = true;
            $efirma->ultima_verificacion = now();
            $efirma->save();

            $tipo = $efirma->tipo === 'fiscal'
                ? ($efirma->user_id ? 'fiscal personal' : 'fiscal de la clínica')
                : 'personal';
            Log::info("E.firma {$tipo} configurada por usuario {$subidaPorId}, RFC: {$efirma->rfc}");

            return response()->json([
                'success' => true,
                'message' => "E.firma {$tipo} configurada correctamente",
                'efirma' => [
                    'rfc' => $efirma->rfc,
                    'nombre_titular' => $efirma->nombre_titular,
                    'numero_serie' => $efirma->numero_serie,
                    'vigencia_fin' => $efirma->vigencia_fin?->format('Y-m-d'),
                    'dias_restantes' => $efirma->dias_restantes,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al configurar e.firma: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los archivos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar la e.firma personal del usuario.
     */
    public function destroy()
    {
        $user = Auth::user();
        $efirma = Efirma::where('user_id', $user->id)
            ->where('tipo', 'personal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una e.firma configurada'
            ], 404);
        }

        $efirma->delete();

        return response()->json([
            'success' => true,
            'message' => 'E.firma eliminada correctamente'
        ]);
    }

    /**
     * Eliminar la e.firma fiscal personal del usuario.
     */
    public function destroyFiscalPersonal()
    {
        $user = Auth::user();
        $efirma = Efirma::where('user_id', $user->id)
            ->where('tipo', 'fiscal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una e.firma fiscal configurada'
            ], 404);
        }

        $efirma->delete();
        Log::info("E.firma fiscal personal eliminada por usuario {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'E.firma fiscal eliminada correctamente'
        ]);
    }

    /**
     * Eliminar la e.firma fiscal de la clínica.
     */
    public function destroyClinica()
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin && !$user->isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo administradores pueden eliminar la e.firma fiscal'
            ], 403);
        }

        $clinicaId = $user->clinica_efectiva_id;
        $efirma = Efirma::where('clinica_id', $clinicaId)
            ->where('tipo', 'fiscal')
            ->first();

        if (!$efirma) {
            return response()->json([
                'success' => false,
                'message' => 'La clínica no tiene e.firma fiscal configurada'
            ], 404);
        }

        $efirma->delete();
        Log::info("E.firma fiscal eliminada por usuario {$user->id} de clínica {$clinicaId}");

        return response()->json([
            'success' => true,
            'message' => 'E.firma fiscal eliminada correctamente'
        ]);
    }

    /**
     * Firmar una receta con la e.firma.
     */
    public function firmarReceta(Request $request, $recetaId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Verificar que el usuario tenga e.firma
        $efirma = Efirma::where('user_id', $user->id)->first();
        if (!$efirma) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una e.firma configurada. Ve a Configuración > E.firma para agregarla.'
            ], 400);
        }

        if (!$efirma->es_vigente) {
            return response()->json([
                'success' => false,
                'message' => 'Tu e.firma está vencida. Actualízala en Configuración > E.firma.'
            ], 400);
        }

        // Obtener la receta
        $receta = Receta::where('id', $recetaId)
            ->where('user_id', $user->id)
            ->with(['paciente', 'medicamentos'])
            ->first();

        if (!$receta) {
            return response()->json([
                'success' => false,
                'message' => 'Receta no encontrada o no tienes permiso para firmarla.'
            ], 404);
        }

        if ($receta->firma_digital) {
            return response()->json([
                'success' => false,
                'message' => 'Esta receta ya está firmada.'
            ], 400);
        }

        // Construir cadena original
        $cadenaOriginal = $this->construirCadenaOriginal($receta);

        // Firmar
        $resultado = $efirma->firmar($cadenaOriginal, $request->password);

        if (!$resultado) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta o error al firmar.'
            ], 400);
        }

        // Guardar firma en la receta
        $receta->update([
            'firma_digital' => $resultado['firma'],
            'cadena_original' => $resultado['cadena_original'],
            'firmada_at' => now(),
            'numero_serie_certificado' => $resultado['numero_serie'],
        ]);

        Log::info("Receta {$recetaId} firmada por usuario {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Receta firmada correctamente',
            'firma' => [
                'fecha' => now()->format('d/m/Y H:i:s'),
                'numero_serie' => $resultado['numero_serie'],
            ]
        ]);
    }

    /**
     * Construir la cadena original para la firma.
     * Formato similar al usado por el SAT para facturas.
     */
    private function construirCadenaOriginal(Receta $receta): string
    {
        $paciente = $receta->paciente;
        $medicamentos = $receta->medicamentos;

        $partes = [
            '||', // Inicio
            $receta->id,
            $receta->fecha,
            $paciente?->nombre ?? '',
            $paciente?->apellidoPat ?? '',
            $paciente?->apellidoMat ?? '',
            $receta->diagnostico_principal ?? '',
        ];

        // Agregar medicamentos
        foreach ($medicamentos as $med) {
            $partes[] = $med->medicamento ?? '';
            $partes[] = $med->dosis ?? '';
            $partes[] = $med->frecuencia ?? '';
            $partes[] = $med->duracion ?? '';
        }

        $partes[] = $receta->indicaciones_generales ?? '';
        $partes[] = now()->format('Y-m-d\TH:i:s'); // Timestamp
        $partes[] = '||'; // Fin

        return implode('|', $partes);
    }

    /**
     * Verificar la firma de una receta.
     */
    public function verificarFirma($recetaId)
    {
        $receta = Receta::find($recetaId);

        if (!$receta) {
            return response()->json([
                'success' => false,
                'message' => 'Receta no encontrada'
            ], 404);
        }

        if (!$receta->firma_digital) {
            return response()->json([
                'firmada' => false,
                'message' => 'Esta receta no está firmada digitalmente'
            ]);
        }

        // Obtener el certificado del usuario que firmó
        $efirma = Efirma::where('numero_serie', $receta->numero_serie_certificado)->first();

        if (!$efirma) {
            return response()->json([
                'firmada' => true,
                'verificada' => false,
                'message' => 'Firmada pero no se puede verificar (certificado no encontrado)',
                'fecha_firma' => $receta->firmada_at?->format('d/m/Y H:i:s'),
                'numero_serie' => $receta->numero_serie_certificado,
            ]);
        }

        // Verificar firma
        $cerContent = $efirma->getCertificadoDecoded();
        $certPem = $cerContent;
        if (strpos($cerContent, '-----BEGIN') === false) {
            $certPem = "-----BEGIN CERTIFICATE-----\n" 
                     . chunk_split(base64_encode($cerContent), 64, "\n") 
                     . "-----END CERTIFICATE-----";
        }

        $publicKey = openssl_pkey_get_public($certPem);
        $firma = base64_decode($receta->firma_digital);
        
        $verificado = openssl_verify(
            $receta->cadena_original, 
            $firma, 
            $publicKey, 
            OPENSSL_ALGO_SHA256
        );

        return response()->json([
            'firmada' => true,
            'verificada' => $verificado === 1,
            'fecha_firma' => $receta->firmada_at?->format('d/m/Y H:i:s'),
            'numero_serie' => $receta->numero_serie_certificado,
            'titular' => $efirma->nombre_titular,
            'rfc' => $efirma->rfc,
        ]);
    }
}
