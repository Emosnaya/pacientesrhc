<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(30);

            // Datos de la consulta
            $table->date('fecha_consulta');
            $table->time('hora')->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('padecimiento_actual')->nullable();

            // Antecedentes Cardiovasculares (JSON compacto)
            $table->json('antecedentes_cardiovasculares')->nullable();
            // Estructura: { iam: {tiene: bool, detalle: str}, angina: {...}, arritmias: {...}, 
            //              ic: {tiene: bool, clase_nyha: str}, valvulopatia: {...}, cardiopatia_congenita: {...},
            //              dispositivo: {tiene: bool, tipo: str, fecha: date}, cirugia_cardiaca: {...},
            //              cateterismo: {...}, angioplastia: {...}, otros: str }

            // Factores de Riesgo Cardiovascular (JSON)
            $table->json('factores_riesgo')->nullable();
            // Estructura: { hta: {tiene: bool, tiempo: str, tratamiento: str}, dm: {tiene: bool, tipo: str, tiempo: str, tratamiento: str},
            //              dislipidemia: {...}, tabaquismo: {tiene: bool, estado: str, cigarros_dia: num, anios: num},
            //              obesidad: bool, sedentarismo: bool, estres: bool, apnea: bool, erc: {tiene: bool, estadio: str}, otros: str }

            // Antecedentes Familiares (JSON)
            $table->json('antecedentes_familiares')->nullable();
            // Estructura: { cardiopatia_isquemica: {tiene: bool, parentesco: str}, muerte_subita: {...},
            //              hta: bool, dm: bool, dislipidemia: bool, miocardiopatia: bool, otros: str }

            // Medicación
            $table->text('medicacion_cardiovascular')->nullable();
            $table->text('medicacion_otros')->nullable();
            $table->text('alergias')->nullable();

            // Síntomas Actuales (JSON)
            $table->json('sintomas')->nullable();
            // Estructura: { dolor_toracico: {tiene: bool, tipo: str, localizacion: str, irradiacion: str, duracion: str, desencadenante: str, alivio: str},
            //              disnea: {tiene: bool, clase_nyha: str}, ortopnea: bool, dpn: bool,
            //              palpitaciones: {tiene: bool, tipo: str}, sincope: {tiene: bool, detalle: str}, presincope: bool,
            //              edema: {tiene: bool, localizacion: str}, fatiga: bool, claudicacion: bool, otros: str }

            // Signos Vitales
            $table->string('ta_sistolica', 10)->nullable();
            $table->string('ta_diastolica', 10)->nullable();
            $table->string('fc', 10)->nullable();
            $table->string('fr', 10)->nullable();
            $table->string('spo2', 10)->nullable();
            $table->string('temperatura', 10)->nullable();
            $table->string('peso', 10)->nullable();
            $table->string('talla', 10)->nullable();
            $table->string('imc', 10)->nullable();
            $table->string('perimetro_abdominal', 10)->nullable();

            // Exploración Cardiovascular (JSON)
            $table->json('exploracion_cardiovascular')->nullable();
            // Estructura: { estado_general: str, cuello: str, iy_cm: str, torax: str, apex: str,
            //              ritmo: str, r1: str, r2: str, r3: bool, r4: bool,
            //              soplo: {tiene: bool, foco: str, grado: str, tipo: str, irradiacion: str},
            //              frote_pericardico: bool, auscultacion_pulmonar: str, 
            //              estertores: {tiene: bool, localizacion: str}, otros: str }

            // Pulsos Periféricos (JSON)
            $table->json('pulsos_perifericos')->nullable();
            // Estructura: { carotideo_der: str, carotideo_izq: str, radial_der: str, radial_izq: str,
            //              femoral_der: str, femoral_izq: str, popliteo_der: str, popliteo_izq: str,
            //              tibial_der: str, tibial_izq: str, pedio_der: str, pedio_izq: str,
            //              edema_mmii: {tiene: bool, grado: str}, varices: bool }

            // Estudios Previos (JSON)
            $table->json('estudios_previos')->nullable();
            // Estructura: { ecg: str, ecocardiograma: str, prueba_esfuerzo: str, holter: str,
            //              mapa: str, cateterismo: str, angiotac: str, rmn_cardiaca: str, otros: str }

            // Laboratorios (JSON)
            $table->json('laboratorios')->nullable();
            // Estructura: { glucosa: str, hba1c: str, creatinina: str, tfg: str,
            //              colesterol_total: str, ldl: str, hdl: str, trigliceridos: str,
            //              hemoglobina: str, bnp: str, troponinas: str, dimero_d: str, otros: str }

            // Diagnósticos
            $table->text('diagnostico_principal')->nullable();
            $table->string('diagnostico_cie10', 20)->nullable();
            $table->text('diagnosticos_secundarios')->nullable();
            $table->string('clasificacion_riesgo', 50)->nullable();

            // Plan de Tratamiento
            $table->text('plan_farmacologico')->nullable();
            $table->text('plan_no_farmacologico')->nullable();
            $table->text('estudios_solicitados')->nullable();
            $table->text('interconsultas')->nullable();
            $table->text('indicaciones')->nullable();
            $table->text('pronostico')->nullable();
            $table->date('proxima_cita')->nullable();
            $table->text('notas_adicionales')->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_consulta']);
            $table->index(['clinica_id', 'fecha_consulta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_clinica_cardiologias');
    }
};
