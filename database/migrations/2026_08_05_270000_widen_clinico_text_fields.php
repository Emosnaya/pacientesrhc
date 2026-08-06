<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicos', function (Blueprint $table) {
            // Notas clínicas con dictado/IA suelen superar VARCHAR(255).
            $table->text('exploracion_fisica')->nullable()->change();
            $table->text('estudios')->nullable()->change();
            $table->text('diagnostico_general')->nullable()->change();
            $table->text('plan')->nullable()->change();
            $table->text('sintomas')->nullable()->change();
            $table->text('otros_ecg')->nullable()->change();
            $table->text('otros_eco')->nullable()->change();
            $table->text('tratamiento')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clinicos', function (Blueprint $table) {
            $table->string('exploracion_fisica')->nullable()->change();
            $table->string('estudios')->nullable()->change();
            $table->string('diagnostico_general')->nullable()->change();
            $table->string('plan')->nullable()->change();
            $table->string('sintomas')->nullable()->change();
            $table->string('otros_ecg')->nullable()->change();
            $table->string('otros_eco')->nullable()->change();
            $table->string('tratamiento')->nullable()->change();
        });
    }
};
