<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('codigo_postal');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
            $table->timestamp('geocoded_at')->nullable()->after('longitud');
            $table->string('geocode_status', 40)->nullable()->after('geocoded_at');
            $table->json('horarios_atencion')->nullable()->after('geocode_status');
            $table->boolean('visible_directorio')->default(true)->after('activa');

            $table->index(['visible_directorio', 'activa'], 'sucursales_directorio_index');
            $table->index(['latitud', 'longitud'], 'sucursales_coords_index');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropIndex('sucursales_directorio_index');
            $table->dropIndex('sucursales_coords_index');
            $table->dropColumn([
                'latitud',
                'longitud',
                'geocoded_at',
                'geocode_status',
                'horarios_atencion',
                'visible_directorio',
            ]);
        });
    }
};
