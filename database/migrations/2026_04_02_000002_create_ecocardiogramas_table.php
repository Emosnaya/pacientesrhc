<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecocardiogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedTinyInteger('tipo_exp')->default(31);

            // Datos del estudio
            $table->date('fecha_estudio');
            $table->time('hora')->nullable();
            $table->string('tipo_estudio', 50)->nullable(); // Transtorácico, Transesofágico, Estrés
            $table->string('indicacion', 100)->nullable();
            $table->string('calidad_imagen', 30)->nullable();

            // Ventrículo Izquierdo (JSON)
            $table->json('ventriculo_izquierdo')->nullable();
            // { diametro_diastolico, diametro_sistolico, volumen_diastolico, volumen_sistolico,
            //   septum, pared_posterior, masa, indice_masa, grosor_relativo,
            //   fevi, fevi_metodo, fraccion_acortamiento, gls }

            // Motilidad regional (JSON)
            $table->json('motilidad_regional')->nullable();
            // { descripcion, hipoquinesia: {tiene, segmentos}, aquinesia: {...}, disquinesia: {...} }

            // Función diastólica (JSON)
            $table->json('funcion_diastolica')->nullable();
            // { e_mitral, a_mitral, relacion_ea, e_prima_septal, e_prima_lateral,
            //   relacion_e_e_prima, tiempo_desaceleracion, triv, patron, grado_disfuncion }

            // Ventrículo Derecho (JSON)
            $table->json('ventriculo_derecho')->nullable();
            // { diametro_basal, diametro_medio, longitud, tapse, onda_s_tricuspide, fac, funcion }

            // Aurículas (JSON)
            $table->json('auriculas')->nullable();
            // { ai: {diametro, area, volumen, volumen_indexado, dilatacion}, ad: {area, dilatacion} }

            // Válvula Mitral (JSON)
            $table->json('valvula_mitral')->nullable();
            // { morfologia, area, gradiente_medio, gradiente_pico, estenosis: {tiene, grado},
            //   insuficiencia: {tiene, grado, mecanismo, vena_contracta, ore, volumen_regurgitante},
            //   prolapso: {tiene, valva} }

            // Válvula Aórtica (JSON)
            $table->json('valvula_aortica')->nullable();
            // { morfologia, area, velocidad_pico, gradiente_pico, gradiente_medio,
            //   estenosis: {tiene, grado}, insuficiencia: {tiene, grado, vena_contracta, tiempo_hemipresion} }

            // Válvula Tricúspide (JSON)
            $table->json('valvula_tricuspide')->nullable();
            // { morfologia, insuficiencia: {tiene, grado, velocidad_pico}, psap, presion_ad_estimada }

            // Válvula Pulmonar (JSON)
            $table->json('valvula_pulmonar')->nullable();
            // { morfologia, insuficiencia: {tiene, grado}, velocidad_pico, gradiente }

            // Aorta (JSON)
            $table->json('aorta')->nullable();
            // { raiz, ascendente, arco, descendente, aneurisma, coartacion, diseccion }

            // Pericardio (JSON)
            $table->json('pericardio')->nullable();
            // { aspecto, derrame: {tiene, cantidad, localizacion}, taponamiento, constriccion }

            // Hallazgos adicionales (JSON)
            $table->json('hallazgos_adicionales')->nullable();
            // { trombo: {tiene, localizacion}, masa: {tiene, descripcion}, 
            //   fop, cia, civ, otros }

            // Conclusiones
            $table->text('conclusiones')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->string('medico_realiza', 100)->nullable();
            $table->string('cedula_medico', 20)->nullable();

            $table->timestamps();

            $table->index(['paciente_id', 'fecha_estudio']);
            $table->index(['clinica_id', 'fecha_estudio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecocardiogramas');
    }
};
