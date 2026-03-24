<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vademecum', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_generico');
            $table->string('nombre_comercial')->nullable();
            $table->string('presentacion')->nullable(); // Tabletas, Cápsulas, Jarabe, etc.
            $table->string('concentracion')->nullable(); // 500mg, 10mg/5ml, etc.
            $table->string('via_administracion')->nullable(); // Oral, IV, IM, Tópica, etc.
            $table->string('categoria')->nullable(); // Analgésico, Antibiótico, etc.
            $table->text('indicaciones')->nullable();
            $table->text('contraindicaciones')->nullable();
            $table->string('dosis_sugerida')->nullable(); // Ej: "1 tableta cada 8 hrs"
            $table->string('duracion_sugerida')->nullable(); // Ej: "7 días"
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('controlado')->default(false); // Medicamento controlado
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('nombre_generico');
            $table->index('nombre_comercial');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vademecum');
    }
};
