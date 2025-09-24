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
use App\Http\Controllers\ReporteFinalController;
use App\Http\Controllers\ReporteFisioController;
use App\Http\Controllers\ReporteNutriController;
use App\Http\Controllers\ReportePsicoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AnalyticsController;
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

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rutas de perfil
    Route::get('/users/{id}', [ProfileController::class, 'show']);
    Route::put('/users/{id}', [ProfileController::class, 'update']);
    Route::post('/users/{id}/upload-image', [ProfileController::class, 'uploadImage']);
    Route::post('/users/{id}/upload-signature', [ProfileController::class, 'uploadSignature']);

    // Almacenar ordenes
    Route::apiResource('/pacientes', PacienteController::class);
    
    // Rutas para el calendario de citas
    Route::apiResource('/citas', CitaController::class);
    Route::get('/citas/calendar/data', [CitaController::class, 'getCalendarData']);
    Route::put('/citas/{id}/status', [CitaController::class, 'changeStatus']);

    Route::apiResource('/esfuerzo',EsfuerzoController::class);
    Route::apiResource('/estratificacion',EstratificacionController::class);
    Route::apiResource('/clinico',ClinicoController::class);
    Route::apiResource('/reporte',ReporteFinalController::class);
    Route::apiResource('/psico',ReportePsicoController::class);
    Route::apiResource('/nutri',ReporteNutriController::class);
    Route::get('/esfuerzo/imprimir/{id}',[PDFController::class, 'esfuerzoPdf']);
    Route::get('/estratificacion/imprimir/{id}',[PDFController::class,'estratificacionPdf']);
    Route::get('/clinico/imprimir/{id}',[PDFController::class,'clinicoPdf']);
    Route::get('/reporte/imprimir/{id}',[PDFController::class,'reportePdf']);
    Route::get('/psico/imprimir/{id}',[PDFController::class,'psicoPdf']);
    Route::get('/nutri/imprimir/{id}',[PDFController::class,'nutriPdf']);
    Route::post('/expediente/send-email', [PDFController::class, 'sendExpedienteByEmail']);
    Route::get('/clinicos/{id}',[InfoController::class,'clinicos']);
    Route::get('/esfuerzos/{id}',[InfoController::class,'esfuerzos']);
    Route::get('/estratificaciones/{id}',[InfoController::class,'estratificaciones']);
    Route::get('/reportes/{id}',[InfoController::class,'reportes']);
    Route::get('/nutricional/{id}',[InfoController::class,'nutricional']);
    Route::get('/psicologico/{id}',[InfoController::class,'psicologico']);
    
    // Ruta para verificar permisos específicos de expedientes
    Route::get('/pacientes/{id}/expedientes/{expedienteType}/{expedienteId}/permissions', [InfoController::class, 'checkExpedientePermissions']);
    
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
});

// Rutas para gestión de usuarios y permisos (solo administradores)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/users', [UserManagementController::class, 'createUser']);
    Route::get('/users', [UserManagementController::class, 'listUsers']);
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

Route::post('/registro', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);