<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportBulkData extends Command
{
    protected $signature = 'import:bulk 
                          {--rollback : Revertir la importación anterior}
                          {--verify : Solo verificar sin importar}';

    protected $description = 'Importa datos bulk desde archivos SQL en orden: pacientes, clínicos, esfuerzos, estratificaciones';

    private $files = [
        'import_bulk.sql',
        'import_esfuerzos.sql',
        'import_estratificaciones2.sql'
    ];

    private $baseDir;

    public function __construct()
    {
        parent::__construct();
        $this->baseDir = base_path('database/imports');
    }

    public function handle()
    {
        if ($this->option('verify')) {
            return $this->verify();
        }

        if ($this->option('rollback')) {
            return $this->rollback();
        }

        $this->import();
    }

    private function import()
    {
        $this->info('🚀 Iniciando importación de datos bulk...');
        $this->newLine();

        // Verificar que existen los archivos
        foreach ($this->files as $file) {
            $path = "{$this->baseDir}/{$file}";
            if (!File::exists($path)) {
                $this->error("❌ No se encuentra el archivo: {$file}");
                $this->error("   Asegúrate de que esté en: {$this->baseDir}");
                return 1;
            }
        }

        // Crear backup antes de importar
        $this->info('📦 Creando backup de seguridad...');
        $backupId = $this->createBackup();
        $this->info("   Backup ID: {$backupId}");
        $this->newLine();

        // Confirmar
        if (!$this->confirm('¿Proceder con la importación? (Usará transacciones, puede revertirse)')) {
            $this->warn('Importación cancelada');
            return 0;
        }

        DB::beginTransaction();

        try {
            foreach ($this->files as $index => $file) {
                $step = $index + 1;
                $this->info("📝 Paso {$step}/3: Importando {$file}...");
                
                $path = "{$this->baseDir}/{$file}";
                $sql = File::get($path);
                
                // Ejecutar el SQL
                DB::unprepared($sql);
                
                $this->info("   ✅ Completado");
                $this->newLine();
            }

            // Verificar resultados
            $this->info('🔍 Verificando importación...');
            $results = $this->getVerificationResults();
            
            $this->table(
                ['Tabla', 'Total', 'Esperado', 'Estado'],
                $results['data']
            );
            $this->newLine();

            if ($results['success']) {
                if ($this->confirm('✅ Verificación exitosa. ¿Confirmar los cambios?', true)) {
                    DB::commit();
                    $this->info('🎉 Importación completada exitosamente');
                    $this->info("   Backup guardado con ID: {$backupId}");
                    $this->info('   Para revertir: php artisan import:bulk --rollback');
                    return 0;
                } else {
                    DB::rollBack();
                    $this->warn('⚠️  Cambios revertidos (rollback)');
                    return 0;
                }
            } else {
                DB::rollBack();
                $this->error('❌ Verificación falló. Cambios revertidos automáticamente.');
                return 1;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error durante la importación:');
            $this->error('   ' . $e->getMessage());
            $this->newLine();
            $this->warn('⚠️  Los cambios han sido revertidos automáticamente (rollback)');
            $this->info('   Los datos anteriores permanecen intactos');
            return 1;
        }
    }

    private function verify()
    {
        $this->info('🔍 Verificando datos actuales en la base de datos...');
        $this->newLine();

        $results = $this->getVerificationResults();
        
        $this->table(
            ['Tabla', 'Total', 'Esperado', 'Estado'],
            $results['data']
        );

        if ($results['success']) {
            $this->info('✅ Todos los datos están correctos');
            return 0;
        } else {
            $this->warn('⚠️  Algunos totales no coinciden');
            return 1;
        }
    }

    private function getVerificationResults()
    {
        // Leer user_id y clinica_id del primer archivo SQL
        $bulkContent = file_get_contents("{$this->baseDir}/import_bulk.sql");
        preg_match('/SET @user_id = (\d+);/', $bulkContent, $userMatch);
        preg_match('/SET @clinica_id = (\d+);/', $bulkContent, $clinicaMatch);
        
        $userId = $userMatch[1] ?? 1;
        $clinicaId = $clinicaMatch[1] ?? 3;
        
        $checks = [
            [
                'tabla' => 'Pacientes',
                'query' => "SELECT COUNT(*) as total FROM pacientes WHERE user_id={$userId} AND clinica_id={$clinicaId}",
                'esperado' => 136
            ],
            [
                'tabla' => 'Clínicos',
                'query' => "SELECT COUNT(*) as total FROM clinicos WHERE user_id={$userId} AND clinica_id={$clinicaId} AND tipo_exp=3",
                'esperado' => 136
            ],
            [
                'tabla' => 'Esfuerzos',
                'query' => "SELECT COUNT(*) as total FROM esfuerzos WHERE user_id={$userId} AND clinica_id={$clinicaId} AND tipo_exp=1",
                'esperado' => 240
            ],
            [
                'tabla' => 'Estratificaciones',
                'query' => "SELECT COUNT(*) as total FROM estratificacions WHERE user_id={$userId} AND clinica_id={$clinicaId} AND tipo_exp=2",
                'esperado' => 136
            ]
        ];

        $data = [];
        $success = true;

        foreach ($checks as $check) {
            $result = DB::select($check['query']);
            $total = $result[0]->total ?? 0;
            $ok = $total == $check['esperado'];
            
            if (!$ok) {
                $success = false;
            }

            $data[] = [
                $check['tabla'],
                $total,
                $check['esperado'],
                $ok ? '✅' : '❌'
            ];
        }

        return [
            'success' => $success,
            'data' => $data
        ];
    }

    private function createBackup()
    {
        $backupId = date('Y-m-d_H-i-s');
        
        $tables = ['pacientes', 'clinicos', 'esfuerzos', 'estratificacions'];
        
        $backupDir = storage_path("app/backups/{$backupId}");
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        foreach ($tables as $table) {
            // Guardar conteo antes
            $count = DB::table($table)
                ->where('user_id', 3)
                ->where('clinica_id', 1)
                ->count();
            
            File::put(
                "{$backupDir}/{$table}_count.txt",
                "Total: {$count}\nFecha: " . now()->toDateTimeString()
            );

            // Guardar IDs para posible rollback
            $ids = DB::table($table)
                ->where('user_id', 3)
                ->where('clinica_id', 1)
                ->pluck('id')
                ->toArray();
            
            if (!empty($ids)) {
                File::put(
                    "{$backupDir}/{$table}_ids.json",
                    json_encode($ids, JSON_PRETTY_PRINT)
                );
            }
        }

        return $backupId;
    }

    private function rollback()
    {
        $this->warn('⚠️  ROLLBACK: Eliminando datos importados...');
        $this->newLine();

        if (!$this->confirm('Esto eliminará TODOS los registros con user_id=3 y clinica_id=1. ¿Continuar?')) {
            $this->info('Rollback cancelado');
            return 0;
        }

        DB::beginTransaction();

        try {
            $deleted = [
                'estratificacions' => DB::table('estratificacions')->where('user_id', 3)->where('clinica_id', 1)->delete(),
                'esfuerzos' => DB::table('esfuerzos')->where('user_id', 3)->where('clinica_id', 1)->delete(),
                'clinicos' => DB::table('clinicos')->where('user_id', 3)->where('clinica_id', 1)->delete(),
                'pacientes' => DB::table('pacientes')->where('user_id', 3)->where('clinica_id', 1)->delete(),
            ];

            $this->table(
                ['Tabla', 'Registros eliminados'],
                collect($deleted)->map(fn($count, $table) => [$table, $count])->values()->toArray()
            );

            if ($this->confirm('¿Confirmar eliminación?', true)) {
                DB::commit();
                $this->info('✅ Rollback completado');
                return 0;
            } else {
                DB::rollBack();
                $this->warn('Rollback cancelado');
                return 0;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error durante rollback: ' . $e->getMessage());
            return 1;
        }
    }
}
