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
        Schema::create('prueba_esfuerzo_pulmonars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            
            // Datos iniciales
            $table->date('fecha_realizacion');
            $table->text('diagnosticos')->nullable();
            $table->boolean('oxigeno_suplementario')->default(false);
            $table->decimal('oxigeno_litros', 5, 2)->nullable();
            
            // Cálculos predictivos (almacenados para referencia)
            $table->decimal('predicho_vo2', 8, 2)->nullable();
            $table->decimal('predicho_mets', 8, 2)->nullable();
            $table->integer('fcmax_100')->nullable();
            $table->integer('fcmax_65')->nullable();
            $table->integer('fcmax_75')->nullable();
            $table->integer('fcmax_85')->nullable();
            
            // BASALES
            $table->integer('basal_fc')->nullable();
            $table->integer('basal_tas')->nullable();
            $table->integer('basal_tad')->nullable();
            $table->integer('basal_saturacion')->nullable();
            $table->integer('basal_borg_disnea')->nullable();
            $table->integer('basal_borg_fatiga')->nullable();
            
            // ALCANZADOS CON LA PRUEBA MAX
            $table->integer('max_fc_pico')->nullable();
            $table->integer('max_tas')->nullable();
            $table->integer('max_tad')->nullable();
            $table->integer('max_saturacion')->nullable();
            $table->integer('max_borg_disnea')->nullable();
            $table->integer('max_borg_fatiga')->nullable();
            $table->decimal('max_velocidad_kmh', 5, 2)->nullable();
            $table->decimal('max_inclinacion', 5, 2)->nullable();
            $table->decimal('max_mets', 8, 2)->nullable();
            $table->text('motivo_detencion')->nullable();
            
            // RECUPERACIÓN 1 MIN
            $table->integer('rec1_fc')->nullable();
            $table->integer('rec1_tas')->nullable();
            $table->integer('rec1_tad')->nullable();
            $table->integer('rec1_saturacion')->nullable();
            $table->integer('rec1_borg_disnea')->nullable();
            $table->integer('rec1_borg_fatiga')->nullable();
            
            // RECUPERACIÓN 3 MIN
            $table->integer('rec3_fc')->nullable();
            $table->integer('rec3_tas')->nullable();
            $table->integer('rec3_tad')->nullable();
            $table->integer('rec3_saturacion')->nullable();
            $table->integer('rec3_borg_disnea')->nullable();
            $table->integer('rec3_borg_fatiga')->nullable();
            
            // RECUPERACIÓN 5 MIN
            $table->integer('rec5_fc')->nullable();
            $table->integer('rec5_tas')->nullable();
            $table->integer('rec5_tad')->nullable();
            $table->integer('rec5_saturacion')->nullable();
            $table->integer('rec5_borg_disnea')->nullable();
            $table->integer('rec5_borg_fatiga')->nullable();
            
            // RESULTADOS
            $table->string('etapa_maxima')->nullable();
            $table->decimal('vel_etapa', 5, 2)->nullable();
            $table->decimal('inclinacion_etapa', 5, 2)->nullable();
            $table->decimal('mets_equivalentes', 8, 2)->nullable();
            $table->decimal('vo2_equivalente', 8, 2)->nullable();
            $table->integer('fc_pico_alcanzado')->nullable();
            $table->integer('saturacion_minima')->nullable();
            $table->integer('borg_max_disnea')->nullable();
            $table->integer('borg_max_fatiga')->nullable();
            $table->decimal('porcentaje_fcmax', 5, 2)->nullable();
            $table->decimal('porcentaje_mets', 5, 2)->nullable();
            $table->decimal('porcentaje_vo2', 5, 2)->nullable();
            $table->string('clase_funcional', 1)->nullable(); // A, B, C, D, E
            
            // INTERPRETACIÓN
            $table->text('interpretacion')->nullable();
            
            // PLAN
            $table->integer('plan_sesiones')->nullable();
            $table->string('plan_tiempo')->nullable(); // Casa/Supervizado Institucional
            $table->decimal('plan_oxigeno_litros', 5, 2)->nullable();
            $table->string('plan_borg_modificado')->nullable();
            $table->integer('plan_fc_inicial_min')->nullable();
            $table->integer('plan_fc_inicial_max')->nullable();
            $table->integer('plan_fc_no_rebasar')->nullable();
            
            // Equipos
            $table->boolean('plan_banda_sin_fin')->default(false);
            $table->boolean('plan_ergometro_brazos')->default(false);
            $table->boolean('plan_bicicleta_estatica')->default(false);
            
            // Banda sin fin
            $table->decimal('plan_banda_velocidad', 5, 2)->nullable();
            $table->string('plan_banda_tiempo')->nullable();
            $table->text('plan_banda_resistencia')->nullable();
            
            // Ergómetro de brazos
            $table->string('plan_ergometro_velocidad')->nullable();
            $table->text('plan_ergometro_resistencia')->nullable();
            
            // Bicicleta estática
            $table->string('plan_bicicleta_velocidad')->nullable();
            $table->text('plan_bicicleta_resistencia')->nullable();
            
            // Manejo complementario
            $table->text('plan_manejo_complementario')->nullable();
            
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
        Schema::dropIfExists('prueba_esfuerzo_pulmonars');
    }
};
