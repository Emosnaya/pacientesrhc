<?php

namespace App\Http\Controllers;

use App\Models\RadiografiaDental;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RadiografiaDentalController extends Controller
{
    /**
     * Obtener todas las radiografías de un paciente
     */
    public function getByPaciente($pacienteId)
    {
        $user = Auth::user();
        
        // Verificar que el paciente pertenece a la misma clínica
        $paciente = Paciente::find($pacienteId);
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos para ver este paciente'], 403);
        }

        $radiografias = RadiografiaDental::where('paciente_id', $pacienteId)
            ->with('user:id,nombre,apellidoPat')
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($radiografias);
    }

    /**
     * Subir una nueva radiografía
     */
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_radiografia' => 'nullable|string',
            'fecha' => 'required|date',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240' // Max 10MB
        ]);

        $user = Auth::user();
        
        // Verificar que el paciente pertenece a la misma clínica
        $paciente = Paciente::find($request->paciente_id);
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }

        // Subir archivo
        $archivo = $request->file('archivo');
        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $ruta = $archivo->storeAs('radiografias_dentales', $nombreArchivo, 'public');

        // Crear registro
        $radiografia = RadiografiaDental::create([
            'paciente_id' => $request->paciente_id,
            'user_id' => $user->id,
            'clinica_id' => $user->clinica_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'ruta_archivo' => $ruta,
            'tipo_radiografia' => $request->tipo_radiografia,
            'fecha' => $request->fecha
        ]);

        return response()->json([
            'message' => 'Radiografía subida exitosamente',
            'radiografia' => $radiografia->load('user:id,nombre,apellidoPat')
        ], 201);
    }

    /**
     * Eliminar una radiografía
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $radiografia = RadiografiaDental::find($id);

        if (!$radiografia) {
            return response()->json(['error' => 'Radiografía no encontrada'], 404);
        }

        // Verificar permisos: admin o creador
        if (!$user->is_admin && $radiografia->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para eliminar esta radiografía'], 403);
        }

        // Verificar que pertenece a la misma clínica
        if ($radiografia->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }

        // Eliminar archivo del storage
        if (Storage::disk('public')->exists($radiografia->ruta_archivo)) {
            Storage::disk('public')->delete($radiografia->ruta_archivo);
        }

        // Eliminar registro
        $radiografia->delete();

        return response()->json(['message' => 'Radiografía eliminada exitosamente'], 200);
    }

    /**
     * Descargar/ver una radiografía
     */
    public function show(Request $request, $id)
    {
        // Si viene token por query string, autenticar con ese token
        if ($request->has('token')) {
            $token = $request->query('token');
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
            
            if (!$user) {
                return response()->json(['error' => 'Token inválido'], 401);
            }
        } else {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
        }

        $radiografia = RadiografiaDental::find($id);

        if (!$radiografia) {
            return response()->json(['error' => 'Radiografía no encontrada'], 404);
        }

        // Verificar que pertenece a la misma clínica
        if ($radiografia->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }

        $rutaCompleta = storage_path('app/public/' . $radiografia->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }

        return response()->file($rutaCompleta);
    }

    /**
     * Mostrar radiografía de forma pública (con token en query string)
     * Esta ruta permite abrir el archivo en el navegador
     */
    public function showPublic(Request $request, $id)
    {
        // Validar token desde query string
        if (!$request->has('token')) {
            abort(401, 'Token requerido');
        }

        $token = $request->query('token');
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            abort(401, 'Token inválido');
        }

        $user = $accessToken->tokenable;

        if (!$user) {
            abort(401, 'Usuario no encontrado');
        }

        $radiografia = RadiografiaDental::find($id);

        if (!$radiografia) {
            abort(404, 'Radiografía no encontrada');
        }

        // Verificar que pertenece a la misma clínica
        if ($radiografia->clinica_id !== $user->clinica_id) {
            abort(403, 'No tienes permisos para ver esta radiografía');
        }

        $rutaCompleta = storage_path('app/public/' . $radiografia->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            abort(404, 'Archivo no encontrado');
        }

        // Obtener el tipo MIME del archivo
        $mimeType = mime_content_type($rutaCompleta);

        // Retornar el archivo con headers apropiados para visualización
        return response()->file($rutaCompleta, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($radiografia->ruta_archivo) . '"'
        ]);
    }
}
