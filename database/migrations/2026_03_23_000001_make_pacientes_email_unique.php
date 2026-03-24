<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Hacer email único en pacientes.
     * Tolerante a emails ya desencriptados (migración parcial anterior).
     */
    public function up(): void
    {
        // Paso 1: Desencriptar emails que aún están encriptados
        $pacientes = DB::table('pacientes')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'email']);
        
        foreach ($pacientes as $paciente) {
            $emailRaw = $paciente->email;
            
            // Detectar si está encriptado (empieza con eyJ que es base64 de JSON de Laravel)
            if (str_starts_with($emailRaw, 'eyJ')) {
                try {
                    $emailPlano = Crypt::decryptString($emailRaw);
                    DB::table('pacientes')
                        ->where('id', $paciente->id)
                        ->update(['email' => strtolower(trim($emailPlano))]);
                } catch (\Exception $e) {
                    // Si falla desencriptar, probablemente ya es texto plano
                    // o está corrupto - dejarlo como está
                    continue;
                }
            } else {
                // Ya está en texto plano, solo normalizar
                DB::table('pacientes')
                    ->where('id', $paciente->id)
                    ->update(['email' => strtolower(trim($emailRaw))]);
            }
        }

        // Paso 2: Cambiar tipo de columna a VARCHAR(255)
        DB::statement('ALTER TABLE pacientes MODIFY email VARCHAR(255) NULL');

        // Paso 3: Limpiar duplicados
        $duplicates = DB::table('pacientes')
            ->select('email')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email');

        foreach ($duplicates as $email) {
            $pacientesConEmail = DB::table('pacientes')
                ->where('email', $email)
                ->orderBy('created_at', 'desc')
                ->get();

            $first = true;
            foreach ($pacientesConEmail as $p) {
                if ($first) {
                    $first = false;
                    continue;
                }
                // Agregar sufijo al duplicado
                DB::table('pacientes')
                    ->where('id', $p->id)
                    ->update(['email' => substr($email . '_dup_' . $p->id, 0, 255)]);
            }
        }

        // Paso 4: Agregar índice único
        DB::statement('ALTER TABLE pacientes ADD UNIQUE INDEX pacientes_email_unique (email)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE pacientes DROP INDEX pacientes_email_unique');
        DB::statement('ALTER TABLE pacientes MODIFY email TEXT NULL');
        
        // Nota: No re-encriptamos en rollback por seguridad
    }
};
