<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->uuid('uuid_publico')->nullable()->unique()->after('id');
        });

        // Generar UUID para pacientes existentes
        DB::table('pacientes')->whereNull('uuid_publico')->orderBy('id')->chunk(500, function ($pacientes) {
            foreach ($pacientes as $paciente) {
                DB::table('pacientes')
                    ->where('id', $paciente->id)
                    ->update(['uuid_publico' => Str::uuid()->toString()]);
            }
        });

        // Hacer el campo NOT NULL después de poblar
        Schema::table('pacientes', function (Blueprint $table) {
            $table->uuid('uuid_publico')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn('uuid_publico');
        });
    }
};
