<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar tipo_paciente de ENUM a VARCHAR para soportar todos los tipos de clínica
        DB::statement("ALTER TABLE pacientes MODIFY COLUMN tipo_paciente VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir a ENUM con los valores originales (solo para rollback)
        DB::statement("ALTER TABLE pacientes MODIFY COLUMN tipo_paciente ENUM('cardiaca', 'pulmonar', 'ambos', 'fisioterapia') DEFAULT 'cardiaca'");
    }
};
