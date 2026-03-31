<?php

namespace App\Console\Commands;

use App\Models\Paciente;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\ReporteFisio;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateWeeklyInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insights:weekly 
                            {--clinica= : ID de la clínica específica (opcional)}
                            {--email= : Email para enviar el reporte (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera reportes semanales automáticos con insights de IA para cada clínica';

    protected $aiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Iniciando generación de reportes semanales...');
        
        $clinicaId = $this->option('clinica');
        $email = $this->option('email');

        try {
            // Si se especificó una clínica, solo procesar esa
            if ($clinicaId) {
                $this->processClinica($clinicaId, $email);
            } else {
                // Procesar todas las clínicas
                $clinicas = \App\Models\Clinica::all();
                
                $this->info("📋 Procesando {$clinicas->count()} clínicas...");
                
                foreach ($clinicas as $clinica) {
                    $this->processClinica($clinica->id);
                }
            }

            $this->info('✅ Reportes generados exitosamente');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error al generar reportes: ' . $e->getMessage());
            Log::error('Error en comando insights:weekly: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Procesar una clínica específica
     */
    protected function processClinica($clinicaId, $emailOverride = null)
    {
        try {
            $this->info("📊 Procesando clínica ID: {$clinicaId}");

            // Obtener pacientes vinculados por clinica_paciente
            $pacientes = Paciente::forClinicaWorkspace((int) $clinicaId)->get()->toArray();

            if (empty($pacientes)) {
                $this->warn("⚠️  Clínica {$clinicaId} no tiene pacientes, saltando...");
                return;
            }

            // Obtener reportes de la última semana
            $reportesNutri = ReporteNutri::where('clinica_id', $clinicaId)
                ->whereHas('paciente', fn ($q) => $q->forClinicaWorkspace((int) $clinicaId))
                ->where('created_at', '>=', now()->subWeek())->get()->toArray();

            $reportesPsico = ReportePsico::where('clinica_id', $clinicaId)
                ->whereHas('paciente', fn ($q) => $q->forClinicaWorkspace((int) $clinicaId))
                ->where('created_at', '>=', now()->subWeek())->get()->toArray();

            $reportesFisio = ReporteFisio::where('clinica_id', $clinicaId)
                ->whereHas('paciente', fn ($q) => $q->forClinicaWorkspace((int) $clinicaId))
                ->where('created_at', '>=', now()->subWeek())->get()->toArray();

            $reportes = array_merge($reportesNutri, $reportesPsico, $reportesFisio);

            // Generar insights
            $this->info("🤖 Generando insights con IA...");
            $insights = $this->aiService->generateDashboardInsights($pacientes, $reportes);

            if (!$insights['success']) {
                $this->error("❌ Error al generar insights para clínica {$clinicaId}");
                return;
            }

            // Guardar reporte en logs
            Log::info("📈 Reporte semanal - Clínica {$clinicaId}", [
                'insights' => $insights['insights'],
                'stats' => $insights['stats'],
                'generated_at' => $insights['generated_at']
            ]);

            $this->info("✅ Insights generados para clínica {$clinicaId}");
            
            // Mostrar resumen en consola
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total Pacientes', $insights['stats']['total_pacientes']],
                    ['Pacientes Activos', $insights['stats']['pacientes_activos']],
                    ['Requieren Seguimiento', $insights['stats']['requieren_seguimiento']],
                    ['Mejoras Significativas', $insights['stats']['mejoras_significativas']],
                    ['Reportes Recientes', $insights['stats']['reportes_recientes']],
                ]
            );

            // Enviar por email si se especificó
            if ($emailOverride) {
                $this->sendEmailReport($emailOverride, $insights, $clinicaId);
            } else {
                // Obtener admin de la clínica
                $admin = User::where('clinica_id', $clinicaId)
                    ->where('is_admin', true)
                    ->first();
                
                if ($admin && $admin->email) {
                    $this->sendEmailReport($admin->email, $insights, $clinicaId);
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Error al procesar clínica {$clinicaId}: " . $e->getMessage());
            Log::error("Error procesando clínica {$clinicaId}: " . $e->getMessage());
        }
    }

    /**
     * Enviar reporte por email
     */
    protected function sendEmailReport($email, $insights, $clinicaId)
    {
        try {
            $this->info("📧 Enviando reporte a: {$email}");

            Mail::send('emails.weekly-insights', ['insights' => $insights], function($message) use ($email, $clinicaId) {
                $message->to($email)
                    ->subject("Reporte Semanal - Clínica {$clinicaId} - " . now()->format('d/m/Y'));
            });

            $this->info("✅ Email enviado exitosamente");

        } catch (\Exception $e) {
            $this->warn("⚠️  No se pudo enviar email: " . $e->getMessage());
            Log::warning("No se pudo enviar email de insights: " . $e->getMessage());
        }
    }
}
