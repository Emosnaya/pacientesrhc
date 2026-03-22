<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Poblar tabla user_clinicas con relaciones existentes
     *
     * @return void
     */
    public function up()
    {
        // Migrar usuarios existentes: cada usuario se vincula a su clínica actual
        // Asumimos que el rol viene de users.rol (1=superadmin, 2=admin, 3=doctor, 4=recepcionista)
        DB::statement('
            INSERT INTO user_clinicas (user_id, clinica_id, rol_en_clinica, es_principal, activa, created_at, updated_at)
            SELECT 
                u.id,
                u.clinica_id,
                CASE 
                    WHEN u.rol = 1 THEN "superadmin"
                    WHEN u.rol = 2 THEN "admin"
                    WHEN u.rol = 3 THEN "doctor"
                    WHEN u.rol = 4 THEN "recepcionista"
                    ELSE "doctor"
                END as rol,
                1 as es_principal,
                1 as activa,
                NOW(),
                NOW()
            FROM users u
            WHERE u.clinica_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM user_clinicas uc 
                WHERE uc.user_id = u.id AND uc.clinica_id = u.clinica_id
            )
        ');

        // Actualizar clinica_activa_id en users si aún no está configurado
        DB::statement('
            UPDATE users u
            SET u.clinica_activa_id = u.clinica_id
            WHERE u.clinica_id IS NOT NULL 
            AND u.clinica_activa_id IS NULL
        ');

        echo "✅ Migrados " . DB::table('user_clinicas')->count() . " registros a user_clinicas\n";
        echo "✅ Actualizados " . DB::table('users')->whereNotNull('clinica_id')->whereNotNull('clinica_activa_id')->count() . " usuarios con workspace activo\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No revertir - mantener datos por seguridad
        // Si es necesario, ejecutar manualmente:
        // DELETE FROM user_clinicas WHERE created_at >= '2026-03-22';
        echo "⚠️ No se revierten datos de user_clinicas por seguridad\n";
    }
};
