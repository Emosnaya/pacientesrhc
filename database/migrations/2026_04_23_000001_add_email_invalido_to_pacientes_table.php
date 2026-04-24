<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->boolean('email_invalido')->default(false)->after('email');
            $table->timestamp('email_invalido_at')->nullable()->after('email_invalido');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['email_invalido', 'email_invalido_at']);
        });
    }
};
