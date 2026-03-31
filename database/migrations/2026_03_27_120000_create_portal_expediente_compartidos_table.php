<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_expediente_compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('tipo_exp');
            $table->unsignedBigInteger('expediente_id');
            $table->string('tipo_nombre_snapshot', 191)->nullable();
            $table->date('fecha_snapshot')->nullable();
            $table->timestamps();

            $table->unique(
                ['paciente_id', 'clinica_id', 'tipo_exp', 'expediente_id'],
                'portal_exp_pac_clin_tipo_expid'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_expediente_compartidos');
    }
};
