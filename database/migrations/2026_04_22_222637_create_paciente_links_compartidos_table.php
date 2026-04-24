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
        Schema::create('paciente_links_compartidos', function (Blueprint $table) {
            $table->id();            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            // tipo: 'archivo' | 'expediente'
            $table->string('tipo', 20);
            // Para archivos: paciente_archivos.id
            // Para expedientes: portal_expediente_compartidos.id
            $table->unsignedBigInteger('referencia_id');
            $table->string('referencia_nombre'); // nombre legible para mostrar
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable(); // null = no expira
            $table->unsignedSmallInteger('vistas')->default(0);
            $table->unsignedSmallInteger('max_vistas')->nullable(); // null = ilimitado
            $table->string('nota')->nullable(); // mensaje opcional al receptor
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paciente_links_compartidos');
    }
};
