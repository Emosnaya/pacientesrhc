<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\HistoriaClinicaDental;
use App\Models\Odontograma;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CreateDemoDentalClinica extends Command
{
    /**
     * Identificador del demo para no duplicar en producción.
     */
    protected const DEMO_CLINICA_EMAIL = 'demo-dental@demo.pacientesrhc';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:create-dental
                            {--force : Si la clínica demo ya existe, no hace nada (reservado para futuro)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea una clínica dental de demostración con sucursal, usuarios, pacientes, citas y pagos (seguro para producción)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================');
        $this->info('  DEMO CLÍNICA DENTAL - Creación');
        $this->info('========================================');
        $this->newLine();

        $existente = Clinica::where('email', self::DEMO_CLINICA_EMAIL)->first();
        if ($existente) {
            $this->warn('La clínica demo dental ya existe (ID: ' . $existente->id . ').');
            $this->info('No se modifican datos existentes. Para ver credenciales, revise la documentación.');
            return self::SUCCESS;
        }

        $this->info('Creando clínica dental demo...');

        $clinica = Clinica::create([
            'nombre' => 'Clínica Dental Demo',
            'tipo_clinica' => 'dental',
            'modulos_habilitados' => ['expediente_dental', 'odontograma', 'tratamientos', 'imagenologia'],
            'email' => self::DEMO_CLINICA_EMAIL,
            'telefono' => '+52 55 1234 5678',
            'direccion' => 'Av. Insurgentes Sur 1234, Col. Del Valle, CDMX',
            'plan' => 'profesional',
            'pagado' => true,
            'activa' => true,
            'permite_multiples_sucursales' => true,
            'max_sucursales' => 3,
            'max_usuarios' => 10,
            'max_pacientes' => 500,
            'fecha_vencimiento' => now()->addYear(),
        ]);

        $this->info('  ✓ Clínica creada (ID: ' . $clinica->id . ')');

        $sucursal = Sucursal::create([
            'clinica_id' => $clinica->id,
            'nombre' => 'Sucursal Principal - Del Valle',
            'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
            'direccion' => $clinica->direccion,
            'telefono' => $clinica->telefono,
            'email' => 'sucursal@demo-dental.pacientesrhc',
            'ciudad' => 'Ciudad de México',
            'estado' => 'CDMX',
            'codigo_postal' => '03100',
            'es_principal' => true,
            'activa' => true,
            'notas' => 'Sucursal demo - Solo datos de prueba',
        ]);

        $this->info('  ✓ Sucursal creada (ID: ' . $sucursal->id . ')');

        $password = 'Demo2025!';
        $passwordHash = Hash::make($password);

        $admin = User::create([
            'nombre' => 'Admin',
            'apellidoPat' => 'Demo',
            'apellidoMat' => 'Dental',
            'email' => 'admin@demo-dental.pacientesrhc',
            'password' => $passwordHash,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'isAdmin' => true,
            'isSuperAdmin' => false,
            'rol' => 'administrativo',
            'email_verified' => true,
        ]);

        $doctor = User::create([
            'nombre' => 'Roberto',
            'apellidoPat' => 'García',
            'apellidoMat' => 'López',
            'email' => 'dr.garcia@demo-dental.pacientesrhc',
            'password' => $passwordHash,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'isAdmin' => false,
            'rol' => 'doctor',
            'email_verified' => true,
        ]);

        $this->info('  ✓ Usuarios creados: Admin y Dr. García');
        $this->info('    Contraseña para ambos: ' . $password);

        $pacientesData = [
            ['nombre' => 'María', 'apellidoPat' => 'Hernández', 'apellidoMat' => 'Sánchez', 'registro' => '1001', 'telefono' => '55 1111 2222', 'email' => 'maria.hernandez@ejemplo.com', 'fechaNac' => '1985-03-15', 'genero' => 0, 'tipo' => 'general'],
            ['nombre' => 'Juan Carlos', 'apellidoPat' => 'López', 'apellidoMat' => 'Martínez', 'registro' => '1002', 'telefono' => '55 2222 3333', 'email' => 'jc.lopez@ejemplo.com', 'fechaNac' => '1990-07-22', 'genero' => 1, 'tipo' => 'general'],
            ['nombre' => 'Ana', 'apellidoPat' => 'González', 'apellidoMat' => 'Ramírez', 'registro' => '1003', 'telefono' => '55 3333 4444', 'email' => 'ana.gonzalez@ejemplo.com', 'fechaNac' => '1978-11-08', 'genero' => 0, 'tipo' => 'ortodoncia'],
            ['nombre' => 'Pedro', 'apellidoPat' => 'Martínez', 'apellidoMat' => 'Flores', 'registro' => '1004', 'telefono' => '55 4444 5555', 'email' => 'pedro.martinez@ejemplo.com', 'fechaNac' => '1995-01-30', 'genero' => 1, 'tipo' => 'general'],
            ['nombre' => 'Laura', 'apellidoPat' => 'Díaz', 'apellidoMat' => 'Torres', 'registro' => '1005', 'telefono' => '55 5555 6666', 'email' => 'laura.diaz@ejemplo.com', 'fechaNac' => '1982-09-12', 'genero' => 0, 'tipo' => 'general'],
            ['nombre' => 'Miguel', 'apellidoPat' => 'Sánchez', 'apellidoMat' => 'Ruiz', 'registro' => '1006', 'telefono' => '55 6666 7777', 'email' => 'miguel.sanchez@ejemplo.com', 'fechaNac' => '1988-05-25', 'genero' => 1, 'tipo' => 'ortodoncia'],
            ['nombre' => 'Carmen', 'apellidoPat' => 'Ruiz', 'apellidoMat' => 'Vázquez', 'registro' => '1007', 'telefono' => '55 7777 8888', 'email' => 'carmen.ruiz@ejemplo.com', 'fechaNac' => '1975-12-03', 'genero' => 0, 'tipo' => 'general'],
            ['nombre' => 'Diego', 'apellidoPat' => 'Morales', 'apellidoMat' => 'Jiménez', 'registro' => '1008', 'telefono' => '55 8888 9999', 'email' => 'diego.morales@ejemplo.com', 'fechaNac' => '1992-04-18', 'genero' => 1, 'tipo' => 'general'],
            ['nombre' => 'Sofía', 'apellidoPat' => 'Ortiz', 'apellidoMat' => 'Castro', 'registro' => '1009', 'telefono' => '55 9999 0000', 'email' => 'sofia.ortiz@ejemplo.com', 'fechaNac' => '2000-08-07', 'genero' => 0, 'tipo' => 'general'],
            ['nombre' => 'Ricardo', 'apellidoPat' => 'Castro', 'apellidoMat' => 'Mendoza', 'registro' => '1010', 'telefono' => '55 1010 2020', 'email' => 'ricardo.castro@ejemplo.com', 'fechaNac' => '1987-02-14', 'genero' => 1, 'tipo' => 'ortodoncia'],
        ];

        $pacientes = [];
        foreach ($pacientesData as $i => $p) {
            $fechaNac = Carbon::parse($p['fechaNac']);
            $pacientes[] = Paciente::create([
                'registro' => $p['registro'],
                'nombre' => $p['nombre'],
                'apellidoPat' => $p['apellidoPat'],
                'apellidoMat' => $p['apellidoMat'],
                'telefono' => $p['telefono'],
                'email' => $p['email'],
                'fechaNacimiento' => $fechaNac,
                'edad' => $fechaNac->age,
                'genero' => $p['genero'],
                'domicilio' => 'Calle Ejemplo ' . ($i + 1) . ', Col. Del Valle',
                'motivo_consulta' => 'Revisión dental / Limpieza',
                'alergias' => $i === 2 ? 'Penicilina' : null,
                'tipo_paciente' => $p['tipo'],
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'color' => ['#4ECDC4', '#FF6B6B', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'][$i % 10],
            ]);
        }

        $this->info('  ✓ ' . count($pacientes) . ' pacientes creados');

        $fechaDemo = now()->subDays(5)->format('Y-m-d');
        $nombreDoctor = $doctor->nombre . ' ' . $doctor->apellidoPat . ' ' . $doctor->apellidoMat;
        foreach ($pacientes as $paciente) {
            $historia = HistoriaClinicaDental::create([
                'paciente_id' => $paciente->id,
                'sucursal_id' => $sucursal->id,
                'user_id' => $doctor->id,
                'fecha' => $fechaDemo,
                'nombre_doctor' => $nombreDoctor,
                'motivo_consulta' => 'Revisión dental / Limpieza - demo',
                'alergias' => $paciente->alergias,
            ]);
            Odontograma::create([
                'paciente_id' => $paciente->id,
                'sucursal_id' => $sucursal->id,
                'historia_clinica_dental_id' => $historia->id,
                'fecha' => $fechaDemo,
                'dientes' => Odontograma::inicializarDientes(),
            ]);
        }
        $this->info('  ✓ Historia clínica dental y odontograma creados para cada paciente');

        $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
        $horas = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '16:00:00', '17:00:00', '18:00:00'];
        $citas = [];
        $diasAtras = [0, 1, 2, 3, 5, 7, 10, 14, 20, 1, 2, 4, 5, 8, 15];
        $diasAdelante = [0, 1, 2, 3, 5, 7, 10, 14];

        for ($i = 0; $i < 15; $i++) {
            $fecha = now()->subDays($diasAtras[$i % count($diasAtras)]);
            $citas[] = Cita::create([
                'paciente_id' => $pacientes[$i % count($pacientes)]->id,
                'admin_id' => $admin->id,
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'fecha' => $fecha->format('Y-m-d'),
                'hora' => $horas[$i % count($horas)],
                'estado' => $estados[$i % 4],
                'primera_vez' => $i < 3,
                'notas' => $i % 3 === 0 ? 'Limpieza y revisión' : 'Consulta de seguimiento',
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            $fecha = now()->addDays($diasAdelante[$i % count($diasAdelante)]);
            $citas[] = Cita::create([
                'paciente_id' => $pacientes[$i % count($pacientes)]->id,
                'admin_id' => $admin->id,
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'fecha' => $fecha->format('Y-m-d'),
                'hora' => $horas[$i % count($horas)],
                'estado' => $i < 2 ? 'pendiente' : 'confirmada',
                'primera_vez' => false,
                'notas' => 'Cita programada - demo',
            ]);
        }

        $this->info('  ✓ ' . count($citas) . ' citas creadas (pasadas y futuras)');

        $conceptos = ['Limpieza dental', 'Revisión general', 'Ortodoncia - control', 'Extracción', 'Resina', 'Radiografía', 'Aplicación flúor', 'Consulta'];
        $metodos = ['efectivo', 'efectivo', 'tarjeta', 'transferencia', 'efectivo'];
        $montos = [450.00, 350.00, 500.00, 1200.00, 280.00, 800.00, 150.00, 400.00, 650.00, 320.00];

        $citasCompletadas = array_filter($citas, fn($c) => $c->estado === 'completada');
        $citasCompletadasIds = array_values(array_map(fn($c) => $c->id, $citasCompletadas));

        for ($i = 0; $i < 12; $i++) {
            $paciente = $pacientes[$i % count($pacientes)];
            $citaId = !empty($citasCompletadasIds) && $i < 6 ? $citasCompletadasIds[$i % count($citasCompletadasIds)] : null;
            Pago::create([
                'paciente_id' => $paciente->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'user_id' => $admin->id,
                'cita_id' => $citaId,
                'monto' => $montos[$i % count($montos)],
                'metodo_pago' => $metodos[$i % count($metodos)],
                'referencia' => $citaId ? 'CITA-' . str_pad($citaId, 6, '0', STR_PAD_LEFT) : null,
                'concepto' => $conceptos[$i % count($conceptos)],
                'notas' => 'Pago demo - datos de prueba',
                'firma_paciente' => null,
            ]);
        }

        $this->info('  ✓ 12 pagos creados (flujo de caja)');

        $this->newLine();
        $this->info('========================================');
        $this->info('  DEMO CREADA CORRECTAMENTE');
        $this->info('========================================');
        $this->table(
            ['Concepto', 'Valor'],
            [
                ['Clínica', $clinica->nombre . ' (ID: ' . $clinica->id . ')'],
                ['Sucursal', $sucursal->nombre],
                ['Admin (email)', $admin->email],
                ['Doctor (email)', $doctor->email],
                ['Contraseña', $password],
                ['Pacientes', count($pacientes)],
                ['Historias clínicas dentales', count($pacientes)],
                ['Odontogramas', count($pacientes)],
                ['Citas', count($citas)],
                ['Pagos', 12],
            ]
        );
        $this->info('Puede iniciar sesión en la app con admin@demo-dental.pacientesrhc o dr.garcia@demo-dental.pacientesrhc');
        $this->newLine();

        return self::SUCCESS;
    }
}
