<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_prenatal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(35);

            // Relación con historia obstétrica (opcional)
            $table->foreignId('historia_obstetrica_id')->nullable()->constrained('historia_obstetrica')->nullOnDelete();

            // Datos de la consulta
            $table->date('fecha_control');
            $table->time('hora')->nullable();
            $table->unsignedSmallInteger('numero_control')->default(1);
            $table->string('semanas_gestacion', 20)->nullable();
            $table->string('trimestre', 20)->nullable();

            // Signos Vitales (JSON)
            $table->json('signos_vitales')->nullable();
            // { peso, presion_arterial_sistolica, presion_arterial_diastolica,
            //   frecuencia_cardiaca, temperatura, ganancia_peso_total,
            //   ganancia_peso_desde_ultimo }

            // Exploración Obstétrica (JSON)
            $table->json('exploracion_obstetrica')->nullable();
            // { altura_uterina, frecuencia_cardiaca_fetal, movimientos_fetales,
            //   presentacion, situacion, posicion, edema, varices,
            //   actividad_uterina }

            // Laboratorios de este control (JSON)
            $table->json('laboratorios')->nullable();
            // { hemoglobina, glucosa, proteinas_orina, glucosa_orina,
            //   bacterias_orina, otros: [] }

            // Ultrasonido (JSON - si se realizó en este control)
            $table->json('ultrasonido')->nullable();
            // { realizado, semanas_eco, peso_fetal, liquido_amniotico,
            //   placenta, observaciones }

            // Vacunas aplicadas (JSON)
            $table->json('vacunas')->nullable();
            // [ { nombre, dosis, lote, fecha } ]

            // Suplementos y medicamentos (JSON)
            $table->json('medicamentos')->nullable();
            // [ { nombre, dosis, indicaciones } ]

            // Signos de alarma revisados (JSON)
            $table->json('signos_alarma_revisados')->nullable();
            // { sangrado, cefalea, vision_borrosa, edema_cara_manos, dolor_abdominal,
            //   fiebre, perdida_liquido, disminucion_movimientos, contracciones,
            //   observaciones }

            // Evaluación de riesgo (JSON)
            $table->json('evaluacion_riesgo')->nullable();
            // { clasificacion_actual, cambio_desde_ultimo, factores_nuevos: [] }

            // Plan
            $table->text('indicaciones')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_proxima_cita')->nullable();
            $table->string('lugar_proxima_cita', 100)->nullable();

            // Alertas (JSON)
            $table->json('alertas')->nullable();
            // { urgente, referencia_necesaria, motivo_referencia, hospital_referencia }

            // Médico
            $table->string('medico_nombre', 100)->nullable();
            $table->string('medico_cedula', 30)->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_control']);
            $table->index(['clinica_id', 'fecha_control']);
            $table->index(['historia_obstetrica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_prenatal');
    }
};
