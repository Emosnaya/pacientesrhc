<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electrocardiogramas', function (Blueprint $table) {
            if (!Schema::hasColumn('electrocardiogramas', 'tipo_ecg')) {
                $table->string('tipo_ecg', 20)->default('reposo')->after('hora');
            }
            if (!Schema::hasColumn('electrocardiogramas', 'comparacion_previo')) {
                $table->text('comparacion_previo')->nullable()->after('interpretacion');
            }
            if (!Schema::hasColumn('electrocardiogramas', 'diagnostico_electrocardiografico')) {
                $table->text('diagnostico_electrocardiografico')->nullable()->after('comparacion_previo');
            }
            if (!Schema::hasColumn('electrocardiogramas', 'medico_realiza')) {
                $table->string('medico_realiza', 100)->nullable()->after('recomendaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('electrocardiogramas', function (Blueprint $table) {
            $columns = ['tipo_ecg', 'comparacion_previo', 'diagnostico_electrocardiografico', 'medico_realiza'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('electrocardiogramas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
