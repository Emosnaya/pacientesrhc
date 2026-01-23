<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('odontogramas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('historia_clinica_dental_id')->nullable()->constrained('historia_clinica_dental')->onDelete('cascade');
            
            $table->date('fecha')->nullable();
            
            // Estado general del odontograma (JSON con todos los dientes)
            $table->json('dientes')->nullable(); // Array de 52 dientes (numeración FDI)
            
            // Análisis Periodontal (SI/NO para cada item)
            $table->boolean('ap_calculo_supragingival')->default(false);
            $table->boolean('ap_calculo_infragingival')->default(false);
            $table->boolean('ap_movilidad_dental')->default(false);
            $table->boolean('ap_bolsas_periodontales')->default(false);
            $table->boolean('ap_pseudobolsas')->default(false);
            $table->boolean('ap_indice_placa')->default(false);
            
            // Detalles de dientes (campos de texto)
            $table->text('ap_calculo_supragingival_dientes')->nullable();
            $table->text('ap_calculo_infragingival_dientes')->nullable();
            $table->text('ap_movilidad_dental_dientes')->nullable();
            $table->text('ap_bolsas_periodontales_dientes')->nullable();
            $table->text('ap_pseudobolsas_dientes')->nullable();
            $table->text('ap_indice_placa_dientes')->nullable();
            
            // Análisis Endodóntico (SI/NO para cada item)
            $table->boolean('ae_endo_defectuosa')->default(false);
            $table->boolean('ae_necrosis_pulpar')->default(false);
            $table->boolean('ae_pulpitis_irreversible')->default(false);
            $table->boolean('ae_lesiones_periapicales')->default(false);
            
            // Detalles de dientes para endodoncia
            $table->text('ae_endo_defectuosa_dientes')->nullable();
            $table->text('ae_necrosis_pulpar_dientes')->nullable();
            $table->text('ae_pulpitis_irreversible_dientes')->nullable();
            $table->text('ae_lesiones_periapicales_dientes')->nullable();
            
            // Diagnóstico, pronóstico y observaciones
            $table->text('diagnostico')->nullable();
            $table->text('pronostico')->nullable();
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('paciente_id');
            $table->index('sucursal_id');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogramas');
    }
};
