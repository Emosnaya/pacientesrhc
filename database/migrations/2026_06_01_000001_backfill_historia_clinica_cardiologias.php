<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill JSON fields to include new keys if missing
        DB::table('historia_clinica_cardiologias')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $updated = false;

                $acv = json_decode($row->antecedentes_cardiovasculares ?? '{}', true) ?: [];
                // Ensure new keys exist
                if (!array_key_exists('cirugias', $acv)) { $acv['cirugias'] = []; $updated = true; }
                if (!array_key_exists('transfusiones', $acv)) { $acv['transfusiones'] = ['tiene' => false, 'detalle' => '']; $updated = true; }
                if (!array_key_exists('enfermedades_respiratorias', $acv)) { $acv['enfermedades_respiratorias'] = ['tiene' => false, 'detalle' => '']; $updated = true; }
                if (!array_key_exists('gastrointestinales', $acv)) { $acv['gastrointestinales'] = ['tiene' => false, 'detalle' => '']; $updated = true; }
                if (!array_key_exists('enfermedades_renales', $acv)) { $acv['enfermedades_renales'] = ['tiene' => false, 'detalle' => '']; $updated = true; }
                if (!array_key_exists('traumatismos_accidentes', $acv)) { $acv['traumatismos_accidentes'] = ['tiene' => false, 'detalle' => '']; $updated = true; }

                $est = json_decode($row->estudios_previos ?? '{}', true) ?: [];
                if (!array_key_exists('radiografia_torax', $est)) { $est['radiografia_torax'] = ''; $updated = true; }
                if (!array_key_exists('perfusion_miocardica', $est)) { $est['perfusion_miocardica'] = ''; $updated = true; }
                if (!array_key_exists('medicina_nuclear', $est)) { $est['medicina_nuclear'] = ''; $updated = true; }
                if (!array_key_exists('angiotac_coronarias', $est)) { $est['angiotac_coronarias'] = ''; $updated = true; }

                $lab = json_decode($row->laboratorios ?? '{}', true) ?: [];
                if (!array_key_exists('bun', $lab)) { $lab['bun'] = ''; $updated = true; }
                if (!array_key_exists('acido_urico', $lab)) { $lab['acido_urico'] = ''; $updated = true; }
                if (!array_key_exists('perfil_tiroideo', $lab)) { $lab['perfil_tiroideo'] = ['tsh' => '', 't3' => '', 't4' => '']; $updated = true; }
                if (!array_key_exists('electrolitos', $lab)) { $lab['electrolitos'] = ['cloro' => '', 'potasio' => '', 'magnesio' => '']; $updated = true; }

                if ($updated) {
                    DB::table('historia_clinica_cardiologias')->where('id', $row->id)->update([
                        'antecedentes_cardiovasculares' => json_encode($acv, JSON_UNESCAPED_UNICODE),
                        'estudios_previos' => json_encode($est, JSON_UNESCAPED_UNICODE),
                        'laboratorios' => json_encode($lab, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // no-op: do not remove data keys on rollback
    }
};
