<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\CompensationProfile;
use App\Models\Liquidacion;
use App\Models\LiquidacionItem;
use App\Models\LiquidacionItemPago;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LiquidacionController extends Controller
{
    private function assertDentalAdmin(): ?JsonResponse
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_efectiva_id);
        if (! $clinica || $clinica->tipo_clinica !== 'dental') {
            return response()->json(['message' => 'Liquidaciones solo disponibles en clínicas dentales'], 403);
        }
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            return response()->json(['message' => 'Solo administradores pueden gestionar liquidaciones'], 403);
        }

        return null;
    }

    public function indexProfiles(): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }

        $profiles = CompensationProfile::with(['user:id,nombre,apellidoPat,apellidoMat,email,rol'])
            ->where('clinica_id', Auth::user()->clinica_efectiva_id)
            ->orderByDesc('activo')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $profiles]);
    }

    public function storeProfile(Request $request): JsonResponse
    {
        return $this->upsertProfile($request);
    }

    public function updateProfile(Request $request, int $id): JsonResponse
    {
        return $this->upsertProfile($request, $id);
    }

    private function upsertProfile(Request $request, ?int $id = null): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $user = Auth::user();
        $validated = Validator::make($request->all(), [
            'user_id' => ($id ? 'sometimes|' : 'required|').'exists:users,id',
            'sueldo_fijo' => 'nullable|numeric|min:0|max:9999999',
            'comision_pct' => 'required|numeric|min:0|max:100',
            'activo' => 'sometimes|boolean',
            'notas' => 'nullable|string|max:1000',
        ])->validate();

        if ($id) {
            $profile = CompensationProfile::where('clinica_id', $user->clinica_efectiva_id)->findOrFail($id);
            $profile->update([
                'sueldo_fijo' => $validated['sueldo_fijo'] ?? null,
                'comision_pct' => $validated['comision_pct'],
                'activo' => $validated['activo'] ?? $profile->activo,
                'notas' => $validated['notas'] ?? $profile->notas,
            ]);
        } else {
            $staff = User::findOrFail($validated['user_id']);
            if ((int) $staff->clinica_id !== (int) $user->clinica_efectiva_id
                && ! $staff->clinicas()->where('clinicas.id', $user->clinica_efectiva_id)->exists()) {
                return response()->json(['message' => 'El usuario no pertenece a esta clínica'], 422);
            }

            $profile = CompensationProfile::updateOrCreate(
                [
                    'clinica_id' => $user->clinica_efectiva_id,
                    'user_id' => $validated['user_id'],
                ],
                [
                    'sueldo_fijo' => $validated['sueldo_fijo'] ?? null,
                    'comision_pct' => $validated['comision_pct'],
                    'activo' => $validated['activo'] ?? true,
                    'notas' => $validated['notas'] ?? null,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $profile->fresh()->load('user:id,nombre,apellidoPat,apellidoMat,email,rol'),
        ]);
    }

    public function destroyProfile(int $id): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $profile = CompensationProfile::where('clinica_id', Auth::user()->clinica_efectiva_id)->findOrFail($id);
        $profile->update(['activo' => false]);

        return response()->json(['success' => true, 'message' => 'Perfil desactivado']);
    }

    public function index(): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }

        $list = Liquidacion::with([
            'items.user:id,nombre,apellidoPat,apellidoMat',
            'generadoPor:id,nombre,apellidoPat',
        ])
            ->where('clinica_id', Auth::user()->clinica_efectiva_id)
            ->orderByDesc('periodo_fin')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(function (Liquidacion $liq) {
                $liq->setAttribute('total_general', round($liq->items->sum('total'), 2));

                return $liq;
            });

        return response()->json(['data' => $list]);
    }

    public function show(int $id): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }

        $liq = Liquidacion::with([
            'items.user:id,nombre,apellidoPat,apellidoMat,email',
            'generadoPor:id,nombre,apellidoPat',
        ])->where('clinica_id', Auth::user()->clinica_efectiva_id)->findOrFail($id);

        $liq->setAttribute('total_general', round($liq->items->sum('total'), 2));

        return response()->json(['data' => $liq]);
    }

    public function preview(Request $request): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $validated = Validator::make($request->all(), [
            'periodo_inicio' => 'required|date',
            'periodo_fin' => 'required|date|after_or_equal:periodo_inicio',
        ])->validate();

        $calc = $this->calcular(
            (int) Auth::user()->clinica_efectiva_id,
            $validated['periodo_inicio'],
            $validated['periodo_fin']
        );

        return response()->json([
            'data' => [
                'periodo_inicio' => $validated['periodo_inicio'],
                'periodo_fin' => $validated['periodo_fin'],
                'items' => $calc['items'],
                'total_general' => $calc['total_general'],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $user = Auth::user();
        $validated = Validator::make($request->all(), [
            'periodo_inicio' => 'required|date',
            'periodo_fin' => 'required|date|after_or_equal:periodo_inicio',
            'notas' => 'nullable|string|max:1000',
        ])->validate();

        $calc = $this->calcular(
            (int) $user->clinica_efectiva_id,
            $validated['periodo_inicio'],
            $validated['periodo_fin']
        );

        if (empty($calc['items'])) {
            return response()->json([
                'message' => 'No hay perfiles activos con sueldo o pagos atribuibles en el periodo',
            ], 422);
        }

        $liquidacion = DB::transaction(function () use ($user, $validated, $calc) {
            $liq = Liquidacion::create([
                'clinica_id' => $user->clinica_efectiva_id,
                'sucursal_id' => $user->sucursal_id,
                'periodo_inicio' => $validated['periodo_inicio'],
                'periodo_fin' => $validated['periodo_fin'],
                'estado' => 'calculada',
                'generado_por' => $user->id,
                'notas' => $validated['notas'] ?? null,
            ]);

            foreach ($calc['items'] as $row) {
                $pagoLinks = $row['_pagos'] ?? [];
                unset($row['_pagos'], $row['user']);
                $item = LiquidacionItem::create([
                    'liquidacion_id' => $liq->id,
                    ...$row,
                ]);
                foreach ($pagoLinks as $link) {
                    LiquidacionItemPago::create([
                        'liquidacion_item_id' => $item->id,
                        'pago_id' => $link['pago_id'],
                        'monto_atribuido' => $link['monto'],
                    ]);
                }
            }

            return $liq->load(['items.user:id,nombre,apellidoPat,apellidoMat']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Liquidación generada',
            'data' => $liquidacion,
        ], 201);
    }

    public function recalcular(int $id): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $user = Auth::user();
        $liq = Liquidacion::where('clinica_id', $user->clinica_efectiva_id)->findOrFail($id);
        if (! $liq->isEditable()) {
            return response()->json(['message' => 'Solo se pueden recalcular liquidaciones abiertas'], 422);
        }

        $calc = $this->calcular(
            (int) $user->clinica_efectiva_id,
            $liq->periodo_inicio->format('Y-m-d'),
            $liq->periodo_fin->format('Y-m-d'),
            $liq->id
        );

        DB::transaction(function () use ($liq, $calc) {
            foreach ($liq->items as $old) {
                LiquidacionItemPago::where('liquidacion_item_id', $old->id)->delete();
            }
            $liq->items()->delete();

            foreach ($calc['items'] as $row) {
                $pagoLinks = $row['_pagos'] ?? [];
                unset($row['_pagos'], $row['user']);
                $item = LiquidacionItem::create([
                    'liquidacion_id' => $liq->id,
                    ...$row,
                ]);
                foreach ($pagoLinks as $link) {
                    LiquidacionItemPago::create([
                        'liquidacion_item_id' => $item->id,
                        'pago_id' => $link['pago_id'],
                        'monto_atribuido' => $link['monto'],
                    ]);
                }
            }
            $liq->update(['estado' => 'calculada']);
        });

        return response()->json([
            'success' => true,
            'data' => $liq->fresh()->load(['items.user:id,nombre,apellidoPat,apellidoMat']),
        ]);
    }

    public function marcarPagada(int $id): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $liq = Liquidacion::where('clinica_id', Auth::user()->clinica_efectiva_id)->findOrFail($id);
        if ($liq->estado === 'pagada') {
            return response()->json(['message' => 'Ya está marcada como pagada'], 422);
        }
        if ($liq->estado === 'cancelada') {
            return response()->json(['message' => 'No se puede pagar una liquidación cancelada'], 422);
        }
        $liq->update(['estado' => 'pagada', 'pagado_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $liq->fresh()->load(['items.user:id,nombre,apellidoPat,apellidoMat']),
        ]);
    }

    public function cancelar(int $id): JsonResponse
    {
        if ($deny = $this->assertDentalAdmin()) {
            return $deny;
        }
        $liq = Liquidacion::where('clinica_id', Auth::user()->clinica_efectiva_id)->findOrFail($id);
        if ($liq->estado === 'pagada') {
            return response()->json(['message' => 'No se puede cancelar una liquidación pagada'], 422);
        }
        DB::transaction(function () use ($liq) {
            foreach ($liq->items as $item) {
                LiquidacionItemPago::where('liquidacion_item_id', $item->id)->delete();
            }
            $liq->update(['estado' => 'cancelada']);
        });

        return response()->json([
            'success' => true,
            'data' => $liq->fresh()->load(['items.user:id,nombre,apellidoPat,apellidoMat']),
        ]);
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total_general: float}
     */
    private function calcular(int $clinicaId, string $inicio, string $fin, ?int $excludeLiquidacionId = null): array
    {
        $profiles = CompensationProfile::with('user:id,nombre,apellidoPat,apellidoMat')
            ->where('clinica_id', $clinicaId)
            ->where('activo', true)
            ->get();

        if ($profiles->isEmpty()) {
            return ['items' => [], 'total_general' => 0.0];
        }

        // Pagos ya usados en otras liquidaciones no canceladas
        $usedPagoIds = LiquidacionItemPago::query()
            ->whereHas('item.liquidacion', function ($q) use ($clinicaId, $excludeLiquidacionId) {
                $q->where('clinica_id', $clinicaId)
                    ->where('estado', '!=', 'cancelada');
                if ($excludeLiquidacionId) {
                    $q->where('id', '!=', $excludeLiquidacionId);
                }
            })
            ->pluck('pago_id')
            ->all();

        $pagos = Pago::with(['paciente:id,user_id,nombre,apellidoPat'])
            ->where('clinica_id', $clinicaId)
            ->betweenDates($inicio, $fin)
            ->when(! empty($usedPagoIds), fn ($q) => $q->whereNotIn('id', $usedPagoIds))
            ->get();

        $items = [];
        foreach ($profiles as $profile) {
            $links = [];
            $base = 0.0;
            foreach ($pagos as $pago) {
                $atribuido = $pago->atribuido_a_user_id
                    ? (int) $pago->atribuido_a_user_id
                    : (int) ($pago->paciente?->user_id ?? 0);
                if ($atribuido !== (int) $profile->user_id) {
                    continue;
                }
                $monto = (float) $pago->monto;
                $base += $monto;
                $links[] = ['pago_id' => $pago->id, 'monto' => round($monto, 2)];
            }

            $pct = (float) $profile->comision_pct;
            $sueldo = (float) ($profile->sueldo_fijo ?? 0);
            $comision = round($base * ($pct / 100), 2);
            $total = round($sueldo + $comision, 2);

            if ($sueldo <= 0 && $base <= 0) {
                continue;
            }

            $items[] = [
                'user_id' => $profile->user_id,
                'compensation_profile_id' => $profile->id,
                'sueldo_fijo' => $sueldo,
                'base_comisionable' => round($base, 2),
                'comision_pct' => $pct,
                'monto_comision' => $comision,
                'total' => $total,
                'cantidad_pagos' => count($links),
                'detalle_json' => ['pago_ids' => array_column($links, 'pago_id')],
                '_pagos' => $links,
                'user' => $profile->user,
            ];
        }

        usort($items, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [
            'items' => $items,
            'total_general' => round(collect($items)->sum('total'), 2),
        ];
    }
}
