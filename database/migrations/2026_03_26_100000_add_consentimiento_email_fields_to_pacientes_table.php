<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Invitación por correo para aceptar aviso de privacidad y términos (LFPDPPP).
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('consentimiento_token_hash', 64)->nullable()->after('version_aviso');
            $table->timestamp('consentimiento_token_expires_at')->nullable()->after('consentimiento_token_hash');
            $table->timestamp('consentimiento_email_enviado_at')->nullable()->after('consentimiento_token_expires_at');
            $table->index('consentimiento_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex(['consentimiento_token_hash']);
            $table->dropColumn([
                'consentimiento_token_hash',
                'consentimiento_token_expires_at',
                'consentimiento_email_enviado_at',
            ]);
        });
    }
};
