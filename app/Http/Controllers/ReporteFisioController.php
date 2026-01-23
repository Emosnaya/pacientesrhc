<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\ReporteFisio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ReporteFisioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pacienteId)
    {
        $user = Auth::user();
        $paciente = Paciente::find($pacienteId);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }
        
        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a los expedientes de este paciente'], 403);
        }
        
        $expedientes = ReporteFisio::where('paciente_id', $pacienteId)->get();
        return response()->json($expedientes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $fisio =  new ReporteFisio();
        $validated = $request->validate([
            'archivo' => 'nullable|file|mimes:pdf,docx,jpeg,png|max:2048'
        ]);

        $paciente = Paciente::Find($request['paciente_id']);
        
        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }

        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('expedientes/' . $paciente->apellidoPat . '_' . $paciente->apellidoMat . '_' . $paciente->id, 'public');
            $validated['archivo'] = $path;
        }

        $fisio->paciente_id = $request['paciente_id'];
        $fisio->archivo = $validated['archivo'];
        $fisio->tipo_exp = 7;
        // Asignar el user_id del dueño del paciente
        $fisio->clinica_id = $user->clinica_id;
        $fisio->sucursal_id = $paciente->sucursal_id;
        $fisio->save();

        return response()->json($fisio, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReporteFisio  $reporteFisio
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $expediente = ReporteFisio::findOrFail($id);
        $paciente = $expediente->paciente;
        
        // Verificar que el expediente pertenece a la misma clínica
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }
        
        return response()->json($expediente);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReporteFisio  $reporteFisio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Los reportes de fisioterapia no se pueden editar
        return response()->json(['error' => 'Los reportes de fisioterapia no se pueden editar'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteFisio  $reporteFisio
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes'], 403);
        }
        
        $expediente = ReporteFisio::findOrFail($id);
        $paciente = $expediente->paciente;
        
        // Verificar que el expediente pertenece a la misma clínica
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
        }

        // Eliminar el archivo asociado
        if ($expediente->archivo) {
            Storage::disk('public')->delete($expediente->archivo);
        }

        $expediente->delete();

        return response()->json(['message' => 'Expediente eliminado correctamente.']);
    }

    public function imprimir($id){
        $expediente = ReporteFisio::findOrFail($id);
    
        // Verifica que el archivo exista
        if (!$expediente->archivo || !Storage::disk('public')->exists($expediente->archivo)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }
    
        // Obtener el archivo desde el almacenamiento público
        $filePath = storage_path("app/public/{$expediente->archivo}");

    // Verifica si el archivo realmente existe en el sistema de archivos
            if (!file_exists($filePath)) {
        return response()->json(['message' => 'Archivo no encontrado en el sistema de archivos'], 404);
        }
    
        // Devolverlo como descarga para que el navegador lo maneje
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf'
        ]);
    }
}
