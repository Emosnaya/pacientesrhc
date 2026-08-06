<?php

namespace Tests\Unit;

use App\Support\FormValue;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class FormValueTest extends TestCase
{
    public function test_null_if_empty_converts_blank_strings(): void
    {
        $this->assertNull(FormValue::nullIfEmpty(''));
        $this->assertNull(FormValue::nullIfEmpty('   '));
        $this->assertNull(FormValue::nullIfEmpty(null));
        $this->assertSame('ok', FormValue::nullIfEmpty('ok'));
        $this->assertSame(0, FormValue::nullIfEmpty(0));
        $this->assertSame(false, FormValue::nullIfEmpty(false));
    }

    public function test_num_or_null(): void
    {
        $this->assertNull(FormValue::numOrNull(''));
        $this->assertNull(FormValue::numOrNull('abc'));
        $this->assertSame(12.5, FormValue::numOrNull('12.5'));
        $this->assertSame(10.0, FormValue::numOrNull(10));
    }

    public function test_hora_or_null_normalizes(): void
    {
        $this->assertNull(FormValue::horaOrNull(''));
        $this->assertNull(FormValue::horaOrNull('25:99'));
        $this->assertSame('09:30', FormValue::horaOrNull('9:30'));
        $this->assertSame('09:30', FormValue::horaOrNull('09:30:00'));
    }

    public function test_sanitize_empties_and_strips_meta(): void
    {
        $out = FormValue::sanitize([
            'id' => 99,
            'paciente' => ['id' => 1],
            'fecha' => '',
            'fc' => '  ',
            'hora' => '8:05:00',
            'nota' => 'texto',
            'items' => [
                ['valor' => ''],
                ['valor' => '1'],
            ],
            'obj' => ['a' => '', 'b' => 'x'],
        ]);

        $this->assertArrayNotHasKey('id', $out);
        $this->assertArrayNotHasKey('paciente', $out);
        $this->assertNull($out['fecha']);
        $this->assertNull($out['fc']);
        $this->assertSame('08:05', $out['hora']);
        $this->assertSame('texto', $out['nota']);
        $this->assertNull($out['items'][0]['valor']);
        $this->assertSame('1', $out['items'][1]['valor']);
        $this->assertNull($out['obj']['a']);
        $this->assertSame('x', $out['obj']['b']);
    }

    public function test_from_request_excepts_meta(): void
    {
        $request = Request::create('/test', 'PUT', [
            'id' => 1,
            'user' => ['id' => 2],
            'fecha' => '',
            'paciente_id' => 5,
            'peso' => '',
        ]);

        $out = FormValue::fromRequest($request);

        $this->assertArrayNotHasKey('id', $out);
        $this->assertArrayNotHasKey('user', $out);
        $this->assertSame(5, $out['paciente_id']);
        $this->assertNull($out['fecha']);
        $this->assertNull($out['peso']);
    }

    public function test_bool01(): void
    {
        $this->assertSame(1, FormValue::bool01(true));
        $this->assertSame(1, FormValue::bool01('1'));
        $this->assertSame(1, FormValue::bool01('true'));
        $this->assertSame(0, FormValue::bool01(''));
        $this->assertSame(0, FormValue::bool01(null));
        $this->assertSame(0, FormValue::bool01(false));
    }
}
