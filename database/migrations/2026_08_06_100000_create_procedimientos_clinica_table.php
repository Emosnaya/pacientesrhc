<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimientos_clinica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->string('categoria', 100)->nullable();
            $table->string('codigo', 50)->nullable();
            $table->decimal('precio', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['clinica_id', 'activo']);
            $table->index(['clinica_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimientos_clinica');
    }
};
