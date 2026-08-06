<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->string('especialidad_solicitada', 120)->nullable()->after('notas');
            $table->string('origen', 20)->default('panel')->after('especialidad_solicitada');
            $table->timestamp('contactado_at')->nullable()->after('origen');
            $table->boolean('requiere_confirmacion')->default(false)->after('contactado_at');

            $table->index(['origen', 'estado']);
            $table->index(['clinica_id', 'requiere_confirmacion', 'estado']);
        });

        Schema::create('cita_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();
            $table->string('tipo', 40); // solicitado|contactado|confirmado|modificado|cancelado|doctor_asignado
            $table->string('actor', 20)->default('sistema'); // paciente|clinica|sistema
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->text('mensaje')->nullable();
            $table->timestamps();

            $table->index(['cita_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_eventos');

        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex(['origen', 'estado']);
            $table->dropIndex(['clinica_id', 'requiere_confirmacion', 'estado']);
            $table->dropColumn(['especialidad_solicitada', 'origen', 'contactado_at', 'requiere_confirmacion']);
        });
    }
};
