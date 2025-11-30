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
        // Usar SQL directo para hacer la cédula nullable
        DB::statement('ALTER TABLE users MODIFY COLUMN cedula VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el cambio usando SQL directo
        DB::statement('ALTER TABLE users MODIFY COLUMN cedula VARCHAR(255) NOT NULL');
    }
};
