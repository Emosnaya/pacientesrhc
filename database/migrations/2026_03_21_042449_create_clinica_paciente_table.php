<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinica_paciente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario que vinculó el paciente
            $table->timestamp('vinculado_at')->useCurrent();
            $table->timestamps();
            
            // Índices únicos para evitar duplicados
            $table->unique(['clinica_id', 'paciente_id']);
        });
        
        // Migrar datos existentes de la tabla pacientes
        DB::statement('
            INSERT INTO clinica_paciente (clinica_id, paciente_id, sucursal_id, user_id, vinculado_at, created_at, updated_at)
            SELECT clinica_id, id, sucursal_id, user_id, created_at, created_at, updated_at
            FROM pacientes
            WHERE clinica_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinica_paciente');
    }
};
