<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_obstetrica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(34);

            // Datos de la consulta
            $table->date('fecha_consulta');
            $table->time('hora')->nullable();
            $table->string('motivo_consulta', 255)->nullable();

            // Datos del Embarazo Actual (JSON)
            $table->json('embarazo_actual')->nullable();
            // { fum, fpp, semanas_gestacion, trimestre, embarazo_planeado, embarazo_deseado,
            //   control_prenatal_previo, num_controles_previos, fecha_primer_control,
            //   metodo_calculo_fpp: 'fum|eco_primer_trimestre' }

            // Antecedentes Obstétricos (JSON)
            $table->json('antecedentes_obstetricos')->nullable();
            // { gestas, partos, cesareas, abortos, ectopicos, molas, hijos_vivos, hijos_muertos,
            //   embarazos_previos: [{ año, semanas, tipo_parto, peso_rn, sexo, complicaciones }],
            //   periodo_intergenesico, fecha_ultimo_evento }

            // Antecedentes Personales (JSON)
            $table->json('antecedentes_personales')->nullable();
            // { enfermedades_cronicas: [], alergias: [], cirugias_previas: [],
            //   medicamentos_habituales: [], transfusiones, grupo_sanguineo, factor_rh }

            // Antecedentes Familiares (JSON)
            $table->json('antecedentes_familiares')->nullable();
            // { diabetes, hipertension, preeclampsia, gemelar, malformaciones,
            //   enfermedades_geneticas, otros }

            // Signos Vitales (JSON)
            $table->json('signos_vitales')->nullable();
            // { peso, talla, imc, peso_pregestacional, ganancia_peso,
            //   presion_arterial, frecuencia_cardiaca, temperatura }

            // Exploración Obstétrica (JSON)
            $table->json('exploracion_obstetrica')->nullable();
            // { altura_uterina, presentacion, situacion, posicion, frecuencia_cardiaca_fetal,
            //   movimientos_fetales, edema, varices, reflejos_osteotendinosos,
            //   mamas: { preparacion_lactancia, anomalias },
            //   cervix: { dilatacion, borramiento, consistencia, posicion, altura_presentacion } }

            // Laboratorios (JSON)
            $table->json('laboratorios')->nullable();
            // { hemoglobina, hematocrito, glucosa, urea, creatinina, acido_urico,
            //   examen_orina, urocultivo, grupo_rh, coombs_indirecto, vdrl,
            //   vih, hepatitis_b, toxoplasma, rubeola, citomegalovirus, herpes,
            //   perfil_tiroideo, otros: [] }

            // Ultrasonidos (JSON)
            $table->json('ultrasonidos')->nullable();
            // [ { fecha, semanas, peso_fetal, liquido_amniotico, placenta,
            //     longitud_femur, circunferencia_abdominal, dbp, observaciones } ]

            // Riesgo Obstétrico (JSON)
            $table->json('riesgo_obstetrico')->nullable();
            // { clasificacion: 'bajo|medio|alto', factores: [], puntuacion }

            // Plan de Manejo (JSON)
            $table->json('plan_manejo')->nullable();
            // { suplementos: [], vacunas: [], recomendaciones: [],
            //   signos_alarma: [], fecha_proxima_cita, lugar_atencion_parto }

            // Campos de texto
            $table->text('padecimiento_actual')->nullable();
            $table->text('notas_evolucion')->nullable();
            $table->text('observaciones')->nullable();

            // Médico
            $table->string('medico_nombre', 100)->nullable();
            $table->string('medico_cedula', 30)->nullable();
            $table->string('medico_especialidad', 100)->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_consulta']);
            $table->index(['clinica_id', 'fecha_consulta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_obstetrica');
    }
};
