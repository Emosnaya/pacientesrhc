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
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN apellidoMat VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN talla DECIMAL(8,2) NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN peso DECIMAL(8,2) NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN cintura DECIMAL(8,2) NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN imc DECIMAL(8,2) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN apellidoMat VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN talla DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN peso DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN cintura DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN imc DECIMAL(8,2) NOT NULL');
    }
};
