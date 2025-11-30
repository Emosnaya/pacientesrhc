<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas para Expediente Pulmonar
Route::resource('expediente-pulmonar', \App\Http\Controllers\ExpedientePulmonarController::class);

// Rutas para Clínicas
Route::resource('clinicas', \App\Http\Controllers\ClinicaController::class);
Route::get('clinicas/{id}/subscription', [\App\Http\Controllers\ClinicaController::class, 'checkSubscription']);
Route::post('clinicas/{id}/renew', [\App\Http\Controllers\ClinicaController::class, 'renewSubscription']);
