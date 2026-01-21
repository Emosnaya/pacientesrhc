<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SucursalController extends Controller
{
    /**
     * Obtener todas las sucursales (Super Admin ve todas, usuarios normales solo de su clínica)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Sucursal::with('clinica');
        
        // Si no es super admin, filtrar por clínica del usuario
        if (!$user->isSuperAdmin) {
            $query->where('clinica_id', $user->clinica_id);
        }
        
        // Filtros opcionales
        if ($request->has('clinica_id')) {
            $query->where('clinica_id', $request->clinica_id);
        }
        
        if ($request->has('activa')) {
            $query->where('activa', $request->activa);
        }
        
        $sucursales = $query->orderBy('es_principal', 'desc')
                           ->orderBy('nombre')
                           ->get();
        
        return response()->json($sucursales);
    }

    /**
     * Obtener sucursales de una clínica específica
     */
    public function getByClinica($clinicaId)
    {
        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso a esta clínica
        if (!$user->isSuperAdmin && $user->clinica_id != $clinicaId) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $sucursales = Sucursal::where('clinica_id', $clinicaId)
                              ->activas()
                              ->orderBy('es_principal', 'desc')
                              ->orderBy('nombre')
                              ->get();
        
        return response()->json($sucursales);
    }

    /**
     * Obtener una sucursal específica
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $sucursal = Sucursal::with('clinica')->findOrFail($id);
        
        // Verificar acceso
        if (!$user->isSuperAdmin && $user->clinica_id != $sucursal->clinica_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        return response()->json($sucursal);
    }

    /**
     * Crear nueva sucursal
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'clinica_id' => 'required|exists:clinicas,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|unique:sucursales,codigo',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'ciudad' => 'nullable|string',
            'estado' => 'nullable|string',
            'codigo_postal' => 'nullable|string',
            'es_principal' => 'nullable|boolean',
            'activa' => 'nullable|boolean',
            'notas' => 'nullable|string'
        ]);
        
        // Verificar que el usuario tenga acceso
        if (!$user->isSuperAdmin && $user->clinica_id != $validated['clinica_id']) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        // Verificar si la clínica puede crear más sucursales
        $clinica = Clinica::findOrFail($validated['clinica_id']);
        if (!$clinica->puedeCrearMasSucursales()) {
            return response()->json([
                'message' => 'Esta clínica no puede crear más sucursales. El plan actual solo permite una sucursal única. Por favor, actualice su plan para agregar más sucursales.'
            ], 403);
        }
        
        // Si es la primera sucursal de la clínica, marcarla como principal
        $existeSucursal = Sucursal::where('clinica_id', $validated['clinica_id'])->exists();
        if (!$existeSucursal) {
            $validated['es_principal'] = true;
        }
        
        // Si se marca como principal, desmarcar otras
        if ($validated['es_principal'] ?? false) {
            Sucursal::where('clinica_id', $validated['clinica_id'])
                    ->update(['es_principal' => false]);
        }
        
        $sucursal = Sucursal::create($validated);
        
        return response()->json([
            'message' => 'Sucursal creada exitosamente',
            'sucursal' => $sucursal->load('clinica')
        ], 201);
    }

    /**
     * Actualizar sucursal
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $sucursal = Sucursal::findOrFail($id);
        
        // Verificar acceso
        if (!$user->isSuperAdmin && $user->clinica_id != $sucursal->clinica_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'codigo' => 'nullable|string|unique:sucursales,codigo,' . $id,
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'ciudad' => 'nullable|string',
            'estado' => 'nullable|string',
            'codigo_postal' => 'nullable|string',
            'es_principal' => 'nullable|boolean',
            'activa' => 'nullable|boolean',
            'notas' => 'nullable|string'
        ]);
        
        // Si se marca como principal, desmarcar otras
        if (($validated['es_principal'] ?? false) && !$sucursal->es_principal) {
            Sucursal::where('clinica_id', $sucursal->clinica_id)
                    ->where('id', '!=', $id)
                    ->update(['es_principal' => false]);
        }
        
        $sucursal->update($validated);
        
        return response()->json([
            'message' => 'Sucursal actualizada exitosamente',
            'sucursal' => $sucursal->load('clinica')
        ]);
    }

    /**
     * Eliminar sucursal
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $sucursal = Sucursal::findOrFail($id);
        
        // Verificar acceso
        if (!$user->isSuperAdmin && $user->clinica_id != $sucursal->clinica_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        // No permitir eliminar si es la única sucursal
        $totalSucursales = Sucursal::where('clinica_id', $sucursal->clinica_id)->count();
        if ($totalSucursales <= 1) {
            return response()->json([
                'message' => 'No se puede eliminar la única sucursal de la clínica'
            ], 422);
        }
        
        // No permitir eliminar si tiene usuarios o pacientes asignados
        if ($sucursal->usuarios()->count() > 0 || $sucursal->pacientes()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una sucursal con usuarios o pacientes asignados'
            ], 422);
        }
        
        $sucursal->delete();
        
        return response()->json(['message' => 'Sucursal eliminada exitosamente']);
    }

    /**
     * Cambiar sucursal activa del usuario
     */
    public function cambiarSucursal(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);
        
        $sucursal = Sucursal::findOrFail($validated['sucursal_id']);
        
        // Verificar que la sucursal pertenezca a la clínica del usuario
        if (!$user->isSuperAdmin && $user->clinica_id != $sucursal->clinica_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        // Actualizar sucursal del usuario
        $user->sucursal_id = $validated['sucursal_id'];
        $user->save();
        
        return response()->json([
            'message' => 'Sucursal cambiada exitosamente',
            'sucursal' => $sucursal
        ]);
    }

    /**
     * Obtener estadísticas de la sucursal
     */
    public function estadisticas($id)
    {
        $user = Auth::user();
        
        $sucursal = Sucursal::findOrFail($id);
        
        // Verificar acceso
        if (!$user->isSuperAdmin && $user->clinica_id != $sucursal->clinica_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $stats = [
            'total_usuarios' => $sucursal->usuarios()->count(),
            'total_pacientes' => $sucursal->pacientes()->count(),
            'total_citas' => $sucursal->citas()->count(),
            'citas_hoy' => $sucursal->citas()->whereDate('fecha', today())->count(),
            'citas_mes' => $sucursal->citas()->whereMonth('fecha', now()->month)->count()
        ];
        
        return response()->json($stats);
    }
}
