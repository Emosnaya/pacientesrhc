<?php

namespace Tests\Unit;

use App\Services\PricingService;
use PHPUnit\Framework\TestCase;

class PricingServiceAnnualDiscountTest extends TestCase
{
    public function test_annual_plan_charges_ten_months_with_two_free(): void
    {
        $this->assertSame(10, PricingService::MESES_ANUALES_COBRADOS);
        $this->assertSame(2, PricingService::MESES_GRATIS_ANUAL);

        $mensual = PricingService::calcular('dental', [], 'mensual');
        $anual = PricingService::calcular('dental', [], 'anual');

        $this->assertSame(1699, $mensual['total']);
        $this->assertSame(1699 * 10, $anual['total']);
        $this->assertSame(1699 * 2, $anual['ahorro_anual']);
        $this->assertSame(2, $anual['meses_gratis']);
        $this->assertSame(1699 * 12, $anual['precio_sin_descuento']);
    }

    public function test_annual_addons_also_use_ten_months(): void
    {
        $anual = PricingService::calcular('dental', ['nutricion'], 'anual');

        // dental base 1699 + nutricion addon 299 = 1998 * 10
        $this->assertSame(1998 * 10, $anual['total']);
        $this->assertSame(1998 * 2, $anual['ahorro_anual']);
    }
}
