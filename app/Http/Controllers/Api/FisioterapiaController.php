<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaAltaFisioterapia;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FisioterapiaController extends Controller
{
    // ============================================
    // HISTORIA CLÍNICA FISIOTERAPIA
    // ============================================
    
    public function indexHistoria($pacienteId)
    {
        $historias = HistoriaClinicaFisioterapia::where('paciente_id', $pacienteId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($historias);
    }

    public function storeHistoria(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = auth()->id();

        $historia = HistoriaClinicaFisioterapia::create($data);

        return response()->json([
            'message' => 'Historia clínica creada exitosamente',
            'data' => $historia->load('user')
        ], 201);
    }

    public function showHistoria($id)
    {
        $historia = HistoriaClinicaFisioterapia::with('user', 'paciente')->findOrFail($id);
        return response()->json($historia);
    }

    public function updateHistoria(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $historia = HistoriaClinicaFisioterapia::findOrFail($id);
        $historia->update($request->all());

        return response()->json([
            'message' => 'Historia clínica actualizada exitosamente',
            'data' => $historia->load('user')
        ]);
    }

    public function destroyHistoria($id)
    {
        $historia = HistoriaClinicaFisioterapia::findOrFail($id);
        $historia->delete();

        return response()->json([
            'message' => 'Historia clínica eliminada exitosamente'
        ]);
    }

    // ============================================
    // NOTA DE EVOLUCIÓN FISIOTERAPIA
    // ============================================
    
    public function indexEvolucion($pacienteId)
    {
        $notas = NotaEvolucionFisioterapia::where('paciente_id', $pacienteId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($notas);
    }

    public function storeEvolucion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = auth()->id();

        $nota = NotaEvolucionFisioterapia::create($data);

        return response()->json([
            'message' => 'Nota de evolución creada exitosamente',
            'data' => $nota->load('user')
        ], 201);
    }

    public function showEvolucion($id)
    {
        $nota = NotaEvolucionFisioterapia::with('user', 'paciente')->findOrFail($id);
        return response()->json($nota);
    }

    public function updateEvolucion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $nota = NotaEvolucionFisioterapia::findOrFail($id);
        $nota->update($request->all());

        return response()->json([
            'message' => 'Nota de evolución actualizada exitosamente',
            'data' => $nota->load('user')
        ]);
    }

    public function destroyEvolucion($id)
    {
        $nota = NotaEvolucionFisioterapia::findOrFail($id);
        $nota->delete();

        return response()->json([
            'message' => 'Nota de evolución eliminada exitosamente'
        ]);
    }

    // ============================================
    // NOTA DE ALTA FISIOTERAPIA
    // ============================================
    
    public function indexAlta($pacienteId)
    {
        $notas = NotaAltaFisioterapia::where('paciente_id', $pacienteId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($notas);
    }

    public function storeAlta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = auth()->id();

        $nota = NotaAltaFisioterapia::create($data);

        return response()->json([
            'message' => 'Nota de alta creada exitosamente',
            'data' => $nota->load('user')
        ], 201);
    }

    public function showAlta($id)
    {
        $nota = NotaAltaFisioterapia::with('user', 'paciente')->findOrFail($id);
        return response()->json($nota);
    }

    public function updateAlta(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $nota = NotaAltaFisioterapia::findOrFail($id);
        $nota->update($request->all());

        return response()->json([
            'message' => 'Nota de alta actualizada exitosamente',
            'data' => $nota->load('user')
        ]);
    }

    public function destroyAlta($id)
    {
        $nota = NotaAltaFisioterapia::findOrFail($id);
        $nota->delete();

        return response()->json([
            'message' => 'Nota de alta eliminada exitosamente'
        ]);
    }
}
