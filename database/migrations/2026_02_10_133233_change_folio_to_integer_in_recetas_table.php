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
        // Primero, convertir valores vacíos o no numéricos a NULL
        DB::statement("UPDATE recetas SET folio = NULL WHERE folio = '' OR folio IS NULL OR folio = '0'");
        
        // Ahora cambiar el tipo de columna
        DB::statement('ALTER TABLE recetas MODIFY folio INT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE recetas MODIFY folio VARCHAR(255) NULL');
    }
};
