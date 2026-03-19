<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('calle')->nullable()->after('domicilio');
            $table->string('num_ext', 20)->nullable()->after('calle');
            $table->string('num_int', 20)->nullable()->after('num_ext');
            $table->string('colonia')->nullable()->after('num_int');
            $table->string('codigo_postal', 10)->nullable()->after('colonia');
            $table->string('ciudad', 100)->nullable()->after('codigo_postal');
            $table->string('estado_dir', 100)->nullable()->after('ciudad');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['calle', 'num_ext', 'num_int', 'colonia', 'codigo_postal', 'ciudad', 'estado_dir']);
        });
    }
};
