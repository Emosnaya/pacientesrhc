<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            if (! Schema::hasColumn('clinicas', 'cita_estado_inicial')) {
                $table->string('cita_estado_inicial', 20)->default('confirmada')->after('portal_bloqueo_dias_post_cancelacion');
            }
            if (! Schema::hasColumn('clinicas', 'citas_solapamiento_modo')) {
                $table->string('citas_solapamiento_modo', 30)->default('permitir')->after('cita_estado_inicial');
            }
            if (! Schema::hasColumn('clinicas', 'whatsapp_notificaciones_activas')) {
                $table->boolean('whatsapp_notificaciones_activas')->default(false)->after('citas_solapamiento_modo');
            }
        });

        // Defaults sensatos por tipo de clínica (solo valores actuales)
        if (Schema::hasColumn('clinicas', 'cita_estado_inicial')) {
            DB::table('clinicas')
                ->where('tipo_clinica', 'dental')
                ->update(['cita_estado_inicial' => 'pendiente']);

            DB::table('clinicas')
                ->whereIn('tipo_clinica', [
                    'cardiaca',
                    'pulmonar',
                    'cardio-pulmonar',
                    'cardiopulmonar',
                    'rehabilitacion_cardiopulmonar',
                    'fisioterapia',
                ])
                ->update(['cita_estado_inicial' => 'confirmada']);
        }

        // Migrar flag legacy del portal al nuevo modo unificado
        if (
            Schema::hasColumn('clinicas', 'portal_permite_multiples_citas_mismo_horario')
            && Schema::hasColumn('clinicas', 'citas_solapamiento_modo')
        ) {
            DB::table('clinicas')
                ->where('portal_permite_multiples_citas_mismo_horario', false)
                ->update(['citas_solapamiento_modo' => 'clinica']);
        }

        Schema::table('pacientes', function (Blueprint $table) {
            if (! Schema::hasColumn('pacientes', 'whatsapp_notificaciones')) {
                $table->boolean('whatsapp_notificaciones')->default(false)->after('telefono');
            }
            if (! Schema::hasColumn('pacientes', 'whatsapp_autorizado_at')) {
                $table->timestamp('whatsapp_autorizado_at')->nullable()->after('whatsapp_notificaciones');
            }
            if (! Schema::hasColumn('pacientes', 'telefono_search_hash')) {
                $table->string('telefono_search_hash', 64)->nullable()->after('whatsapp_autorizado_at');
                $table->index('telefono_search_hash');
            }
        });

        if (! Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();
                $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->string('tipo', 40); // confirmacion | recordatorio | respuesta
                $table->string('direccion', 10)->default('outbound'); // outbound | inbound
                $table->string('estado', 30)->default('queued'); // queued | sent | failed | received
                $table->string('twilio_sid', 80)->nullable()->index();
                $table->string('telefono_to', 30)->nullable();
                $table->text('body')->nullable();
                $table->text('error')->nullable();
                $table->boolean('accionable')->default(true);
                $table->timestamps();

                $table->index(['cita_id', 'accionable']);
                $table->index(['paciente_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');

        Schema::table('pacientes', function (Blueprint $table) {
            if (Schema::hasColumn('pacientes', 'telefono_search_hash')) {
                $table->dropIndex(['telefono_search_hash']);
                $table->dropColumn('telefono_search_hash');
            }
            if (Schema::hasColumn('pacientes', 'whatsapp_autorizado_at')) {
                $table->dropColumn('whatsapp_autorizado_at');
            }
            if (Schema::hasColumn('pacientes', 'whatsapp_notificaciones')) {
                $table->dropColumn('whatsapp_notificaciones');
            }
        });

        Schema::table('clinicas', function (Blueprint $table) {
            foreach (['whatsapp_notificaciones_activas', 'citas_solapamiento_modo', 'cita_estado_inicial'] as $col) {
                if (Schema::hasColumn('clinicas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
