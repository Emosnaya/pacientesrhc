<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contexto de la invitación (registro vs nueva vinculación) para textos en correo y pantalla pública.
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('consentimiento_invitacion_contexto', 40)->nullable()->after('consentimiento_email_enviado_at');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn('consentimiento_invitacion_contexto');
        });
    }
};
