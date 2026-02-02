<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nota_seguimiento_pulmonar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('set null');
            $table->foreignId('expediente_pulmonar_id')->nullable()->constrained('expediente_pulmonars')->onDelete('set null');

            $table->date('fecha_consulta');
            $table->time('hora_consulta');

            $table->text('ficha_identificacion')->nullable()->comment('Ficha de identificación del paciente');
            $table->text('diagnosticos')->nullable();
            $table->text('s_subjetivo')->nullable()->comment('S - Subjetivo');
            $table->text('o_objetivo')->nullable()->comment('O - Objetivo');
            $table->text('a_apreciacion')->nullable()->comment('A - Apreciación');
            $table->text('p_plan')->nullable()->comment('P - Plan');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nota_seguimiento_pulmonar');
    }
};
