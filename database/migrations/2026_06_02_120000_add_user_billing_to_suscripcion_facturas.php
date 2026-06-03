<?php

use App\Models\Clinica;
use App\Models\SuscripcionFacturas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripcion_facturas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->string('billing_scope', 16)->default('clinica')->after('user_id');
            $table->index(['user_id', 'estado']);
        });

        Schema::table('suscripcion_facturas', function (Blueprint $table) {
            $table->unsignedBigInteger('clinica_id')->nullable()->change();
        });

        // Suscripciones en consultorios privados ? titular = cuenta del doctor (propietario como fallback)
        SuscripcionFacturas::query()
            ->whereNull('user_id')
            ->whereHas('clinica', fn ($q) => $q->where('es_consultorio_privado', true))
            ->with('clinica')
            ->each(function (SuscripcionFacturas $suscripcion) {
                $clinica = $suscripcion->clinica;
                if (! $clinica) {
                    return;
                }

                $userId = $clinica->propietario_user_id;
                if (! $userId) {
                    return;
                }

                $suscripcion->update([
                    'user_id' => $userId,
                    'billing_scope' => SuscripcionFacturas::SCOPE_USUARIO,
                ]);

                $clinica->update(['facturacion_addon_activo' => false]);
            });
    }

    public function down(): void
    {
        Schema::table('suscripcion_facturas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'estado']);
            $table->dropColumn(['user_id', 'billing_scope']);
        });

        Schema::table('suscripcion_facturas', function (Blueprint $table) {
            $table->unsignedBigInteger('clinica_id')->nullable(false)->change();
        });
    }
};
