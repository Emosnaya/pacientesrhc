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
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            // Datos de Prueba de Esfuerzo
            $table->decimal('fc_basal', 8, 2)->nullable()->after('pe_fecha');
            $table->decimal('fc_maxima', 8, 2)->nullable()->after('fc_basal');
            $table->decimal('fc_borg_12', 8, 2)->nullable()->after('fc_maxima');
            $table->decimal('dp_borg_12', 12, 2)->nullable()->after('fc_borg_12');
            $table->decimal('mets_borg_12', 8, 2)->nullable()->after('dp_borg_12');
            $table->decimal('carga_maxima', 8, 2)->nullable()->after('mets_borg_12');
            $table->decimal('tolerancia_esfuerzo', 8, 2)->nullable()->after('carga_maxima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estrati_aacvprs', function (Blueprint $table) {
            $table->dropColumn([
                'fc_basal',
                'fc_maxima',
                'fc_borg_12',
                'dp_borg_12',
                'mets_borg_12',
                'carga_maxima',
                'tolerancia_esfuerzo'
            ]);
        });
    }
};
