<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinica;
use App\Models\HistoriaClinicaDental;
use App\Models\Odontograma;

class CompletarDemoDental extends Command
{
    protected const DEMO_CLINICA_EMAIL = 'demo-dental@demo.pacientesrhc';

    protected $signature = 'demo:completar-dental';
    protected $description = 'Añade historia clínica dental y odontograma a los pacientes del demo dental que aún no los tengan (para demos creados antes de incluir estos expedientes)';

    public function handle(): int
    {
        $this->info('Buscando clínica demo dental...');

        $clinica = Clinica::where('email', self::DEMO_CLINICA_EMAIL)->first();
        if (!$clinica) {
            $this->warn('No existe la clínica demo dental. Ejecute primero: php artisan demo:create-dental');
            return self::FAILURE;
        }

        $sucursal = $clinica->sucursales()->where('es_principal', true)->first()
            ?? $clinica->sucursales()->first();
        if (!$sucursal) {
            $this->warn('La clínica demo no tiene sucursal.');
            return self::FAILURE;
        }

        $doctor = $clinica->users()->where('rol', 'doctor')->first();
        if (!$doctor) {
            $this->warn('No hay usuario doctor en la clínica demo.');
            return self::FAILURE;
        }

        $pacientes = $clinica->pacientes()->get();
        if ($pacientes->isEmpty()) {
            $this->info('No hay pacientes en el demo dental.');
            return self::SUCCESS;
        }

        $fechaDemo = now()->subDays(5)->format('Y-m-d');
        $nombreDoctor = $doctor->nombre . ' ' . $doctor->apellidoPat . ' ' . $doctor->apellidoMat;
        $creadasHistoria = 0;
        $creadosOdontograma = 0;

        foreach ($pacientes as $paciente) {
            $historia = HistoriaClinicaDental::where('paciente_id', $paciente->id)->first();

            if (!$historia) {
                $historia = HistoriaClinicaDental::create([
                    'paciente_id' => $paciente->id,
                    'sucursal_id' => $sucursal->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fechaDemo,
                    'nombre_doctor' => $nombreDoctor,
                    'motivo_consulta' => 'Revisión dental / Limpieza - demo',
                    'alergias' => $paciente->alergias,
                ]);
                $creadasHistoria++;
            }

            $tieneOdontograma = Odontograma::where('paciente_id', $paciente->id)->exists();
            if (!$tieneOdontograma) {
                Odontograma::create([
                    'paciente_id' => $paciente->id,
                    'sucursal_id' => $sucursal->id,
                    'historia_clinica_dental_id' => $historia->id,
                    'fecha' => $fechaDemo,
                    'dientes' => Odontograma::inicializarDientes(),
                ]);
                $creadosOdontograma++;
            }
        }

        $this->info('  ✓ Historias clínicas dentales creadas: ' . $creadasHistoria);
        $this->info('  ✓ Odontogramas creados: ' . $creadosOdontograma);
        $this->newLine();
        $this->info('Demo dental completado. Puede revisar pacientes en la app.');
        return self::SUCCESS;
    }
}
