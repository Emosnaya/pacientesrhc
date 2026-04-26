<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Presupuesto;

class PresupuestoService
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function syncItems(Presupuesto $presupuesto, array $items): float
    {
        $presupuesto->items()->delete();

        $total = 0.0;
        foreach ($items as $index => $item) {
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio_unitario'] ?? 0);
            $descuento = max(0.0, (float) ($item['descuento'] ?? 0));
            $subtotal = max(0.0, ($cantidad * $precio) - $descuento);
            $total += $subtotal;

            $presupuesto->items()->create([
                'concepto' => (string) ($item['concepto'] ?? ''),
                'descripcion' => $item['descripcion'] ?? null,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'descuento' => $descuento,
                'subtotal' => $subtotal,
                'orden' => $index,
            ]);
        }

        $presupuesto->monto_total = round($total, 2);
        $presupuesto->save();

        return $presupuesto->monto_total;
    }

    /**
     * @return array{total_pagado: float, saldo_pendiente: float, porcentaje_avance: float, cantidad_pagos: int}
     */
    public function calcularAvancePagos(Presupuesto $presupuesto): array
    {
        $desde = $presupuesto->fecha_emision?->toDateString() ?? $presupuesto->created_at?->toDateString();

        $pagos = Pago::query()
            ->where('clinica_id', $presupuesto->clinica_id)
            ->where('paciente_id', $presupuesto->paciente_id)
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        if ($desde) {
            $pagos = $pagos->filter(function (Pago $pago) use ($desde) {
                $fecha = $pago->fecha_pago?->toDateString() ?? $pago->created_at?->toDateString();
                return $fecha && $fecha >= $desde;
            })->values();
        }

        $totalPagado = (float) $pagos->sum(function (Pago $pago) {
            return (float) $pago->monto;
        });

        $total = max(0.0, (float) $presupuesto->monto_total);
        $saldo = max(0.0, $total - $totalPagado);
        $porcentaje = $total > 0 ? min(100, round(($totalPagado / $total) * 100, 2)) : 0.0;

        return [
            'total_pagado' => round($totalPagado, 2),
            'saldo_pendiente' => round($saldo, 2),
            'porcentaje_avance' => $porcentaje,
            'cantidad_pagos' => $pagos->count(),
        ];
    }
}
