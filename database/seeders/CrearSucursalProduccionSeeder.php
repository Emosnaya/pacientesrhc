<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Paciente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrearSucursalProduccionSeeder extends Seeder
{
    /**
     * SEEDER ESPECÍFICO PARA PRODUCCIÓN
     * 
     * Crea sucursal principal SOLO para la clínica ID=1
     * Asigna todos los datos existentes a esta sucursal
     * Incluye validaciones de seguridad
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('  MIGRACIÓN DE PRODUCCIÓN - SUCURSALES');
        $this->command->info('========================================');
        $this->command->newLine();
        
        // ID de la clínica en producción
        $clinicaId = 1;
        
        // Verificar que la clínica existe
        $clinica = Clinica::find($clinicaId);
        
        if (!$clinica) {
            $this->command->error("❌ ERROR: No se encontró la clínica con ID {$clinicaId}");
            return;
        }
        
        $this->command->info("✓ Clínica encontrada: {$clinica->nombre}");
        
        // Verificar si ya tiene sucursal
        $sucursalExistente = Sucursal::where('clinica_id', $clinicaId)->first();
        
        if ($sucursalExistente) {
            $this->command->warn("⚠️  La clínica ya tiene una sucursal: {$sucursalExistente->nombre}");
            $this->command->info("   Código: {$sucursalExistente->codigo}");
            
            // Preguntar si desea continuar
            if (!$this->command->confirm('¿Desea verificar y actualizar asignaciones?', true)) {
                $this->command->info('Operación cancelada.');
                return;
            }
            
            $sucursal = $sucursalExistente;
        } else {
            // Crear sucursal principal
            $this->command->info('📍 Creando sucursal principal...');
            
            $sucursal = Sucursal::create([
                'clinica_id' => $clinica->id,
                'nombre' => $clinica->nombre . ' - Principal',
                'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
                'direccion' => $clinica->direccion ?? null,
                'telefono' => $clinica->telefono ?? null,
                'email' => $clinica->email ?? null,
                'es_principal' => true,
                'activa' => true,
                'notas' => 'Sucursal principal creada durante migración a producción - ' . now()->format('Y-m-d H:i:s')
            ]);
            
            $this->command->info("✓ Sucursal creada exitosamente");
            $this->command->info("   Nombre: {$sucursal->nombre}");
            $this->command->info("   Código: {$sucursal->codigo}");
        }
        
        $this->command->newLine();
        $this->command->info('👥 Asignando usuarios a la sucursal...');
        
        // Contar usuarios antes
        $usuariosTotales = User::where('clinica_id', $clinicaId)->count();
        $usuariosSinAsignar = User::where('clinica_id', $clinicaId)->whereNull('sucursal_id')->count();
        
        $this->command->info("   Total usuarios: {$usuariosTotales}");
        $this->command->info("   Sin asignar: {$usuariosSinAsignar}");
        
        // Asignar usuarios
        $usuariosActualizados = User::where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursal->id]);
        
        $this->command->info("   ✓ Usuarios asignados: {$usuariosActualizados}");
        
        $this->command->newLine();
        $this->command->info('🏥 Asignando pacientes a la sucursal...');
        
        // Contar vínculos clinica_paciente antes
        $pacientesTotales = DB::table('clinica_paciente')->where('clinica_id', $clinicaId)->count();
        $pacientesSinAsignar = DB::table('clinica_paciente')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->count();
        
        $this->command->info("   Total pacientes: {$pacientesTotales}");
        $this->command->info("   Sin asignar: {$pacientesSinAsignar}");
        
        // Asignar sucursal en clinica_paciente y sincronizar columna legacy en pacientes
        $pacientesActualizados = DB::table('clinica_paciente')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursal->id, 'updated_at' => now()]);

        Paciente::whereIn('id', function ($q) use ($clinicaId, $sucursal) {
            $q->select('paciente_id')->from('clinica_paciente')
                ->where('clinica_id', $clinicaId)
                ->where('sucursal_id', $sucursal->id);
        })->whereNull('sucursal_id')->update(['sucursal_id' => $sucursal->id]);
        
        $this->command->info("   ✓ Pacientes asignados: {$pacientesActualizados}");
        
        $this->command->newLine();
        $this->command->info('📋 Asignando expedientes y citas a la sucursal...');
        
        // Asignar todos los expedientes y citas
        $contadores = $this->asignarExpedientesYCitas($clinicaId, $sucursal->id);
        
        $this->command->newLine();
        $this->command->info('📊 RESUMEN DE ASIGNACIONES:');
        $this->command->info('----------------------------');
        
        foreach ($contadores as $tabla => $cantidad) {
            if ($cantidad > 0) {
                $this->command->info("   ✓ {$tabla}: {$cantidad}");
            }
        }
        
        $this->command->newLine();
        $this->command->info('🔍 VERIFICACIÓN FINAL:');
        $this->command->info('----------------------------');
        
        // Verificar que no queden registros sin asignar
        $usuariosSinAsignar = User::where('clinica_id', $clinicaId)->whereNull('sucursal_id')->count();
        $pacientesSinAsignar = DB::table('clinica_paciente')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->count();
        
        if ($usuariosSinAsignar === 0 && $pacientesSinAsignar === 0) {
            $this->command->info("   ✓ Todos los usuarios asignados correctamente");
            $this->command->info("   ✓ Todos los pacientes asignados correctamente");
        } else {
            $this->command->warn("   ⚠️  Usuarios sin asignar: {$usuariosSinAsignar}");
            $this->command->warn("   ⚠️  Pacientes sin asignar: {$pacientesSinAsignar}");
        }
        
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ MIGRACIÓN COMPLETADA EXITOSAMENTE');
        $this->command->info('========================================');
        
        // Log de la migración
        Log::info('Migración de sucursales completada', [
            'clinica_id' => $clinicaId,
            'sucursal_id' => $sucursal->id,
            'usuarios' => $usuariosActualizados,
            'pacientes' => $pacientesActualizados,
            'expedientes' => array_sum($contadores)
        ]);
    }
    
    /**
     * Asigna todos los expedientes de una clínica a su sucursal
     */
    private function asignarExpedientesYCitas($clinicaId, $sucursalId): array
    {
        $contadores = [];
        
        // Citas
        $contadores['citas'] = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
        
        // Expedientes cardiología
        $contadores['clinicos'] = DB::table('clinicos')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['esfuerzos'] = DB::table('esfuerzos')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['estratificacions'] = DB::table('estratificacions')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['reporte_finals'] = DB::table('reporte_finals')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
        
        // Expedientes pulmonares
        $contadores['expediente_pulmonars'] = DB::table('expediente_pulmonars')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['prueba_esfuerzo_pulmonars'] = DB::table('prueba_esfuerzo_pulmonars')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['reporte_final_pulmonars'] = DB::table('reporte_final_pulmonars')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
        
        // Expedientes fisioterapia
        $contadores['historia_clinica_fisioterapias'] = DB::table('historia_clinica_fisioterapias')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['nota_evolucion_fisioterapias'] = DB::table('nota_evolucion_fisioterapias')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['nota_alta_fisioterapias'] = DB::table('nota_alta_fisioterapias')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
        
        // Otros expedientes
        $contadores['reporte_fisios'] = DB::table('reporte_fisios')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['reporte_psicolos'] = DB::table('reporte_psicolos')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['reporte_nutris'] = DB::table('reporte_nutris')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
            
        $contadores['cualidad_fisicas'] = DB::table('cualidad_fisicas')
            ->where('clinica_id', $clinicaId)
            ->whereNull('sucursal_id')
            ->update(['sucursal_id' => $sucursalId]);
        
        return $contadores;
    }
}
