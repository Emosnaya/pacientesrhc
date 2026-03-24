<?php

namespace App\Http\Controllers;

use App\Models\DatosFiscales;
use App\Models\SolicitudFactura;
use App\Models\CatRegimenFiscal;
use App\Models\CatUsoCfdi;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
            ->orderBy('created_at', 'desc');

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

        try {
            $subtotal = (float) $request->subtotal;
            $iva = (float) ($request->iva ?? 0);
            $total = $subtotal + $iva;

            $solicitud = SolicitudFactura::create([
                'clinica_id' => $clinicaId,
                'paciente_id' => $request->paciente_id,
                'datos_fiscales_id' => $request->datos_fiscales_id,
                'pago_id' => $request->pago_id,
                'emisor_tipo' => $request->emisor_tipo,
                'doctor_id' => $request->emisor_tipo === 'doctor' ? $user->id : null,
                'concepto' => $request->concepto,
                'clave_prod_serv' => $request->clave_prod_serv ?? '85121800', // Servicios médicos
                'clave_unidad' => $request->clave_unidad ?? 'E48', // Unidad de servicio
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'estado' => SolicitudFactura::ESTADO_PENDIENTE,
                'solicitada_por' => $user->id,
                'notas' => $request->notas,
            ]);

            Log::info("Solicitud de factura #{$solicitud->id} creada para paciente {$request->paciente_id}");

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de factura creada correctamente',
                'solicitud' => $solicitud->load(['paciente', 'datosFiscales'])
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
    // PLACEHOLDER PARA INTEGRACIÓN CON PAC
    // =====================================================

    /**
     * Timbrar factura (PLACEHOLDER - requiere integración con PAC)
     */
    public function timbrarFactura($id)
    {
        // TODO: Integrar con PAC (Facturama, Finkok, etc.)
        return response()->json([
            'success' => false,
            'message' => 'Funcionalidad de timbrado no disponible. Se requiere integración con un PAC (Proveedor Autorizado de Certificación).'
        ], 501);
    }

    /**
     * Cancelar CFDI (PLACEHOLDER - requiere integración con PAC)
     */
    public function cancelarCfdi(Request $request, $id)
    {
        // TODO: Integrar con PAC para cancelación
        return response()->json([
            'success' => false,
            'message' => 'Funcionalidad de cancelación no disponible. Se requiere integración con un PAC.'
        ], 501);
    }

    /**
     * Descargar XML de factura
     */
    public function downloadXml($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->first();

        if (!$solicitud || !$solicitud->xml_path) {
            return response()->json([
                'success' => false,
                'message' => 'XML no disponible'
            ], 404);
        }

        // TODO: Implementar descarga del archivo
        return response()->json([
            'success' => false,
            'message' => 'Descarga de XML no implementada'
        ], 501);
    }

    /**
     * Descargar PDF de factura
     */
    public function downloadPdf($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->first();

        if (!$solicitud || !$solicitud->pdf_path) {
            return response()->json([
                'success' => false,
                'message' => 'PDF no disponible'
            ], 404);
        }

        // TODO: Implementar descarga del archivo
        return response()->json([
            'success' => false,
            'message' => 'Descarga de PDF no implementada'
        ], 501);
    }

    /**
     * Reenviar factura por email
     */
    public function reenviarFactura(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $solicitud = SolicitudFactura::where('id', $id)
            ->where('clinica_id', $clinicaId)
            ->where('estado', SolicitudFactura::ESTADO_FACTURADA)
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        }

        // TODO: Implementar envío de email con XML y PDF adjuntos
        return response()->json([
            'success' => false,
            'message' => 'Reenvío de factura no implementado'
        ], 501);
    }
}
