<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // historia_clinica_dental
        Schema::table('historia_clinica_dental', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('sucursal_id')
                  ->constrained('clinicas')->onDelete('cascade');
        });

        // odontogramas
        Schema::table('odontogramas', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('sucursal_id')
                  ->constrained('clinicas')->onDelete('cascade');
        });

        // Rellenar clinica_id a partir de la sucursal para registros existentes
        DB::statement('
            UPDATE historia_clinica_dental h
            JOIN sucursales s ON s.id = h.sucursal_id
            SET h.clinica_id = s.clinica_id
            WHERE h.clinica_id IS NULL
        ');

        DB::statement('
            UPDATE odontogramas o
            JOIN sucursales s ON s.id = o.sucursal_id
            SET o.clinica_id = s.clinica_id
            WHERE o.clinica_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('historia_clinica_dental', function (Blueprint $table) {
            $table->dropForeign(['clinica_id']);
            $table->dropColumn('clinica_id');
        });

        Schema::table('odontogramas', function (Blueprint $table) {
            $table->dropForeign(['clinica_id']);
            $table->dropColumn('clinica_id');
        });
    }
};
