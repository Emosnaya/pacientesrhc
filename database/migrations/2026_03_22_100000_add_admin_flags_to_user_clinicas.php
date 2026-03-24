<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campos isAdmin e isSuperAdmin a user_clinicas.
     * 
     * Nueva arquitectura: Los permisos de admin son POR CLÍNICA.
     * Un usuario puede ser admin en UNA clínica pero colaborador en OTRA.
     */
    public function up(): void
    {
        Schema::table('user_clinicas', function (Blueprint $table) {
            $table->boolean('isAdmin')->default(false)->after('rol_en_clinica');
            $table->boolean('isSuperAdmin')->default(false)->after('isAdmin');
        });
    }

    public function down(): void
    {
        Schema::table('user_clinicas', function (Blueprint $table) {
            $table->dropColumn(['isAdmin', 'isSuperAdmin']);
        });
    }
};
