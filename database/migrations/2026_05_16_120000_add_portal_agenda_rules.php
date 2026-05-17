<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->boolean('portal_permite_multiples_citas_mismo_horario')->default(true)->after('color_principal');
            $table->unsignedTinyInteger('portal_max_reagendas_paciente')->default(2)->after('portal_permite_multiples_citas_mismo_horario');
            $table->unsignedSmallInteger('portal_bloqueo_dias_post_cancelacion')->default(7)->after('portal_max_reagendas_paciente');
        });

        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->boolean('portal_agenda_bloqueado')->default(false)->after('portal_visible_expediente_resumen');
            $table->date('portal_agenda_bloqueado_hasta')->nullable()->after('portal_agenda_bloqueado');
            $table->string('portal_agenda_bloqueo_motivo', 255)->nullable()->after('portal_agenda_bloqueado_hasta');
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedTinyInteger('reagenda_intentos')->default(0)->after('confirmacion_whatsapp');
            $table->foreignId('reagendada_de_cita_id')->nullable()->after('reagenda_intentos')->constrained('citas')->nullOnDelete();
            $table->boolean('cancelada_por_regla')->default(false)->after('reagendada_de_cita_id');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reagendada_de_cita_id');
            $table->dropColumn(['reagenda_intentos', 'cancelada_por_regla']);
        });

        Schema::table('clinica_paciente', function (Blueprint $table) {
            $table->dropColumn([
                'portal_agenda_bloqueado',
                'portal_agenda_bloqueado_hasta',
                'portal_agenda_bloqueo_motivo',
            ]);
        });

        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn([
                'portal_permite_multiples_citas_mismo_horario',
                'portal_max_reagendas_paciente',
                'portal_bloqueo_dias_post_cancelacion',
            ]);
        });
    }
};
