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
     * Avance de pagos de un presupuesto.
     *
     * Un pago puede venir aplicado explícitamente al presupuesto (`presupuesto_id`) o ser un cobro
     * suelto de la clínica. Los cobros sueltos se reparten en cascada entre los presupuestos del
     * paciente en esa clínica, del más antiguo al más nuevo, para que un mismo pago no se cuente en
     * dos presupuestos a la vez.
     *
     * @return array{total_pagado: float, saldo_pendiente: float, porcentaje_avance: float, cantidad_pagos: int, pagos_asignados: float, pagos_generales_aplicados: float}
     */
    public function calcularAvancePagos(Presupuesto $presupuesto): array
    {
        $pagos = Pago::query()
            ->where('clinica_id', $presupuesto->clinica_id)
            ->where('paciente_id', $presupuesto->paciente_id)
            ->orderBy('fecha_pago')
            ->orderBy('id')
            ->get();

        $pagoFecha = fn (Pago $pago): ?string => $pago->fecha_pago?->toDateString()
            ?? $pago->created_at?->toDateString();

        $asignados = $pagos->where('presupuesto_id', $presupuesto->id);
        $montoAsignado = (float) $asignados->sum(fn (Pago $pago) => (float) $pago->monto);

        $asignadoPorPresupuesto = $pagos
            ->whereNotNull('presupuesto_id')
            ->groupBy('presupuesto_id')
            ->map(fn ($grupo) => (float) $grupo->sum(fn (Pago $pago) => (float) $pago->monto));

        $bolsaGenerales = $pagos
            ->whereNull('presupuesto_id')
            ->map(fn (Pago $pago) => [
                'id' => $pago->id,
                'fecha' => $pagoFecha($pago),
                'restante' => (float) $pago->monto,
            ])
            ->values()
            ->all();

        $hermanos = Presupuesto::query()
            ->where('clinica_id', $presupuesto->clinica_id)
            ->where('paciente_id', $presupuesto->paciente_id)
            ->whereNotIn('estado', ['rechazado', 'cancelado'])
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get();

        if (! $hermanos->contains('id', $presupuesto->id)) {
            $hermanos->push($presupuesto);
        }

        $montoGenerales = 0.0;
        $idsGenerales = [];

        foreach ($hermanos as $hermano) {
            $capacidad = max(0.0, (float) $hermano->monto_total - (float) ($asignadoPorPresupuesto[$hermano->id] ?? 0));
            if ($capacidad <= 0) {
                continue;
            }

            $desde = $hermano->fecha_emision?->toDateString() ?? $hermano->created_at?->toDateString();
            $esActual = (int) $hermano->id === (int) $presupuesto->id;

            foreach ($bolsaGenerales as $indice => $item) {
                if ($capacidad <= 0) {
                    break;
                }
                if ($item['restante'] <= 0) {
                    continue;
                }
                if ($desde && (! $item['fecha'] || $item['fecha'] < $desde)) {
                    continue;
                }

                $aplicado = min($item['restante'], $capacidad);
                $bolsaGenerales[$indice]['restante'] = $item['restante'] - $aplicado;
                $capacidad -= $aplicado;

                if ($esActual) {
                    $montoGenerales += $aplicado;
                    $idsGenerales[$item['id']] = true;
                }
            }
        }

        $totalPagado = $montoAsignado + $montoGenerales;
        $total = max(0.0, (float) $presupuesto->monto_total);
        $saldo = max(0.0, $total - $totalPagado);
        $porcentaje = $total > 0 ? min(100, round(($totalPagado / $total) * 100, 2)) : 0.0;

        return [
            'total_pagado' => round($totalPagado, 2),
            'saldo_pendiente' => round($saldo, 2),
            'porcentaje_avance' => $porcentaje,
            'cantidad_pagos' => $asignados->count() + count($idsGenerales),
            'pagos_asignados' => round($montoAsignado, 2),
            'pagos_generales_aplicados' => round($montoGenerales, 2),
        ];
    }
}
