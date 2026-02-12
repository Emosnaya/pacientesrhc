<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\HistoriaClinicaDental;
use App\Models\Odontograma;
use App\Models\Pago;
use App\Models\Receta;
use App\Models\RecetaMedicamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CargarSucursalesDemoDental extends Command
{
    protected const DEMO_CLINICA_EMAIL = 'demo-dental@demo.pacientesrhc';

    protected $signature = 'demo:cargar-sucursales-dental
                            {--por-sucursal=10 : Número de pacientes a crear por sucursal}';
    protected $description = 'Por cada sucursal de la clínica demo dental: agrega N pacientes (por defecto 10) con historia clínica, odontograma, pagos y 2 recetas cada uno';

    public function handle(): int
    {
        $porSucursal = (int) $this->option('por-sucursal');
        if ($porSucursal < 1) {
            $porSucursal = 10;
        }

        $this->info('Buscando clínica demo dental...');
        $clinica = Clinica::where('email', self::DEMO_CLINICA_EMAIL)->first();
        if (!$clinica) {
            $this->warn('No existe la clínica demo dental (email: ' . self::DEMO_CLINICA_EMAIL . ').');
            return self::FAILURE;
        }

        $sucursales = $clinica->sucursales()->get();
        if ($sucursales->isEmpty()) {
            $this->warn('La clínica no tiene sucursales.');
            return self::FAILURE;
        }

        $doctor = $clinica->users()->where('rol', 'doctor')->first();
        $admin = $clinica->users()->where('isAdmin', true)->first();
        if (!$doctor || !$admin) {
            $this->warn('Faltan usuario doctor o admin en la clínica demo.');
            return self::FAILURE;
        }

        $nombreDoctor = $doctor->nombre . ' ' . $doctor->apellidoPat . ' ' . $doctor->apellidoMat;
        $fechaDemo = now()->subDays(5)->format('Y-m-d');
        $nombres = [
            ['María', 'Hernández', 'Sánchez'], ['Juan', 'López', 'Martínez'], ['Ana', 'González', 'Ramírez'],
            ['Pedro', 'Martínez', 'Flores'], ['Laura', 'Díaz', 'Torres'], ['Miguel', 'Sánchez', 'Ruiz'],
            ['Carmen', 'Ruiz', 'Vázquez'], ['Diego', 'Morales', 'Jiménez'], ['Sofía', 'Ortiz', 'Castro'],
            ['Ricardo', 'Castro', 'Mendoza'], ['Elena', 'Flores', 'Ríos'], ['Jorge', 'Ramírez', 'Luna'],
        ];
        $colores = ['#4ECDC4', '#FF6B6B', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'];

        $totalPacientes = 0;
        $totalPagos = 0;
        $totalRecetas = 0;

        foreach ($sucursales as $sucursal) {
            $existentes = Paciente::where('sucursal_id', $sucursal->id)->count();
            $aCrear = $porSucursal;
            if ($existentes >= $porSucursal) {
                $this->line('  Sucursal «' . $sucursal->nombre . '» ya tiene ' . $existentes . ' pacientes. Omitiendo.');
                continue;
            }
            $aCrear = $porSucursal - $existentes;
            $baseRegistro = (int) Paciente::where('clinica_id', $clinica->id)->max(DB::raw('CAST(registro AS UNSIGNED)')) ?: 1000;
            $this->info('Sucursal: ' . $sucursal->nombre . ' (ID ' . $sucursal->id . ') – creando ' . $aCrear . ' pacientes...');

            for ($i = 0; $i < $aCrear; $i++) {
                $idx = ($totalPacientes + $i) % count($nombres);
                $n = $nombres[$idx];
                $fechaNac = Carbon::now()->subYears(30 + ($idx % 40));
                $registro = (string) ($baseRegistro + $i + 1);

                $paciente = Paciente::create([
                    'registro' => $registro,
                    'nombre' => $n[0],
                    'apellidoPat' => $n[1],
                    'apellidoMat' => $n[2],
                    'telefono' => '55 ' . str_pad($totalPacientes + $i + 1, 4, '0', STR_PAD_LEFT) . ' 0000',
                    'email' => strtolower($n[0]) . '.' . strtolower($n[1]) . '-' . $sucursal->id . '@ejemplo.com',
                    'fechaNacimiento' => $fechaNac,
                    'edad' => $fechaNac->age,
                    'genero' => $idx % 2,
                    'domicilio' => 'Calle Demo ' . ($totalPacientes + $i + 1) . ', Col. ' . $sucursal->nombre,
                    'motivo_consulta' => 'Revisión dental / Limpieza',
                    'tipo_paciente' => $idx % 3 === 0 ? 'ortodoncia' : 'general',
                    'user_id' => $doctor->id,
                    'clinica_id' => $clinica->id,
                    'sucursal_id' => $sucursal->id,
                    'color' => $colores[($totalPacientes + $i) % count($colores)],
                ]);

                $historia = HistoriaClinicaDental::create([
                    'paciente_id' => $paciente->id,
                    'sucursal_id' => $sucursal->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fechaDemo,
                    'nombre_doctor' => $nombreDoctor,
                    'motivo_consulta' => 'Revisión dental / Limpieza - demo sucursal',
                    'alergias' => null,
                ]);

                Odontograma::create([
                    'paciente_id' => $paciente->id,
                    'sucursal_id' => $sucursal->id,
                    'historia_clinica_dental_id' => $historia->id,
                    'fecha' => $fechaDemo,
                    'dientes' => Odontograma::inicializarDientes(),
                ]);

                for ($r = 0; $r < 2; $r++) {
                    $folio = 'REC-' . $clinica->id . '-' . str_pad($sucursal->id, 2, '0', STR_PAD_LEFT) . '-' . str_pad($paciente->id . '-' . $r, 6, '0', STR_PAD_LEFT);
                    $receta = Receta::create([
                        'folio' => $folio,
                        'paciente_id' => $paciente->id,
                        'user_id' => $doctor->id,
                        'sucursal_id' => $sucursal->id,
                        'clinica_id' => $clinica->id,
                        'fecha' => Carbon::now()->subDays(10 - $r * 5)->format('Y-m-d'),
                        'diagnostico_principal' => 'Caries dental / Gingivitis leve',
                        'indicaciones_generales' => 'Higiene bucal tres veces al día. Control en 30 días.',
                    ]);
                    RecetaMedicamento::create([
                        'receta_id' => $receta->id,
                        'medicamento' => $r === 0 ? 'Amoxicilina 500 mg' : 'Ibuprofeno 400 mg',
                        'presentacion' => 'Cápsulas / Tabletas',
                        'dosis' => $r === 0 ? '1 cápsula' : '1 tableta',
                        'frecuencia' => $r === 0 ? 'Cada 8 horas' : 'Cada 8 horas si hay dolor',
                        'duracion' => $r === 0 ? '7 días' : '3-5 días',
                        'indicaciones_especificas' => 'Tomar con alimentos. Demo.',
                        'orden' => 1,
                    ]);
                    $totalRecetas++;
                }

                Pago::create([
                    'paciente_id' => $paciente->id,
                    'clinica_id' => $clinica->id,
                    'sucursal_id' => $sucursal->id,
                    'user_id' => $admin->id,
                    'monto' => [450.00, 350.00, 500.00, 280.00, 400.00][$i % 5],
                    'metodo_pago' => ['efectivo', 'tarjeta', 'efectivo'][$i % 3],
                    'concepto' => 'Limpieza / Revisión - demo',
                    'notas' => 'Pago demo sucursal ' . $sucursal->nombre,
                    'firma_paciente' => null,
                ]);
                $totalPagos++;
            }
            $totalPacientes += $aCrear;
        }

        $this->info('  ✓ Pacientes creados (total en esta ejecución): ' . $totalPacientes);
        $this->info('  ✓ Historias clínicas dentales y odontogramas: ' . $totalPacientes);
        $this->info('  ✓ Recetas (2 por paciente): ' . $totalRecetas);
        $this->info('  ✓ Pagos: ' . $totalPagos);
        $this->newLine();
        $this->info('Listo. Revise por sucursal en la app.');
        return self::SUCCESS;
    }
}
