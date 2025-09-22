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
        
        // Verificar permisos básicos del paciente
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los expedientes de este paciente'], 403);
        }
        
        // Si es admin del paciente, mostrar todos los reportes
        if ($user->isAdmin() && $paciente->user_id == $user->id) {
            $expedientes = ReporteFisio::where('paciente_id', $pacienteId)->get();
            return response()->json($expedientes);
        }
        
        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', ReporteFisio::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();
        
        $expedientes = ReporteFisio::where('paciente_id', $pacienteId)
            ->whereIn('id', $accessibleReportIds)
            ->get();
        
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
        $fisio =  new ReporteFisio();
        $validated = $request->validate([
            'archivo' => 'nullable|file|mimes:pdf,docx,jpeg,png|max:2048'
        ]);

        $paciente = Paciente::Find($request['paciente_id']);

        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('expedientes/' . $paciente->apellidoPat . '_' . $paciente->apellidoMat . '_' . $paciente->id, 'public');
            $validated['archivo'] = $path;
        }

        $fisio->paciente_id = $request['paciente_id'];
        $fisio->archivo = $validated['archivo'];
        $fisio->tipo_exp = 7;
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
        
        // Los administradores solo pueden ver expedientes de sus propios pacientes
        if ($user->isAdmin() && $paciente->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para ver este expediente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver este expediente'], 403);
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
        $expediente = ReporteFisio::findOrFail($id);
        $paciente = $expediente->paciente;
        
        // Los administradores solo pueden eliminar expedientes de sus propios pacientes
        if ($user->isAdmin() && $paciente->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este expediente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_delete')) {
            return response()->json(['error' => 'No tienes permisos para eliminar este expediente'], 403);
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
