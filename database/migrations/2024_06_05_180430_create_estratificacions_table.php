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
        Schema::create('estratificacions', function (Blueprint $table) {
            $table->id();
            $table->date('primeravez_rhc')->nullable();
            $table->date('pe_fecha')->nullable();
            $table->date('estrati_fecha')->nullable();
            $table->string('diagnostico')->nullable();
            $table->string('c_isquemia')->nullable();
            $table->boolean('im')->nullable();
            $table->boolean('ima')->nullable();
            $table->boolean('imas')->nullable();
            $table->boolean('imaa')->nullable();
            $table->boolean('imal')->nullable();
            $table->boolean('imae')->nullable();
            $table->boolean('iminf')->nullable();
            $table->boolean('impi')->nullable();
            $table->boolean('impi_vd')->nullable();
            $table->boolean('imlat')->nullable();
            $table->boolean('imsesst')->nullable();
            $table->boolean('imComplicado')->nullable();
            $table->string('valvular')->nullable();
            $table->boolean('otro')->nullable();
            $table->boolean('mcd')->nullable();
            $table->boolean('icc')->nullable();
            $table->boolean('reanimacion_cardio')->nullable();
            $table->boolean('falla_entrenar')->nullable();
            $table->boolean('tabaquismo')->nullable();
            $table->boolean('dislipidemia')->nullable();
            $table->boolean('dm')->nullable();
            $table->boolean('has')->nullable();
            $table->boolean('obesidad')->nullable();
            $table->boolean('estres')->nullable();
            $table->boolean('sedentarismo')->nullable();
            $table->string('riesgo_otro')->nullable();
            $table->boolean('depresion')->nullable();
            $table->boolean('ansiedad')->nullable();
            $table->string('sintomatologia')->nullable();
            $table->double('puntuacion_atp2000')->nullable();
            $table->double('heart_score')->nullable();
            $table->double('col_total')->nullable();
            $table->double('ldl')->nullable();
            $table->double('hdl')->nullable();
            $table->double('tg')->nullable();
            $table->double('fevi')->nullable();
            $table->double('pcr')->nullable();
            $table->string('enf_coronaria')->nullable();
            $table->string('isquemia')->nullable();
            $table->string('holter')->nullable();
            $table->boolean('pe_capacidad')->nullable();
            $table->double('fc_basal')->nullable();
            $table->double('fc_maxima')->nullable();
            $table->double('fc_borg_12')->nullable();
            $table->double('dp_borg_12')->nullable();
            $table->double('mets_borg_12')->nullable();
            $table->double('carga_max_bnda')->nullable();
            $table->double('tolerancia_max_esfuerzo')->nullable();
            $table->double('respuesta_presora')->nullable();
            $table->double('indice_ta_esf')->nullable();
            $table->double('porc_fc_pre_alcanzado')->nullable();
            $table->double('r_cronotr')->nullable();
            $table->double('porder_cardiaco')->nullable();
            $table->double('recuperacion_tas')->nullable();
            $table->double('recuperacion_fc')->nullable();
            $table->double('duke')->nullable();
            $table->double('veteranos')->nullable();
            $table->boolean('ectopia_ventricular')->nullable();
            $table->string('umbral_isquemico')->nullable();
            $table->boolean('supranivel_st')->nullable();
            $table->string('infra_st_mayor2_135')->nullable();
            $table->string('infra_st_mayor2_5mets')->nullable();
            $table->string('riesgo_global')->nullable();
            $table->string('grupo')->nullable();
            $table->integer('semanas')->nullable();
            $table->integer('borg')->nullable();
            $table->string('fc_diana_str')->nullable();
            $table->double('karvonen')->nullable();
            $table->double('blackburn')->nullable();
            $table->double('narita')->nullable();
            $table->double('fc_diana')->nullable();
            $table->double('dp_diana')->nullable();
            $table->double('carga_inicial')->nullable();
            $table->string('comentarios')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->integer('tipo_exp')->nullable();
            $table->string('CSE')->nullable();
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
        Schema::dropIfExists('estratificacions');
    }
};
