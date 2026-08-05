<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sillones')) {
            Schema::create('sillones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
                $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
                $table->string('nombre', 80);
                $table->string('color', 7)->default('#3B82F6');
                $table->boolean('activo')->default(true);
                $table->unsignedSmallInteger('orden')->default(0);
                $table->timestamps();

                $table->index(['clinica_id', 'sucursal_id', 'activo']);
            });
        }

        if (Schema::hasTable('citas') && ! Schema::hasColumn('citas', 'sillon_id')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->foreignId('sillon_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('sillones')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('citas') && Schema::hasColumn('citas', 'sillon_id')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('sillon_id');
            });
        }
        Schema::dropIfExists('sillones');
    }
};
