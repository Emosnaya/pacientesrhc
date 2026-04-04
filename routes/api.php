<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClinicoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\EsfuerzoController;
use App\Http\Controllers\EstratificacionController;
use App\Http\Controllers\EstratiAacvprController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DoctorFirmaController;
use App\Http\Controllers\ReporteFinalController;
use App\Http\Controllers\ReporteFisioController;
use App\Http\Controllers\ReporteNutriController;
use App\Http\Controllers\ReportePsicoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExpedienteUnificadoController;
use App\Http\Controllers\ExpedientePulmonarController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\FisioterapiaController;
use App\Http\Controllers\CualidadFisicaController;
use App\Http\Controllers\ReporteFinalPulmonarController;
use App\Http\Controllers\PruebaEsfuerzoPulmonarController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\HistoriaClinicaDentalController;
use App\Http\Controllers\OdontogramaController;
use App\Http\Controllers\RadiografiaDentalController;
use App\Http\Controllers\NotaSeguimientoPulmonarController;
use App\Http\Controllers\ConsultorioController;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\EfirmaController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SuscripcionConsultorioController;
use App\Http\Controllers\InternalAccessController;
use App\Http\Controllers\InternalConsultorioProvisionController;
use App\Http\Controllers\PacienteConsentimientoController;
use App\Http\Controllers\PacientePortalAuthController;
use App\Http\Controllers\PacientePortalController;
use App\Http\Controllers\HistoriaClinicaCardiologiaController;
use App\Http\Controllers\EcocardiogramaController;
use App\Http\Controllers\ElectrocardiogramaController;
use App\Http\Controllers\HistoriaGinecologicaController;
use App\Http\Controllers\HistoriaObstetricaController;
use App\Http\Controllers\ControlPrenatalController;
use App\Models\ReporteFisio;
use App\Models\ReportePsico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ═══════════════════════════════════════════════════════════════════
// RUTAS PÚBLICAS - Auto-registro de CONSULTORIOS PRIVADOS
// Las CLÍNICAS se registran manualmente por el equipo administrativo
// ═══════════════════════════════════════════════════════════════════
Route::prefix('registro')->group(function() {
    Route::get('/planes', [SubscriptionController::class, 'getPlans']);
    Route::post('/checkout', [SubscriptionController::class, 'createCheckoutSession']);
    Route::get('/verify-session/{sessionId}', [SubscriptionController::class, 'verifySession']);
});

// Webhook de Stripe (sin autenticación, Stripe valida con signature)
Route::post('/registro/webhook/stripe', [SubscriptionController::class, 'handleWebhook']);

// Desbloqueo por clave de operador (secreto en .env del servidor)
Route::post('/public/access/clinic-registration', [InternalAccessController::class, 'unlockClinicRegistration'])
    ->middleware('throttle:10,1');
Route::post('/public/access/internal-consultorio', [InternalAccessController::class, 'unlockInternalConsultorio'])
    ->middleware('throttle:10,1');

// Aceptación de aviso de privacidad / términos (enlace del correo al paciente)
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/public/paciente/consentimiento', [PacienteConsentimientoController::class, 'verificar']);
    Route::post('/public/paciente/consentimiento/aceptar', [PacienteConsentimientoController::class, 'aceptar']);
});

// Portal del paciente (primer acceso: OTP + contraseña; sin multi.tenant)
Route::middleware(['throttle:otp'])->group(function () {
    Route::post('/paciente-portal/request-otp', [PacientePortalAuthController::class, 'requestOtp']);
    Route::post('/paciente-portal/verify-otp', [PacientePortalAuthController::class, 'verifyOtp']);
});
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/paciente-portal/login', [PacientePortalAuthController::class, 'login']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/paciente-portal/set-password', [PacientePortalAuthController::class, 'setPassword']);
});

// Portal paciente: datos propios (requiere contraseña ya configurada)
Route::middleware(['auth:sanctum', 'multi.tenant', 'patient.portal'])->group(function () {
    Route::get('/paciente-portal/clinicas', [PacientePortalController::class, 'clinicas']);
    Route::get('/paciente-portal/clinicas/{clinicaId}/resumen', [PacientePortalController::class, 'clinicaResumen']);
    Route::get('/paciente-portal/clinicas/{clinicaId}/citas', [PacientePortalController::class, 'citas']);
    Route::get('/paciente-portal/perfil', [PacientePortalController::class, 'perfil']);
    Route::put('/paciente-portal/perfil', [PacientePortalController::class, 'updatePerfil']);
    Route::get('/paciente-portal/citas-calendario', [PacientePortalController::class, 'citasCalendario']);
    Route::get('/paciente-portal/expedientes-compartidos', [PacientePortalController::class, 'expedientesCompartidos']);
    Route::get('/paciente-portal/documento-compartido/{id}/pdf', [PacientePortalController::class, 'documentoCompartidoPdf']);
});

// ═══════════════════════════════════════════════════════════════════
// RUTAS AUTENTICADAS
// ═══════════════════════════════════════════════════════════════════
Route::middleware(['auth:sanctum', 'multi.tenant'])->group(function() {
    
    // Comprar consultorio adicional (addon)
    Route::post('/consultorio/comprar-adicional', [SubscriptionController::class, 'comprarConsultorioAdicional']);
    Route::get('/consultorio/comprar-adicional/usage', [SubscriptionController::class, 'getConsultorioUsage']);
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load(['sucursal', 'clinica', 'clinicaActiva', 'pacienteRecord']);

        if ($user->paciente_id) {
            $user->tiene_modulo_facturacion = false;
            $user->sucursales_permitidas_ids = [];
            $user->puede_crear_consultorio = false;
            $user->isAdmin = false;
            $user->isSuperAdmin = false;
            $user->rol_en_clinica = null;

            return $user;
        }

        $user->applyWorkspacePermissionsFromPivot();

        $efectiva = $user->clinica_efectiva_id;
        if ($efectiva) {
            $c = \App\Models\Clinica::query()->find($efectiva);
            $user->tiene_modulo_facturacion = $c && $c->facturacion_addon_activo;
            $user->sucursales_permitidas_ids = $user->getSucursalesPermitidasIdsForClinica($efectiva);
        } else {
            $user->tiene_modulo_facturacion = false;
            $user->sucursales_permitidas_ids = [];
        }

        $user->puede_crear_consultorio = $user->puedeCrearConsultorios();

        return $user;
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // SOPORTE / AYUDA
    // ==========================================
    Route::post('/soporte/ticket', [\App\Http\Controllers\SoporteController::class, 'crearTicket']);
    
    // Rutas de perfil
    Route::get('/profile/{id}', [ProfileController::class, 'show']);
    Route::put('/profile/{id}', [ProfileController::class, 'update']);
    Route::post('/profile/{id}/upload-image', [ProfileController::class, 'uploadImage']);
    Route::post('/profile/{id}/upload-signature', [ProfileController::class, 'uploadSignature']);
    Route::post('/profile/{id}/upload-universidad-logo', [ProfileController::class, 'uploadUniversidadLogo']);
    Route::delete('/profile/{id}/delete-image', [ProfileController::class, 'deleteImage']);
    Route::delete('/profile/{id}/delete-signature', [ProfileController::class, 'deleteSignature']);

    // Verificación de pacientes existentes (importación con OTP)
    Route::prefix('pacientes/verificacion')->middleware('throttle:otp')->group(function () {
        Route::post('/check-email', [\App\Http\Controllers\PacienteVerificationController::class, 'checkEmail']);
        Route::post('/request-otp', [\App\Http\Controllers\PacienteVerificationController::class, 'requestOtp']);
        Route::post('/verify-otp', [\App\Http\Controllers\PacienteVerificationController::class, 'verifyOtp']);
    });

    // Almacenar ordenes
    Route::put('/pacientes/{paciente}/portal-visibilidad', [PacienteController::class, 'updatePortalVisibilidad']);
    Route::get('/pacientes/{paciente}/portal-expedientes-compartidos', [PacienteController::class, 'portalExpedientesCompartidos']);
    Route::put('/pacientes/{paciente}/portal-expedientes-compartidos', [PacienteController::class, 'syncPortalExpedientesCompartidos']);
    Route::apiResource('/pacientes', PacienteController::class);
    Route::post('/pacientes/express', [PacienteController::class, 'createExpress']); // Flujo urgencias
    Route::post('/pacientes/{paciente}/vincular-clinica', [PacienteController::class, 'vincularClinica']);
    
    // Rutas para el calendario de citas
    Route::apiResource('/citas', CitaController::class);
    Route::get('/citas/calendar/data', [CitaController::class, 'getCalendarData']);
    Route::put('/citas/{id}/status', [CitaController::class, 'changeStatus']);
    Route::post('/citas/multiple', [CitaController::class, 'storeMultiple']);
    Route::delete('/citas/{id}/force', [CitaController::class, 'forceDelete']);

    // Rutas para eventos/recordatorios/tareas
    Route::apiResource('/eventos', EventoController::class);
    Route::get('/eventos/calendar/data', [EventoController::class, 'getCalendarData']);

    Route::apiResource('/esfuerzo',EsfuerzoController::class);
    Route::apiResource('/estratificacion',EstratificacionController::class);
    Route::apiResource('/estrati-aacvpr',EstratiAacvprController::class);
    Route::apiResource('/clinico',ClinicoController::class);
    Route::apiResource('/reporte',ReporteFinalController::class);
    Route::apiResource('/psico',ReportePsicoController::class);
    Route::apiResource('/nutri',ReporteNutriController::class);
    
    // Rutas de Historia Clínica Dental y Odontograma
    Route::apiResource('/historia-dental', HistoriaClinicaDentalController::class);
    Route::get('/historia-dental/paciente/{pacienteId}', [HistoriaClinicaDentalController::class, 'getByPaciente']);
    Route::apiResource('/odontogramas', OdontogramaController::class);
    Route::get('/odontogramas/paciente/{pacienteId}', [OdontogramaController::class, 'getByPaciente']);
    Route::get('/odontogramas/paciente/{pacienteId}/latest', [OdontogramaController::class, 'getLatestByPaciente']);

    // Rutas de Radiografías Dentales
    Route::get('/radiografias-dentales/paciente/{pacienteId}', [RadiografiaDentalController::class, 'getByPaciente']);
    Route::post('/radiografias-dentales', [RadiografiaDentalController::class, 'store']);
    Route::get('/radiografias-dentales/{id}', [RadiografiaDentalController::class, 'show']);
    Route::delete('/radiografias-dentales/{id}', [RadiografiaDentalController::class, 'destroy']);

    // Recetas médicas
    Route::get('/recetas', [RecetaController::class, 'index']);
    Route::get('/recetas/paciente/{pacienteId}', [RecetaController::class, 'getByPaciente']);
    Route::get('/recetas/imprimir/{id}', [PDFController::class, 'recetaPdf']);
    Route::get('/recetas/config/pdf', [RecetaController::class, 'getPdfConfig']);
    Route::put('/recetas/config/pdf', [RecetaController::class, 'updatePdfConfig']);
    Route::get('/recetas/{id}', [RecetaController::class, 'show']);
    Route::post('/recetas', [RecetaController::class, 'store']);
    Route::post('/recetas/{id}/firmar', [EfirmaController::class, 'firmarReceta']);
    Route::get('/recetas/{id}/verificar-firma', [EfirmaController::class, 'verificarFirma']);

    // E.firma (firma electrónica avanzada SAT)
    // E.firma personal del usuario (para firmar recetas - requisito COFEPRIS)
    Route::get('/efirma', [EfirmaController::class, 'show']);
    Route::post('/efirma', [EfirmaController::class, 'store']);
    Route::patch('/efirma/usos', [EfirmaController::class, 'updateUsos']);
    Route::delete('/efirma', [EfirmaController::class, 'destroy']);
    
    // E.firma fiscal personal del usuario (para facturar individualmente en consultorio compartido)
    Route::get('/efirma/fiscal', [EfirmaController::class, 'showFiscalPersonal']);
    Route::post('/efirma/fiscal', [EfirmaController::class, 'storeFiscalPersonal']);
    Route::delete('/efirma/fiscal', [EfirmaController::class, 'destroyFiscalPersonal']);
    
    // E.firma fiscal de la clínica (solo admin/superadmin - para facturas de la clínica)
    Route::get('/efirma/clinica', [EfirmaController::class, 'showClinica']);
    Route::post('/efirma/clinica', [EfirmaController::class, 'storeClinica']);
    Route::delete('/efirma/clinica', [EfirmaController::class, 'destroyClinica']);

    // ==========================================
    // MÓDULO DE FACTURACIÓN
    // ==========================================
    Route::prefix('facturacion')->group(function() {
        // Catálogos SAT (referencia; también protegidos por add-on para no filtrar flujo sin contrato)
        Route::middleware(['facturacion.addon'])->group(function () {
            Route::get('/catalogos/regimenes', [FacturacionController::class, 'catalogoRegimenes']);
            Route::get('/catalogos/usos-cfdi', [FacturacionController::class, 'catalogoUsosCfdi']);

            Route::get('/pacientes/{pacienteId}/datos-fiscales', [FacturacionController::class, 'getDatosFiscales']);
            Route::post('/pacientes/{pacienteId}/datos-fiscales', [FacturacionController::class, 'storeDatosFiscales']);
            Route::delete('/datos-fiscales/{id}', [FacturacionController::class, 'deleteDatosFiscales']);

            Route::get('/solicitudes', [FacturacionController::class, 'listSolicitudes']);
            Route::post('/solicitudes', [FacturacionController::class, 'createSolicitud']);
            Route::get('/solicitudes/{id}', [FacturacionController::class, 'showSolicitud']);
            Route::post('/solicitudes/{id}/cancelar', [FacturacionController::class, 'cancelSolicitud']);
            Route::post('/solicitudes/{id}/reintentar', [FacturacionController::class, 'retrySolicitud']);

            Route::post('/solicitudes/{id}/timbrar', [FacturacionController::class, 'timbrarFactura']);
            Route::post('/solicitudes/{id}/cancelar-cfdi', [FacturacionController::class, 'cancelarCfdi']);

            Route::get('/solicitudes/{id}/xml', [FacturacionController::class, 'downloadXml']);
            Route::get('/solicitudes/{id}/pdf', [FacturacionController::class, 'downloadPdf']);
            Route::post('/solicitudes/{id}/reenviar', [FacturacionController::class, 'reenviarFactura']);
        });
    });

    // ==========================================
    // MÓDULO DE FINANZAS - Motor Financiero
    // ==========================================
    Route::prefix('finanzas')->group(function() {
        // Gestión de pagos
        Route::post('/pagos', [FinanzasController::class, 'registrarPago']);
        Route::get('/pagos', [FinanzasController::class, 'index']);
        Route::get('/pagos/{id}', [FinanzasController::class, 'show']);
        Route::put('/pagos/{id}', [FinanzasController::class, 'updatePago']);
        Route::delete('/pagos/{id}', [FinanzasController::class, 'destroyPago']);
        
        // Gestión de egresos (retiros/gastos)
        Route::post('/egresos', [FinanzasController::class, 'registrarEgreso']);
        Route::get('/egresos', [FinanzasController::class, 'indexEgresos']);
        Route::get('/egresos/{id}', [FinanzasController::class, 'showEgreso']);
        Route::delete('/egresos/{id}', [FinanzasController::class, 'destroyEgreso']);
        
        // Historial de pagos por paciente
        Route::get('/pacientes/{pacienteId}/pagos', [FinanzasController::class, 'historialPaciente']);
        // Enviar recibo por correo
        Route::post('/recibo/send-email', [FinanzasController::class, 'sendReciboByEmail']);
        // Ver recibo en PDF
        Route::get('/recibo/{id}/pdf', [FinanzasController::class, 'verReciboPdf']);
        
        // Corte de caja
        Route::get('/corte-caja', [FinanzasController::class, 'corteCajaDiario']);
        
        // Corte del día/mes/año
        Route::get('/corte', [FinanzasController::class, 'corte']);
        Route::get('/corte/exportar', [FinanzasController::class, 'exportarCorte']);
        
        // Estadísticas financieras
        Route::get('/estadisticas', [FinanzasController::class, 'estadisticas']);
    });

    Route::get('/esfuerzo/imprimir/{id}',[PDFController::class, 'esfuerzoPdf']);
    Route::get('/estratificacion/imprimir/{id}',[PDFController::class,'estratificacionPdf']);
    Route::get('/estrati-aacvpr/imprimir/{id}',[PDFController::class,'estratiAacvprPdf']);
    Route::get('/clinico/imprimir/{id}',[PDFController::class,'clinicoPdf']);
    Route::get('/reporte/imprimir/{id}',[PDFController::class,'reportePdf']);
    Route::get('/psico/imprimir/{id}',[PDFController::class,'psicoPdf']);
    Route::get('/nutri/imprimir/{id}',[PDFController::class,'nutriPdf']);
    Route::get('/fisioterapia/historia/imprimir/{id}',[PDFController::class,'historiaFisioterapiaPdf']);
    Route::get('/fisioterapia/evolucion/imprimir/{id}',[PDFController::class,'notaEvolucionFisioterapiaPdf']);
    Route::get('/fisioterapia/alta/imprimir/{id}',[PDFController::class,'notaAltaFisioterapiaPdf']);
    Route::get('/historia-dental/imprimir/{id}',[PDFController::class,'historiaDentalPdf']);
    Route::get('/odontogramas/imprimir/{id}',[PDFController::class,'odontogramaPdf']);
    Route::get('/nota-seguimiento-pulmonar/imprimir/{id}', [PDFController::class, 'notaSeguimientoPulmonarPdf']);
    Route::post('/expediente/send-email', [PDFController::class, 'sendExpedienteByEmail']);
    
    // Obtener lista de doctores con firma para seleccionar
    Route::get('/doctores-con-firma', [DoctorFirmaController::class, 'getDoctoresConFirma']);
    
    Route::get('/clinicos/{id}',[InfoController::class,'clinicos']);
    Route::get('/esfuerzos/{id}',[InfoController::class,'esfuerzos']);
    Route::get('/estratificaciones/{id}',[InfoController::class,'estratificaciones']);
    Route::get('/estrati-aacvprs/{id}',[InfoController::class,'estratiAacvprs']);
    Route::get('/reportes/{id}',[InfoController::class,'reportes']);
    Route::get('/nutricional/{id}',[InfoController::class,'nutricional']);
    Route::get('/psicologico/{id}',[InfoController::class,'psicologico']);
    
    // Ruta para verificar permisos específicos de expedientes
    Route::get('/pacientes/{id}/expedientes/{expedienteType}/{expedienteId}/permissions', [InfoController::class, 'checkExpedientePermissions']);
    
    // Ruta unificada para obtener todos los expedientes de un paciente
    Route::get('/expedientes/{pacienteId}', [ExpedienteUnificadoController::class, 'getExpedientesByPaciente']);
    
    // Rutas de Sucursales
    Route::apiResource('/sucursales', SucursalController::class);
    Route::get('/sucursales/clinica/{clinicaId}', [SucursalController::class, 'getByClinica']);
    Route::post('/sucursales/cambiar', [SucursalController::class, 'cambiarSucursal']);
    Route::get('/sucursales/{id}/estadisticas', [SucursalController::class, 'estadisticas']);
    
    // Rutas de IA
    Route::post('/ai/transcribe', [AIController::class, 'transcribe']);
    Route::post('/ai/autocomplete', [AIController::class, 'autocomplete']);
    Route::post('/ai/summarize', [AIController::class, 'summarize']);
    Route::post('/ai/chat', [AIController::class, 'chat']); // Asistente médico virtual
    Route::get('/ai/context', [AIController::class, 'getContext']); // Contexto proactivo del día
    Route::post('/ai/action', [AIController::class, 'executeAction']); // Ejecutar acciones del asistente
    
    // Rutas de Dashboard Insights con IA
    Route::get('/dashboard/insights', [DashboardController::class, 'generateInsights']);
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/alerts', [DashboardController::class, 'getAlerts']);
    
    Route::delete('/psicolo/{id}',[InfoController::class,'destroyPsico']);
    Route::delete('/nutriolo/{id}',[InfoController::class,'destroyNutri']);
    Route::delete('/final/{id}',[InfoController::class,'destroyFinal']);
    Route::delete('/esfu/{id}',[InfoController::class,'destroyEsfuer']);
    Route::delete('/estra/{id}',[InfoController::class,'destroyEstrati']);
    Route::delete('/clini/{id}',[InfoController::class,'destroyClinico']);
    Route::get('/nutrio/{id}',[InfoController::class,'nutriIndex']);
    Route::get('/psicolog/{id}',[InfoController::class,'psicoIndex']);
    Route::prefix('fisio')->group(function () {
        Route::get('/{pacienteId}', [ReporteFisioController::class, 'index']); // Lista expedientes de un paciente // Crear expediente
        Route::get('/detalle/{id}', [ReporteFisioController::class, 'show']); // Mostrar detalle
        Route::put('/{id}', [ReporteFisioController::class, 'update']); // Actualizar expediente
        Route::delete('/{id}', [ReporteFisioController::class, 'destroy']);
    });
    Route::post('/fis', [ReporteFisioController::class, 'store']);
    Route::get('/fisio/{id}/imprimir', [ReporteFisioController::class, 'imprimir']);
    
    // Rutas para Expediente Pulmonar
    Route::prefix('pulmonar')->group(function () {
        // IMPORTANTE: Las rutas más específicas deben ir primero
        Route::get('/paciente/{pacienteId}', [ExpedientePulmonarController::class, 'index']); // Lista expedientes de un paciente
        Route::get('/detalle/{id}', [ExpedientePulmonarController::class, 'show']); // Alias para compatibilidad
        Route::get('/{id}/imprimir', [PDFController::class, 'pulmonarPdf']); // Imprimir expediente pulmonar
        Route::get('/{id}', [ExpedientePulmonarController::class, 'show']); // Mostrar detalle de un expediente (debe ir después de las rutas específicas)
        Route::post('/', [ExpedientePulmonarController::class, 'store']); // Crear expediente
        Route::put('/{id}', [ExpedientePulmonarController::class, 'update']); // Actualizar expediente
        Route::delete('/{id}', [ExpedientePulmonarController::class, 'destroy']); // Eliminar expediente
    });

    // Rutas para expedientes de Fisioterapia
    Route::prefix('fisioterapia')->group(function () {
        // Historia Clínica de Fisioterapia
        Route::prefix('historia')->group(function () {
            Route::get('/paciente/{pacienteId}', [FisioterapiaController::class, 'indexHistoria']);
            Route::post('/', [FisioterapiaController::class, 'storeHistoria']);
            Route::get('/{id}', [FisioterapiaController::class, 'showHistoria']);
            Route::put('/{id}', [FisioterapiaController::class, 'updateHistoria']);
            Route::delete('/{id}', [FisioterapiaController::class, 'destroyHistoria']);
        });

        // Nota de Evolución de Fisioterapia
        Route::prefix('evolucion')->group(function () {
            Route::get('/paciente/{pacienteId}', [FisioterapiaController::class, 'indexEvolucion']);
            Route::post('/', [FisioterapiaController::class, 'storeEvolucion']);
            Route::get('/{id}', [FisioterapiaController::class, 'showEvolucion']);
            Route::put('/{id}', [FisioterapiaController::class, 'updateEvolucion']);
            Route::delete('/{id}', [FisioterapiaController::class, 'destroyEvolucion']);
        });

        // Nota de Alta de Fisioterapia
        Route::prefix('alta')->group(function () {
            Route::get('/paciente/{pacienteId}', [FisioterapiaController::class, 'indexAlta']);
            Route::post('/', [FisioterapiaController::class, 'storeAlta']);
            Route::get('/{id}', [FisioterapiaController::class, 'showAlta']);
            Route::put('/{id}', [FisioterapiaController::class, 'updateAlta']);
            Route::delete('/{id}', [FisioterapiaController::class, 'destroyAlta']);
        });
    });

    // Rutas para Cualidades Físicas No Aeróbicas
    Route::prefix('cualidades-fisicas')->group(function () {
        Route::get('/', [CualidadFisicaController::class, 'index']);
        Route::post('/', [CualidadFisicaController::class, 'store']);
        Route::get('/latest', [CualidadFisicaController::class, 'latest']);
        Route::get('/{id}', [CualidadFisicaController::class, 'show']);
        Route::put('/{id}', [CualidadFisicaController::class, 'update']);
        Route::delete('/{id}', [CualidadFisicaController::class, 'destroy']);
        Route::get('/{id}/print', [CualidadFisicaController::class, 'print']);
        Route::get('/{id}/download', [CualidadFisicaController::class, 'download']);
    });

    // Rutas para Reporte Final Pulmonar
    Route::prefix('reporte-final-pulmonar')->group(function () {
        Route::get('/', [ReporteFinalPulmonarController::class, 'index']);
        Route::post('/', [ReporteFinalPulmonarController::class, 'store']);
        Route::post('/generar-automatico', [ReporteFinalPulmonarController::class, 'generateFromComparison']); // Generar desde comparación
        Route::get('/{id}', [ReporteFinalPulmonarController::class, 'show']);
        Route::put('/{id}', [ReporteFinalPulmonarController::class, 'update']);
        Route::delete('/{id}', [ReporteFinalPulmonarController::class, 'destroy']);
        Route::get('/{id}/print', [ReporteFinalPulmonarController::class, 'print']);
        Route::get('/{id}/download', [ReporteFinalPulmonarController::class, 'download']);
    });

    // Rutas para Nota de Seguimiento Pulmonar
    Route::prefix('nota-seguimiento-pulmonar')->group(function () {
        Route::get('/', [NotaSeguimientoPulmonarController::class, 'index']);
        Route::post('/', [NotaSeguimientoPulmonarController::class, 'store']);
        Route::get('/{id}', [NotaSeguimientoPulmonarController::class, 'show']);
        Route::put('/{id}', [NotaSeguimientoPulmonarController::class, 'update']);
        Route::delete('/{id}', [NotaSeguimientoPulmonarController::class, 'destroy']);
    });

    // Rutas para Prueba de Esfuerzo Pulmonar
    Route::prefix('prueba-esfuerzo-pulmonar')->group(function () {
        Route::get('/', [PruebaEsfuerzoPulmonarController::class, 'index']);
        Route::get('/paciente/{pacienteId}', [PruebaEsfuerzoPulmonarController::class, 'getByPaciente']); // Lista de pruebas por paciente
        Route::post('/', [PruebaEsfuerzoPulmonarController::class, 'store']);
        Route::get('/{pruebaEsfuerzoPulmonar}', [PruebaEsfuerzoPulmonarController::class, 'show']);
        Route::put('/{pruebaEsfuerzoPulmonar}', [PruebaEsfuerzoPulmonarController::class, 'update']);
        Route::delete('/{pruebaEsfuerzoPulmonar}', [PruebaEsfuerzoPulmonarController::class, 'destroy']);
        Route::get('/{id}/print', [PruebaEsfuerzoPulmonarController::class, 'generatePDF']);
        Route::get('/{id}/download', [PruebaEsfuerzoPulmonarController::class, 'downloadPDF']);
    });

    // ==========================================
    // MÓDULO CARDIOLOGÍA (Expedientes)
    // ==========================================
    
    // Historia Clínica Cardiológica
    Route::prefix('cardiologia/historia')->group(function () {
        Route::get('/paciente/{pacienteId}', [HistoriaClinicaCardiologiaController::class, 'index']);
        Route::post('/', [HistoriaClinicaCardiologiaController::class, 'store']);
        Route::get('/{id}', [HistoriaClinicaCardiologiaController::class, 'show']);
        Route::put('/{id}', [HistoriaClinicaCardiologiaController::class, 'update']);
        Route::delete('/{id}', [HistoriaClinicaCardiologiaController::class, 'destroy']);
        Route::get('/{id}/pdf', [HistoriaClinicaCardiologiaController::class, 'pdf']);
    });

    // Ecocardiograma
    Route::prefix('cardiologia/ecocardiograma')->group(function () {
        Route::get('/paciente/{pacienteId}', [EcocardiogramaController::class, 'index']);
        Route::post('/', [EcocardiogramaController::class, 'store']);
        Route::get('/{id}', [EcocardiogramaController::class, 'show']);
        Route::put('/{id}', [EcocardiogramaController::class, 'update']);
        Route::delete('/{id}', [EcocardiogramaController::class, 'destroy']);
        Route::get('/{id}/pdf', [EcocardiogramaController::class, 'pdf']);
        Route::get('/compare/{id1}/{id2}', [EcocardiogramaController::class, 'compare']);
    });

    // Electrocardiograma
    Route::prefix('cardiologia/ecg')->group(function () {
        Route::get('/paciente/{pacienteId}', [ElectrocardiogramaController::class, 'index']);
        Route::post('/', [ElectrocardiogramaController::class, 'store']);
        Route::get('/{id}', [ElectrocardiogramaController::class, 'show']);
        Route::put('/{id}', [ElectrocardiogramaController::class, 'update']);
        Route::delete('/{id}', [ElectrocardiogramaController::class, 'destroy']);
        Route::get('/{id}/pdf', [ElectrocardiogramaController::class, 'pdf']);
        Route::get('/compare/{id1}/{id2}', [ElectrocardiogramaController::class, 'compare']);
    });

    // ==========================================
    // MÓDULO GINECOLOGÍA Y OBSTETRICIA
    // ==========================================
    
    // Historia Ginecológica
    Route::prefix('ginecologia/historia')->group(function () {
        Route::get('/paciente/{pacienteId}', [HistoriaGinecologicaController::class, 'index']);
        Route::post('/', [HistoriaGinecologicaController::class, 'store']);
        Route::get('/{id}', [HistoriaGinecologicaController::class, 'show']);
        Route::put('/{id}', [HistoriaGinecologicaController::class, 'update']);
        Route::delete('/{id}', [HistoriaGinecologicaController::class, 'destroy']);
        Route::get('/{id}/pdf', [HistoriaGinecologicaController::class, 'pdf']);
    });

    // Historia Obstétrica
    Route::prefix('obstetricia/historia')->group(function () {
        Route::get('/paciente/{pacienteId}', [HistoriaObstetricaController::class, 'index']);
        Route::post('/', [HistoriaObstetricaController::class, 'store']);
        Route::get('/{id}', [HistoriaObstetricaController::class, 'show']);
        Route::put('/{id}', [HistoriaObstetricaController::class, 'update']);
        Route::delete('/{id}', [HistoriaObstetricaController::class, 'destroy']);
        Route::get('/{id}/pdf', [HistoriaObstetricaController::class, 'pdf']);
    });

    // Control Prenatal
    Route::prefix('obstetricia/control-prenatal')->group(function () {
        Route::get('/paciente/{pacienteId}', [ControlPrenatalController::class, 'index']);
        Route::post('/', [ControlPrenatalController::class, 'store']);
        Route::get('/{id}', [ControlPrenatalController::class, 'show']);
        Route::put('/{id}', [ControlPrenatalController::class, 'update']);
        Route::delete('/{id}', [ControlPrenatalController::class, 'destroy']);
        Route::get('/{id}/pdf', [ControlPrenatalController::class, 'pdf']);
        Route::get('/resumen/{historiaObstetricaId}', [ControlPrenatalController::class, 'resumenEmbarazo']);
    });

    // ==========================================
    // MÓDULO CONSULTORIO PRIVADO
    // ==========================================
    Route::prefix('consultorio')->group(function () {
        Route::post('/setup', [ConsultorioController::class, 'setup']);
        Route::get('/mis-clinicas', [ConsultorioController::class, 'misClinicas']);
        Route::post('/cambiar-workspace', [ConsultorioController::class, 'cambiarWorkspace']);
        Route::post('/invitar', [ConsultorioController::class, 'invitar']);
        // Aceptar / rechazar invitación (requiere auth, el front redirige tras login)
        Route::post('/invitacion/{token}/aceptar', [ConsultorioController::class, 'aceptarInvitacion']);
        Route::post('/invitacion/{token}/rechazar', [ConsultorioController::class, 'rechazarInvitacion']);
        Route::get('/{clinicaId}/colaboradores', [ConsultorioController::class, 'listarColaboradores']);
        Route::delete('/{clinicaId}/colaboradores/{userId}', [ConsultorioController::class, 'eliminarColaborador']);
        Route::get('/{clinicaId}/invitaciones', [ConsultorioController::class, 'invitacionesPendientes']);
    });

    // MÓDULO SUSCRIPCIONES PARA CONSULTORIOS PRIVADOS
    // ==========================================
    Route::prefix('suscripcion-consultorio')->group(function () {
        Route::get('/estado', [SuscripcionConsultorioController::class, 'estado']);
        Route::get('/planes', [SuscripcionConsultorioController::class, 'planes']);
        Route::post('/iniciar-trial', [SuscripcionConsultorioController::class, 'iniciarTrial']);
        Route::post('/crear-suscripcion', [SuscripcionConsultorioController::class, 'crearSuscripcion']);
        Route::post('/comprar-consultorio-adicional', [SuscripcionConsultorioController::class, 'comprarConsultorioAdicional']);
        Route::post('/cancelar', [SuscripcionConsultorioController::class, 'cancelar']);
    });

});

// Rutas para gestión de usuarios y permisos (solo administradores)
Route::prefix('admin')->middleware(['auth:sanctum', 'multi.tenant'])->group(function () {
    Route::post('/users', [UserManagementController::class, 'createUser']);
    Route::get('/users', [UserManagementController::class, 'listAllUsers']);
    Route::get('/users/doctors', [UserManagementController::class, 'listDoctors']);
    Route::put('/users/{id}', [UserManagementController::class, 'updateUser']);
    Route::delete('/users/{id}', [UserManagementController::class, 'deleteUser']);
    Route::post('/permissions/assign', [UserManagementController::class, 'assignPermission']);
    Route::post('/permissions/assign-bulk', [UserManagementController::class, 'assignBulkPermissions']);
    Route::post('/permissions/revoke-bulk', [UserManagementController::class, 'revokeBulkPermissions']);
    Route::post('/permissions/revoke', [UserManagementController::class, 'revokePermission']);
    Route::get('/users/{userId}/permissions', [UserManagementController::class, 'getUserPermissions']);
    Route::get('/my-resources', [UserManagementController::class, 'getMyResourcesWithPermissions']);
});

// Ruta para analíticas (solo administradores)
Route::get('/analytics', [AnalyticsController::class, 'index'])->middleware('auth:sanctum');

// Ruta para obtener lista de usuarios/doctores (todos los usuarios autenticados)
Route::get('/users', [UserManagementController::class, 'listDoctors'])->middleware(['auth:sanctum', 'multi.tenant']);

Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/registro', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword'])->middleware('throttle:auth');
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->middleware('throttle:auth');

// Invitación consultorio — pública para preview antes de login
Route::get('/consultorio/invitacion/{token}', [ConsultorioController::class, 'verInvitacion']);

// Registro de clínicas (público pero protegido si CLINIC_REGISTRATION_SECRET está definido)
Route::middleware(['clinic.registration'])->group(function () {
    Route::get('/clinicas/tipos', [\App\Http\Controllers\ClinicaController::class, 'getTipos']);
    Route::post('/clinicas', [\App\Http\Controllers\ClinicaController::class, 'store']);
});

// Provisionamiento interno de consultorio (operador)
Route::middleware(['internal.consultorio.setup'])->group(function () {
    Route::post('/internal/consultorio/provision', [InternalConsultorioProvisionController::class, 'store']);
});

// Rutas para obtener información de clínicas (autenticadas)
Route::middleware(['auth:sanctum', 'multi.tenant'])->group(function () {
    Route::get('/clinicas/{id}', [\App\Http\Controllers\ClinicaController::class, 'show']);
    Route::get('/clinica/current', [\App\Http\Controllers\ClinicaController::class, 'getCurrentClinica']);
    Route::put('/clinica/update', [\App\Http\Controllers\ClinicaController::class, 'updateCurrentClinica']);
    Route::put('/clinica/{id}', [\App\Http\Controllers\ClinicaController::class, 'update']);
    Route::post('/clinica/upload-logo', [\App\Http\Controllers\ClinicaController::class, 'uploadLogo']);
    Route::post('/clinica/{id}/upload-logo', [\App\Http\Controllers\ClinicaController::class, 'uploadLogo']);
    Route::delete('/clinica/{id}/delete-logo', [\App\Http\Controllers\ClinicaController::class, 'deleteLogo']);
});

// ═══════════════════════════════════════════════════════════════════
// RUTAS INTERNAL - Panel interno de Lynkamed (Backoffice)
// Solo para usuarios internos de Lynkamed (AdminUser)
// ═══════════════════════════════════════════════════════════════════
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminClinicasController;
use App\Http\Controllers\AdminConsultoriosController;

// Autenticación internal (pública)
Route::prefix('internal')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
});

// Rutas protegidas de internal
Route::prefix('internal')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    // Auth
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']);

    // Dashboard stats
    Route::get('/dashboard/stats', function () {
        return response()->json([
            'success' => true,
            'stats' => [
                'total_clinicas' => \App\Models\Clinica::where('es_consultorio_privado', false)->count(),
                'total_consultorios' => \App\Models\Clinica::where('es_consultorio_privado', true)->count(),
                'total_usuarios' => \App\Models\User::count(),
                'suscripciones_activas' => \App\Models\Clinica::where('es_consultorio_privado', true)
                    ->where('pagado', true)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                          ->orWhere('fecha_vencimiento', '>=', now());
                    })
                    ->count(),
            ],
        ]);
    });

    // Clínicas
    Route::get('/clinicas', [AdminClinicasController::class, 'index']);
    Route::get('/clinicas/stats', [AdminClinicasController::class, 'stats']);
    Route::get('/clinicas/tipos', [\App\Http\Controllers\ClinicaController::class, 'getTipos']); // tipos de clínica
    Route::post('/clinicas', [AdminClinicasController::class, 'store']); // Crear clínica + usuario (sin pago)
    Route::get('/clinicas/{clinica}', [AdminClinicasController::class, 'show']);
    Route::put('/clinicas/{clinica}', [AdminClinicasController::class, 'update']);
    Route::post('/clinicas/{clinica}/toggle-status', [AdminClinicasController::class, 'toggleStatus']);

    // Consultorios (son clínicas con es_consultorio_privado = true)
    Route::get('/consultorios', [AdminConsultoriosController::class, 'index']);
    Route::get('/consultorios/stats', [AdminConsultoriosController::class, 'stats']);
    Route::get('/consultorios/{consultorio}', [AdminConsultoriosController::class, 'show'])
        ->where('consultorio', '[0-9]+');
    Route::post('/consultorios/provisionar', [AdminConsultoriosController::class, 'provisionar']);
    Route::post('/consultorios/{consultorio}/extender', [AdminConsultoriosController::class, 'extenderSuscripcion'])
        ->where('consultorio', '[0-9]+');
    Route::post('/consultorios/{consultorio}/toggle-status', [AdminConsultoriosController::class, 'toggleStatus'])
        ->where('consultorio', '[0-9]+');
});
