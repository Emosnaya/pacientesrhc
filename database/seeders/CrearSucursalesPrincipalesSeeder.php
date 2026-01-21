<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Paciente;

class CrearSucursalesPrincipalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder crea automáticamente una sucursal principal para cada clínica
     * que no tenga sucursales. Además, asigna todos los usuarios y pacientes
     * existentes a esta sucursal principal.
     */
    public function run(): void
    {
        $this->command->info('Creando sucursales principales para clínicas...');
        
        $clinicas = Clinica::doesntHave('sucursales')->get();
        $contadorCreadas = 0;
        
        foreach ($clinicas as $clinica) {
            $this->command->info("Procesando clínica: {$clinica->nombre}");
            
            // Crear sucursal principal
            $sucursal = Sucursal::create([
                'clinica_id' => $clinica->id,
                'nombre' => $clinica->nombre . ' - Principal',
                'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
                'direccion' => $clinica->direccion ?? null,
                'telefono' => $clinica->telefono ?? null,
                'email' => $clinica->email ?? null,
                'ciudad' => $clinica->ciudad ?? null,
                'estado' => $clinica->estado ?? null,
                'es_principal' => true,
                'activa' => true,
                'notas' => 'Sucursal principal creada automáticamente'
            ]);
            
            // Asignar usuarios de esta clínica a la sucursal principal
            $usuariosActualizados = User::where('clinica_id', $clinica->id)
                ->whereNull('sucursal_id')
                ->update(['sucursal_id' => $sucursal->id]);
            
            // Asignar pacientes de esta clínica a la sucursal principal
            $pacientesActualizados = Paciente::where('clinica_id', $clinica->id)
                ->whereNull('sucursal_id')
                ->update(['sucursal_id' => $sucursal->id]);
            
            // Asignar todos los expedientes asociados
            $this->asignarExpedientesASucursal($clinica->id, $sucursal->id);
            
            $contadorCreadas++;
            $this->command->info("  ✓ Sucursal creada: {$sucursal->nombre}");
            $this->command->info("  ✓ Usuarios asignados: {$usuariosActualizados}");
            $this->command->info("  ✓ Pacientes asignados: {$pacientesActualizados}");
        }
        
        $this->command->info("\n✓ Proceso completado. Sucursales creadas: {$contadorCreadas}");
    }
    
    /**
     * Asigna todos los expedientes de una clínica a su sucursal principal
     */
    private function asignarExpedientesASucursal($clinicaId, $sucursalId): void
    {
        // Expedientes cardiología
        \DB::table('clinicos')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('esfuerzos')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('estratificacions')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('reporte_finals')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        
        // Expedientes pulmonares
        \DB::table('expediente_pulmonars')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('prueba_esfuerzo_pulmonars')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('reporte_final_pulmonars')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        
        // Expedientes fisioterapia
        \DB::table('historia_clinica_fisioterapias')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('nota_evolucion_fisioterapias')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('nota_alta_fisioterapias')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        
        // Otros expedientes
        \DB::table('reporte_fisios')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('reporte_psicolos')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('reporte_nutris')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        \DB::table('cualidad_fisicas')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
        
        // Citas
        \DB::table('citas')->where('clinica_id', $clinicaId)->whereNull('sucursal_id')->update(['sucursal_id' => $sucursalId]);
    }
}
