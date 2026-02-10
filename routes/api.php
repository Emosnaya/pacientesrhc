<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClinicoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\EsfuerzoController;
use App\Http\Controllers\EstratificacionController;
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
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\FinanzasController;
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

Route::middleware(['auth:sanctum', 'multi.tenant'])->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['sucursal', 'clinica']);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rutas de perfil
    Route::get('/profile/{id}', [ProfileController::class, 'show']);
    Route::put('/profile/{id}', [ProfileController::class, 'update']);
    Route::post('/profile/{id}/upload-image', [ProfileController::class, 'uploadImage']);
    Route::post('/profile/{id}/upload-signature', [ProfileController::class, 'uploadSignature']);
    Route::post('/profile/{id}/upload-universidad-logo', [ProfileController::class, 'uploadUniversidadLogo']);
    Route::delete('/profile/{id}/delete-image', [ProfileController::class, 'deleteImage']);
    Route::delete('/profile/{id}/delete-signature', [ProfileController::class, 'deleteSignature']);

    // Almacenar ordenes
    Route::apiResource('/pacientes', PacienteController::class);
    
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

    // ==========================================
    // MÓDULO DE FINANZAS - Motor Financiero
    // ==========================================
    Route::prefix('finanzas')->group(function() {
        // Gestión de pagos
        Route::post('/pagos', [FinanzasController::class, 'registrarPago']);
        Route::get('/pagos', [FinanzasController::class, 'index']);
        Route::get('/pagos/{id}', [FinanzasController::class, 'show']);
        Route::delete('/pagos/{id}', [FinanzasController::class, 'destroy']);
        
        // Historial de pagos por paciente
        Route::get('/pacientes/{pacienteId}/pagos', [FinanzasController::class, 'historialPaciente']);
        // Enviar recibo por correo
        Route::post('/recibo/send-email', [FinanzasController::class, 'sendReciboByEmail']);
        
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

Route::post('/registro', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);

// Rutas para registro de clínicas (públicas)
Route::get('/clinicas/tipos', [\App\Http\Controllers\ClinicaController::class, 'getTipos']);
Route::post('/clinicas', [\App\Http\Controllers\ClinicaController::class, 'store']);

// Rutas para obtener información de clínicas (autenticadas)
Route::middleware(['auth:sanctum', 'multi.tenant'])->group(function () {
    Route::get('/clinicas/{id}', [\App\Http\Controllers\ClinicaController::class, 'show']);
    Route::get('/clinica/current', [\App\Http\Controllers\ClinicaController::class, 'getCurrentClinica']);
    Route::put('/clinica/update', [\App\Http\Controllers\ClinicaController::class, 'updateCurrentClinica']);
    Route::post('/clinica/upload-logo', [\App\Http\Controllers\ClinicaController::class, 'uploadLogo']);
});
