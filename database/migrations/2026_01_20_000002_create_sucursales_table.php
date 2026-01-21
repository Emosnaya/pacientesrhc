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
        
        // Agregar sucursal_id a tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        // Agregar sucursal_id a tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        // Agregar sucursal_id a tabla citas
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        // Agregar sucursal_id a expedientes cardiología
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
        
        // Agregar sucursal_id a expedientes pulmonares
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
        
        // Agregar sucursal_id a expedientes fisioterapia
        Schema::table('historia_clinica_fisioterapias', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('nota_evolucion_fisioterapias', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('nota_alta_fisioterapias', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        // Agregar sucursal_id a otros expedientes
        Schema::table('reporte_fisios', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_psicolos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('reporte_nutris', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
        
        Schema::table('cualidad_fisicas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->onDelete('set null');
            $table->index('sucursal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar foreign keys primero - Expedientes
        Schema::table('cualidad_fisicas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_nutris', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_psicolos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('reporte_fisios', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('nota_alta_fisioterapias', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('nota_evolucion_fisioterapias', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        Schema::table('historia_clinica_fisioterapias', function (Blueprint $table) {
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
