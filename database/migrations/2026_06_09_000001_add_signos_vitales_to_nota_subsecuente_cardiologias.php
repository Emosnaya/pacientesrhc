<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_subsecuente_cardiologias', function (Blueprint $table) {
            $table->string('ta_sistolica', 10)->nullable()->after('exploracion_fisica');
            $table->string('ta_diastolica', 10)->nullable()->after('ta_sistolica');
            $table->string('fc', 10)->nullable()->after('ta_diastolica');
            $table->string('fr', 10)->nullable()->after('fc');
            $table->string('spo2', 10)->nullable()->after('fr');
            $table->string('temperatura', 10)->nullable()->after('spo2');
            $table->string('peso', 10)->nullable()->after('temperatura');
            $table->string('talla', 10)->nullable()->after('peso');
            $table->string('imc', 10)->nullable()->after('talla');
            $table->string('perimetro_abdominal', 10)->nullable()->after('imc');
        });
    }

    public function down(): void
    {
        Schema::table('nota_subsecuente_cardiologias', function (Blueprint $table) {
            $table->dropColumn([
                'ta_sistolica', 'ta_diastolica', 'fc', 'fr', 'spo2',
                'temperatura', 'peso', 'talla', 'imc', 'perimetro_abdominal',
            ]);
        });
    }
};
