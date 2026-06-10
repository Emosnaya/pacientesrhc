<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->foreignId('nota_subsecuente_id')
                ->nullable()
                ->after('paciente_id')
                ->constrained('nota_subsecuente_cardiologias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nota_subsecuente_id');
        });
    }
};
