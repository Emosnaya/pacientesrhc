<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE presupuestos SET estado = 'enviado' WHERE estado = 'borrador'");

        DB::statement("ALTER TABLE presupuestos MODIFY estado ENUM('borrador','enviado','aceptado','rechazado','cancelado','completado') NOT NULL DEFAULT 'enviado'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE presupuestos MODIFY estado ENUM('borrador','enviado','aceptado','rechazado','cancelado','completado') NOT NULL DEFAULT 'borrador'");
    }
};
