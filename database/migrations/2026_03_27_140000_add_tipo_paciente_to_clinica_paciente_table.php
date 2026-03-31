<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipo de paciente por vínculo clínica–paciente (mismo expediente, distinto programa por espacio).
     */
    public function up(): void
    {
        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->string('tipo_paciente', 255)->nullable()->after('motivo_consulta');
        });

        if (Schema::hasTable('pacientes') && Schema::hasTable('clinica_paciente')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('
                    UPDATE clinica_paciente cp
                    INNER JOIN pacientes p ON p.id = cp.paciente_id
                    SET cp.tipo_paciente = p.tipo_paciente
                    WHERE cp.tipo_paciente IS NULL
                ');
            } else {
                $rows = DB::table('clinica_paciente')->select('paciente_id', 'clinica_id')->get();
                foreach ($rows as $row) {
                    $tipo = DB::table('pacientes')->where('id', $row->paciente_id)->value('tipo_paciente');
                    if ($tipo !== null) {
                        DB::table('clinica_paciente')
                            ->where('paciente_id', $row->paciente_id)
                            ->where('clinica_id', $row->clinica_id)
                            ->update(['tipo_paciente' => $tipo]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->dropColumn('tipo_paciente');
        });
    }
};
