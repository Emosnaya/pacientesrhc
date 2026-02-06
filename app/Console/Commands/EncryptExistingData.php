<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Paciente;
use App\Models\Receta;
use App\Models\ExpedientePulmonar;
use App\Models\Odontograma;
use App\Models\HistoriaClinicaDental;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaAltaFisioterapia;
use App\Models\ReporteFisio;
use App\Models\Clinico;
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\Esfuerzo;
use App\Models\PruebaEsfuerzoPulmonar;

class EncryptExistingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:encrypt-existing 
                            {--model= : Cifrar solo un modelo específico (ej: Paciente)}
                            {--dry-run : Simular sin hacer cambios reales en la base de datos}
                            {--force : Forzar ejecución sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cifra datos existentes sin cifrar en la base de datos para cumplir con NOM-024';

    /**
     * Estadísticas del proceso
     *
     * @var array
     */
    private $stats = [
        'total' => 0,
        'encrypted' => 0,
        'skipped' => 0,
        'errors' => 0
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirmación de seguridad
        if (!$this->option('force')) {
            $this->warn('⚠️  ADVERTENCIA: Este comando modificará datos en la base de datos.');
            $this->warn('   Asegúrate de tener un backup completo antes de continuar.');
            $this->newLine();
            
            if (!$this->confirm('¿Deseas continuar?', false)) {
                $this->error('Operación cancelada por el usuario.');
                return 1;
            }
        }

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('🔍 MODO DRY-RUN: No se harán cambios reales en la base de datos');
            $this->newLine();
        }

        $this->info('🔐 Iniciando proceso de cifrado de datos existentes...');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Definir modelos y sus campos a cifrar
        $modelsToEncrypt = $this->getModelsConfiguration();

        // Filtrar por modelo específico si se especificó
        $specificModel = $this->option('model');
        if ($specificModel) {
            $fullModelClass = "App\\Models\\{$specificModel}";
            if (!isset($modelsToEncrypt[$fullModelClass])) {
                $this->error("❌ Modelo '{$specificModel}' no encontrado.");
                $this->info('Modelos disponibles: ' . implode(', ', array_map('class_basename', array_keys($modelsToEncrypt))));
                return 1;
            }
            $modelsToEncrypt = [$fullModelClass => $modelsToEncrypt[$fullModelClass]];
            $this->info("Procesando solo: {$specificModel}");
            $this->newLine();
        }

        // Procesar cada modelo
        foreach ($modelsToEncrypt as $modelClass => $fields) {
            $this->encryptModelData($modelClass, $fields, $isDryRun);
        }

        // Mostrar resumen final
        $this->displaySummary();

        return 0;
    }

    /**
     * Configuración de modelos y campos a cifrar
     */
    private function getModelsConfiguration(): array
    {
        return [
            Paciente::class => [
                'nombre', 'apellidoPat', 'apellidoMat', 'telefono', 
                'email', 'domicilio', 'diagnostico', 'medicamentos'
            ],
            Receta::class => [
                'diagnostico_principal', 'indicaciones_generales'
            ],
            ExpedientePulmonar::class => [
                'antecedentes_heredo_familiares', 'antecedentes_alergicos', 
                'antecedentes_quirurgicos', 'antecedentes_traumaticos', 
                'antecedentes_exposicionales', 'tabaquismo_detalle',
                'alcoholismo_detalle', 'toxicomanias_detalle', 
                'diagnosticos_finales', 'plan_tratamiento', 'motivo_envio'
            ],
            Odontograma::class => [
                'diagnostico', 'pronostico', 'observaciones',
                'ap_calculo_supragingival_dientes', 'ap_calculo_infragingival_dientes',
                'ap_movilidad_dental_dientes', 'ap_bolsas_periodontales_dientes',
                'ap_pseudobolsas_dientes', 'ap_indice_placa_dientes',
                'ae_endo_defectuosa_dientes', 'ae_necrosis_pulpar_dientes',
                'ae_pulpitis_irreversible_dientes', 'ae_lesiones_periapicales_dientes'
            ],
            HistoriaClinicaDental::class => [
                'alergias', 'medicamento_detalle', 'anestesicos_detalle',
                'medicamentos_alergicos_detalle', 'historia_enfermedad', 'motivo_consulta',
                'at_fuma_detalle', 'at_drogas_detalle', 'at_toma_detalle'
            ],
            HistoriaClinicaFisioterapia::class => [
                'motivo_consulta', 'padecimiento_actual', 'antecedentes_heredofamiliares',
                'antecedentes_personales_patologicos', 'antecedentes_personales_no_patologicos',
                'antecedentes_quirurgicos_traumaticos', 'diagnostico_medico',
                'diagnostico_fisioterapeutico', 'objetivos_tratamiento', 'pronostico'
            ],
            NotaEvolucionFisioterapia::class => [
                'diagnostico_fisioterapeutico', 'observaciones_subjetivas', 
                'observaciones_objetivas', 'tecnicas_modalidades_aplicadas', 
                'ejercicio_terapeutico', 'respuesta_tratamiento', 'plan'
            ],
            NotaAltaFisioterapia::class => [
                'diagnostico_medico', 'diagnostico_fisioterapeutico_inicial', 
                'tratamiento_otorgado', 'evolucion_resultados', 'mejoria_funcional', 
                'objetivos_alcanzados', 'estado_funcional_alta', 
                'recomendaciones_seguimiento', 'pronostico_funcional'
            ],
            ReporteFisio::class => ['contenido'],
            Clinico::class => ['contenido'],
            ReporteFinal::class => ['contenido'],
            ReporteNutri::class => ['contenido'],
            Esfuerzo::class => ['contenido'],
            PruebaEsfuerzoPulmonar::class => [
                'interpretacion', 'plan_manejo_complementario'
            ],
        ];
    }

    /**
     * Cifrar datos de un modelo específico
     */
    private function encryptModelData(string $modelClass, array $fields, bool $isDryRun): void
    {
        $modelName = class_basename($modelClass);
        $this->info("📦 Procesando: <fg=cyan>{$modelName}</>");

        try {
            // Obtener el nombre de la tabla
            $model = new $modelClass;
            $tableName = $model->getTable();
            
            // Contar total de registros
            $totalRecords = DB::table($tableName)->count();
            $this->stats['total'] += $totalRecords;

            if ($totalRecords === 0) {
                $this->warn("   ⚠️  Sin registros en la tabla");
                $this->newLine();
                return;
            }

            $this->line("   Total de registros: {$totalRecords}");

            // Crear barra de progreso
            $bar = $this->output->createProgressBar($totalRecords);
            $bar->setFormat('   %current%/%max% [%bar%] %percent:3s%% - %message%');
            $bar->setMessage('Iniciando...');
            $bar->start();

            $encrypted = 0;
            $skipped = 0;
            $errors = 0;

            // Procesar en chunks para no sobrecargar memoria
            DB::table($tableName)->orderBy('id')->chunk(100, function ($records) use (
                $tableName, $fields, $isDryRun, $bar, &$encrypted, &$skipped, &$errors
            ) {
                foreach ($records as $record) {
                    $bar->setMessage("ID: {$record->id}");
                    
                    try {
                        $needsUpdate = false;
                        $updates = [];

                        foreach ($fields as $field) {
                            // Verificar si el campo existe en el registro
                            if (!property_exists($record, $field)) {
                                continue;
                            }

                            $value = $record->$field;

                            // Si el campo es null o vacío, saltar
                            if (is_null($value) || $value === '') {
                                continue;
                            }

                            // Detectar si ya está cifrado
                            if ($this->isAlreadyEncrypted($value)) {
                                continue; // Ya está cifrado, omitir
                            }

                            // Cifrar el valor
                            $encryptedValue = Crypt::encryptString($value);
                            $updates[$field] = $encryptedValue;
                            $needsUpdate = true;
                        }

                        // Actualizar registro si hay cambios
                        if ($needsUpdate) {
                            if (!$isDryRun) {
                                DB::table($tableName)
                                    ->where('id', $record->id)
                                    ->update($updates);
                            }
                            $encrypted++;
                            $this->stats['encrypted']++;
                        } else {
                            $skipped++;
                            $this->stats['skipped']++;
                        }

                    } catch (\Exception $e) {
                        $errors++;
                        $this->stats['errors']++;
                        $this->newLine();
                        $this->error("   ❌ Error en ID {$record->id}: {$e->getMessage()}");
                    }

                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();

            // Resumen del modelo
            $this->line("   <fg=green>✅ Cifrados: {$encrypted}</>");
            $this->line("   <fg=yellow>⏭️  Omitidos: {$skipped}</>");
            if ($errors > 0) {
                $this->line("   <fg=red>❌ Errores: {$errors}</>");
            }
            $this->newLine();

        } catch (\Exception $e) {
            $this->error("   ❌ Error procesando {$modelName}: {$e->getMessage()}");
            $this->newLine();
        }
    }

    /**
     * Detectar si un valor ya está cifrado
     */
    private function isAlreadyEncrypted(string $value): bool
    {
        // Laravel Encrypter guarda en formato JSON con iv, value, mac
        // Formato: eyJpdiI6IlhYWCIsInZhbHVlIjoiWVlZIiwibWFjIjoiWlpaIn0=
        
        // Si es muy corto, probablemente no está cifrado
        if (strlen($value) < 50) {
            return false;
        }

        // Intentar decodificar base64
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        // Verificar si es JSON válido
        $payload = json_decode($decoded, true);
        if (!is_array($payload)) {
            return false;
        }

        // Verificar estructura de Laravel Encrypter
        return isset($payload['iv']) 
            && isset($payload['value']) 
            && isset($payload['mac']);
    }

    /**
     * Mostrar resumen final del proceso
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RESUMEN FINAL DEL PROCESO');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("Total de registros procesados: <fg=cyan>{$this->stats['total']}</>");
        $this->line("<fg=green>✅ Registros cifrados exitosamente: {$this->stats['encrypted']}</>");
        $this->line("<fg=yellow>⏭️  Registros omitidos (ya cifrados): {$this->stats['skipped']}</>");
        
        if ($this->stats['errors'] > 0) {
            $this->line("<fg=red>❌ Errores encontrados: {$this->stats['errors']}</>");
            $this->warn('   Revisa los errores anteriores para más detalles.');
        }
        
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 MODO DRY-RUN: Esto fue una simulación.');
            $this->warn('   Ejecuta sin --dry-run para aplicar los cambios reales.');
        } else {
            $this->info('✅ Proceso de cifrado completado exitosamente.');
            $this->info('   Todos los datos sensibles han sido cifrados en la base de datos.');
            $this->newLine();
            $this->line('Próximos pasos:');
            $this->line('1. Verifica que la aplicación funciona correctamente');
            $this->line('2. Genera PDFs de prueba para confirmar que se muestran bien');
            $this->line('3. Revisa los logs de auditoría: php artisan audit:logs --days=1');
        }

        $this->newLine();
    }
}
