<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->text('exploracion_fisica')->nullable()->after('perimetro_abdominal');
        });

        DB::table('historia_clinica_cardiologias')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                if (!empty($row->exploracion_fisica)) {
                    continue;
                }

                $exp = json_decode($row->exploracion_cardiovascular ?? '{}', true) ?: [];
                $texto = trim($exp['otros'] ?? '');

                if ($texto !== '') {
                    DB::table('historia_clinica_cardiologias')->where('id', $row->id)->update([
                        'exploracion_fisica' => $texto,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->dropColumn('exploracion_fisica');
        });
    }
};
