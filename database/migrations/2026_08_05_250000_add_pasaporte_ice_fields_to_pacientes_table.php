<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            if (! Schema::hasColumn('pacientes', 'grupo_sanguineo')) {
                $table->string('grupo_sanguineo', 8)->nullable()->after('alergias');
            }
            if (! Schema::hasColumn('pacientes', 'contacto_emergencia_nombre')) {
                $table->text('contacto_emergencia_nombre')->nullable()->after('grupo_sanguineo');
            }
            if (! Schema::hasColumn('pacientes', 'contacto_emergencia_telefono')) {
                $table->text('contacto_emergencia_telefono')->nullable()->after('contacto_emergencia_nombre');
            }
            if (! Schema::hasColumn('pacientes', 'notas_emergencia')) {
                $table->text('notas_emergencia')->nullable()->after('contacto_emergencia_telefono');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            foreach (['grupo_sanguineo', 'contacto_emergencia_nombre', 'contacto_emergencia_telefono', 'notas_emergencia'] as $col) {
                if (Schema::hasColumn('pacientes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
