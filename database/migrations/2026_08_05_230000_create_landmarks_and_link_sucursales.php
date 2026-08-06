<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landmarks', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('tipo', 40)->default('hospital'); // hospital|clinica|plaza|universidad|otro
            $table->string('ciudad')->default('Ciudad de México');
            $table->string('alcaldia')->nullable();
            $table->string('estado')->default('CDMX');
            $table->string('direccion')->nullable();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(1000);
            $table->timestamps();

            $table->index(['activo', 'ciudad', 'tipo']);
            $table->index(['latitud', 'longitud']);
        });

        Schema::table('sucursales', function (Blueprint $table) {
            $table->foreignId('landmark_id')
                ->nullable()
                ->after('visible_directorio')
                ->constrained('landmarks')
                ->nullOnDelete();
            $table->string('landmark_detalle', 255)->nullable()->after('landmark_id');
            $table->boolean('coords_manuales')->default(false)->after('landmark_detalle');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landmark_id');
            $table->dropColumn(['landmark_detalle', 'coords_manuales']);
        });

        Schema::dropIfExists('landmarks');
    }
};
