<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reporte_psicos')) {
            Schema::table('reporte_psicos', function (Blueprint $table) {
                foreach ([
                    'motivo_consulta', 'antecedentes_personales', 'antecedentes_familiares',
                    'tratamiento_actual', 'aspectos_sociales', 'escalas_utilizadas',
                    'sintomas_actuales', 'plan_tratamiento', 'seguimiento',
                    'alchol_consumo', 'drogas_recreativas',
                ] as $col) {
                    if (Schema::hasColumn('reporte_psicos', $col)) {
                        $table->text($col)->nullable()->change();
                    }
                }
            });
        }

        if (Schema::hasTable('reporte_nutris')) {
            Schema::table('reporte_nutris', function (Blueprint $table) {
                foreach (['diagnostico', 'recomendaciones', 'recomendacion', 'observaciones'] as $col) {
                    if (Schema::hasColumn('reporte_nutris', $col)) {
                        $table->text($col)->nullable()->change();
                    }
                }
            });
        }

        if (Schema::hasTable('esfuerzos')) {
            Schema::table('esfuerzos', function (Blueprint $table) {
                foreach (['conclusiones', 'motivoSuspension'] as $col) {
                    if (Schema::hasColumn('esfuerzos', $col)) {
                        $table->text($col)->nullable()->change();
                    }
                }
            });
        }

        if (Schema::hasTable('estratificacions')) {
            Schema::table('estratificacions', function (Blueprint $table) {
                foreach (['comentarios', 'sintomatologia'] as $col) {
                    if (Schema::hasColumn('estratificacions', $col)) {
                        $table->text($col)->nullable()->change();
                    }
                }
            });
        }

        if (Schema::hasTable('historia_obstetricas')) {
            Schema::table('historia_obstetricas', function (Blueprint $table) {
                if (Schema::hasColumn('historia_obstetricas', 'motivo_consulta')) {
                    $table->text('motivo_consulta')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('historia_ginecologicas')) {
            Schema::table('historia_ginecologicas', function (Blueprint $table) {
                if (Schema::hasColumn('historia_ginecologicas', 'motivo_consulta')) {
                    $table->text('motivo_consulta')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        // No-op: widening to TEXT is safe to keep.
    }
};
