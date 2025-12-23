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
    try {
        $info = [
            'status' => 'ok',
            'public_path' => public_path(),
            'storage_path' => storage_path(),
        ];
        
        // Verificar symlink
        $publicStoragePath = public_path('storage');
        $info['public_storage'] = [
            'path' => $publicStoragePath,
            'exists' => file_exists($publicStoragePath),
            'is_link' => @is_link($publicStoragePath),
            'target' => @is_link($publicStoragePath) ? @readlink($publicStoragePath) : null,
        ];
        
        // Verificar carpeta de logos
        $logosPath = storage_path('app/public/clinicas/logos');
        $info['logos_folder'] = [
            'path' => $logosPath,
            'exists' => file_exists($logosPath),
            'is_dir' => is_dir($logosPath),
            'is_readable' => @is_readable($logosPath),
        ];
        
        // Listar archivos si la carpeta existe
        if (file_exists($logosPath) && is_dir($logosPath)) {
            $files = @scandir($logosPath);
            if ($files) {
                $info['logos_files'] = array_values(array_filter($files, function($f) {
                    return $f !== '.' && $f !== '..';
                }));
            }
        }
        
        $info['config'] = [
            'app_url' => config('app.url'),
            'filesystem_disk' => config('filesystems.default'),
        ];
        
        return response()->json($info);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});

// Rutas para Expediente Pulmonar
Route::resource('expediente-pulmonar', \App\Http\Controllers\ExpedientePulmonarController::class);

// Rutas para Clínicas
Route::resource('clinicas', \App\Http\Controllers\ClinicaController::class);
Route::get('clinicas/{id}/subscription', [\App\Http\Controllers\ClinicaController::class, 'checkSubscription']);
Route::post('clinicas/{id}/renew', [\App\Http\Controllers\ClinicaController::class, 'renewSubscription']);
