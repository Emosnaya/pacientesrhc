<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * version_aviso almacena aviso+terminos (fechas o etiquetas); 20 caracteres era insuficiente.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE pacientes MODIFY version_aviso VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pacientes ALTER COLUMN version_aviso TYPE VARCHAR(255)');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE pacientes MODIFY version_aviso VARCHAR(20) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pacientes ALTER COLUMN version_aviso TYPE VARCHAR(20)');
        }
    }
};
