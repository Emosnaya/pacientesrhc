<?php

namespace App\Support;

use App\Models\Electrocardiograma;

class ElectrocardiogramaPdfHelper
{
    public static function label(array $map, mixed $value, string $fallback = '—'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        $key = is_string($value) ? $value : (string) $value;

        return $map[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public static function siNo(?bool $value): string
    {
        return $value ? 'Sí' : 'No';
    }

    public static function imagenPath(Electrocardiograma $ecg): ?string
    {
        return $ecg->imagen_path ?: ($ecg->getAttributes()['imagen_ecg'] ?? null);
    }

    public static function maps(): array
    {
        return [
            'tipo_ritmo' => [
                'sinusal' => 'Sinusal',
                'auricular' => 'Auricular',
                'nodal' => 'Nodal',
                'ventricular' => 'Ventricular',
                'fa' => 'Fibrilación auricular',
                'flutter' => 'Flutter auricular',
                'marcapasos' => 'Marcapasos',
            ],
            'conduccion_av' => [
                'normal' => 'Normal (1:1)',
                'bav1' => 'BAV 1er grado',
                'bav2_1' => 'BAV 2° grado Mobitz I',
                'bav2_2' => 'BAV 2° grado Mobitz II',
                'bav3' => 'BAV 3er grado',
            ],
            'formula_qtc' => [
                'bazett' => 'Bazett',
                'fridericia' => 'Fridericia',
                'framingham' => 'Framingham',
            ],
            'desviacion' => [
                'izquierda' => 'Desviación izquierda',
                'derecha' => 'Desviación derecha',
                'extrema' => 'Desviación extrema',
            ],
            'frecuencia_es' => [
                'sencillas' => 'Sencillas',
                'frecuentes' => 'Frecuentes',
                'pareadas' => 'Pareadas',
                'dupletas' => 'Dupletas',
                'tripletas' => 'Tripletas',
            ],
            'tv_tipo' => [
                'monomorfica_no_sostenida' => 'Monomórfica no sostenida',
                'monomorfica_sostenida' => 'Monomórfica sostenida',
                'helicoidal' => 'Helicoidal',
            ],
            'marcapasos_tipo' => [
                'unicameral' => 'Unicameral',
                'bicameral' => 'Bicameral',
                'biventriculular' => 'Biventricular (TRC)',
                'dai' => 'DAI',
                'crt' => 'CRT',
                'crt_dai' => 'CRT/DAI',
                'estimulador_rama_izquierda' => 'Estimulador de rama izquierda',
            ],
            'marcapasos_captura' => [
                'adecuada' => 'Adecuada',
                'falla_captura' => 'Falla de captura',
            ],
            'marcapasos_sensado' => [
                'adecuado' => 'Adecuado',
                'subdeteccion' => 'Subdetección',
                'sobredeteccion' => 'Sobredetección',
            ],
            'bloqueo_grado' => [
                'completo' => 'Completo',
                'incompleto' => 'Incompleto',
            ],
        ];
    }
}
