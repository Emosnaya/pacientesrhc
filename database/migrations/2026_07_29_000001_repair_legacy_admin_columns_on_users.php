<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'isAdmin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('isAdmin')->default(false)->after('password');
            });
        }

        if (! Schema::hasColumn('users', 'isSuperAdmin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('isSuperAdmin')->default(false)->after('isAdmin');
            });
        }
    }

    public function down(): void
    {
        // Migración de reparación: no elimina columnas que pueden ser legacy.
    }
};
