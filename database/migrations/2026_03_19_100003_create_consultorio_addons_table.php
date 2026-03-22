<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultorio_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->decimal('price_monthly', 10, 2)->default(299.00);
            $table->decimal('price_yearly', 10, 2)->default(2990.00);
            $table->enum('billing_cycle', ['mensual', 'anual'])->default('mensual');
            $table->boolean('is_active')->default(true);
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('clinica_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultorio_addons');
    }
};
