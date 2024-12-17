<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\ReporteFisio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReporteFisioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pacienteId)
    {
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
        $expediente = ReporteFisio::findOrFail($id);
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
        $expediente = ReporteFisio::findOrFail($id);

        $validated = $request->validate([
            'archivo' => 'nullable|file|mimes:pdf,docx,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('archivo')) {
            // Eliminar el archivo anterior si existe
            if ($expediente->archivo) {
                Storage::disk('public')->delete($expediente->archivo);
            }

            $path = $request->file('archivo')->store('expedientes', 'public');
            $validated['archivo'] = $path;
        }

        $expediente->update($validated);

        return response()->json($expediente);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteFisio  $reporteFisio
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $expediente = ReporteFisio::findOrFail($id);

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
