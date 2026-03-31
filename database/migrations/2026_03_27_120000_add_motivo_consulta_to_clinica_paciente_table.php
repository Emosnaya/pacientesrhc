<?php

use App\Models\Paciente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->text('motivo_consulta')->nullable()->after('portal_visible_expediente_resumen');
        });

        foreach (Paciente::query()->get() as $paciente) {
            $motivo = $paciente->motivo_consulta;
            if ($motivo === null || trim((string) $motivo) === '') {
                continue;
            }
            $encrypted = Crypt::encryptString($motivo);
            $rows = DB::table('clinica_paciente')
                ->where('paciente_id', $paciente->id)
                ->get(['clinica_id']);
            foreach ($rows as $row) {
                DB::table('clinica_paciente')
                    ->where('paciente_id', $paciente->id)
                    ->where('clinica_id', $row->clinica_id)
                    ->update(['motivo_consulta' => $encrypted]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->dropColumn('motivo_consulta');
        });
    }
};
