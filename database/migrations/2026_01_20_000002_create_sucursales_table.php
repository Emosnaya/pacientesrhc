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
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('codigo')->unique()->nullable(); // Código único de sucursal
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->boolean('es_principal')->default(false); // Sucursal principal/matriz
            $table->boolean('activa')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
            
            // Índices para mejorar búsquedas
            $table->index('clinica_id');
            $table->index('activa');
            $table->index(['clinica_id', 'es_principal']);
        });
        
        // Agregar sucursal_id a tablas que YA tienen clinica_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('clinicos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('esfuerzos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('estratificacions', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_finals', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('expediente_pulmonars', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('prueba_esfuerzo_pulmonars', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_final_pulmonars', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_fisios', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_psicos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_nutris', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('cualidades_fisicas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        // Agregar clinica_id y sucursal_id a tablas de fisioterapia que NO tienen clinica_id
        Schema::table('historia_clinica_fisioterapia', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('clinica_id');
            $table->index('sucursal_id');
        });
        
        Schema::table('nota_evolucion_fisioterapia', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('clinica_id');
            $table->index('sucursal_id');
        });
        
        Schema::table('nota_alta_fisioterapia', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('clinica_id');
            $table->index('sucursal_id');
        });
        
        Schema::table('ia_usage', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('clinica_id');
            $table->index('sucursal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar foreign keys y columnas - orden inverso
        Schema::table('ia_usage', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['clinica_id']);
            $table->dropColumn(['sucursal_id', 'clinica_id']);
        });
        
        Schema::table('nota_alta_fisioterapia', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['clinica_id']);
            $table->dropColumn(['sucursal_id', 'clinica_id']);
        });
        
        Schema::table('nota_evolucion_fisioterapia', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['clinica_id']);
            $table->dropColumn(['sucursal_id', 'clinica_id']);
        });
        
        Schema::table('historia_clinica_fisioterapia', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['clinica_id']);
            $table->dropColumn(['sucursal_id', 'clinica_id']);
        });
        
        Schema::table('cualidades_fisicas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_nutris', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_psicos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_fisios', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_final_pulmonars', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('prueba_esfuerzo_pulmonars', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('expediente_pulmonars', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_finals', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('estratificacions', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('esfuerzos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('clinicos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::dropIfExists('sucursales');
    }
};
