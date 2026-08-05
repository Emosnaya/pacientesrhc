<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagos') && ! Schema::hasColumn('pagos', 'atribuido_a_user_id')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->foreignId('atribuido_a_user_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('compensation_profiles')) {
            Schema::create('compensation_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('sueldo_fijo', 12, 2)->nullable();
                $table->decimal('comision_pct', 5, 2)->default(0);
                $table->boolean('activo')->default(true);
                $table->text('notas')->nullable();
                $table->timestamps();

                $table->unique(['clinica_id', 'user_id']);
                $table->index(['clinica_id', 'activo']);
            });
        }

        if (! Schema::hasTable('liquidaciones')) {
            Schema::create('liquidaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
                $table->date('periodo_inicio');
                $table->date('periodo_fin');
                $table->string('estado', 20)->default('calculada'); // borrador|calculada|pagada|cancelada
                $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('pagado_at')->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();

                $table->index(['clinica_id', 'periodo_inicio', 'periodo_fin']);
                $table->index(['clinica_id', 'estado']);
            });
        }

        if (! Schema::hasTable('liquidacion_items')) {
            Schema::create('liquidacion_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('liquidacion_id')->constrained('liquidaciones')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('compensation_profile_id')->nullable()->constrained('compensation_profiles')->nullOnDelete();
                $table->decimal('sueldo_fijo', 12, 2)->default(0);
                $table->decimal('base_comisionable', 12, 2)->default(0);
                $table->decimal('comision_pct', 5, 2)->default(0);
                $table->decimal('monto_comision', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->unsignedInteger('cantidad_pagos')->default(0);
                $table->json('detalle_json')->nullable();
                $table->timestamps();

                $table->unique(['liquidacion_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('liquidacion_item_pagos')) {
            Schema::create('liquidacion_item_pagos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('liquidacion_item_id')->constrained('liquidacion_items')->cascadeOnDelete();
                $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
                $table->decimal('monto_atribuido', 12, 2);
                $table->timestamps();

                $table->unique(['pago_id']); // un pago solo en una liquidación activa
                $table->index('liquidacion_item_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidacion_item_pagos');
        Schema::dropIfExists('liquidacion_items');
        Schema::dropIfExists('liquidaciones');
        Schema::dropIfExists('compensation_profiles');

        if (Schema::hasTable('pagos') && Schema::hasColumn('pagos', 'atribuido_a_user_id')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('atribuido_a_user_id');
            });
        }
    }
};
