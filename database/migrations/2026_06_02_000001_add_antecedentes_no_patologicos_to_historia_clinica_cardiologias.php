<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->json('antecedentes_no_patologicos')->nullable()->after('antecedentes_gineco_obstetricos');
            // Estructura: { tabaquismo: {tiene, estado, cigarros_dia, anios},
            //              actividad_fisica: {tiene, detalle}, alcoholismo: {tiene, detalle},
            //              consumo_drogas: {tiene, detalle},
            //              obesidad: bool, sedentarismo: bool, estres: bool, apnea: bool, otros: str }
        });

        $empty = [
            'tabaquismo' => ['tiene' => false, 'estado' => '', 'cigarros_dia' => '', 'anios' => ''],
            'actividad_fisica' => ['tiene' => false, 'detalle' => ''],
            'alcoholismo' => ['tiene' => false, 'detalle' => ''],
            'consumo_drogas' => ['tiene' => false, 'detalle' => ''],
            'obesidad' => false,
            'sedentarismo' => false,
            'estres' => false,
            'apnea' => false,
            'otros' => '',
        ];

        DB::table('historia_clinica_cardiologias')->orderBy('id')->chunk(100, function ($rows) use ($empty) {
            foreach ($rows as $row) {
                $anp = json_decode($row->antecedentes_no_patologicos ?? 'null', true);
                if (is_array($anp) && !empty($anp)) {
                    continue;
                }

                $fr = json_decode($row->factores_riesgo ?? '{}', true) ?: [];
                $anp = array_merge($empty, [
                    'tabaquismo' => array_merge($empty['tabaquismo'], $fr['tabaquismo'] ?? []),
                    'obesidad' => (bool) ($fr['obesidad'] ?? false),
                    'sedentarismo' => (bool) ($fr['sedentarismo'] ?? false),
                    'estres' => (bool) ($fr['estres'] ?? false),
                    'apnea' => (bool) ($fr['apnea'] ?? false),
                    'otros' => $fr['otros'] ?? '',
                ]);

                DB::table('historia_clinica_cardiologias')->where('id', $row->id)->update([
                    'antecedentes_no_patologicos' => json_encode($anp, JSON_UNESCAPED_UNICODE),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->dropColumn('antecedentes_no_patologicos');
        });
    }
};
