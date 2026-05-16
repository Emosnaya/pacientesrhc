<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_clinica_nutricion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->onDelete('set null');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->integer('tipo_exp')->default(36);

            // ── Datos generales ──────────────────────────────────────────────
            $table->date('fecha_elaboracion');
            $table->string('numero_expediente')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('tutor_nombre')->nullable();   // padre/tutor si menor
            $table->text('motivo_consulta')->nullable();

            // ── Antecedentes heredofamiliares (JSON) ─────────────────────────
            // { diabetes, hipertension, cancer, obesidad, cardiopatias,
            //   dislipidemias, enf_renal, enf_hepatica, endocrino_metabolicas,
            //   otras, observaciones }
            // Cada clave: { si: bool, no: bool, parentesco: string }
            $table->json('antecedentes_heredofamiliares')->nullable();

            // ── Antecedentes personales patológicos ──────────────────────────
            $table->text('antecedentes_personales_patologicos')->nullable();
            $table->text('alergias_intolerancias')->nullable();
            $table->string('horas_sueno')->nullable();

            // ── Consumo de sustancias bioactivas (JSON) ──────────────────────
            // { alcohol, tabaco, bebidas_cafeinadas, drogas }
            // Cada clave: { presencia: bool, tipo, frecuencia, cantidad }
            $table->json('sustancias_bioactivas')->nullable();

            // ── Antecedentes gineco-obstétricos (JSON) ───────────────────────
            // { menarca, ritmo, eumenorrea, dismenorrea, mpf,
            //   gesta, para, abortos, cesareas, fum, observaciones }
            $table->json('antecedentes_gineco_obstetricos')->nullable();

            // ── Padecimiento actual y terapéutica (JSON array) ───────────────
            // [ { padecimiento, terapeutica }, ... ]  (A, B, C, D)
            $table->json('padecimiento_terapeutica')->nullable();

            // Uso de medicamentos (checkboxes)
            $table->json('uso_medicamentos')->nullable();
            // { laxantes, diureticos, antiacidos, analgesicos, antihistaminicos,
            //   medicamentos_bajar_peso, hipoglucemiantes, hipolipemiantes }

            // ── Valoración cardiovascular (JSON array) ───────────────────────
            // [ { fecha, ta, fc_p, fc_p1, fc_p2, indice_ruffier, diagnostico } ]
            $table->json('valoracion_cardiovascular')->nullable();

            // ── Indicadores antropométricos ──────────────────────────────────
            $table->string('peso_habitual')->nullable();
            $table->string('peso_maximo')->nullable();
            $table->string('peso_minimo')->nullable();
            $table->date('fecha_evaluacion_antrop')->nullable();
            $table->string('edad_anos')->nullable();
            $table->string('peso_actual')->nullable();
            $table->string('talla_cm')->nullable();

            // ── Pliegues cutáneos mm (JSON) ──────────────────────────────────
            $table->json('pliegues_cutaneos')->nullable();
            // { pectoral, triceps, subescapular, biceps, supracrestal,
            //   supraespinal, abdominal, muslo, pantorrilla }

            // ── Perímetros cm (JSON) ─────────────────────────────────────────
            $table->json('perimetros')->nullable();
            // { cabeza, cuello, brazo_relajado, brazo_contraccion, antebrazo,
            //   muneca_minimo, torax_mesoesternal, cintura_minimo, cadera_maximo,
            //   muslo_medio, pantorrilla_maximo, tobillo_minimo }

            // ── Diámetros cm (JSON) ──────────────────────────────────────────
            $table->json('diametros')->nullable();
            // { biacromial, torax_anteroposterior, torax_transverso,
            //   biliocrestoideo, humero_biepicondilo, femur_biepicondilo,
            //   muneca_biestiloideo }

            // ── Longitudes cm (JSON) ─────────────────────────────────────────
            $table->json('longitudes')->nullable();
            // { radio_estiloideo, trocanter_tibial, tibial_lateral }

            // ── Índices (JSON) ───────────────────────────────────────────────
            $table->json('indices')->nullable();
            // { somatotipo, imc, diagnostico_imc, densidad_corporal,
            //   masa_muscular_kerr, masa_muscular_matiegka,
            //   masa_residual, masa_osea, grasa_actual_siri,
            //   grasa_optimo, diferencia_porcentaje_grasa,
            //   diferencia_kg_grasa, peso_ideal, objetivo_antropometrico }

            // ── Actividad física o deporte (JSON array) ──────────────────────
            // [ { tipo, antiguedad, frecuencia, horario, duracion,
            //     intensidad, costo_energetico_actividad, costo_energetico_total } ]
            $table->json('actividad_fisica')->nullable();
            $table->string('total_minutos_semana')->nullable();
            $table->string('costo_energetico_total_act')->nullable();
            $table->boolean('cumple_acsm')->nullable();
            $table->json('no_cumple_acsm')->nullable();
            // { tiempo: bool, frecuencia: bool, intensidad: bool }

            // ── Recomendación actividad física (JSON array) ──────────────────
            // [ { tipo, fcmax_karvonen, zona_fc_min, zona_fc_max,
            //     frecuencia, duracion, intensidad,
            //     costo_energetico_actividad, costo_energetico_total } ]
            $table->json('recomendacion_actividad_fisica')->nullable();
            $table->string('total_minutos_semana_rec')->nullable();
            $table->string('costo_energetico_total_rec')->nullable();
            $table->text('observaciones_actividad')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_clinica_nutricion');
    }
};
