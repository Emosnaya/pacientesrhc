<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            if (! Schema::hasColumn('pacientes', 'foto')) {
                $table->string('foto')->nullable()->after('color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            if (Schema::hasColumn('pacientes', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }
};
