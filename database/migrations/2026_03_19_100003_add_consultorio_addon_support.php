<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            // Número de consultorios adicionales pagados
            $table->integer('consultorios_adicionales')->default(0)->after('max_sucursales');
        });

        Schema::table('users', function (Blueprint $table) {
            // Agregar índice para mejorar queries de consultorios
            $table->index('clinica_activa_id');
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn('consultorios_adicionales');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['clinica_activa_id']);
        });
    }
};
