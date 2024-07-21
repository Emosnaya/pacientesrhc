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
        Schema::create('reporte_finals', function (Blueprint $table) {
            $table->id();
            $table->date("fecha_inicio");
            $table->date("fecha_final");
            $table->double("fecha");
            $table->double("fc_basal");
            $table->double("doble_pr_bas");
            $table->double("fc_maxima");
            $table->double("doble_pr_max");
            $table->double("fc_borg12");
            $table->double("doble_pr_b12");
            $table->double("carga_max");
            $table->double("mets_por");
            $table->double("tiempo_ejer");
            $table->double("recup_fc");
            $table->double("umbral_isq");
            $table->double("umbral_isq_fc");
            $table->double("max_des_st");
            $table->double("indice_ta_es");
            $table->double("recup_tas");
            $table->double("resp_crono");
            $table->double("iem");
            $table->double("pod_car_eje");
            $table->double("duke");
            $table->double("veteranos");
            $table->double("score_ang");
            $table->integer("pe_1");
            $table->integer("pe_2");
            $table->integer('tipo_exp')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('reporte_finals');
    }
};
