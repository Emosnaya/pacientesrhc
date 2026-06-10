<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('historia_clinica_cardiologias')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $lab = json_decode($row->laboratorios ?? '{}', true) ?: [];
                $updated = false;

                foreach (['leucocitos', 'plaquetas', 'hematocrito'] as $key) {
                    if (!array_key_exists($key, $lab)) {
                        $lab[$key] = '';
                        $updated = true;
                    }
                }

                if (!isset($lab['electrolitos']) || !is_array($lab['electrolitos'])) {
                    $lab['electrolitos'] = ['cloro' => '', 'potasio' => '', 'magnesio' => '', 'calcio' => ''];
                    $updated = true;
                } elseif (!array_key_exists('calcio', $lab['electrolitos'])) {
                    $lab['electrolitos']['calcio'] = '';
                    $updated = true;
                }

                if (!isset($lab['perfil_tiroideo']) || !is_array($lab['perfil_tiroideo'])) {
                    $lab['perfil_tiroideo'] = ['tsh' => '', 't3' => '', 't4' => '', 't3_libre' => ''];
                    $updated = true;
                } elseif (!array_key_exists('t3_libre', $lab['perfil_tiroideo'])) {
                    $lab['perfil_tiroideo']['t3_libre'] = '';
                    $updated = true;
                }

                if ($updated) {
                    DB::table('historia_clinica_cardiologias')->where('id', $row->id)->update([
                        'laboratorios' => json_encode($lab, JSON_UNESCAPED_UNICODE),
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
