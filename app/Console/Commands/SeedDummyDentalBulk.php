<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\HistoriaClinicaDental;
use App\Models\Odontograma;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Inserta datos dummy de pacientes dentales con historia clínica y odontograma.
 * Usa Eloquent para que la encriptación (APP_KEY) se aplique correctamente y no se rompa producción.
 * No elimina ni modifica datos existentes.
 */
class SeedDummyDentalBulk extends Command
{
    protected $signature = 'demo:seed-dummy-dental-bulk
                            {clinica_id : ID de la clínica (ej. 4)}
                            {--sucursales=all : IDs de sucursales separados por coma, o "all"}
                            {--total=30 : Número total de pacientes a crear (repartidos entre sucursales)}';

    protected $description = 'Inserta pacientes dummy con historia clínica dental y odontograma por sucursal (seguro para prod, usa encriptación)';

    public function handle(): int
    {
        $clinicaId = (int) $this->argument('clinica_id');
        $total = (int) $this->option('total');
        $sucursalesOpt = $this->option('sucursales');

        $clinica = Clinica::find($clinicaId);
        if (!$clinica) {
            $this->error('Clínica no encontrada con ID: ' . $clinicaId);
            return self::FAILURE;
        }

        $sucursales = $sucursalesOpt === 'all'
            ? $clinica->sucursales()->where('activa', true)->get()
            : $clinica->sucursales()->whereIn('id', array_map('intval', explode(',', $sucursalesOpt)))->get();

        if ($sucursales->isEmpty()) {
            $this->error('No hay sucursales válidas para esta clínica.');
            return self::FAILURE;
        }

        $doctor = $clinica->users()->whereIn('rol', ['doctor', 'doctora'])->first();
        if (!$doctor) {
            $this->error('No hay usuario doctor/doctora en la clínica.');
            return self::FAILURE;
        }

        $nombreDoctor = trim($doctor->nombre . ' ' . $doctor->apellidoPat . ' ' . $doctor->apellidoMat);
        $fechaDemo = now()->subDays(rand(1, 30))->format('Y-m-d');

        $nombres = [
            ['Guadalupe', 'Reyes', 'Soto'], ['Fernando', 'Mendoza', 'Cruz'], ['Adriana', 'Vargas', 'Luna'],
            ['Roberto', 'Sánchez', 'López'], ['Laura', 'Gutiérrez', 'Morales'], ['Miguel', 'Torres', 'Ríos'],
            ['Carmen', 'Flores', 'Díaz'], ['Diego', 'Hernández', 'Ortiz'], ['Sofía', 'Ramírez', 'Castro'],
            ['Ricardo', 'Jiménez', 'Mendoza'], ['Elena', 'Ruiz', 'Vega'], ['Jorge', 'Castro', 'Núñez'],
            ['Patricia', 'Ortiz', 'Santiago'], ['Luis', 'Martínez', 'Guerrero'], ['Ana', 'Díaz', 'Ramos'],
        ];
        $colores = ['#4ECDC4', '#FF6B6B', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'];
        $tipos = ['general', 'ortodoncia', 'general', 'endodoncia', 'general'];

        $maxRegistro = (int) Paciente::where('clinica_id', $clinicaId)->max(DB::raw('CAST(registro AS UNSIGNED)'));
        $baseRegistro = $maxRegistro ?: 1000;

        $this->info('Clínica: ' . $clinica->nombre . ' | Sucursales: ' . $sucursales->pluck('nombre')->join(', '));
        $this->info('Creando ' . $total . ' pacientes con historia clínica dental y odontograma...');

        $creados = 0;
        $sucursalIds = $sucursales->pluck('id')->toArray();

        for ($i = 0; $i < $total; $i++) {
            $sucursal = $sucursales[$i % $sucursales->count()];
            $idx = $i % count($nombres);
            $n = $nombres[$idx];
            $fechaNac = Carbon::now()->subYears(25 + ($i % 50));

            $paciente = Paciente::create([
                'registro' => (string) ($baseRegistro + $i + 1),
                'nombre' => $n[0],
                'apellidoPat' => $n[1],
                'apellidoMat' => $n[2],
                'telefono' => '55 ' . str_pad(1000 + $i, 4, '0', STR_PAD_LEFT) . ' ' . str_pad(2000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => strtolower($n[0]) . '.' . strtolower($n[1]) . '.dummy' . $i . '@ejemplo.com',
                'fechaNacimiento' => $fechaNac,
                'edad' => $fechaNac->age,
                'genero' => $idx % 2,
                'domicilio' => 'Calle Dummy ' . ($i + 1) . ', Col. ' . substr($sucursal->nombre, 0, 20),
                'motivo_consulta' => $i % 3 === 0 ? 'Revisión y limpieza' : ($i % 3 === 1 ? 'Dolor molar' : 'Ortodoncia - control'),
                'alergias' => $i % 7 === 2 ? 'Penicilina' : null,
                'tipo_paciente' => $tipos[$i % count($tipos)],
                'user_id' => $doctor->id,
                'clinica_id' => $clinicaId,
                'sucursal_id' => $sucursal->id,
                'color' => $colores[$i % count($colores)],
            ]);

            $historia = HistoriaClinicaDental::create([
                'paciente_id' => $paciente->id,
                'sucursal_id' => $sucursal->id,
                'user_id' => $doctor->id,
                'fecha' => $fechaDemo,
                'nombre_doctor' => $nombreDoctor,
                'alergias' => $paciente->alergias,
                'motivo_consulta' => 'Revisión dental. Datos dummy para pruebas.',
                'historia_enfermedad' => 'Paciente sano. Última revisión hace más de 6 meses. Sin antecedentes relevantes.',
                'veces_cepilla_dia' => 2,
                'higienizacion_metodo' => 'Cepillo manual',
                'af_hipertension' => $i % 5 === 0,
                'ip_diabetes' => false,
                'ao_limpieza_6meses' => $i % 4 !== 0,
                'ao_cepilla_dientes' => true,
                'sv_ta' => '120/80',
                'sv_pulso' => '72',
                'sv_fc' => '72',
                'sv_peso' => '70',
                'sv_altura' => '165',
                'etb_carrillos' => 'Sin lesiones',
                'etb_encias' => 'Encías normales, ligero sangrado en 36.',
                'etb_lengua' => 'Normal',
                'etb_paladar' => 'Normal',
                'etb_atm' => 'Sin datos',
                'etb_labios' => 'Normales',
            ]);

            Odontograma::create([
                'paciente_id' => $paciente->id,
                'sucursal_id' => $sucursal->id,
                'historia_clinica_dental_id' => $historia->id,
                'fecha' => $fechaDemo,
                'dientes' => Odontograma::inicializarDientes(),
                'diagnostico' => 'Buen estado general. Limpieza indicada. Sin caries activas.',
                'pronostico' => 'Favorable con higiene adecuada.',
                'observaciones' => 'Paciente dummy. Control en 6 meses.',
            ]);

            $creados++;
            if ($creados % 10 === 0) {
                $this->line('  ' . $creados . ' pacientes creados...');
            }
        }

        $this->info('  ✓ ' . $creados . ' pacientes creados con historia clínica dental y odontograma.');
        $this->info('  Repartidos en ' . $sucursales->count() . ' sucursal(es). No se modificó ni eliminó ningún dato existente.');
        return self::SUCCESS;
    }
}
