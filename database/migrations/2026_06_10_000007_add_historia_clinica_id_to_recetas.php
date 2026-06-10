<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->foreignId('historia_clinica_id')
                ->nullable()
                ->after('nota_subsecuente_id')
                ->constrained('historia_clinica_cardiologias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('historia_clinica_id');
        });
    }
};
