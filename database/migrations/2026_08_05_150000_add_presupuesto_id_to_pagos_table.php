<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El vínculo es opcional: un pago puede ser contabilidad suelta de la clínica
     * o quedar aplicado a un presupuesto concreto del paciente.
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('presupuesto_id')
                ->nullable()
                ->after('cita_id')
                ->constrained('presupuestos')
                ->nullOnDelete();

            $table->index(['presupuesto_id', 'fecha_pago'], 'pagos_presupuesto_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex('pagos_presupuesto_fecha_index');
            $table->dropConstrainedForeignId('presupuesto_id');
        });
    }
};
