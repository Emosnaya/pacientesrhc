<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * clinica_activa_id: la clínica/consultorio en que el usuario está trabajando ahora mismo.
     * null = usa clinica_id (asignación estática original).
     * Cuando un doctor tiene su consultorio privado además de su clínica, puede cambiar aquí.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('clinica_activa_id')->nullable()->after('clinica_id');
            $table->foreign('clinica_activa_id')->references('id')->on('clinicas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinica_activa_id']);
            $table->dropColumn('clinica_activa_id');
        });
    }
};
