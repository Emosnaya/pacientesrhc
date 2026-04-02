<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\UserClinica;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\ReporteFisio;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaAltaFisioterapia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateDemoRehabClinica extends Command
{
    protected const DEMO_EMAILS = [
        'cardiopulmonar-fisio' => 'demo-cardiopulmonar-fisio@demo.pacientesrhc',
        'cardiopulmonar'       => 'demo-cardiopulmonar@demo.pacientesrhc',
        'fisioterapia'         => 'demo-fisioterapia@demo.pacientesrhc',
    ];

    protected const PASSWORD = 'Demo2025!';

    protected $signature = 'demo:create-rehab
                            {tipo : Tipo de clínica: cardiopulmonar-fisio | cardiopulmonar | fisioterapia}
                            {--fresh : Elimina la demo existente y la recrea desde cero}';

    protected $description = 'Crea una clínica de demostración (rehab cardiopulmonar+fisio, solo cardiopulmonar o solo fisioterapia) con sucursal, usuarios, pacientes, citas, pagos y expedientes';

    public function handle(): int
    {
        $tipo = $this->argument('tipo');
        if (!isset(self::DEMO_EMAILS[$tipo])) {
            $this->error('Tipo inválido. Use: cardiopulmonar-fisio, cardiopulmonar o fisioterapia');
            return self::FAILURE;
        }

        $emailClinica = self::DEMO_EMAILS[$tipo];
        $this->info('========================================');
        $this->info('  DEMO REHAB - ' . $tipo);
        $this->info('========================================');
        $this->newLine();

        $existente = Clinica::where('email', $emailClinica)->first();
        if ($existente) {
            if ($this->option('fresh')) {
                $this->warn('Eliminando demo existente (ID: ' . $existente->id . ')...');
                $this->eliminarDemoCompleta($existente);
                $this->info('  ✓ Demo anterior eliminada');
            } else {
                $this->warn('La clínica demo ya existe (ID: ' . $existente->id . '). No se modifican datos.');
                $this->info('  Usa --fresh para eliminar y recrear desde cero');
                return self::SUCCESS;
            }
        }

        $config = $this->getConfig($tipo);
        $clinica = $this->createClinica($emailClinica, $config);
        
        // Crear DOS sucursales para clínicas tipo cardiopulmonar
        $sucursales = $this->createSucursales($clinica, $tipo);
        $sucursalPrincipal = $sucursales[0];
        
        $admin = $this->createAdmin($clinica, $sucursalPrincipal, $tipo);
        $doctor = $this->createDoctor($clinica, $sucursalPrincipal, $tipo);
        
        // Crear consultorio adicional de otra especialidad para el doctor
        $consultorioAdicional = null;
        if ($tipo === 'cardiopulmonar-fisio' || $tipo === 'cardiopulmonar') {
            $consultorioAdicional = $this->createConsultorioAdicional($doctor, $tipo);
        }
        
        $pacientes = $this->createPacientes($clinica, $sucursalPrincipal, $doctor, $config);
        
        // Si hay segunda sucursal, crear pacientes adicionales
        if (count($sucursales) > 1) {
            $pacientesSucursal2 = $this->createPacientes($clinica, $sucursales[1], $doctor, $config, 'SUC2');
            $pacientes = array_merge($pacientes, $pacientesSucursal2);
        }
        
        $citas = $this->createCitas($pacientes, $admin, $doctor, $clinica, $sucursalPrincipal);
        $this->createPagos($pacientes, $citas, $admin, $clinica, $sucursalPrincipal);
        $this->createExpedientes($pacientes, $doctor, $config);

        $this->printResumen($clinica, $sucursales, $admin, $doctor, $pacientes, $citas, $tipo, $consultorioAdicional);
        return self::SUCCESS;
    }

    protected function getConfig(string $tipo): array
    {
        $baseRehab = [
            'tipo_clinica' => 'rehabilitacion_cardiopulmonar',
            'modulos' => [
                'expediente_cardiaco', 'expediente_pulmonar', 'expediente_fisioterapia',
                'prueba_esfuerzo', 'estratificacion', 'reporte_final', 'reporte_nutri', 'reporte_psico'
            ],
        ];
        $soloFisio = [
            'tipo_clinica' => 'fisioterapia',
            'modulos' => ['expediente_fisio', 'sesiones', 'ejercicios', 'evaluaciones'],
        ];

        $configs = [
            'cardiopulmonar-fisio' => array_merge($baseRehab, [
                'nombre' => 'Rehab Cardiopulmonar + Fisioterapia Demo',
                'tipos_paciente' => ['cardiaca', 'pulmonar', 'ambos', 'fisioterapia'],
                'expedientes' => ['clinico', 'esfuerzo', 'estratificacion', 'reporte_final', 'reporte_nutri', 'reporte_psico', 'reporte_fisio', 'historia_fisio', 'evolucion_fisio', 'alta_fisio'],
            ]),
            'cardiopulmonar' => array_merge($baseRehab, [
                'nombre' => 'Rehab Cardiopulmonar Demo',
                'tipos_paciente' => ['cardiaca', 'pulmonar', 'ambos'],
                'expedientes' => ['clinico', 'esfuerzo', 'estratificacion', 'reporte_final', 'reporte_nutri', 'reporte_psico'],
            ]),
            'fisioterapia' => array_merge($soloFisio, [
                'nombre' => 'Fisioterapia Demo',
                'tipos_paciente' => ['fisioterapia'],
                'expedientes' => ['reporte_fisio', 'historia_fisio', 'evolucion_fisio', 'alta_fisio'],
            ]),
        ];
        return $configs[$tipo];
    }

    protected function createClinica(string $email, array $config): Clinica
    {
        $c = Clinica::create([
            'nombre' => $config['nombre'],
            'tipo_clinica' => $config['tipo_clinica'],
            'modulos_habilitados' => $config['modulos'],
            'email' => $email,
            'telefono' => '+52 55 1234 5678',
            'direccion' => 'Av. Rehabilitación 100, Col. Centro, CDMX',
            'plan' => 'profesional',
            'pagado' => true,
            'activa' => true,
            'permite_multiples_sucursales' => true,
            'max_sucursales' => 3,
            'max_usuarios' => 10,
            'max_pacientes' => 500,
            'fecha_vencimiento' => now()->addYear(),
        ]);
        $this->info('  ✓ Clínica creada (ID: ' . $c->id . ')');
        return $c;
    }

    /**
     * Crear DOS sucursales para la clínica (Principal y una adicional)
     */
    protected function createSucursales(Clinica $clinica, string $tipo): array
    {
        $sucursales = [];
        
        // Sucursal Principal
        $s1 = Sucursal::create([
            'clinica_id' => $clinica->id,
            'nombre' => 'Sucursal Principal - Centro',
            'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
            'direccion' => $clinica->direccion,
            'telefono' => $clinica->telefono,
            'email' => 'principal@demo-' . str_replace('_', '-', $tipo) . '.pacientesrhc',
            'ciudad' => 'Ciudad de México',
            'estado' => 'CDMX',
            'codigo_postal' => '06000',
            'es_principal' => true,
            'activa' => true,
            'notas' => 'Sucursal demo principal - Solo datos de prueba',
        ]);
        $sucursales[] = $s1;
        $this->info('  ✓ Sucursal 1 creada: ' . $s1->nombre . ' (ID: ' . $s1->id . ')');
        
        // Segunda Sucursal (solo para cardiopulmonar y cardiopulmonar-fisio)
        if (in_array($tipo, ['cardiopulmonar-fisio', 'cardiopulmonar'])) {
            $s2 = Sucursal::create([
                'clinica_id' => $clinica->id,
                'nombre' => 'Sucursal Sur - Coyoacán',
                'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-002',
                'direccion' => 'Av. Universidad 1500, Col. Copilco, Coyoacán, CDMX',
                'telefono' => '+52 55 9876 5432',
                'email' => 'sur@demo-' . str_replace('_', '-', $tipo) . '.pacientesrhc',
                'ciudad' => 'Ciudad de México',
                'estado' => 'CDMX',
                'codigo_postal' => '04360',
                'es_principal' => false,
                'activa' => true,
                'notas' => 'Segunda sucursal demo - Solo datos de prueba',
            ]);
            $sucursales[] = $s2;
            $this->info('  ✓ Sucursal 2 creada: ' . $s2->nombre . ' (ID: ' . $s2->id . ')');
        }
        
        return $sucursales;
    }

    protected function createAdmin(Clinica $clinica, Sucursal $sucursal, string $tipo): User
    {
        $slug = str_replace('_', '-', $tipo);
        $u = User::create([
            'nombre' => 'Admin',
            'apellidoPat' => 'Demo',
            'apellidoMat' => ucfirst($tipo),
            'email' => 'admin@demo-' . $slug . '.pacientesrhc',
            'password' => Hash::make(self::PASSWORD),
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'isAdmin' => true,
            'isSuperAdmin' => false,
            'rol' => 'administrativo',
            'email_verified' => true,
        ]);
        return $u;
    }

    protected function createDoctor(Clinica $clinica, Sucursal $sucursal, string $tipo): User
    {
        $slug = str_replace('_', '-', $tipo);
        $rol = $tipo === 'fisioterapia' ? 'fisioterapeuta' : 'doctor';
        $u = User::create([
            'nombre' => 'Luis',
            'apellidoPat' => 'Martínez',
            'apellidoMat' => 'Rehab',
            'email' => 'dr.martinez@demo-' . $slug . '.pacientesrhc',
            'password' => Hash::make(self::PASSWORD),
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'isAdmin' => false,
            'rol' => $rol,
            'email_verified' => true,
        ]);
        $this->info('  ✓ Usuarios creados. Contraseña: ' . self::PASSWORD);
        return $u;
    }

    /**
     * Crear un consultorio adicional de otra especialidad (Nutrición) para el doctor
     * Esto demuestra que un doctor puede tener múltiples espacios de trabajo
     */
    protected function createConsultorioAdicional(User $doctor, string $tipo): ?Clinica
    {
        $slug = str_replace('_', '-', $tipo);
        $emailConsultorio = 'consultorio-nutri-' . $slug . '@demo.pacientesrhc';
        
        // Verificar si ya existe
        $existente = Clinica::where('email', $emailConsultorio)->first();
        if ($existente) {
            $this->warn('  ⚠️ Consultorio adicional ya existe (ID: ' . $existente->id . ')');
            return $existente;
        }
        
        // Crear consultorio de Nutrición para el doctor
        $consultorio = Clinica::create([
            'nombre' => 'Consultorio Nutrición - Dr. Martínez',
            'tipo_clinica' => 'nutricion',
            'modulos_habilitados' => ['expediente_nutricion', 'plan_alimentacion', 'seguimiento'],
            'email' => $emailConsultorio,
            'telefono' => '+52 55 5555 1234',
            'direccion' => 'Consultorio 305, Torre Médica Sur, CDMX',
            'plan' => 'basico',
            'pagado' => true,
            'activa' => true,
            'permite_multiples_sucursales' => false,
            'max_sucursales' => 1,
            'max_usuarios' => 3,
            'max_pacientes' => 100,
            'fecha_vencimiento' => now()->addYear(),
            'es_consultorio_privado' => true,
        ]);
        
        // Crear sucursal para el consultorio
        $sucursalConsultorio = Sucursal::create([
            'clinica_id' => $consultorio->id,
            'nombre' => 'Consultorio Principal',
            'codigo' => 'CON-' . str_pad($consultorio->id, 3, '0', STR_PAD_LEFT) . '-001',
            'direccion' => $consultorio->direccion,
            'telefono' => $consultorio->telefono,
            'email' => $consultorio->email,
            'es_principal' => true,
            'activa' => true,
        ]);
        
        // Vincular al doctor como propietario del consultorio
        DB::table('user_clinicas')->insert([
            'user_id' => $doctor->id,
            'clinica_id' => $consultorio->id,
            'rol_en_clinica' => 'propietario',
            'isAdmin' => true,
            'isSuperAdmin' => true,
            'activa' => true,
            'invitado_por' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Crear solo 2 pacientes de ejemplo para el consultorio
        $nombresPacientes = [
            ['Carlos', 'Mendoza', 'Ruiz'],
            ['Elena', 'Vargas', 'Luna'],
        ];
        
        foreach ($nombresPacientes as $i => $n) {
            $fechaNac = Carbon::now()->subYears(35 + ($i * 10));
            Paciente::create([
                'registro' => 'NUT-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nombre' => $n[0],
                'apellidoPat' => $n[1],
                'apellidoMat' => $n[2],
                'telefono' => '55 1234 ' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'email' => strtolower($n[0]) . '.' . strtolower($n[1]) . '.nutri.c' . $consultorio->id . '@ejemplo.com',
                'fechaNacimiento' => $fechaNac,
                'edad' => $fechaNac->age,
                'genero' => $i % 2,
                'domicilio' => 'Calle Nutrición ' . ($i + 1) . ', Col. Salud',
                'motivo_consulta' => 'Consulta nutricional',
                'user_id' => $doctor->id,
                'clinica_id' => $consultorio->id,
                'sucursal_id' => $sucursalConsultorio->id,
                'color' => ['#10B981', '#F59E0B'][$i],
            ]);
        }
        
        $this->info('  ✓ Consultorio adicional creado: ' . $consultorio->nombre . ' (ID: ' . $consultorio->id . ')');
        $this->info('    → 2 pacientes de ejemplo creados');
        $this->info('    → Dr. Martínez ahora tiene acceso a 2 espacios de trabajo');
        
        return $consultorio;
    }

    protected function createPacientes(Clinica $clinica, Sucursal $sucursal, User $doctor, array $config, string $prefix = ''): array
    {
        $tipos = $config['tipos_paciente'];
        
        // Usar nombres diferentes si es la segunda sucursal
        $nombres = $prefix === 'SUC2' ? [
            ['Alejandra', 'Ramírez', 'López'], ['Fernando', 'García', 'Hernández'], ['Gabriela', 'Torres', 'Díaz'],
            ['Roberto', 'Flores', 'Martínez'], ['Patricia', 'Jiménez', 'Sánchez'],
        ] : [
            ['María', 'Hernández', 'Sánchez'], ['Juan', 'López', 'Martínez'], ['Ana', 'González', 'Ramírez'],
            ['Pedro', 'Martínez', 'Flores'], ['Laura', 'Díaz', 'Torres'], ['Miguel', 'Sánchez', 'Ruiz'],
            ['Carmen', 'Ruiz', 'Vázquez'], ['Diego', 'Morales', 'Jiménez'], ['Sofía', 'Ortiz', 'Castro'],
            ['Ricardo', 'Castro', 'Mendoza'],
        ];
        
        $pacientes = [];
        $offset = $prefix === 'SUC2' ? 100 : 0; // Offset para IDs únicos
        $clinicaSuffix = 'c' . $clinica->id; // Sufijo único por clínica para evitar duplicados
        
        foreach ($nombres as $i => $n) {
            $fechaNac = Carbon::now()->subYears(50 + ($i % 25));
            $tipoP = $tipos[$i % count($tipos)];
            $pacientes[] = Paciente::create([
                'registro' => ($prefix ? $prefix . '-' : '') . 'R' . str_pad($i + 1 + $offset, 4, '0', STR_PAD_LEFT),
                'nombre' => $n[0],
                'apellidoPat' => $n[1],
                'apellidoMat' => $n[2],
                'telefono' => '55 ' . str_pad($i + 1 + $offset, 4, '0', STR_PAD_LEFT) . ' ' . str_pad(($i + 2) * 1111, 4, '0', STR_PAD_LEFT),
                'email' => strtolower($n[0]) . '.' . strtolower($n[1]) . ($prefix ? '.suc2' : '') . '.' . $clinicaSuffix . '@ejemplo.com',
                'fechaNacimiento' => $fechaNac,
                'edad' => $fechaNac->age,
                'genero' => $i % 2,
                'domicilio' => 'Calle Demo ' . ($i + 1) . ', Col. ' . ($prefix === 'SUC2' ? 'Coyoacán' : 'Centro'),
                'motivo_consulta' => $tipoP === 'fisioterapia' ? 'Rehabilitación física' : 'Rehabilitación cardiopulmonar',
                'tipo_paciente' => $tipoP,
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'color' => ['#4ECDC4', '#FF6B6B', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'][$i % 10],
            ]);
        }
        $this->info('  ✓ ' . count($pacientes) . ' pacientes creados en ' . $sucursal->nombre);
        return $pacientes;
    }

    protected function createCitas(array $pacientes, User $admin, User $doctor, Clinica $clinica, Sucursal $sucursal): array
    {
        $horas = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '16:00:00', '17:00:00'];
        $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
        $citas = [];
        foreach ([7, 3, 1, 0, 14, 5, 2, 0, 21, 4] as $i => $dias) {
            $fecha = now()->subDays($dias);
            $citas[] = Cita::create([
                'paciente_id' => $pacientes[$i % count($pacientes)]->id,
                'admin_id' => $admin->id,
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'fecha' => $fecha->format('Y-m-d'),
                'hora' => $horas[$i % count($horas)],
                'estado' => $estados[$i % 4],
                'primera_vez' => $i < 2,
                'notas' => 'Consulta demo',
            ]);
        }
        foreach ([1, 3, 5, 7, 10] as $i => $dias) {
            $fecha = now()->addDays($dias);
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
                'notas' => 'Cita futura demo',
            ]);
        }
        $this->info('  ✓ ' . count($citas) . ' citas creadas');
        return $citas;
    }

    protected function createPagos(array $pacientes, array $citas, User $admin, Clinica $clinica, Sucursal $sucursal): void
    {
        $completadas = array_values(array_filter($citas, fn($c) => $c->estado === 'completada'));
        $conceptos = ['Consulta rehab', 'Sesión fisioterapia', 'Prueba de esfuerzo', 'Evaluación nutricional', 'Sesión psicológica', 'Control'];
        $metodos = ['efectivo', 'tarjeta', 'transferencia', 'efectivo'];
        $montos = [500.00, 600.00, 800.00, 350.00, 450.00, 400.00];
        for ($i = 0; $i < 10; $i++) {
            $citaId = !empty($completadas) && $i < 5 ? $completadas[$i % count($completadas)]->id : null;
            Pago::create([
                'paciente_id' => $pacientes[$i % count($pacientes)]->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'user_id' => $admin->id,
                'cita_id' => $citaId,
                'monto' => $montos[$i % count($montos)],
                'metodo_pago' => $metodos[$i % count($metodos)],
                'referencia' => $citaId ? 'CITA-' . $citaId : null,
                'concepto' => $conceptos[$i % count($conceptos)],
                'notas' => 'Pago demo',
                'firma_paciente' => null,
            ]);
        }
        $this->info('  ✓ 10 pagos creados');
    }

    protected function createExpedientes(array $pacientes, User $doctor, array $config): void
    {
        $exp = $config['expedientes'];
        $fecha = now()->subDays(5)->format('Y-m-d');

        foreach ($pacientes as $idx => $paciente) {
            $tipoP = $paciente->tipo_paciente;
            $tieneFisio = ($tipoP === 'fisioterapia');

            // Solo crear expedientes con campos mínimos requeridos (sin 'contenido')
            if (in_array('clinico', $exp, true) && in_array($tipoP, ['cardiaca', 'pulmonar', 'ambos'], true)) {
                Clinico::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                    'diagnostico_general' => 'Expediente demo - Rehabilitación cardiopulmonar',
                    'plan' => 'Plan de rehabilitación integral',
                ]);
            }
            if (in_array('esfuerzo', $exp, true) && in_array($tipoP, ['cardiaca', 'pulmonar', 'ambos'], true)) {
                Esfuerzo::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                ]);
            }
            if (in_array('estratificacion', $exp, true) && in_array($tipoP, ['cardiaca', 'pulmonar', 'ambos'], true)) {
                Estratificacion::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                ]);
            }
            if (in_array('reporte_final', $exp, true) && in_array($tipoP, ['cardiaca', 'pulmonar', 'ambos'], true)) {
                ReporteFinal::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                ]);
            }
            if (in_array('reporte_nutri', $exp, true) && in_array($tipoP, ['cardiaca', 'pulmonar', 'ambos'], true)) {
                ReporteNutri::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                ]);
            }
            if (in_array('reporte_psico', $exp, true) && in_array($tipoP, ['cardiaca', 'pulmonar', 'ambos'], true)) {
                ReportePsico::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                ]);
            }
            if (in_array('reporte_fisio', $exp, true) && $tieneFisio) {
                ReporteFisio::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                ]);
            }
            if (in_array('historia_fisio', $exp, true) && $tieneFisio) {
                HistoriaClinicaFisioterapia::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                    'hora' => '10:00',
                    'ocupacion' => 'Oficinista',
                    'motivo_consulta' => 'Dolor lumbar',
                    'padecimiento_actual' => 'Lumbalgia mecánica',
                    'antecedentes_heredofamiliares' => 'Ninguno relevante',
                    'antecedentes_personales_patologicos' => 'Ninguno',
                    'antecedentes_personales_no_patologicos' => 'Sedentarismo',
                    'antecedentes_quirurgicos_traumaticos' => 'Ninguno',
                    'signos_vitales' => 'TA 120/80, FC 72',
                    'inspeccion' => 'Postura con ligera rectificación lumbar',
                    'palpacion' => 'Contractura paravertebral L4-L5',
                    'rango_movimiento' => 'Limitación flexión lumbar',
                    'fuerza_muscular' => '5/5 MMII',
                    'pruebas_especiales' => 'Lasègue negativo',
                    'diagnostico_medico' => 'Lumbalgia mecánica',
                    'diagnostico_fisioterapeutico' => 'Disfunción lumbar',
                    'objetivos_tratamiento' => 'Disminuir dolor y mejorar movilidad',
                    'modalidades_terapeuticas' => 'Terapia manual, ejercicio',
                    'educacion_paciente' => 'Higiene postural',
                    'pronostico' => 'Favorable',
                ]);
            }
            if (in_array('evolucion_fisio', $exp, true) && $tieneFisio) {
                NotaEvolucionFisioterapia::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                    'hora' => '10:30',
                    'diagnostico_fisioterapeutico' => 'Disfunción lumbar',
                    'observaciones_subjetivas' => 'Mejoría del dolor',
                    'dolor_eva' => 4,
                    'funcionalidad' => 'Mejor',
                    'observaciones_objetivas' => 'Mayor rango de movimiento',
                    'tecnicas_modalidades_aplicadas' => 'Masoterapia, estiramientos',
                    'ejercicio_terapeutico' => 'Core y flexibilidad',
                    'respuesta_tratamiento' => 'Buena',
                    'plan' => 'Continuar 3 sesiones más',
                ]);
            }
            if (in_array('alta_fisio', $exp, true) && $tieneFisio && $idx < 4) {
                NotaAltaFisioterapia::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'fecha' => $fecha,
                    'diagnostico_medico' => 'Lumbalgia mecánica',
                    'diagnostico_fisioterapeutico_inicial' => 'Disfunción lumbar',
                    'fecha_inicio_atencion' => Carbon::parse($fecha)->subDays(14),
                    'fecha_termino' => $fecha,
                    'numero_sesiones' => 6,
                    'tratamiento_otorgado' => 'Terapia manual y ejercicio',
                    'evolucion_resultados' => 'Alta satisfactoria',
                    'dolor_alta_eva' => 2,
                    'mejoria_funcional' => 'Sí',
                    'objetivos_alcanzados' => 'Sí',
                    'estado_funcional_alta' => 'Independiente',
                    'recomendaciones_seguimiento' => 'Ejercicio domiciliario',
                    'pronostico_funcional' => 'Favorable',
                ]);
            }
        }
        $this->info('  ✓ Expedientes de demostración creados');
    }

    protected function printResumen(Clinica $clinica, array $sucursales, User $admin, User $doctor, array $pacientes, array $citas, string $tipo, ?Clinica $consultorioAdicional = null): void
    {
        $slug = str_replace('_', '-', $tipo);
        $this->newLine();
        $this->info('========================================');
        $this->info('  DEMO CREADA CORRECTAMENTE');
        $this->info('========================================');
        
        // Construir lista de sucursales
        $sucursalesStr = implode(', ', array_map(fn($s) => $s->nombre, $sucursales));
        
        $tableData = [
            ['Clínica', $clinica->nombre . ' (ID: ' . $clinica->id . ')'],
            ['Sucursales', count($sucursales) . ' → ' . $sucursalesStr],
            ['Admin (email)', $admin->email],
            ['Doctor/Fisio (email)', $doctor->email],
            ['Contraseña', self::PASSWORD],
            ['Pacientes', count($pacientes)],
            ['Citas', count($citas)],
        ];
        
        // Agregar info del consultorio adicional si existe
        if ($consultorioAdicional) {
            $tableData[] = ['', ''];
            $tableData[] = ['--- Consultorio Adicional ---', ''];
            $tableData[] = ['Consultorio', $consultorioAdicional->nombre . ' (ID: ' . $consultorioAdicional->id . ')'];
            $tableData[] = ['Tipo', 'Nutrición (consultorio privado)'];
            $tableData[] = ['Propietario', $doctor->email];
        }
        
        $this->table(['Concepto', 'Valor'], $tableData);
        
        $this->newLine();
        $this->info('Inicie sesión con: ' . $admin->email . ' o ' . $doctor->email);
        
        if ($consultorioAdicional) {
            $this->newLine();
            $this->info('🔄 El Dr. Martínez puede cambiar entre espacios de trabajo:');
            $this->info('   1. ' . $clinica->nombre . ' (Rehabilitación)');
            $this->info('   2. ' . $consultorioAdicional->nombre . ' (Nutrición)');
        }
        
        $this->newLine();
    }

    /**
     * Elimina la demo completa: clínica, sucursales, usuarios, pacientes, citas, pagos, expedientes
     */
    protected function eliminarDemoCompleta(Clinica $clinica): void
    {
        $clinicaId = $clinica->id;
        
        // Obtener IDs de pacientes para eliminar expedientes
        $pacienteIds = Paciente::where('clinica_id', $clinicaId)->pluck('id')->toArray();
        
        // Eliminar expedientes
        if (!empty($pacienteIds)) {
            Clinico::whereIn('paciente_id', $pacienteIds)->delete();
            Esfuerzo::whereIn('paciente_id', $pacienteIds)->delete();
            Estratificacion::whereIn('paciente_id', $pacienteIds)->delete();
            ReporteFinal::whereIn('paciente_id', $pacienteIds)->delete();
            ReporteNutri::whereIn('paciente_id', $pacienteIds)->delete();
            ReportePsico::whereIn('paciente_id', $pacienteIds)->delete();
            ReporteFisio::whereIn('paciente_id', $pacienteIds)->delete();
            HistoriaClinicaFisioterapia::whereIn('paciente_id', $pacienteIds)->delete();
            NotaEvolucionFisioterapia::whereIn('paciente_id', $pacienteIds)->delete();
            NotaAltaFisioterapia::whereIn('paciente_id', $pacienteIds)->delete();
        }
        
        // Eliminar pagos y citas
        Pago::where('clinica_id', $clinicaId)->delete();
        Cita::where('clinica_id', $clinicaId)->delete();
        
        // Eliminar pacientes
        Paciente::where('clinica_id', $clinicaId)->delete();
        
        // Eliminar usuarios (y sus vínculos en user_clinicas)
        $userIds = User::where('clinica_id', $clinicaId)->pluck('id')->toArray();
        if (!empty($userIds)) {
            DB::table('user_clinicas')->whereIn('user_id', $userIds)->delete();
            DB::table('user_sucursal')->whereIn('user_id', $userIds)->delete();
            
            // También eliminar consultorios adicionales creados por estos usuarios
            $consultoriosIds = DB::table('user_clinicas')
                ->whereIn('user_id', $userIds)
                ->where('rol_en_clinica', 'propietario')
                ->pluck('clinica_id')
                ->toArray();
            
            foreach ($consultoriosIds as $cid) {
                if ($cid !== $clinicaId) {
                    Sucursal::where('clinica_id', $cid)->delete();
                    Clinica::where('id', $cid)->delete();
                }
            }
            
            User::whereIn('id', $userIds)->delete();
        }
        
        // Eliminar sucursales
        Sucursal::where('clinica_id', $clinicaId)->delete();
        
        // Eliminar clínica
        $clinica->delete();
    }
}
