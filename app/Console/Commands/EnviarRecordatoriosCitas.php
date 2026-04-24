<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class EnviarRecordatoriosCitas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citas:recordatorios
                            {--dias=1 : Días de anticipación para enviar recordatorios}
                            {--test : Modo prueba - solo muestra las citas sin enviar}
                            {--clinica= : ID de clínica específica (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de WhatsApp para citas programadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!config('services.twilio.whatsapp_enabled')) {
            $this->warn('⚠️  WhatsApp está DESHABILITADO en la configuración');
            $this->info('   Para habilitar, configura WHATSAPP_ENABLED=true en .env');
            return self::FAILURE;
        }

        $dias = (int) $this->option('dias');
        $test = $this->option('test');
        $clinicaId = $this->option('clinica');
        $fechaObjetivo = now()->addDays($dias)->format('Y-m-d');
        
        $this->info('========================================');
        $this->info('  RECORDATORIOS DE CITAS VÍA WHATSAPP');
        $this->info('========================================');
        $this->newLine();
        $this->info("📅 Buscando citas para: {$fechaObjetivo} ({$dias} día(s) de anticipación)");
        if ($test) {
            $this->warn('🔬 MODO PRUEBA - No se enviarán mensajes reales');
        }
        $this->newLine();
        
        // Construir query
        $query = Cita::where('fecha', $fechaObjetivo)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function($q) {
                $q->where('recordatorio_enviado', false)
                  ->orWhereNull('recordatorio_enviado');
            })
            ->with(['paciente', 'user', 'clinica', 'sucursal']);
            
        if ($clinicaId) {
            $query->where('clinica_id', $clinicaId);
            $this->info("🏥 Filtrando por clínica ID: {$clinicaId}");
        }
            
        $citas = $query->get();
            
        $this->info("✓ Encontradas: {$citas->count()} citas");
        $this->newLine();
        
        if ($citas->isEmpty()) {
            $this->info('✓ No hay citas pendientes para enviar recordatorios');
            return self::SUCCESS;
        }
        
        $whatsapp = new WhatsAppService();
        $enviados = 0;
        $errores = 0;
        $sinTelefono = 0;
        
        $this->info('Procesando citas:');
        $this->newLine();
        
        foreach ($citas as $cita) {
            $paciente = $cita->paciente;
            $hora = Carbon::parse($cita->hora)->format('H:i');
            $doctor = $cita->user->nombre . ' ' . $cita->user->apellidoPat;
            
            if (!$paciente->telefono) {
                $this->warn("  ⚠️  {$paciente->nombre} {$paciente->apellidoPat} - SIN TELÉFONO");
                $sinTelefono++;
                continue;
            }
            
            if ($test) {
                $this->line("  📱 {$paciente->nombre} {$paciente->apellidoPat}");
                $this->line("     Tel: {$paciente->telefono} | Hora: {$hora} | Dr(a). {$doctor}");
                $this->line("     Clínica: {$cita->clinica->nombre} | Sucursal: {$cita->sucursal->nombre}");
                $enviados++;
            } else {
                if ($whatsapp->enviarRecordatorioCita($cita)) {
                    $this->info("  ✅ {$paciente->nombre} {$paciente->apellidoPat} - {$paciente->telefono}");
                    $enviados++;
                    
                    // Pequeña pausa para no saturar la API
                    usleep(500000); // 0.5 segundos
                } else {
                    $this->error("  ❌ {$paciente->nombre} {$paciente->apellidoPat} - ERROR al enviar");
                    $errores++;
                }
            }
        }
        
        $this->newLine();
        $this->info('========================================');
        $this->info('  RESUMEN');
        $this->info('========================================');
        $this->table(
            ['Concepto', 'Cantidad'],
            [
                ['Total citas', $citas->count()],
                [$test ? 'Simulados' : 'Enviados', $enviados],
                ['Errores', $errores],
                ['Sin teléfono', $sinTelefono],
            ]
        );
        
        if (!$test && $enviados > 0) {
            $this->info('✓ Recordatorios enviados exitosamente');
        }
        
        return self::SUCCESS;
    }
}
