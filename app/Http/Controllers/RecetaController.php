<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RecetaController extends Controller
{
    /**
     * Listar recetas (todas de la sucursal o por paciente).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        $clinicaId = $user->clinica_id;

        $query = Receta::where('clinica_id', $clinicaId)
            ->with(['paciente', 'user', 'medicamentos'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        $recetas = $query->get();
        return response()->json($recetas);
    }

    /**
     * Recetas de un paciente.
     */
    public function getByPaciente(Request $request, $pacienteId)
    {
        $user = Auth::user();
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

        $query = Receta::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->with(['user', 'medicamentos'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $recetas = $query->get();
        return response()->json($recetas);
    }

    /**
     * Ver una receta.
     */
    public function show($id)
    {
        $user = Auth::user();
        $receta = Receta::where('clinica_id', $user->clinica_id)
            ->with(['paciente', 'user', 'sucursal', 'medicamentos'])
            ->findOrFail($id);
        return response()->json($receta);
    }

    /**
     * Crear receta.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'required|date',
            'diagnostico_principal' => 'nullable|string|max:500',
            'indicaciones_generales' => 'nullable|string',
            'medicamentos' => 'nullable|array',
            'medicamentos.*.medicamento' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.presentacion' => 'nullable|string|max:255',
            'medicamentos.*.dosis' => 'nullable|string|max:255',
            'medicamentos.*.frecuencia' => 'nullable|string|max:255',
            'medicamentos.*.duracion' => 'nullable|string|max:255',
            'medicamentos.*.indicaciones_especificas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        $paciente = \App\Models\Paciente::findOrFail($request->paciente_id);

        $receta = Receta::create([
            'paciente_id' => $request->paciente_id,
            'user_id' => $user->id,
            'sucursal_id' => $sucursalId,
            'clinica_id' => $user->clinica_id ?? $paciente->clinica_id,
            'fecha' => $request->fecha,
            'diagnostico_principal' => $request->diagnostico_principal,
            'indicaciones_generales' => $request->indicaciones_generales,
        ]);

        $medicamentos = $request->medicamentos ?? [];
        foreach ($medicamentos as $orden => $item) {
            if (empty($item['medicamento'])) {
                continue;
            }
            $receta->medicamentos()->create([
                'medicamento' => $item['medicamento'],
                'presentacion' => $item['presentacion'] ?? null,
                'dosis' => $item['dosis'] ?? null,
                'frecuencia' => $item['frecuencia'] ?? null,
                'duracion' => $item['duracion'] ?? null,
                'indicaciones_especificas' => $item['indicaciones_especificas'] ?? null,
                'orden' => $orden,
            ]);
        }

        $receta->load(['paciente', 'user', 'medicamentos']);
        return response()->json([
            'success' => true,
            'message' => 'Receta guardada correctamente',
            'data' => $receta
        ], 201);
    }

    /**
     * Obtener configuración del diseño PDF de recetas de la clínica.
     */
    public function getPdfConfig(Request $request)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_id);
        if (!$clinica) {
            return response()->json([
                'orden_secciones' => ['header', 'titulo', 'paciente', 'diagnostico', 'medicamentos', 'indicaciones', 'firma']
            ]);
        }
        $config = $clinica->receta_pdf_config ?? [];
        $orden = $config['orden_secciones'] ?? ['header', 'titulo', 'paciente', 'diagnostico', 'medicamentos', 'indicaciones', 'firma'];
        return response()->json([
            'orden_secciones' => $orden,
            'secciones_disponibles' => [
                ['id' => 'header', 'nombre' => 'Encabezado (logo y datos clínica)'],
                ['id' => 'titulo', 'nombre' => 'Título y fecha'],
                ['id' => 'paciente', 'nombre' => 'Datos del paciente'],
                ['id' => 'diagnostico', 'nombre' => 'Diagnóstico'],
                ['id' => 'medicamentos', 'nombre' => 'Medicamentos prescritos'],
                ['id' => 'indicaciones', 'nombre' => 'Indicaciones generales'],
                ['id' => 'firma', 'nombre' => 'Firma del médico'],
            ]
        ]);
    }

    /**
     * Actualizar configuración del diseño PDF de recetas de la clínica.
     */
    public function updatePdfConfig(Request $request)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_id);
        if (!$clinica) {
            return response()->json(['message' => 'Clínica no encontrada'], 404);
        }
        $validator = Validator::make($request->all(), [
            'orden_secciones' => 'required|array',
            'orden_secciones.*' => 'string|in:header,titulo,paciente,diagnostico,medicamentos,indicaciones,firma',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $orden = $request->orden_secciones;
        $clinica->receta_pdf_config = ['orden_secciones' => $orden];
        $clinica->save();
        return response()->json([
            'success' => true,
            'message' => 'Diseño del PDF actualizado',
            'orden_secciones' => $orden
        ]);
    }
}
