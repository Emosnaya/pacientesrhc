<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['q_ant', 'q_poster_inferior'] as $column) {
            DB::statement("
                UPDATE clinicos
                SET {$column} = CASE
                    WHEN {$column} IN ('1', 'true', 's', 'si', 'sí', 'yes', 'Sí', 'SI', 'TRUE') THEN '1'
                    ELSE '0'
                END
                WHERE {$column} IS NOT NULL
            ");

            DB::statement("ALTER TABLE clinicos MODIFY COLUMN {$column} TINYINT(1) NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        foreach (['q_ant', 'q_poster_inferior'] as $column) {
            DB::statement("ALTER TABLE clinicos MODIFY COLUMN {$column} VARCHAR(255) NULL");
        }
    }
};
