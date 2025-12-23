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

// Ruta de diagnóstico para verificar storage (eliminar después de usar)
Route::get('/debug-storage', function () {
    $publicPath = public_path('storage');
    $storagePath = storage_path('app/public');
    $clinicasPath = storage_path('app/public/clinicas/logos');
    
    $info = [
        'public_storage_path' => $publicPath,
        'public_storage_exists' => file_exists($publicPath),
        'public_storage_is_link' => is_link($publicPath),
        'public_storage_target' => is_link($publicPath) ? readlink($publicPath) : null,
        
        'storage_path' => $storagePath,
        'storage_exists' => file_exists($storagePath),
        
        'clinicas_path' => $clinicasPath,
        'clinicas_exists' => file_exists($clinicasPath),
        'clinicas_is_readable' => is_readable($clinicasPath),
        'clinicas_permissions' => file_exists($clinicasPath) ? substr(sprintf('%o', fileperms($clinicasPath)), -4) : null,
        
        'app_url' => config('app.url'),
        'filesystem_disk' => config('filesystems.default'),
    ];
    
    // Listar archivos en clinicas/logos si existe
    if (file_exists($clinicasPath)) {
        $files = scandir($clinicasPath);
        $info['clinicas_files'] = array_filter($files, function($file) {
            return $file !== '.' && $file !== '..';
        });
    }
    
    // Verificar un archivo específico
    $testFile = '2Sef845lyZcNL1Hy1erqXPSbP5dd6edQNHfVn0z5.png';
    $testPath = storage_path('app/public/clinicas/logos/' . $testFile);
    $info['test_file'] = [
        'name' => $testFile,
        'full_path' => $testPath,
        'exists' => file_exists($testPath),
        'is_readable' => file_exists($testPath) ? is_readable($testPath) : false,
        'size' => file_exists($testPath) ? filesize($testPath) : null,
        'permissions' => file_exists($testPath) ? substr(sprintf('%o', fileperms($testPath)), -4) : null,
    ];
    
    return response()->json($info);
});

// Rutas para Expediente Pulmonar
Route::resource('expediente-pulmonar', \App\Http\Controllers\ExpedientePulmonarController::class);

// Rutas para Clínicas
Route::resource('clinicas', \App\Http\Controllers\ClinicaController::class);
Route::get('clinicas/{id}/subscription', [\App\Http\Controllers\ClinicaController::class, 'checkSubscription']);
Route::post('clinicas/{id}/renew', [\App\Http\Controllers\ClinicaController::class, 'renewSubscription']);
