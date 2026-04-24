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
        Schema::create('paciente_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->string('nombre_original');
            $table->string('ruta'); // storage path relativo
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamanio')->nullable(); // bytes
            $table->string('descripcion')->nullable();
            $table->boolean('subido_por_paciente')->default(false);
            $table->foreignId('subido_por_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paciente_archivos');
    }
};
