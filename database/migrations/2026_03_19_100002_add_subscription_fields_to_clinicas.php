<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('pagado');
            $table->string('stripe_customer_id')->nullable()->after('stripe_subscription_id');
            $table->enum('plan_type', ['basico', 'profesional', 'premium'])->default('profesional')->after('plan');
            $table->enum('billing_cycle', ['mensual', 'anual'])->default('mensual')->after('plan_type');
            $table->timestamp('trial_ends_at')->nullable()->after('fecha_vencimiento');
            $table->timestamp('next_billing_date')->nullable()->after('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_subscription_id',
                'stripe_customer_id',
                'plan_type',
                'billing_cycle',
                'trial_ends_at',
                'next_billing_date'
            ]);
        });
    }
};
