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
            
            // Verificar permisos básicos del paciente
            if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
                return response()->json(['error' => 'No tienes permisos para ver los expedientes de este paciente'], 403);
            }
            
            // Si es admin del paciente, mostrar todos los expedientes
            if ($user->isAdmin() && $paciente->user_id == $user->id) {
                $expedientes = ExpedientePulmonar::where('paciente_id', $pacienteId)
                    ->with(['paciente', 'user'])
                    ->orderBy('fecha_consulta', 'desc')
                    ->get();
                return response()->json($expedientes);
            }
            
            // Si no es admin, solo mostrar expedientes específicos con permisos
            $accessibleExpedienteIds = $user->permissions()
                ->where('permissionable_type', ExpedientePulmonar::class)
                ->where('can_read', true)
                ->pluck('permissionable_id')
                ->toArray();
            
            $expedientes = ExpedientePulmonar::where('paciente_id', $pacienteId)
                ->whereIn('id', $accessibleExpedienteIds)
                ->with(['paciente', 'user'])
                ->orderBy('fecha_consulta', 'desc')
                ->get();
            
            return response()->json($expedientes);
        }
        
        // Comportamiento para web (sin pacienteId)
        $expedientes = ExpedientePulmonar::with(['paciente', 'user'])
            ->whereHas('paciente', function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->where('user_id', $user->id);
                } else {
                    // Para usuarios no-admin, verificar permisos
                    $query->whereIn('id', $user->getAccessiblePacientes()->pluck('id'));
                }
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
        
        // Obtener pacientes pulmonares accesibles
        $pacientes = collect();
        
        // Obtener pacientes pulmonares
        $pacientesPulmonares = $user->getAccessiblePacientes('pulmonar');
        $pacientes = $pacientes->merge($pacientesPulmonares);
        
        // Obtener pacientes que pueden ser ambos tipos
        $pacientesAmbos = $user->getAccessiblePacientes('ambos');
        $pacientes = $pacientes->merge($pacientesAmbos);

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

        // Verificar permisos
        if (!$user->canAccessPaciente($paciente, 'can_write')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'No tienes permisos para crear expedientes para este paciente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para crear expedientes para este paciente.');
        }

        $data = $request->all();
        $data['user_id'] = $user->id;
        $data['clinica_id'] = $user->clinica_id;

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
        
        // Verificar permisos
        if (!$user->canAccessPaciente($expedientePulmonar->paciente, 'can_read')) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json(['error' => 'No tienes permisos para ver este expediente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para ver este expediente.');
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
        
        // Verificar permisos
        if (!$user->canAccessPaciente($expedientePulmonar->paciente, 'can_write')) {
            return redirect()->back()->with('error', 'No tienes permisos para editar este expediente.');
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
        
        // Verificar permisos
        if (!$user->canAccessPaciente($expedientePulmonar->paciente, 'can_write')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'No tienes permisos para editar este expediente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para editar este expediente.');
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
        
        // Si es un ID (petición API), buscar el expediente
        if (is_numeric($expedientePulmonar)) {
            $expedientePulmonar = ExpedientePulmonar::findOrFail($expedientePulmonar);
        }
        
        // Verificar permisos
        if (!$user->canAccessPaciente($expedientePulmonar->paciente, 'can_write')) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json(['error' => 'No tienes permisos para eliminar este expediente.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para eliminar este expediente.');
        }

        $expedientePulmonar->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Expediente pulmonar eliminado exitosamente.']);
        }

        return redirect()->route('expediente-pulmonar.index')
            ->with('success', 'Expediente pulmonar eliminado exitosamente.');
    }
}
