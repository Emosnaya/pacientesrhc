<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electrocardiogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(32);

            // Datos del estudio
            $table->date('fecha_estudio');
            $table->time('hora')->nullable();
            $table->string('indicacion', 100)->nullable();
            $table->string('contexto_clinico', 50)->nullable();

            // Datos técnicos
            $table->string('velocidad_papel', 10)->default('25');
            $table->string('calibracion', 10)->default('10');

            // Ritmo y frecuencia (JSON)
            $table->json('ritmo_frecuencia')->nullable();
            // { ritmo, fc, regularidad, origen }

            // Intervalos (JSON)
            $table->json('intervalos')->nullable();
            // { pr, qrs, qt, qtc, formula_qtc }

            // Eje eléctrico (JSON)
            $table->json('eje_electrico')->nullable();
            // { eje_qrs, eje_p, eje_t }

            // Onda P (JSON)
            $table->json('onda_p')->nullable();
            // { morfologia, duracion, amplitud, crecimiento_ai, crecimiento_ad }

            // Complejo QRS (JSON)
            $table->json('complejo_qrs')->nullable();
            // { morfologia, duracion, amplitud_max, bajo_voltaje,
            //   bloqueo_rama: {tiene, tipo}, hemibloqueo: {tiene, tipo},
            //   hipertrofia_vi: {tiene, criterios}, hipertrofia_vd: {tiene, criterios},
            //   ondas_q: {tiene, localizacion, patologicas} }

            // Segmento ST (JSON)
            $table->json('segmento_st')->nullable();
            // { normal, elevacion: {tiene, derivaciones, magnitud}, 
            //   depresion: {tiene, derivaciones, magnitud, tipo} }

            // Onda T (JSON)
            $table->json('onda_t')->nullable();
            // { morfologia, inversiones: {tiene, derivaciones}, aplanamiento, picudas }

            // Arritmias (JSON)
            $table->json('arritmias')->nullable();
            // { extrasistoles_sv, extrasistoles_v, fa, flutter, taquicardia_sv,
            //   taquicardia_v, bradicardia, bloqueo_av: {tiene, grado}, pausa_sinusal }

            // Marcapasos (JSON)
            $table->json('marcapasos')->nullable();
            // { presente, tipo_estimulacion, espigas_visibles, captura, sensado }

            // Imagen del ECG
            $table->string('imagen_path')->nullable();

            // Conclusiones
            $table->text('interpretacion')->nullable();
            $table->text('conclusiones')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->boolean('urgente')->default(false);
            $table->boolean('comparado_previo')->default(false);
            $table->text('cambios_vs_previo')->nullable();
            $table->string('medico_interpreta', 100)->nullable();
            $table->string('cedula_medico', 20)->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_estudio']);
            $table->index(['clinica_id', 'fecha_estudio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electrocardiogramas');
    }
};
