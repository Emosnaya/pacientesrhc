<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->text('motivo')->nullable()->after('concepto');
        });

        // Hacer paciente_id nullable y ajustar FK
        DB::statement('ALTER TABLE pagos DROP FOREIGN KEY pagos_paciente_id_foreign');
        DB::statement('ALTER TABLE pagos MODIFY paciente_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_paciente_id_foreign FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir FK y columna paciente_id
        DB::statement('ALTER TABLE pagos DROP FOREIGN KEY pagos_paciente_id_foreign');
        DB::statement('ALTER TABLE pagos MODIFY paciente_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_paciente_id_foreign FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE');

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('motivo');
        });
    }
};
