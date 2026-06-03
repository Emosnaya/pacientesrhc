<?php

namespace App\Services;

/**
 * Servicio de precios centralizado.
 * Soporta el modelo aditivo: precio_base(especialidad_primaria) + suma(addons).
 *
 * Todos los precios en MXN, IVA incluido, periodo de lanzamiento.
 */
class PricingService
{
    /**
     * Precios base (lanzamiento) por tipo de clínica/consultorio.
     * [tipo_clinica => [mensual, anual]]
     */
    public static array $BASE_LAUNCH = [
        'rehabilitacion_cardiopulmonar' => ['mensual' => 2399, 'anual' => 26990],
        'dental'       => ['mensual' => 1699, 'anual' => 17999],
        'cardiologia'  => ['mensual' => 1699, 'anual' => 17999],
        'fisioterapia' => ['mensual' => 1299, 'anual' => 13990],
        'ginecologia'  => ['mensual' => 1299, 'anual' => 13990],
        'pediatria'    => ['mensual' => 1299, 'anual' => 13990],
        'neurologia'   => ['mensual' => 1299, 'anual' => 13990],
        'neumologia'   => ['mensual' => 1299, 'anual' => 13990],
        'general'      => ['mensual' => 1299, 'anual' => 13990],
        'nutricion'    => ['mensual' =>  999, 'anual' =>  9990],
        'psicologia'   => ['mensual' =>  999, 'anual' =>  9990],
        'psiquiatria'  => ['mensual' => 1299, 'anual' => 13990],
        // Consultorio privado (usa este key internamente)
        'consultorio'  => ['mensual' => 1299, 'anual' => 11990],
    ];

    /**
     * Precios normales (post-lanzamiento) por tipo de clínica.
     */
    public static array $BASE_NORMAL = [
        'rehabilitacion_cardiopulmonar' => ['mensual' => 2899, 'anual' => 32999],
        'dental'       => ['mensual' => 1999, 'anual' => 21999],
        'cardiologia'  => ['mensual' => 1999, 'anual' => 21999],
        'fisioterapia' => ['mensual' => 1699, 'anual' => 18999],
        'ginecologia'  => ['mensual' => 1699, 'anual' => 18999],
        'pediatria'    => ['mensual' => 1699, 'anual' => 18999],
        'neurologia'   => ['mensual' => 1699, 'anual' => 18999],
        'neumologia'   => ['mensual' => 1699, 'anual' => 18999],
        'general'      => ['mensual' => 1699, 'anual' => 18999],
        'nutricion'    => ['mensual' => 1299, 'anual' => 13999],
        'psicologia'   => ['mensual' => 1299, 'anual' => 13999],
        'psiquiatria'  => ['mensual' => 1699, 'anual' => 18999],
        'consultorio'  => ['mensual' => 1699, 'anual' => 14999],
    ];

    /**
     * Precio de los módulos add-on (al agregar una especialidad secundaria).
     * [modulo_key => [mensual, anual]]
     */
    public static array $ADDON_PRICES = [
        'nutricion'                     => ['mensual' => 299,  'anual' => 2990],
        'psicologia'                    => ['mensual' => 299,  'anual' => 2990],
        'fisioterapia'                  => ['mensual' => 399,  'anual' => 3990],
        'dental'                        => ['mensual' => 599,  'anual' => 5990],
        'ginecologia'                   => ['mensual' => 399,  'anual' => 3990],
        'cardiologia'                   => ['mensual' => 499,  'anual' => 4990],
        'pediatria'                     => ['mensual' => 399,  'anual' => 3990],
        'neurologia'                    => ['mensual' => 399,  'anual' => 3990],
        'neumologia'                    => ['mensual' => 399,  'anual' => 3990],
        'psiquiatria'                   => ['mensual' => 399,  'anual' => 3990],
        'general'                       => ['mensual' => 299,  'anual' => 2990],
        'rehabilitacion_cardiopulmonar' => ['mensual' => 799,  'anual' => 7990],
    ];

    /**
     * Módulos que ya están incluidos en el precio base de cada especialidad
     * primaria (NO deben cobrarse como addon si también aparecen en modulos_habilitados).
     *
     * Para rehabilitación, los sub-módulos cardiaco/pulmonar/fisioterapia
     * están incluidos; fisioterapia como especialidad add-on también se excluye.
     */
    public static array $INCLUDED_IN_PRIMARY = [
        'rehabilitacion_cardiopulmonar' => ['rehabilitacion_cardiopulmonar', 'fisioterapia', 'cardiaco', 'pulmonar'],
        'dental'       => ['dental'],
        'cardiologia'  => ['cardiologia'],
        'fisioterapia' => ['fisioterapia'],
        'ginecologia'  => ['ginecologia'],
        'pediatria'    => ['pediatria'],
        'neurologia'   => ['neurologia'],
        'neumologia'   => ['neumologia'],
        'general'      => ['general'],
        'nutricion'    => ['nutricion'],
        'psicologia'   => ['psicologia'],
        'psiquiatria'  => ['psiquiatria'],
        'consultorio'  => [],
    ];

    /**
     * Calcula el precio total aditivo para una clínica.
     *
     * @param  string   $tipoPrimario     tipo_clinica (especialidad base)
     * @param  array    $modulosHabilitados  array de claves de módulos activos
     * @param  string   $billingCycle     'mensual' | 'anual'
     * @param  bool     $esConsultorio    si es consultorio privado (precio fijo)
     * @param  bool     $useLaunchPrices  true durante periodo de lanzamiento
     * @return array    [base, addons_total, total, items, ahorro_anual]
     */
    public static function calcular(
        string $tipoPrimario,
        array  $modulosHabilitados = [],
        string $billingCycle = 'mensual',
        bool   $esConsultorio = false,
        bool   $useLaunchPrices = true
    ): array {
        $baseTable  = $useLaunchPrices ? self::$BASE_LAUNCH  : self::$BASE_NORMAL;
        $tipoKey    = $tipoPrimario ?: 'general';
        $basePrices = $baseTable[$tipoKey] ?? $baseTable['general'];
        $base       = $basePrices[$billingCycle];

        // Módulos que ya están incluidos en el precio base
        $included = array_merge(
            self::$INCLUDED_IN_PRIMARY[$tipoKey] ?? [],
            [$tipoKey]
        );

        // Filtrar addons: solo los que NO están incluidos en el base
        $addonItems = [];
        foreach ($modulosHabilitados as $modulo) {
            if (in_array($modulo, $included, true)) continue;
            if (!isset(self::$ADDON_PRICES[$modulo]))  continue;

            $precio = self::$ADDON_PRICES[$modulo][$billingCycle];
            $addonItems[] = [
                'key'    => $modulo,
                'precio' => $precio,
            ];
        }

        $addonTotal = array_sum(array_column($addonItems, 'precio'));
        $total      = $base + $addonTotal;

        // Ahorro al pagar anual (solo relevante en mensual display)
        $baseMensual    = $baseTable[$tipoKey]['mensual'] ?? $base;
        $ahorroAnual    = ($baseMensual * 12) - ($baseTable[$tipoKey]['anual'] ?? $base * 12);

        return [
            'base'         => $base,
            'addons_total' => $addonTotal,
            'total'        => $total,
            'items'        => $addonItems,
            'ahorro_anual' => max(0, $ahorroAnual),
        ];
    }

    /**
     * Helper rápido: precio mensual total de lanzamiento.
     */
    public static function mensualLanzamiento(string $tipo, array $modulos = [], bool $esConsultorio = false): int
    {
        return self::calcular($tipo, $modulos, 'mensual', $esConsultorio)['total'];
    }

    /**
     * Helper rápido: precio anual total de lanzamiento.
     */
    public static function anualLanzamiento(string $tipo, array $modulos = [], bool $esConsultorio = false): int
    {
        return self::calcular($tipo, $modulos, 'anual', $esConsultorio)['total'];
    }

    /**
     * Precio de un consultorio adicional (~50 % del plan base de la especialidad).
     */
    public static function precioConsultorioAdicional(string $tipoPrimario, string $billingCycle = 'mensual'): int
    {
        $base = self::$BASE_LAUNCH[$tipoPrimario] ?? self::$BASE_LAUNCH['general'];
        $mes = (int) round($base['mensual'] * 0.5);
        $anio = (int) round($base['anual'] * 0.5);

        return $billingCycle === 'anual' ? $anio : $mes;
    }

    /**
     * Etiqueta legible de la especialidad.
     */
    public static function labelEspecialidad(?string $tipo): string
    {
        $tipos = config('clinica_tipos.tipos', []);

        return $tipos[$tipo]['nombre'] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
    }

    /**
     * Catálogo de precios por especialidad para nuevos consultorios privados.
     * No depende del workspace activo del usuario.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function catalogoConsultorioEspecialidades(): array
    {
        $catalogo = [];
        $tiposOrden = array_keys(self::$BASE_LAUNCH);

        foreach ($tiposOrden as $tipo) {
            if ($tipo === 'consultorio') {
                continue;
            }

            $mes = self::calcular($tipo, [], 'mensual');
            $anio = self::calcular($tipo, [], 'anual');
            $normalMes = self::$BASE_NORMAL[$tipo]['mensual'] ?? self::$BASE_NORMAL['general']['mensual'];
            $normalAnual = self::$BASE_NORMAL[$tipo]['anual'] ?? self::$BASE_NORMAL['general']['anual'];
            $adicionalMes = self::precioConsultorioAdicional($tipo, 'mensual');
            $adicionalAnual = self::precioConsultorioAdicional($tipo, 'anual');

            $catalogo[] = [
                'tipo_clinica' => $tipo,
                'especialidad_label' => self::labelEspecialidad($tipo),
                'precio_mensual' => $mes['total'],
                'precio_anual' => $anio['total'],
                'precio_normal_mensual' => $normalMes,
                'precio_normal_anual' => $normalAnual,
                'ahorro_anual' => max(0, ($mes['total'] * 12) - $anio['total']),
                'precio_adicional_mensual' => $adicionalMes,
                'precio_adicional_anual' => $adicionalAnual,
                'ahorro_adicional_anual' => max(0, ($adicionalMes * 12) - $adicionalAnual),
            ];
        }

        return $catalogo;
    }

    /**
     * Entrada del catálogo por tipo, o null.
     */
    public static function catalogoItem(?string $tipo): ?array
    {
        $tipo = $tipo && isset(self::$BASE_LAUNCH[$tipo]) ? $tipo : 'general';

        foreach (self::catalogoConsultorioEspecialidades() as $item) {
            if ($item['tipo_clinica'] === $tipo) {
                return $item;
            }
        }

        return null;
    }
}
