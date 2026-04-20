<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega numero_expediente a clinica_paciente para que cada clínica
     * pueda tener su propio número de expediente para el mismo paciente.
     * 
     * Esta migración es idempotente: funciona tanto si la columna ya existe como si no.
     */
    public function up()
    {
        // 1. Crear la columna si no existe
        if (!Schema::hasColumn('clinica_paciente', 'numero_expediente')) {
            Schema::table('clinica_paciente', function (Blueprint $table) {
                $table->string('numero_expediente', 50)->nullable()->after('user_id');
            });
        }

        // 2. Eliminar el índice único si existe (para poder poblar datos sin conflictos)
        try {
            Schema::table('clinica_paciente', function (Blueprint $table) {
                $table->dropUnique('idx_expediente_unico_por_clinica');
            });
        } catch (\Exception $e) {
            // El índice puede no existir, ignorar
        }

        // 3. Poblar numero_expediente desde pacientes.registro manejando duplicados
        $clinicas = DB::table('clinica_paciente')
            ->select('clinica_id')
            ->distinct()
            ->pluck('clinica_id');

        foreach ($clinicas as $clinicaId) {
            $expedientesUsados = [];

            $vinculos = DB::table('clinica_paciente')
                ->join('pacientes', 'clinica_paciente.paciente_id', '=', 'pacientes.id')
                ->where('clinica_paciente.clinica_id', $clinicaId)
                ->whereNull('clinica_paciente.numero_expediente') // Solo los que no tienen expediente
                ->orWhere('clinica_paciente.numero_expediente', '')
                ->where('clinica_paciente.clinica_id', $clinicaId) // Repetir filtro por el orWhere
                ->select('clinica_paciente.id', 'pacientes.registro', 'clinica_paciente.paciente_id', 'clinica_paciente.numero_expediente')
                ->orderBy('clinica_paciente.vinculado_at')
                ->orderBy('clinica_paciente.id')
                ->get();

            // Primero, cargar expedientes ya asignados para evitar conflictos
            $existentes = DB::table('clinica_paciente')
                ->where('clinica_id', $clinicaId)
                ->whereNotNull('numero_expediente')
                ->where('numero_expediente', '!=', '')
                ->pluck('numero_expediente');
            
            foreach ($existentes as $exp) {
                $expedientesUsados[$exp] = true;
            }

            foreach ($vinculos as $vinculo) {
                // Saltar si ya tiene expediente válido
                if (!empty($vinculo->numero_expediente)) {
                    continue;
                }

                $registroOriginal = $vinculo->registro ?? null;
                
                if (empty($registroOriginal)) {
                    $nuevoExpediente = $this->generarExpedienteUnico($expedientesUsados, 'EXP');
                } else {
                    $nuevoExpediente = $this->generarExpedienteUnico($expedientesUsados, $registroOriginal);
                }

                $expedientesUsados[$nuevoExpediente] = true;

                DB::table('clinica_paciente')
                    ->where('id', $vinculo->id)
                    ->update(['numero_expediente' => $nuevoExpediente]);
            }
        }

        // 4. Crear el índice único
        try {
            Schema::table('clinica_paciente', function (Blueprint $table) {
                $table->unique(['clinica_id', 'numero_expediente'], 'idx_expediente_unico_por_clinica');
            });
        } catch (\Exception $e) {
            // El índice puede ya existir, ignorar
        }
    }

    /**
     * Genera un expediente único, agregando sufijo si ya existe
     */
    private function generarExpedienteUnico(array $usados, string $base): string
    {
        if (!isset($usados[$base])) {
            return $base;
        }

        $contador = 2;
        while (isset($usados["{$base}_{$contador}"])) {
            $contador++;
        }

        return "{$base}_{$contador}";
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Eliminar índice si existe
        try {
            Schema::table('clinica_paciente', function (Blueprint $table) {
                $table->dropUnique('idx_expediente_unico_por_clinica');
            });
        } catch (\Exception $e) {
            // Ignorar
        }

        // Eliminar columna si existe
        if (Schema::hasColumn('clinica_paciente', 'numero_expediente')) {
            Schema::table('clinica_paciente', function (Blueprint $table) {
                $table->dropColumn('numero_expediente');
            });
        }
    }
};
