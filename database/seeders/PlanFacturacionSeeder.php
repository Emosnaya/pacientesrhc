<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanFacturacion;

class PlanFacturacionSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Plan Básico',
                'clave' => 'basico',
                'cantidad_facturas_min' => 1,
                'cantidad_facturas_max' => 100,
                'precio_mensual' => 499.00,
                'descripcion' => 'Ideal para consultorios pequeños. Incluye hasta 100 facturas al mes.',
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Pro',
                'clave' => 'pro',
                'cantidad_facturas_min' => 101,
                'cantidad_facturas_max' => 300,
                'precio_mensual' => 899.00,
                'descripcion' => 'Para clínicas medianas. Incluye hasta 300 facturas al mes con operación fiscal administrada por LynkaMed.',
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Enterprise',
                'clave' => 'enterprise',
                'cantidad_facturas_min' => 301,
                'cantidad_facturas_max' => null,
                'precio_mensual' => 0.00,
                'descripcion' => 'Para grandes redes. Facturas ilimitadas. Contacta ventas para cotización.',
                'activo' => true,
            ],
        ];

        foreach ($planes as $plan) {
            PlanFacturacion::updateOrCreate(
                ['clave' => $plan['clave']],
                $plan
            );
        }
    }
}
