<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrar isAdmin e isSuperAdmin de users a user_clinicas.
     * 
     * Lógica:
     * 1. Para cada usuario CON isAdmin=1 o isSuperAdmin=1 en tabla users
     * 2. Actualizar su registro en user_clinicas para SU CLÍNICA ORIGINAL (clinica_id)
     * 3. Asignar isAdmin/isSuperAdmin en la relación
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_clinicas') || !Schema::hasTable('users')) {
            return;
        }

        // Migrar superadmins
        DB::statement('
            UPDATE user_clinicas uc
            INNER JOIN users u ON uc.user_id = u.id
            SET uc.isSuperAdmin = 1, uc.isAdmin = 1, uc.rol_en_clinica = "propietario"
            WHERE u.isSuperAdmin = 1 
            AND uc.clinica_id = u.clinica_id
        ');

        // Migrar admins (que no sean superadmins)
        DB::statement('
            UPDATE user_clinicas uc
            INNER JOIN users u ON uc.user_id = u.id
            SET uc.isAdmin = 1, uc.rol_en_clinica = "propietario"
            WHERE u.isAdmin = 1 
            AND u.isSuperAdmin = 0
            AND uc.clinica_id = u.clinica_id
        ');

        echo "✅ Migrados permisos de admin/superadmin a user_clinicas\n";
    }

    public function down(): void
    {
        // No revertir - datos quedan en user_clinicas por seguridad
    }
};
