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
        // Modificar el enum para incluir 'fisioterapia'
        DB::statement("ALTER TABLE pacientes MODIFY COLUMN tipo_paciente ENUM('cardiaca', 'pulmonar', 'ambos', 'fisioterapia') DEFAULT 'cardiaca'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE pacientes MODIFY COLUMN tipo_paciente ENUM('cardiaca', 'pulmonar', 'ambos') DEFAULT 'cardiaca'");
    }
};

