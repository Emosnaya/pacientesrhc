<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periodontograma extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'sucursal_id',
        'user_id',
        'fecha',
        'dientes',
        'diagnostico',
        'plan_tratamiento',
        'observaciones',
        'porcentaje_bop',
        'promedio_pd',
        'piezas_pd_ge_5',
    ];

    protected $casts = [
        'fecha' => 'date',
        'dientes' => 'array',
        'porcentaje_bop' => 'integer',
        'promedio_pd' => 'float',
        'piezas_pd_ge_5' => 'integer',
        'diagnostico' => 'encrypted',
        'plan_tratamiento' => 'encrypted',
        'observaciones' => 'encrypted',
    ];

    public const SITIOS = ['DV', 'V', 'MV', 'DL', 'L', 'ML'];

    public const DIENTES_PERMANENTES = [
        18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28,
        48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38,
    ];

    public static function inicializarDientes(): array
    {
        $out = [];
        foreach (self::DIENTES_PERMANENTES as $numero) {
            $out[] = self::dienteVacio($numero);
        }

        return $out;
    }

    public static function dienteVacio(int $numero): array
    {
        return [
            'numero' => $numero,
            'ausente' => false,
            'movilidad' => 0,
            'furca' => null,
            'pd' => array_fill(0, 6, null),
            'gm' => array_fill(0, 6, null),
            'bop' => array_fill(0, 6, false),
            'placa' => array_fill(0, 6, false),
        ];
    }

    public static function calcularResumen(?array $dientes): array
    {
        $bopPositivos = 0;
        $bopTotal = 0;
        $sumaPd = 0.0;
        $countPd = 0;
        $piezasGe5 = 0;

        foreach ($dientes ?? [] as $d) {
            if (! is_array($d) || ! empty($d['ausente'])) {
                continue;
            }
            $tieneGe5 = false;
            for ($i = 0; $i < 6; $i++) {
                $bopTotal++;
                if (! empty($d['bop'][$i])) {
                    $bopPositivos++;
                }
                $pd = $d['pd'][$i] ?? null;
                if ($pd !== null && $pd !== '') {
                    $val = (float) $pd;
                    $sumaPd += $val;
                    $countPd++;
                    if ($val >= 5) {
                        $tieneGe5 = true;
                    }
                }
            }
            if ($tieneGe5) {
                $piezasGe5++;
            }
        }

        return [
            'porcentaje_bop' => $bopTotal > 0 ? (int) round(($bopPositivos / $bopTotal) * 100) : null,
            'promedio_pd' => $countPd > 0 ? round($sumaPd / $countPd, 2) : null,
            'piezas_pd_ge_5' => $piezasGe5,
        ];
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
