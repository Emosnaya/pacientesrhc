<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            $table->integer('max_pacientes')->default(100);
            $table->integer('max_usuarios')->default(1);
            $table->integer('max_sucursales')->default(1);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insertar plan único profesional
        DB::table('subscription_plans')->insert([
            [
                'name' => 'Plan Profesional',
                'slug' => 'profesional',
                'description' => 'Incluye 1 consultorio privado con todas las funciones',
                'price_monthly' => 499.00,
                'price_yearly' => 4990.00, // 2 meses gratis (499*10)
                'max_pacientes' => 999999, // Ilimitados
                'max_usuarios' => 5, // Por consultorio
                'max_sucursales' => 3, // Por consultorio
                'features' => json_encode([
                    '1 Consultorio Privado incluido',
                    'Pacientes ilimitados',
                    'Hasta 5 usuarios por consultorio',
                    'Expedientes digitales completos',
                    'Calendario y gestión de citas',
                    'Recetas digitales',
                    'Reportes y analíticas',
                    'Integraciones API',
                    'Soporte prioritario'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
