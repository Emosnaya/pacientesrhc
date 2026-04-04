<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_ginecologica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(33);

            // Datos de la consulta
            $table->date('fecha_consulta');
            $table->time('hora')->nullable();
            $table->string('motivo_consulta', 255)->nullable();

            // Antecedentes Ginecológicos (JSON)
            $table->json('antecedentes_ginecologicos')->nullable();
            // { menarca, ritmo_menstrual, duracion_menstruacion, fum, ciclos_regulares,
            //   dismenorrea, vida_sexual_activa, edad_ivsa, num_parejas_sexuales,
            //   metodo_anticonceptivo, fecha_ultimo_pap, resultado_pap,
            //   fecha_ultima_mamografia, resultado_mamografia }

            // Antecedentes Obstétricos (JSON)
            $table->json('antecedentes_obstetricos')->nullable();
            // { gestas, partos, cesareas, abortos, ectopicos, molas,
            //   fecha_ultimo_parto, tipo_ultimo_parto, complicaciones_previas }

            // Signos Vitales (JSON)
            $table->json('signos_vitales')->nullable();
            // { peso, talla, imc, presion_arterial, frecuencia_cardiaca, temperatura }

            // Exploración Física (JSON)
            $table->json('exploracion_fisica')->nullable();
            // { mamas: {inspeccion, palpacion, axila, hallazgos},
            //   abdomen: {inspeccion, palpacion, hallazgos},
            //   genitales_externos: {inspeccion, hallazgos},
            //   especuloscopia: {cuello, vagina, secrecion, hallazgos},
            //   tacto_vaginal: {utero_tamano, utero_posicion, anexos, fondos_saco, hallazgos} }

            // Estudios Solicitados (JSON)
            $table->json('estudios_solicitados')->nullable();
            // { laboratorio: [], gabinete: [], otros: [] }

            // Diagnósticos (JSON)
            $table->json('diagnosticos')->nullable();
            // [ { cie10, descripcion, tipo: 'principal|secundario' } ]

            // Tratamiento (JSON)
            $table->json('tratamiento')->nullable();
            // { medicamentos: [{ nombre, dosis, via, frecuencia, duracion }],
            //   indicaciones_generales, fecha_proxima_cita }

            // Campos de texto
            $table->text('padecimiento_actual')->nullable();
            $table->text('notas_adicionales')->nullable();
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
        Schema::dropIfExists('historia_ginecologica');
    }
};
