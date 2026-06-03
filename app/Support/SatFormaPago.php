<?php

namespace App\Support;

/**
 * Mapeo de mtodo de pago interno (caja) a c_FormaPago del SAT / Facturapi payment_form.
 */
final class SatFormaPago
{
    public const PUE = 'PUE';

    public static function desdeMetodoPago(?string $metodoPago): string
    {
        return match ($metodoPago) {
            'efectivo' => '01',
            'transferencia' => '03',
            'tarjeta_credito' => '04',
            'tarjeta_debito' => '28',
            // Pagos antiguos sin especificar: débito (evita asumir crédito en el CFDI)
            'tarjeta' => '28',
            default => '01',
        };
    }

    public static function etiqueta(?string $codigo): string
    {
        return match ($codigo) {
            '01' => 'Efectivo',
            '03' => 'Transferencia electrnica',
            '04' => 'Tarjeta de crdito',
            '28' => 'Tarjeta de dbito',
            default => 'Desconocido',
        };
    }
}
