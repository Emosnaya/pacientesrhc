<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historia_clinica_dental', function (Blueprint $table) {
            // Agregar nueva columna para medicamentos actuales (JSON)
            $table->json('medicamentos_actuales')->nullable()->after('alergias');
            
            // Eliminar campo 'lugar' ya que no se usa
            $table->dropColumn('lugar');
            
            // Eliminar campos antiguos de medicamentos (reemplazados por medicamentos_actuales)
            $table->dropColumn(['toma_medicamento', 'medicamento_detalle']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historia_clinica_dental', function (Blueprint $table) {
            // Restaurar campos eliminados
            $table->string('lugar')->nullable()->after('user_id');
            $table->boolean('toma_medicamento')->default(false)->after('alergias');
            $table->text('medicamento_detalle')->nullable()->after('toma_medicamento');
            
            // Eliminar columna agregada
            $table->dropColumn('medicamentos_actuales');
        });
    }
};
