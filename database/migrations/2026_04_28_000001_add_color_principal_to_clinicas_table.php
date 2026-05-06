<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->string('color_principal', 7)->default('#0A1628')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn('color_principal');
        });
    }
};
