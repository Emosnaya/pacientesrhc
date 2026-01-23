<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Evento::query();

            // Filtrar por clínica del usuario autenticado
            $query->forClinica($user->clinica_id);
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
            
            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            // Filtros
            if ($request->has('fecha')) {
                $query->byDate($request->fecha);
            }

            if ($request->has('mes') && $request->has('año')) {
                $query->byMonth($request->mes, $request->año);
            }

            if ($request->has('tipo')) {
                $query->byTipo($request->tipo);
            }

            $eventos = $query->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $eventos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los eventos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener eventos agrupados por fecha para el calendario
     */
    public function getCalendarData(Request $request)
    {
        try {
            $user = Auth::user();
            $mes = $request->get('mes', now()->month);
            $año = $request->get('año', now()->year);

            $query = Evento::forClinica($user->clinica_id)
                ->byMonth($mes, $año);
            
            // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
            // Si no viene en el request, usar la del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
            
            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            $eventos = $query->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->get();

            // Agrupar eventos por fecha para el calendario (igual que CitaController)
            $eventosPorFecha = $eventos->groupBy(function ($evento) {
                return $evento->fecha->format('Y-m-d');
            });

            return response()->json([
                'success' => true,
                'data' => $eventosPorFecha
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los eventos del calendario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Convertir hora vacía a null para evitar error de validación
            $data = $request->all();
            if (isset($data['hora']) && $data['hora'] === '') {
                $data['hora'] = null;
            }

            $validator = Validator::make($data, [
                'tipo' => 'required|in:recordatorio,tarea,evento',
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'fecha' => 'required|date',
                'hora' => 'nullable|date_format:H:i',
                'color' => 'nullable|string|max:7',
                'completado' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determinar sucursal_id: priorizar request (para super admins) o usar del usuario
            $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;

            $evento = Evento::create([
                'user_id' => $user->id,
                'clinica_id' => $user->clinica_id,
                'sucursal_id' => $sucursalId,
                'tipo' => $data['tipo'],
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'] ?? null,
                'fecha' => $data['fecha'],
                'hora' => $data['hora'],
                'color' => $data['color'] ?? $this->getDefaultColor($data['tipo']),
                'completado' => $data['completado'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Evento creado exitosamente',
                'data' => $evento
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $evento = Evento::forClinica($user->clinica_id)->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $evento
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $evento = Evento::forClinica($user->clinica_id)->findOrFail($id);

            // Convertir hora vacía a null para evitar error de validación
            $data = $request->all();
            if (isset($data['hora']) && $data['hora'] === '') {
                $data['hora'] = null;
            }

            $validator = Validator::make($data, [
                'tipo' => 'sometimes|in:recordatorio,tarea,evento',
                'titulo' => 'sometimes|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'fecha' => 'sometimes|date',
                'hora' => 'nullable|date_format:H:i',
                'color' => 'nullable|string|max:7',
                'completado' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar solo los campos presentes
            $updateData = [];
            foreach (['tipo', 'titulo', 'descripcion', 'fecha', 'hora', 'color', 'completado'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            $evento->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Evento actualizado exitosamente',
                'data' => $evento
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $evento = Evento::forClinica($user->clinica_id)->findOrFail($id);
            $evento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Evento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener color por defecto según el tipo
     */
    private function getDefaultColor($tipo)
    {
        $colores = [
            'recordatorio' => '#F59E0B', // Amarillo/Naranja
            'tarea' => '#10B981',        // Verde
            'evento' => '#3B82F6'        // Azul
        ];

        return $colores[$tipo] ?? '#3B82F6';
    }
}
