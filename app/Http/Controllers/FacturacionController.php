<?php

namespace App\Http\Controllers;

use App\Models\DatosFiscales;
use App\Models\Efirma;
use App\Models\Pago;
use App\Models\SolicitudFactura;
use App\Models\SuscripcionFacturas;
use App\Support\ConsultorioScope;
use App\Support\SatFormaPago;
use Carbon\Carbon;
use App\Models\CatRegimenFiscal;
use App\Models\CatUsoCfdi;
use App\Models\Paciente;
use App\Services\FacturapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class FacturacionController extends Controller
{
    // =====================================================
    // CATÁLOGOS SAT
    // =====================================================

    /**
     * Obtener catálogo de regímenes fiscales
     */
    public function catalogoRegimenes(Request $request)
    {
        $query = CatRegimenFiscal::activos();

        // Filtrar por tipo de persona si se especifica
        if ($request->has('persona_fisica')) {
            $query->paraPersonaFisica();
        }
        if ($request->has('persona_moral')) {
            $query->paraPersonaMoral();
        }

        return response()->json([
            'success' => true,
            'regimenes' => $query->orderBy('clave')->get()
        ]);
    }

    /**
     * Obtener catálogo de usos de CFDI
     */
    public function catalogoUsosCfdi(Request $request)
    {
        $query = CatUsoCfdi::activos();

        if ($request->has('persona_fisica')) {
            $query->paraPersonaFisica();
        }
        if ($request->has('persona_moral')) {
            $query->paraPersonaMoral();
        }
        if ($request->has('servicios_medicos')) {
            $query->paraServiciosMedicos();
        }

        return response()->json([
            'success' => true,
            'usos_cfdi' => $query->orderBy('clave')->get()
        ]);
    }

    // =====================================================
    // DATOS FISCALES DEL PACIENTE
    // =====================================================

    /**
     * Obtener datos fiscales de un paciente
     */
    public function getDatosFiscales($pacienteId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $datosFiscales = DatosFiscales::where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->activos()
            ->with(['regimenFiscalInfo', 'usoCfdiInfo'])
            ->first();

        if (!$datosFiscales) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos fiscales registrados para este paciente',
                'datos_fiscales' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'datos_fiscales' => $datosFiscales
        ]);
    }

    /**
     * Guardar o actualizar datos fiscales de un paciente
     */
    public function storeDatosFiscales(Request $request, $pacienteId)
    {
        $validator = Validator::make($request->all(), [
            'rfc' => 'required|string|min:12|max:13',
            'razon_social' => 'required|string|max:255',
            'codigo_postal' => 'required|string|size:5',
            'regimen_fiscal' => 'required|string|size:3',
            'uso_cfdi' => 'required|string|min:3|max:4',
            'email_facturacion' => 'nullable|email|max:255',
            'calle' => 'nullable|string|max:255',
            'numero_exterior' => 'nullable|string|max:50',
            'numero_interior' => 'nullable|string|max:50',
            'colonia' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
        ], [
            'rfc.required' => 'El RFC es requerido',
            'rfc.min' => 'El RFC debe tener al menos 12 caracteres',
            'rfc.max' => 'El RFC no puede tener más de 13 caracteres',
            'razon_social.required' => 'La razón social es requerida',
            'codigo_postal.required' => 'El código postal es requerido',
            'codigo_postal.size' => 'El código postal debe tener 5 dígitos',
            'regimen_fiscal.required' => 'El régimen fiscal es requerido',
            'uso_cfdi.required' => 'El uso del CFDI es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        // Validar RFC
        $rfc = strtoupper(trim($request->rfc));
        if (!DatosFiscales::validarRfc($rfc)) {
            return response()->json([
                'success' => false,
                'message' => 'El RFC no tiene un formato válido'
            ], 422);
        }

        // Verificar que el paciente existe y pertenece a la clínica
        $paciente = Paciente::where('id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->first();

        if (!$paciente) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        try {
            // Buscar si ya existe un registro con este RFC para este paciente/clínica
            $datosFiscales = DatosFiscales::firstOrNew([
                'paciente_id' => $pacienteId,
                'clinica_id' => $clinicaId,
                'rfc' => $rfc,
            ]);

            $datosFiscales->fill([
                'razon_social' => strtoupper($request->razon_social),
                'codigo_postal' => $request->codigo_postal,
                'regimen_fiscal' => $request->regimen_fiscal,
                'uso_cfdi' => $request->uso_cfdi,
                'email_facturacion' => $request->email_facturacion,
                'calle' => $request->calle,
                'numero_exterior' => $request->numero_exterior,
                'numero_interior' => $request->numero_interior,
                'colonia' => $request->colonia,
                'localidad' => $request->localidad,
                'municipio' => $request->municipio,
                'estado' => $request->estado,
                'activo' => true,
            ]);

            $datosFiscales->save();

            Log::info("Datos fiscales guardados para paciente {$pacienteId}, RFC: {$rfc}");

            return response()->json([
                'success' => true,
                'message' => 'Datos fiscales guardados correctamente',
                'datos_fiscales' => $datosFiscales->load(['regimenFiscalInfo', 'usoCfdiInfo'])
            ]);

        } catch (\Exception $e) {
            Log::error("Error al guardar datos fiscales: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los datos fiscales'
            ], 500);
        }
    }

    /**
     * Eliminar (desactivar) datos fiscales
     */
    public function deleteDatosFiscales($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $datosFiscales = DatosFiscales::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->first();

        if (!$datosFiscales) {
            return response()->json([
                'success' => false,
                'message' => 'Datos fiscales no encontrados'
            ], 404);
        }

        // Solo desactivar, no eliminar (para mantener historial de facturas)
        $datosFiscales->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Datos fiscales eliminados'
        ]);
    }

    // =====================================================
    // SOLICITUDES DE FACTURA
    // =====================================================

    /**
     * Listar solicitudes de factura
     */
    public function listSolicitudes(Request $request)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $query = SolicitudFactura::where('clinica_id', $clinicaId)
            ->with(['paciente:id,nombre,apellidoPat,apellidoMat', 'datosFiscales:id,rfc,razon_social', 'pago:id,monto,fecha_pago'])
            ->orderByDesc('folio')
            ->orderByDesc('created_at');

        ConsultorioScope::scopeSolicitudes($query, $user);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('paciente', function ($pq) use ($term) {
                    $pq->where('nombre', 'like', $term)
                        ->orWhere('apellidoPat', 'like', $term)
                        ->orWhere('apellidoMat', 'like', $term);
                })->orWhereHas('datosFiscales', function ($dq) use ($term) {
                    $dq->where('rfc', 'like', $term)
                        ->orWhere('razon_social', 'like', $term);
                });
            });
        }

        $solicitudes = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'solicitudes' => $solicitudes
        ]);
    }

    /**
     * Crear solicitud de factura
     */
    public function createSolicitud(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'datos_fiscales_id' => 'required|exists:datos_fiscales,id',
            'pago_id' => 'nullable|exists:pagos,id',
            'emisor_tipo' => 'required|in:clinica,doctor',
            'concepto' => 'required|string|max:1000',
            'subtotal' => 'required|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        // Verificar que los datos fiscales pertenecen al paciente y clínica
        $datosFiscales = DatosFiscales::where('id', $request->datos_fiscales_id)
            ->where('paciente_id', $request->paciente_id)
            ->where('clinica_id', $clinicaId)
            ->activos()
            ->first();

        if (!$datosFiscales) {
            return response()->json([
                'success' => false,
                'message' => 'Datos fiscales no válidos'
            ], 400);
        }

        $validacionPlan = SuscripcionFacturas::validarEmision($clinicaId, $user);
        if (! $validacionPlan['ok']) {
            return response()->json([
                'success' => false,
                'message' => $validacionPlan['message'],
                $validacionPlan['error_key'] => true,
            ], $validacionPlan['http_status']);
        }
        $suscripcion = $validacionPlan['suscripcion'];

        $clinica = \App\Models\Clinica::find($clinicaId);
        $esConsultorio = (bool) $clinica?->es_consultorio_privado;
        $emisorTipo = $request->input('emisor_tipo');

        if ($esConsultorio) {
            $emisorTipo = SolicitudFactura::EMISOR_DOCTOR;
        }

        if ($emisorTipo === SolicitudFactura::EMISOR_DOCTOR) {
            $efirmaDoctor = Efirma::paraFacturacionUsuario($user->id);

            if (! $efirmaDoctor?->listaParaFacturapi()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configura tu CSD personal en Perfil → Facturación CFDI para emitir facturas a tu nombre.',
                    'falta_configuracion' => true,
                ], 400);
            }

            // CSD fiscal personal no usa toggle; legacy personal sí
            if ($efirmaDoctor->tipo === 'personal' && ! ($efirmaDoctor->usar_para_facturacion ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activa "Emitir facturas (CFDI)" en Perfil → Seguridad o sube tu CSD en Facturación CFDI.',
                    'falta_toggle_facturacion' => true,
                ], 400);
            }
        } elseif (! $clinica?->facturapi_organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'La clínica no tiene el CSD/e.firma fiscal configurado. Sube tu certificado en la pestaña "E.Firma Fiscal".',
                'falta_configuracion' => true,
            ], 400);
        }

        $pago = null;
        $formaPago = $request->input('forma_pago');
        $metodoPagoCfdi = $request->input('metodo_pago_cfdi', SatFormaPago::PUE);

        if ($request->filled('pago_id')) {
            $pago = Pago::where('id', $request->pago_id)
                ->where('clinica_id', $clinicaId)
                ->first();

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago no pertenece a esta clínica',
                ], 400);
            }

            if ($esConsultorio && (int) $pago->user_id !== (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes facturar pagos que registraste tú',
                ], 403);
            }

            if ($pago->paciente_id && (int) $pago->paciente_id !== (int) $request->paciente_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago no corresponde al paciente seleccionado',
                ], 400);
            }

            if (SolicitudFactura::pagoTieneSolicitudActiva($pago->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pago ya tiene una factura asociada o una solicitud en proceso.',
                    'pago_ya_facturado' => true,
                ], 409);
            }

            $formaPago = $formaPago ?: SatFormaPago::desdeMetodoPago($pago->metodo_pago);
        }

        try {
            $subtotal = (float) $request->subtotal;
            $iva = (float) ($request->iva ?? 0);
            $total = $subtotal + $iva;

            $solicitud = SolicitudFactura::create([
                'clinica_id' => $clinicaId,
                'paciente_id' => $request->paciente_id,
                'datos_fiscales_id' => $request->datos_fiscales_id,
                'pago_id' => $request->pago_id,
                'emisor_tipo' => $emisorTipo,
                'doctor_id' => $emisorTipo === SolicitudFactura::EMISOR_DOCTOR ? $user->id : null,
                'concepto' => $request->concepto,
                'clave_prod_serv' => $request->clave_prod_serv ?? '85121800',
                'clave_unidad' => $request->clave_unidad ?? 'E48',
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'forma_pago' => $formaPago,
                'metodo_pago_cfdi' => $metodoPagoCfdi,
                'estado' => SolicitudFactura::ESTADO_PENDIENTE,
                'solicitada_por' => $user->id,
                'notas' => $request->notas,
            ]);

            Log::info("Solicitud de factura #{$solicitud->id} creada para paciente {$request->paciente_id}");

            // Auto-timbrar inmediatamente
            $facturapi = new FacturapiService();
            $resultado = $facturapi->timbrarFactura($solicitud);

            if ($resultado['success'] && $suscripcion) {
                $suscripcion->incrementarFacturasUsadas();
            }

            return response()->json([
                'success' => true,
                'message' => $resultado['success']
                    ? 'Factura timbrada exitosamente'
                    : 'Solicitud creada. El timbrado falló: ' . ($resultado['error'] ?? 'error desconocido'),
                'timbrada' => $resultado['success'],
                'solicitud' => $solicitud->fresh()->load(['paciente', 'datosFiscales'])
            ]);

        } catch (\Exception $e) {
            Log::error("Error al crear solicitud de factura: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la solicitud de factura'
            ], 500);
        }
    }

    /**
     * Ver detalle de una solicitud
     */
    public function showSolicitud($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->with(['paciente', 'datosFiscales', 'pago', 'doctor', 'solicitadaPor', 'procesadaPor'])
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver esta solicitud',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'solicitud' => $solicitud
        ]);
    }

    /**
     * Cancelar solicitud pendiente
     */
    public function cancelSolicitud(Request $request, $id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar esta solicitud',
            ], 403);
        }

        if ($solicitud->esta_facturada) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar una factura ya timbrada desde aquí. Use el proceso de cancelación de CFDI.'
            ], 400);
        }

        if ($solicitud->esta_cancelada) {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud ya está cancelada'
            ], 400);
        }

        $solicitud->marcarCancelada($request->motivo ?? 'Cancelada por usuario');

        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada correctamente'
        ]);
    }

    /**
     * Reintentar solicitud con error
     */
    public function retrySolicitud($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para reintentar esta solicitud',
            ], 403);
        }

        if (!$solicitud->puede_reintentar) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud no se puede reintentar'
            ], 400);
        }

        $solicitud->reintentar();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud marcada para reintento'
        ]);
    }

    // =====================================================
    // FACTURAPI - TIMBRADO CFDI
    // =====================================================

    /**
     * Timbrar factura a través de Facturapi
     */
    public function timbrarFactura(Request $request, $solicitudId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $solicitudId)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_PENDIENTE)
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada o ya procesada'
            ], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para timbrar esta solicitud',
            ], 403);
        }

        $validacionPlan = SuscripcionFacturas::validarEmision($clinicaId, $user);
        if (! $validacionPlan['ok']) {
            return response()->json([
                'success' => false,
                'message' => $validacionPlan['message'],
                $validacionPlan['error_key'] => true,
            ], $validacionPlan['http_status']);
        }

        try {
            $facturapi = new FacturapiService();
            $result = $facturapi->timbrarFactura($solicitud);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Factura timbranda exitosamente',
                    'uuid' => $result['uuid'],
                    'folio' => $result['folio'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error timbrando factura', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al timbrar la factura'
            ], 500);
        }
    }

    /**
     * Cancelar CFDI
     */
    public function cancelarCfdi(Request $request, $solicitudId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $solicitudId)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar esta factura',
            ], 403);
        }

        try {
            $facturapi = new FacturapiService();
            $motivo = $request->input('motivo', '02'); // 02 = Error en comprobante
            $result = $facturapi->cancelarCfdi($solicitud, $motivo);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'CFDI cancelado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar'
            ], 500);
        }
    }

    /**
     * Descargar XML
     */
    public function downloadXml($solicitudId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $solicitudId)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $facturapi = new FacturapiService();
            $xml = $facturapi->downloadXml($solicitud);

            if (!$xml) {
                return response()->json(['success' => false, 'message' => 'Error al descargar'], 500);
            }

            return response($xml, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => "attachment; filename=\"{$solicitud->uuid}.xml\"",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Descargar PDF
     */
    public function downloadPdf($solicitudId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $solicitudId)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
        }

        if (! ConsultorioScope::puedeAccederRecursoMedico($user, $solicitud->solicitada_por)) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $facturapi = new FacturapiService();
            $pdf = $facturapi->downloadPdf($solicitud);

            if (!$pdf) {
                return response()->json(['success' => false, 'message' => 'Error al descargar'], 500);
            }

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"factura-{$solicitud->folio_fiscal}.pdf\"",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Obtener estado de factura
     */
    public function obtenerEstadoFactura($solicitudId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $solicitudId)
            ->where('clinica_id', $clinicaId)
            ->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
        }

        try {
            $facturapi = new FacturapiService();
            $result = $facturapi->obtenerEstadoFactura($solicitud);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Reenviar factura timbrada al email del paciente
     */
    public function reenviarFactura(Request $request, $solicitudId)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $solicitudId)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->with(['paciente', 'datosFiscales', 'clinica'])
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada o no está timbrada',
            ], 404);
        }

        $email = $request->input('email')
            ?? $solicitud->datosFiscales?->email_facturacion
            ?? $solicitud->paciente?->email;

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'No hay email configurado. Proporciona un email de destino.',
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'El email proporcionado no es válido.',
            ], 422);
        }

        try {
            // Descargar PDF desde Facturapi
            $facturapi = new FacturapiService();
            $pdfContent = $facturapi->downloadPdf($solicitud);
            $xmlContent = $facturapi->downloadXml($solicitud);

            // Enviar por correo
            \Illuminate\Support\Facades\Mail::send(
                [],
                [],
                function ($message) use ($solicitud, $email, $pdfContent, $xmlContent) {
                    $nombreClinica = $solicitud->clinica?->nombre ?? 'LynkaMed';
                    $message->to($email)
                        ->subject("Factura {$solicitud->folio_fiscal} - {$nombreClinica}")
                        ->text(
                            "Adjunto encontrarás tu factura electrónica CFDI 4.0.\n\n"
                            . "Folio Fiscal: {$solicitud->folio_fiscal}\n"
                            . "Total: $" . number_format($solicitud->total, 2) . " MXN\n"
                        );

                    if ($pdfContent) {
                        $message->attachData($pdfContent, "factura-{$solicitud->folio_fiscal}.pdf", [
                            'mime' => 'application/pdf',
                        ]);
                    }

                    if ($xmlContent) {
                        $message->attachData($xmlContent, "factura-{$solicitud->folio_fiscal}.xml", [
                            'mime' => 'application/xml',
                        ]);
                    }
                }
            );

            $solicitud->registrarEnvio($email);

            Log::info("Factura {$solicitud->id} reenviada a {$email}");

            return response()->json([
                'success' => true,
                'message' => "Factura reenviada exitosamente a {$email}",
            ]);

        } catch (\Exception $e) {
            Log::error("Error reenviando factura {$solicitudId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar la factura: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resumen de facturas del mes (contador para la vista de facturación).
     */
    public function resumenMes()
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $estado = SuscripcionFacturas::estadoFacturacionParaClinica($clinicaId, null, $user);

        return response()->json([
            'success' => true,
            'data' => array_merge($estado, [
                'mes' => Carbon::now()->locale('es')->isoFormat('MMMM YYYY'),
            ]),
        ]);
    }

    /**
     * Pagos con paciente que aún no tienen factura (para facturar desde Facturación).
     */
    public function pagosSinFacturar(Request $request)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $validacionPlan = SuscripcionFacturas::validarEmision($clinicaId, $user);
        if (! $validacionPlan['ok']) {
            return response()->json([
                'success' => false,
                'message' => $validacionPlan['message'],
                $validacionPlan['error_key'] => true,
            ], $validacionPlan['http_status']);
        }

        $pagoIdsFacturados = SolicitudFactura::where('clinica_id', $clinicaId)
            ->whereNotNull('pago_id')
            ->whereIn('estado', SolicitudFactura::ESTADOS_OCUPAN_PAGO)
            ->pluck('pago_id');

        $query = Pago::with(['paciente:id,nombre,apellidoPat,apellidoMat,registro'])
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('paciente_id')
            ->whereNotIn('id', $pagoIdsFacturados)
            ->orderByDesc('created_at');

        ConsultorioScope::scopePagos($query, $user);

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('paciente', function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('apellidoPat', 'like', $term)
                    ->orWhere('apellidoMat', 'like', $term);
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $pagos = $query->paginate($perPage);

        $pagos->getCollection()->transform(function (Pago $pago) {
            return [
                'id' => $pago->id,
                'monto' => (float) $pago->monto,
                'metodo_pago' => $pago->metodo_pago,
                'concepto' => $pago->concepto,
                'notas' => $pago->notas,
                'fecha_pago' => $pago->fecha_pago?->format('Y-m-d'),
                'created_at' => $pago->created_at?->toIso8601String(),
                'paciente' => $pago->paciente ? [
                    'id' => $pago->paciente->id,
                    'nombre' => trim("{$pago->paciente->nombre} {$pago->paciente->apellidoPat} {$pago->paciente->apellidoMat}"),
                    'expediente' => $pago->paciente->registro,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'pagos' => $pagos,
        ]);
    }

    /**
     * Exportar facturas timbradas de un período en un ZIP (PDF + XML por factura).
     */
    public function exportarZip(Request $request)
    {
        $request->validate([
            'periodo' => 'required|in:dia,mes,anio',
            'fecha' => 'nullable|date',
            'mes' => 'nullable|date_format:Y-m',
            'anio' => 'nullable|integer|min:2000|max:2100',
        ]);

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        try {
            [$inicio, $fin, $slug] = $this->resolverRangoExportacion($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $solicitudesQuery = SolicitudFactura::where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->whereNotNull('facturapi_invoice_id')
            ->whereBetween('fecha_timbrado', [$inicio->copy()->startOfDay(), $fin->copy()->endOfDay()])
            ->with('clinica')
            ->orderBy('fecha_timbrado')
            ->limit(300);

        ConsultorioScope::scopeSolicitudes($solicitudesQuery, $user);
        $solicitudes = $solicitudesQuery->get();

        if ($solicitudes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay facturas timbradas en el período seleccionado.',
            ], 404);
        }

        $tempDir = storage_path('app/temp');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/facturas-' . $clinicaId . '-' . uniqid() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear el archivo ZIP.',
            ], 500);
        }

        $facturapi = new FacturapiService();
        $agregadas = 0;
        $errores = 0;

        foreach ($solicitudes as $solicitud) {
            $folio = $this->folioArchivoFactura($solicitud);
            $pdf = $facturapi->downloadPdf($solicitud);
            $xml = $facturapi->downloadXml($solicitud);

            if ($pdf) {
                $zip->addFromString("{$folio}/{$folio}.pdf", $pdf);
                $agregadas++;
            }
            if ($xml) {
                $zip->addFromString("{$folio}/{$folio}.xml", $xml);
            }
            if (! $pdf && ! $xml) {
                $errores++;
            }
        }

        $zip->close();

        if ($agregadas === 0) {
            @unlink($zipPath);

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron descargar los archivos de las facturas del período.',
            ], 500);
        }

        $nombreZip = "facturas-{$slug}.zip";

        return response()->download($zipPath, $nombreZip, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolverRangoExportacion(Request $request): array
    {
        $periodo = $request->input('periodo', 'dia');

        if ($periodo === 'mes') {
            $mes = $request->input('mes', Carbon::now()->format('Y-m'));
            if (! preg_match('/^\d{4}-\d{2}$/', $mes)) {
                throw new \InvalidArgumentException('Mes inválido. Use formato YYYY-MM.');
            }
            $inicio = Carbon::parse($mes . '-01')->startOfMonth();
            $fin = $inicio->copy()->endOfMonth();
            $slug = $mes;

            return [$inicio, $fin, $slug];
        }

        if ($periodo === 'anio') {
            $anio = (int) $request->input('anio', Carbon::now()->year);
            $inicio = Carbon::create($anio, 1, 1)->startOfYear();
            $fin = Carbon::create($anio, 12, 31)->endOfYear();
            $slug = (string) $anio;

            return [$inicio, $fin, $slug];
        }

        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $inicio = Carbon::parse($fecha)->startOfDay();
        $fin = $inicio->copy()->endOfDay();
        $slug = $inicio->format('Y-m-d');

        return [$inicio, $fin, $slug];
    }

    private function folioArchivoFactura(SolicitudFactura $solicitud): string
    {
        $etiqueta = $solicitud->etiquetaFolio();

        return preg_replace('/[^A-Za-z0-9_-]/', '_', $etiqueta);
    }
}
