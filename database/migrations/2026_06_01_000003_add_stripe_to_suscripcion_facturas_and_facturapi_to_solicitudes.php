<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar campos Stripe a suscripcion_facturas
        Schema::table('suscripcion_facturas', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('facturapi_subscription_id');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_checkout_session_id');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_subscription_id');
        });

        // Agregar campos Facturapi a solicitudes_factura
        Schema::table('solicitudes_factura', function (Blueprint $table) {
            $table->string('facturapi_invoice_id')->nullable()->after('uuid');
            $table->json('facturapi_response')->nullable()->after('facturapi_invoice_id');
        });

        // Agregar stripe_price_id a planes_facturacion
        Schema::table('planes_facturacion', function (Blueprint $table) {
            $table->string('stripe_price_id')->nullable()->after('precio_mensual');
        });
    }

    public function down(): void
    {
        Schema::table('suscripcion_facturas', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_subscription_id', 'stripe_payment_intent_id']);
        });

        Schema::table('solicitudes_factura', function (Blueprint $table) {
            $table->dropColumn(['facturapi_invoice_id', 'facturapi_response']);
        });

        Schema::table('planes_facturacion', function (Blueprint $table) {
            $table->dropColumn('stripe_price_id');
        });
    }
};
