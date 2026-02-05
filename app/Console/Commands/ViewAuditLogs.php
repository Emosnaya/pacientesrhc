<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class ViewAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:logs 
                            {--model= : Filtrar por modelo (Paciente, Receta, etc.)}
                            {--evento= : Filtrar por evento (created, updated, deleted, viewed)}
                            {--user= : Filtrar por ID de usuario}
                            {--clinica= : Filtrar por ID de clínica}
                            {--days=7 : Número de días hacia atrás}
                            {--limit=50 : Límite de resultados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta los logs de auditoría del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = AuditLog::query()
            ->with(['user', 'clinica'])
            ->latest();

        // Aplicar filtros
        if ($model = $this->option('model')) {
            $query->where('modelo_afectado', 'LIKE', "%{$model}%");
        }

        if ($evento = $this->option('evento')) {
            $query->where('evento', $evento);
        }

        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        if ($clinicaId = $this->option('clinica')) {
            $query->where('clinica_id', $clinicaId);
        }

        if ($days = $this->option('days')) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $limit = $this->option('limit');
        $logs = $query->limit($limit)->get();

        if ($logs->isEmpty()) {
            $this->info('No se encontraron logs de auditoría con los criterios especificados.');
            return;
        }

        $this->info("Mostrando {$logs->count()} logs de auditoría:");
        $this->newLine();

        $tableData = $logs->map(function ($log) {
            return [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->nombre ?? 'N/A',
                $log->clinica?->nombre ?? 'N/A',
                $log->evento,
                class_basename($log->modelo_afectado),
                $log->id_recurso,
                $log->ip_address,
            ];
        })->toArray();

        $this->table(
            ['ID', 'Fecha', 'Usuario', 'Clínica', 'Evento', 'Modelo', 'ID Recurso', 'IP'],
            $tableData
        );

        // Opción para ver detalles de un log específico
        if ($this->confirm('¿Deseas ver los detalles de algún log específico?', false)) {
            $logId = $this->ask('Ingresa el ID del log');
            $this->showLogDetails($logId);
        }
    }

    /**
     * Muestra los detalles de un log específico
     */
    protected function showLogDetails(int $logId): void
    {
        $log = AuditLog::with(['user', 'clinica'])->find($logId);

        if (!$log) {
            $this->error("No se encontró el log con ID {$logId}");
            return;
        }

        $this->newLine();
        $this->info("=== DETALLES DEL LOG #{$logId} ===");
        $this->newLine();

        $details = [
            ['Campo', 'Valor'],
            ['ID', $log->id],
            ['Fecha/Hora', $log->created_at->format('Y-m-d H:i:s')],
            ['Usuario', $log->user?->nombre ?? 'N/A'],
            ['Clínica', $log->clinica?->nombre ?? 'N/A'],
            ['Evento', $log->evento],
            ['Modelo', $log->modelo_afectado],
            ['ID Recurso', $log->id_recurso],
            ['IP Address', $log->ip_address],
            ['User Agent', $log->user_agent],
            ['Descripción', $log->descripcion],
        ];

        $this->table(['Campo', 'Valor'], $details);

        if ($log->datos_anteriores) {
            $this->newLine();
            $this->info('DATOS ANTERIORES:');
            $this->line(json_encode($log->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        if ($log->datos_nuevos) {
            $this->newLine();
            $this->info('DATOS NUEVOS:');
            $this->line(json_encode($log->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
