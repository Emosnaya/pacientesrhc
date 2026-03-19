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
        // Modificar el ENUM para agregar 'bloqueo'
        DB::statement("ALTER TABLE eventos MODIFY COLUMN tipo ENUM('recordatorio', 'tarea', 'evento', 'bloqueo') NOT NULL DEFAULT 'evento'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir al ENUM original (eliminar 'bloqueo')
        DB::statement("ALTER TABLE eventos MODIFY COLUMN tipo ENUM('recordatorio', 'tarea', 'evento') NOT NULL DEFAULT 'evento'");
    }
};
