<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClinicoController;
use App\Http\Controllers\EsfuerzoController;
use App\Http\Controllers\EstratificacionController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ReporteFinalController;
use App\Http\Controllers\ReporteFisioController;
use App\Http\Controllers\ReporteNutriController;
use App\Http\Controllers\ReportePsicoController;
use App\Http\Controllers\UserController;
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

    // Almacenar ordenes
    Route::apiResource('/pacientes', PacienteController::class);

    Route::apiResource('/esfuerzo',EsfuerzoController::class);
    Route::apiResource('/estratificacion',EstratificacionController::class);
    Route::apiResource('/users',UserController::class);
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
    Route::get('/clinicos/{id}',[InfoController::class,'clinicos']);
    Route::get('/esfuerzos/{id}',[InfoController::class,'esfuerzos']);
    Route::get('/estratificaciones/{id}',[InfoController::class,'estratificaciones']);
    Route::get('/reportes/{id}',[InfoController::class,'reportes']);
    Route::get('/nutricional/{id}',[InfoController::class,'nutricional']);
    Route::get('/psicologico/{id}',[InfoController::class,'psicologico']);
    Route::delete('/psicolo/{id}',[InfoController::class,'destroyPsico']);
    Route::delete('/nutriolo/{id}',[InfoController::class,'destroyNutri']);
    Route::delete('/final/{id}',[InfoController::class,'destroyFinal']);
    Route::delete('/esfu/{id}',[InfoController::class,'destroyEsfuer']);
    Route::delete('/estra/{id}',[InfoController::class,'destroyEstrati']);
    Route::delete('/clini/{id}',[InfoController::class,'destroyClinico']);
    Route::get('/nutrio/{id}',[InfoController::class,'nutriIndex']);
    Route::get('/psicolog/{id}',[InfoController::class,'psicoIndex']);
    Route::prefix('fisio')->group(function () {
        Route::get('/{pacienteId}', [ReporteFisioController::class, 'index']); // Lista expedientes de un paciente
        Route::post('/', [ReporteFisioController::class, 'store']); // Crear expediente
        Route::get('/detalle/{id}', [ReporteFisioController::class, 'show']); // Mostrar detalle
        Route::put('/{id}', [ReporteFisioController::class, 'update']); // Actualizar expediente
        Route::delete('/{id}', [ReporteFisioController::class, 'destroy']);
    });
    Route::get('/fisio/{id}/imprimir', [ReporteFisioController::class, 'imprimir']);
});

Route::post('/registro', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
