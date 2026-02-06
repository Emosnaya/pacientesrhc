<?php

namespace App\Http\Controllers;

use App\Models\ExpedientePulmonar;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpedientePulmonarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pacienteId = null)
    {
        $user = Auth::user();
        
        // Si se proporciona pacienteId, es una petición API
        if ($pacienteId !== null) {
            $paciente = Paciente::find($pacienteId);
            
            if (!$paciente) {
                return response()->json(['error' => 'Paciente no encontrado'], 404);
            }
            
            // Verificar que el paciente pertenece a la misma clínica
            if ($paciente->clinica_id !== $user->clinica_id) {
                return response()->json(['error' => 'No tienes acceso a los expedientes de este paciente'], 403);
            }
            
            $expedientes = ExpedientePulmonar::where('paciente_id', $pacienteId)
                ->with(['paciente', 'user'])
                ->orderBy('fecha_consulta', 'desc')
                ->get();
            return response()->json($expedientes);
        }
        
        // Comportamiento para web (sin pacienteId)
        $expedientes = ExpedientePulmonar::with(['paciente', 'user'])
            ->whereHas('paciente', function($query) use ($user) {
                $query->where('clinica_id', $user->clinica_id);
            })
            ->orderBy('fecha_consulta', 'desc')
            ->paginate(15);

        return view('expediente-pulmonar.index', compact('expedientes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        
        // Obtener pacientes pulmonares de la clínica
        $pacientes = Paciente::where('clinica_id', $user->clinica_id)
            ->whereIn('tipo_paciente', ['pulmonar', 'ambos'])
            ->get();

        return view('expediente-pulmonar.create', compact('pacientes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_consulta' => 'required|date',
            'hora_consulta' => 'required|date_format:H:i',
        ]);

        $user = Auth::user();
        $paciente = Paciente::findOrFail($request->paciente_id);

        // Verificar que el paciente pertenece a la misma clínica
        if ($paciente->clinica_id !== $user->clinica_id) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'No tienes acceso a este paciente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes acceso a este paciente.');
        }

        $data = $request->all();
        // Asignar el user_id del dueño del paciente
        $data['user_id'] = $paciente->user_id;
        $data['clinica_id'] = $user->clinica_id;
        $data['sucursal_id'] = $user->sucursal_id;

        // Procesar campos JSON
        if ($request->has('enfermedades_cronicas')) {
            $data['enfermedades_cronicas'] = is_string($request->enfermedades_cronicas) 
                ? json_decode($request->enfermedades_cronicas, true) 
                : $request->enfermedades_cronicas;
        }
        if ($request->has('medicamentos_actuales')) {
            $data['medicamentos_actuales'] = is_string($request->medicamentos_actuales) 
                ? json_decode($request->medicamentos_actuales, true) 
                : $request->medicamentos_actuales;
        }

        $expediente = ExpedientePulmonar::create($data);
        $expediente->load(['paciente', 'user']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($expediente, 201);
        }

        return redirect()->route('expediente-pulmonar.show', $expediente)
            ->with('success', 'Expediente pulmonar creado exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpedientePulmonar|int  $expedientePulmonar
     * @return \Illuminate\Http\Response
     */
    public function show($expedientePulmonar)
    {
        $user = Auth::user();
        
        // Si es un ID (petición API), buscar el expediente
        if (is_numeric($expedientePulmonar)) {
            $expedientePulmonar = ExpedientePulmonar::findOrFail($expedientePulmonar);
        }
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $expedientePulmonar->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json(['error' => 'No tienes acceso a este expediente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes acceso a este expediente.');
        }

        $expedientePulmonar->load(['paciente', 'user']);
        
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json($expedientePulmonar);
        }
        
        return view('expediente-pulmonar.show', compact('expedientePulmonar'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpedientePulmonar  $expedientePulmonar
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpedientePulmonar $expedientePulmonar)
    {
        $user = Auth::user();
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $expedientePulmonar->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            return redirect()->back()->with('error', 'No tienes acceso a este expediente.');
        }

        $expedientePulmonar->load(['paciente']);
        
        return view('expediente-pulmonar.edit', compact('expedientePulmonar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpedientePulmonar|int  $expedientePulmonar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $expedientePulmonar)
    {
        $user = Auth::user();
        
        // Si es un ID (petición API), buscar el expediente
        if (is_numeric($expedientePulmonar)) {
            $expedientePulmonar = ExpedientePulmonar::findOrFail($expedientePulmonar);
        }
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $expedientePulmonar->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'No tienes acceso a este expediente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes acceso a este expediente.');
        }

        $request->validate([
            'fecha_consulta' => 'required|date',
            'hora_consulta' => 'required|date_format:H:i',
        ]);

        $data = $request->all();

        // Procesar campos JSON
        if ($request->has('enfermedades_cronicas')) {
            $data['enfermedades_cronicas'] = is_string($request->enfermedades_cronicas) 
                ? json_decode($request->enfermedades_cronicas, true) 
                : $request->enfermedades_cronicas;
        }
        if ($request->has('medicamentos_actuales')) {
            $data['medicamentos_actuales'] = is_string($request->medicamentos_actuales) 
                ? json_decode($request->medicamentos_actuales, true) 
                : $request->medicamentos_actuales;
        }

        $expedientePulmonar->update($data);
        $expedientePulmonar->load(['paciente', 'user']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($expedientePulmonar);
        }

        return redirect()->route('expediente-pulmonar.show', $expedientePulmonar)
            ->with('success', 'Expediente pulmonar actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpedientePulmonar|int  $expedientePulmonar
     * @return \Illuminate\Http\Response
     */
    public function destroy($expedientePulmonar)
    {
        $user = Auth::user();
        
        // Solo los administradores pueden eliminar
        if (!$user->isAdmin()) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json(['error' => 'Solo los administradores pueden eliminar expedientes.'], 403);
            }
            return redirect()->back()->with('error', 'Solo los administradores pueden eliminar expedientes.');
        }
        
        // Si es un ID (petición API), buscar el expediente
        if (is_numeric($expedientePulmonar)) {
            $expedientePulmonar = ExpedientePulmonar::findOrFail($expedientePulmonar);
        }
        
        // Verificar que el expediente pertenece a la misma clínica
        $paciente = $expedientePulmonar->paciente;
        if (!$paciente || $paciente->clinica_id !== $user->clinica_id) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json(['error' => 'No tienes acceso a este expediente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes acceso a este expediente.');
        }

        $expedientePulmonar->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Expediente pulmonar eliminado exitosamente.']);
        }

        return redirect()->route('expediente-pulmonar.index')
            ->with('success', 'Expediente pulmonar eliminado exitosamente.');
    }
}
