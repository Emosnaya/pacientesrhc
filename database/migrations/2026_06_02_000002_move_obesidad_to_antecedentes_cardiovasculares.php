<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('historia_clinica_cardiologias')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $acv = json_decode($row->antecedentes_cardiovasculares ?? '{}', true) ?: [];
                $anp = json_decode($row->antecedentes_no_patologicos ?? '{}', true) ?: [];
                $fr = json_decode($row->factores_riesgo ?? '{}', true) ?: [];
                $updated = false;

                if (!($acv['obesidad'] ?? false) && (($anp['obesidad'] ?? false) || ($fr['obesidad'] ?? false))) {
                    $acv['obesidad'] = true;
                    $updated = true;
                }

                if (array_key_exists('obesidad', $anp)) {
                    unset($anp['obesidad']);
                    $updated = true;
                }
                if (array_key_exists('apnea', $anp)) {
                    unset($anp['apnea']);
                    $updated = true;
                }

                if ($updated) {
                    DB::table('historia_clinica_cardiologias')->where('id', $row->id)->update([
                        'antecedentes_cardiovasculares' => json_encode($acv, JSON_UNESCAPED_UNICODE),
                        'antecedentes_no_patologicos' => json_encode($anp, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // no-op
    }
};
