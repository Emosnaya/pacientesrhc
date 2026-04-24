<?php

namespace App\Http\Controllers;

use App\Models\InventarioItem;
use App\Models\InventarioMovimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // ─── helpers ───────────────────────────────────────────────────────────────

    private function clinicaId(): int
    {
        return (int) Auth::user()->clinica_efectiva_id;
    }

    private function requireAdmin(): void
    {
        $user = Auth::user();
        if (!$user->is_admin && !$user->isSuperAdmin()) {
            abort(403, 'Solo los administradores pueden realizar esta acción');
        }
    }

    // ─── ITEMS ─────────────────────────────────────────────────────────────────

    /**
     * GET /api/inventario
     * Lista todos los ítems del workspace activo con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $clinicaId = $this->clinicaId();

        $query = InventarioItem::where('clinica_id', $clinicaId)
            ->when($request->sucursal_id, fn($q) => $q->where('sucursal_id', $request->sucursal_id))
            ->when($request->categoria,   fn($q) => $q->where('categoria', $request->categoria))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->buscar, fn($q) => $q->where('nombre', 'like', "%{$request->buscar}%"))
            ->when($request->stock_bajo === 'true', fn($q) => $q->whereColumn('cantidad', '<=', 'cantidad_minima')->where('cantidad_minima', '>', 0))
            ->orderBy('nombre');

        $items = $query->get()->map(fn($i) => array_merge($i->toArray(), ['stock_bajo' => $i->stock_bajo]));

        return response()->json([
            'items'       => $items,
            'total'       => $items->count(),
            'stock_bajo'  => $items->where('stock_bajo', true)->count(),
            'categorias'  => InventarioItem::where('clinica_id', $clinicaId)
                ->whereNotNull('categoria')
                ->distinct()
                ->pluck('categoria'),
        ]);
    }

    /**
     * POST /api/inventario
     */
    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin();

        $data = $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'categoria'       => 'nullable|string|max:100',
            'unidad'          => 'nullable|string|max:50',
            'cantidad'        => 'required|numeric|min:0',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'precio_costo'    => 'nullable|numeric|min:0',
            'sucursal_id'     => 'nullable|exists:sucursales,id',
        ]);

        $item = InventarioItem::create(array_merge($data, [
            'clinica_id' => $this->clinicaId(),
            'activo'     => true,
        ]));

        // Registrar movimiento inicial si la cantidad es > 0
        if ($item->cantidad > 0) {
            InventarioMovimiento::create([
                'item_id'           => $item->id,
                'user_id'           => Auth::id(),
                'tipo'              => 'entrada',
                'cantidad'          => $item->cantidad,
                'cantidad_anterior' => 0,
                'cantidad_nueva'    => $item->cantidad,
                'motivo'            => 'Stock inicial',
            ]);
        }

        return response()->json($item->fresh(), 201);
    }

    /**
     * PUT /api/inventario/{item}
     */
    public function update(Request $request, InventarioItem $item): JsonResponse
    {
        $this->requireAdmin();
        $this->authorizeItem($item);

        $data = $request->validate([
            'nombre'          => 'sometimes|required|string|max:255',
            'descripcion'     => 'nullable|string',
            'categoria'       => 'nullable|string|max:100',
            'unidad'          => 'nullable|string|max:50',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'precio_costo'    => 'nullable|numeric|min:0',
            'activo'          => 'sometimes|boolean',
            'sucursal_id'     => 'nullable|exists:sucursales,id',
        ]);

        $item->update($data);

        return response()->json(array_merge($item->fresh()->toArray(), ['stock_bajo' => $item->fresh()->stock_bajo]));
    }

    /**
     * DELETE /api/inventario/{item}
     */
    public function destroy(InventarioItem $item): JsonResponse
    {
        $this->requireAdmin();
        $this->authorizeItem($item);
        $item->delete();
        return response()->json(['message' => 'Ítem eliminado']);
    }

    // ─── MOVIMIENTOS ───────────────────────────────────────────────────────────

    /**
     * GET /api/inventario/{item}/movimientos
     */
    public function movimientos(InventarioItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $movs = $item->movimientos()
            ->with('user:id,nombre,apellidoPat')
            ->take(50)
            ->get();

        return response()->json($movs);
    }

    /**
     * POST /api/inventario/{item}/movimiento
     * Registra entrada, salida o ajuste y actualiza la cantidad.
     */
    public function registrarMovimiento(Request $request, InventarioItem $item): JsonResponse
    {
        $this->requireAdmin();
        $this->authorizeItem($item);

        $data = $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $item) {
            $anterior = $item->cantidad;

            $nueva = match ($data['tipo']) {
                'entrada' => $anterior + $data['cantidad'],
                'salida'  => max(0, $anterior - $data['cantidad']),
                'ajuste'  => $data['cantidad'],
            };

            $item->update(['cantidad' => $nueva]);

            InventarioMovimiento::create([
                'item_id'           => $item->id,
                'user_id'           => Auth::id(),
                'tipo'              => $data['tipo'],
                'cantidad'          => $data['cantidad'],
                'cantidad_anterior' => $anterior,
                'cantidad_nueva'    => $nueva,
                'motivo'            => $data['motivo'] ?? null,
            ]);
        });

        $item->refresh();
        return response()->json(array_merge($item->toArray(), ['stock_bajo' => $item->stock_bajo]));
    }

    // ─── Private ───────────────────────────────────────────────────────────────

    private function authorizeItem(InventarioItem $item): void
    {
        if ($item->clinica_id !== $this->clinicaId()) {
            abort(403, 'Sin acceso a este ítem');
        }
    }
}
