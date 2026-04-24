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
        Schema::create('paciente_archivo_compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_archivo_id')->constrained('paciente_archivos')->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade'); // destino
            $table->timestamp('compartido_at')->useCurrent();
            $table->unique(['paciente_archivo_id', 'clinica_id'], 'pac_arch_comp_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paciente_archivo_compartidos');
    }
};
