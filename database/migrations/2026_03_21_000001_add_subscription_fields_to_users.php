<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campos de suscripción para usuarios con consultorios privados.
     * 
     * Los usuarios que son solo colaboradores NO necesitan suscripción.
     * Los usuarios que quieren crear consultorios privados SÍ necesitan suscripción.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Suscripción para consultorios privados
            $table->boolean('tiene_suscripcion_consultorio')->default(false)->after('isSuperAdmin');
            $table->enum('plan_consultorio', ['basico', 'profesional', 'premium'])->nullable()->after('tiene_suscripcion_consultorio');
            $table->enum('ciclo_facturacion', ['mensual', 'anual'])->default('mensual')->after('plan_consultorio');
            
            // Stripe/Pasarela de pago
            $table->string('stripe_customer_id')->nullable()->after('ciclo_facturacion');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            
            // Fechas de suscripción
            $table->timestamp('suscripcion_inicio')->nullable()->after('stripe_subscription_id');
            $table->timestamp('suscripcion_fin')->nullable()->after('suscripcion_inicio');
            $table->timestamp('trial_ends_at')->nullable()->after('suscripcion_fin');
            
            // Consultorios adicionales comprados
            $table->integer('consultorios_adicionales_comprados')->default(0)->after('trial_ends_at')
                ->comment('Consultorios adicionales al primero incluido en el plan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tiene_suscripcion_consultorio',
                'plan_consultorio',
                'ciclo_facturacion',
                'stripe_customer_id',
                'stripe_subscription_id',
                'suscripcion_inicio',
                'suscripcion_fin',
                'trial_ends_at',
                'consultorios_adicionales_comprados',
            ]);
        });
    }
};
