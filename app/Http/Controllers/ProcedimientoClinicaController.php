<?php

namespace App\Http\Controllers;

use App\Models\ProcedimientoClinica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcedimientoClinicaController extends Controller
{
    private function clinicaId(): int
    {
        return (int) Auth::user()->clinica_efectiva_id;
    }

    private function requireAdmin(): void
    {
        $user = Auth::user();
        if (! $user->hasAdminAccess()) {
            abort(403, 'Solo administradores pueden gestionar el catálogo de procedimientos');
        }
    }

    /**
     * GET /api/procedimientos-clinica
     * Lista del workspace activo. Staff autenticado puede leer (para pickers).
     */
    public function index(Request $request): JsonResponse
    {
        $clinicaId = $this->clinicaId();
        if (! $clinicaId) {
            return response()->json(['message' => 'No hay clínica activa'], 422);
        }

        $query = ProcedimientoClinica::forClinica($clinicaId)
            ->when($request->filled('activo'), function ($q) use ($request) {
                $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->categoria))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $term = '%'.$request->buscar.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('nombre', 'like', $term)
                        ->orWhere('codigo', 'like', $term)
                        ->orWhere('categoria', 'like', $term);
                });
            })
            ->orderBy('orden')
            ->orderBy('nombre');

        $items = $query->get();

        $categorias = ProcedimientoClinica::forClinica($clinicaId)
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return response()->json([
            'data' => $items,
            'total' => $items->count(),
            'categorias' => $categorias,
        ]);
    }

    /**
     * POST /api/procedimientos-clinica
     */
    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin();

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'categoria' => 'nullable|string|max:100',
            'codigo' => 'nullable|string|max:50',
            'precio' => 'required|numeric|min:0',
            'activo' => 'sometimes|boolean',
            'orden' => 'nullable|integer|min:0',
        ]);

        $item = ProcedimientoClinica::create([
            ...$data,
            'clinica_id' => $this->clinicaId(),
            'activo' => $data['activo'] ?? true,
            'orden' => $data['orden'] ?? 0,
        ]);

        return response()->json(['data' => $item, 'message' => 'Procedimiento creado'], 201);
    }

    /**
     * PUT /api/procedimientos-clinica/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->requireAdmin();

        $item = ProcedimientoClinica::forClinica($this->clinicaId())->findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'categoria' => 'nullable|string|max:100',
            'codigo' => 'nullable|string|max:50',
            'precio' => 'sometimes|required|numeric|min:0',
            'activo' => 'sometimes|boolean',
            'orden' => 'nullable|integer|min:0',
        ]);

        $item->update($data);

        return response()->json(['data' => $item->fresh(), 'message' => 'Procedimiento actualizado']);
    }

    /**
     * DELETE /api/procedimientos-clinica/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->requireAdmin();

        $item = ProcedimientoClinica::forClinica($this->clinicaId())->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Procedimiento eliminado']);
    }
}
