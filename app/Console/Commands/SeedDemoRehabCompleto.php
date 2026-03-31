<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Clinico;
use App\Models\CualidadFisica;
use App\Models\Egreso;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\EstratiAacvpr;
use App\Models\ExpedientePulmonar;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\NotaAltaFisioterapia;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaSeguimientoPulmonar;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\PruebaEsfuerzoPulmonar;
use App\Models\Receta;
use App\Models\RecetaMedicamento;
use App\Models\ReporteFinal;
use App\Models\ReporteFinalPulmonar;
use App\Models\ReporteFisio;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\UserPermission;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Demo rehabilitación (cardíaca, pulmonar, fisioterapia): ~10 pacientes, un paciente con todos los expedientes,
 * varios usuarios (superadmin, admin, doctor, nutri, psico, fisio, recepción), recetas, pagos, egresos y citas.
 *
 * Cifrado: los modelos usan cast "encrypted" de Laravel (AES-256-CBC con APP_KEY). Cada entorno (dev/prod) debe
 * tener su propia APP_KEY; los datos demo solo son legibles en la misma instancia donde se generaron. No copiar
 * filas cifradas entre bases con distinta clave.
 *
 * Portal del paciente: el primer paciente (R9999-DEMO) tiene usuario web con el mismo correo, sin clinica_id,
 * contraseña demo y password_set_at, más visibilidad en clinica_paciente (citas, datos básicos, resumen).
 */
class SeedDemoRehabCompleto extends Command
{
    public const CLINICA_EMAIL = 'demo-rehab@lynkamed.mx';

    protected const PASSWORD = 'Demo2025!';

    /** Claves de modulos_habilitados alineadas con useClinicaPermisos (cardiaco, pulmonar, fisioterapia, nutricion, psicologia). */
    protected const MODULOS_REHAB_COMPLETO = ['cardiaco', 'pulmonar', 'fisioterapia', 'nutricion', 'psicologia'];

    protected $signature = 'demo:seed-rehab-completo
                            {--force : Elimina la clínica demo previa (mismo email) y vuelve a crear}';

    protected $description = 'Carga demo: rehab cardiopulmonar+fisio (~10 pacientes, 1 con todos los expedientes), usuarios, citas, finanzas y 3 recetas';

    public function handle(): int
    {
        $this->warn('Cifrado: APP_KEY actual (' . (config('app.key') ? 'definida' : '¡falta!') . '). Misma clave que al consultar los datos en esta instancia.');
        $this->newLine();

        $existente = Clinica::where('email', self::CLINICA_EMAIL)->first();
        if ($existente && ! $this->option('force')) {
            $this->warn('La demo ya existe (clínica ID ' . $existente->id . '). Use --force para eliminarla y regenerar.');

            return self::SUCCESS;
        }

        if ($existente && $this->option('force')) {
            $this->purgeDemoClinica($existente);
            $this->info('Demo anterior eliminada.');
        }

        DB::transaction(function () {
            $clinica = $this->createClinica();
            $sucursal = $this->createSucursal($clinica);
            $users = $this->createUsersAndPivots($clinica, $sucursal);

            $doctor = $users['doctor'];
            $admin = $users['admin'];
            $nutri = $users['nutriologo'];
            $psico = $users['psicologo'];
            $fisio = $users['fisioterapeuta'];

            $pacientes = $this->createPacientes($clinica, $sucursal, $doctor);
            $this->linkClinicaPaciente($clinica, $sucursal, $doctor, $pacientes);
            $this->createPortalPacienteUser($pacientes[0]);

            $citas = $this->createCitas($pacientes, $admin, $doctor, $clinica, $sucursal);
            $this->createPagos($pacientes, $citas, $admin, $clinica, $sucursal);
            $this->createEgresos($clinica, $sucursal, $admin);

            $pacienteCompleto = $pacientes[0];
            $this->createAllExpedientesForPaciente(
                $pacienteCompleto,
                $clinica,
                $sucursal,
                $doctor,
                $nutri,
                $psico,
                $fisio
            );
            $this->createSparseExpedientesForOthers(array_slice($pacientes, 1), $doctor, $clinica, $sucursal, $fisio);

            $this->createRecetas($pacienteCompleto, $doctor, $clinica, $sucursal);
        });

        $this->newLine();
        $this->info('Demo lista. Email clínica: ' . self::CLINICA_EMAIL);
        $this->table(
            ['Usuario', 'Email', 'Notas'],
            [
                ['Superadmin', 'demo-rehab-superadmin@lynkamed.mx', 'isSuperAdmin + isAdmin'],
                ['Admin', 'demo-rehab-admin@lynkamed.mx', 'isAdmin'],
                ['Doctor (recetas)', 'demo-rehab-dr-garcia@lynkamed.mx', 'doctor'],
                ['Nutriólogo', 'demo-rehab-nutri@lynkamed.mx', 'nutriologo'],
                ['Psicólogo', 'demo-rehab-psico@lynkamed.mx', 'psicologo'],
                ['Fisioterapeuta', 'demo-rehab-fisio@lynkamed.mx', 'fisioterapeuta'],
                ['Recepción', 'demo-rehab-recepcion@lynkamed.mx', 'recepcionista'],
                ['Portal paciente (R9999-DEMO)', 'demo-rehab-paciente-completo@lynkamed.mx', 'misma contraseña; sin clínica en users'],
            ]
        );
        $this->info('Contraseña común (staff y portal paciente demo): ' . self::PASSWORD);
        $this->info('Paciente con todos los expedientes: registro R9999-DEMO — portal: demo-rehab-paciente-completo@lynkamed.mx');

        return self::SUCCESS;
    }

    protected function createClinica(): Clinica
    {
        return Clinica::create([
            'nombre' => 'Rehab Cardiopulmonar Demo (expedientes completos)',
            'tipo_clinica' => 'rehabilitacion_cardiopulmonar',
            'modulos_habilitados' => self::MODULOS_REHAB_COMPLETO,
            'email' => self::CLINICA_EMAIL,
            'telefono' => '+52 55 5555 0101',
            'direccion' => 'Av. Salud 200, Col. Centro, CDMX',
            'plan' => 'profesional',
            'pagado' => true,
            'activa' => true,
            'permite_multiples_sucursales' => true,
            'max_sucursales' => 5,
            'max_usuarios' => 25,
            'max_pacientes' => 500,
            'fecha_vencimiento' => now()->addYear(),
        ]);
    }

    protected function createSucursal(Clinica $clinica): Sucursal
    {
        return Sucursal::create([
            'clinica_id' => $clinica->id,
            'nombre' => 'Sucursal Principal Demo Rehab',
            'codigo' => 'SUC-RH-' . str_pad((string) $clinica->id, 3, '0', STR_PAD_LEFT),
            'direccion' => $clinica->direccion,
            'telefono' => $clinica->telefono,
            'email' => 'demo-rehab-sucursal@lynkamed.mx',
            'ciudad' => 'Ciudad de México',
            'estado' => 'CDMX',
            'codigo_postal' => '03100',
            'es_principal' => true,
            'activa' => true,
            'notas' => 'Solo datos de demostración',
        ]);
    }

    /**
     * @return array<string, User>
     */
    protected function createUsersAndPivots(Clinica $clinica, Sucursal $sucursal): array
    {
        $defs = [
            'superadmin' => [
                'nombre' => 'Patricia', 'apellidoPat' => 'Vega', 'apellidoMat' => 'Luna',
                'email' => 'demo-rehab-superadmin@lynkamed.mx', 'rol' => 'doctor',
                'cedula_especialista' => '1234567',
                'pivot' => ['isSuperAdmin' => true, 'isAdmin' => true, 'rol_en_clinica' => 'colaborador'],
            ],
            'admin' => [
                'nombre' => 'Roberto', 'apellidoPat' => 'Admin', 'apellidoMat' => 'Demo',
                'email' => 'demo-rehab-admin@lynkamed.mx', 'rol' => 'administrativo',
                'pivot' => ['isSuperAdmin' => false, 'isAdmin' => true, 'rol_en_clinica' => 'colaborador'],
            ],
            'doctor' => [
                'nombre' => 'Carlos', 'apellidoPat' => 'García', 'apellidoMat' => 'Médico',
                'email' => 'demo-rehab-dr-garcia@lynkamed.mx', 'rol' => 'doctor',
                'cedula_especialista' => '7654321',
                'pivot' => ['isSuperAdmin' => false, 'isAdmin' => false, 'rol_en_clinica' => 'colaborador'],
            ],
            'nutriologo' => [
                'nombre' => 'Laura', 'apellidoPat' => 'Nutri', 'apellidoMat' => 'Demo',
                'email' => 'demo-rehab-nutri@lynkamed.mx', 'rol' => 'nutriologa',
                'pivot' => ['isSuperAdmin' => false, 'isAdmin' => false, 'rol_en_clinica' => 'colaborador'],
            ],
            'psicologo' => [
                'nombre' => 'Daniel', 'apellidoPat' => 'Psico', 'apellidoMat' => 'Demo',
                'email' => 'demo-rehab-psico@lynkamed.mx', 'rol' => 'psicologo',
                'pivot' => ['isSuperAdmin' => false, 'isAdmin' => false, 'rol_en_clinica' => 'colaborador'],
            ],
            'fisioterapeuta' => [
                'nombre' => 'Elena', 'apellidoPat' => 'Fisio', 'apellidoMat' => 'Demo',
                'email' => 'demo-rehab-fisio@lynkamed.mx', 'rol' => 'fisioterapeuta',
                'pivot' => ['isSuperAdmin' => false, 'isAdmin' => false, 'rol_en_clinica' => 'colaborador'],
            ],
            'recepcionista' => [
                'nombre' => 'Mónica', 'apellidoPat' => 'Recep', 'apellidoMat' => 'Demo',
                'email' => 'demo-rehab-recepcion@lynkamed.mx', 'rol' => 'recepcionista',
                'pivot' => ['isSuperAdmin' => false, 'isAdmin' => false, 'rol_en_clinica' => 'colaborador'],
            ],
        ];

        $out = [];
        foreach ($defs as $key => $d) {
            $pivot = $d['pivot'];
            unset($d['pivot']);
            $user = User::create(array_merge([
                'password' => Hash::make(self::PASSWORD),
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'email_verified' => true,
                'isAdmin' => false,
                'isSuperAdmin' => false,
            ], $d));

            DB::table('user_clinicas')->insert([
                'user_id' => $user->id,
                'clinica_id' => $clinica->id,
                'rol_en_clinica' => $pivot['rol_en_clinica'],
                'isAdmin' => $pivot['isAdmin'],
                'isSuperAdmin' => $pivot['isSuperAdmin'],
                'activa' => true,
                'invitado_por' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $out[$key] = $user;
        }

        return $out;
    }

    /**
     * @return list<Paciente>
     */
    protected function createPacientes(Clinica $clinica, Sucursal $sucursal, User $doctor): array
    {
        $nombres = [
            ['Expediente', 'Completo', 'Demo'],
            ['María', 'Hernández', 'Sánchez'],
            ['Juan', 'López', 'Martínez'],
            ['Ana', 'González', 'Ramírez'],
            ['Pedro', 'Martínez', 'Flores'],
            ['Laura', 'Díaz', 'Torres'],
            ['Miguel', 'Sánchez', 'Ruiz'],
            ['Carmen', 'Ruiz', 'Vázquez'],
            ['Diego', 'Morales', 'Jiménez'],
            ['Sofía', 'Ortiz', 'Castro'],
        ];
        $tipos = ['ambos', 'cardiaca', 'pulmonar', 'ambos', 'fisioterapia', 'cardiaca', 'pulmonar', 'ambos', 'fisioterapia', 'cardiaca'];
        $pacientes = [];

        foreach ($nombres as $i => $n) {
            $fechaNac = Carbon::now()->subYears(48 + ($i % 20));
            $pacientes[] = Paciente::create([
                'registro' => $i === 0 ? 'R9999-DEMO' : 'R' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'nombre' => $n[0],
                'apellidoPat' => $n[1],
                'apellidoMat' => $n[2],
                'telefono' => '55 5000 ' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'email' => $i === 0 ? 'demo-rehab-paciente-completo@lynkamed.mx' : strtolower($n[0] . '.' . $n[1] . $i) . '.demo@lynkamed.mx',
                'fechaNacimiento' => $fechaNac,
                'edad' => $fechaNac->age,
                'genero' => $i % 2,
                'domicilio' => 'Calle Demo ' . ($i + 1) . ', Col. Centro',
                'motivo_consulta' => 'Programa de rehabilitación cardiopulmonar / física (demo)',
                'tipo_paciente' => $tipos[$i],
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'color' => ['#4ECDC4', '#FF6B6B', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'][$i],
            ]);
        }

        return $pacientes;
    }

    /**
     * @param  list<Paciente>  $pacientes
     */
    protected function linkClinicaPaciente(Clinica $clinica, Sucursal $sucursal, User $doctor, array $pacientes): void
    {
        $now = now();
        foreach ($pacientes as $idx => $p) {
            $portalDemo = $idx === 0;
            DB::table('clinica_paciente')->updateOrInsert(
                ['clinica_id' => $clinica->id, 'paciente_id' => $p->id],
                [
                    'sucursal_id' => $sucursal->id,
                    'user_id' => $doctor->id,
                    'tipo_paciente' => $p->tipo_paciente,
                    'portal_visible_citas' => $portalDemo,
                    'portal_visible_datos_basicos' => $portalDemo,
                    'portal_visible_expediente_resumen' => $portalDemo,
                    'vinculado_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Cuenta Sanctum para el portal (PacientePortalAuthController::login + multi.tenant sin clínica staff).
     */
    protected function createPortalPacienteUser(Paciente $paciente): void
    {
        User::query()
            ->where('email', $paciente->email)
            ->whereNotNull('paciente_id')
            ->delete();

        User::create([
            'nombre' => $paciente->nombre,
            'apellidoPat' => $paciente->apellidoPat,
            'apellidoMat' => $paciente->apellidoMat,
            'email' => $paciente->email,
            'password' => Hash::make(self::PASSWORD),
            'password_set_at' => now(),
            'paciente_id' => $paciente->id,
            'rol' => 'paciente',
            'clinica_id' => null,
            'sucursal_id' => null,
            'clinica_activa_id' => null,
            'isAdmin' => false,
            'isSuperAdmin' => false,
            'email_verified' => true,
        ]);
    }

    /**
     * @param  list<Paciente>  $pacientes
     * @return list<Cita>
     */
    protected function createCitas(array $pacientes, User $admin, User $doctor, Clinica $clinica, Sucursal $sucursal): array
    {
        $horas = ['09:00:00', '10:30:00', '11:00:00', '12:00:00', '16:00:00', '17:30:00'];
        $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
        $citas = [];

        foreach ([7, 3, 1, 0, 14, 5, 2, 9, 21, 4] as $i => $dias) {
            $fecha = now()->subDays($dias);
            $citas[] = Cita::create([
                'paciente_id' => $pacientes[$i % count($pacientes)]->id,
                'admin_id' => $admin->id,
                'user_id' => $doctor->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'fecha' => $fecha->format('Y-m-d'),
                'hora' => $horas[$i % count($horas)],
                'estado' => $estados[$i % count($estados)],
                'primera_vez' => $i < 2,
                'notas' => 'Cita demo rehabilitación',
            ]);
        }

        foreach ([2, 5, 8, 12, 15] as $i => $dias) {
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
                'notas' => 'Seguimiento programado (demo)',
            ]);
        }

        return $citas;
    }

    /**
     * @param  list<Paciente>  $pacientes
     * @param  list<Cita>  $citas
     */
    protected function createPagos(array $pacientes, array $citas, User $admin, Clinica $clinica, Sucursal $sucursal): void
    {
        $completadas = array_values(array_filter($citas, fn ($c) => $c->estado === 'completada'));
        $conceptos = ['Sesión rehab', 'Evaluación inicial', 'Prueba de esfuerzo', 'Nutrición', 'Psicología', 'Fisioterapia'];
        $metodos = ['efectivo', 'tarjeta', 'transferencia'];
        $montos = [650.00, 850.00, 1200.00, 400.00, 450.00, 550.00];

        for ($i = 0; $i < 12; $i++) {
            $citaId = count($completadas) > 0 && $i < 6 ? $completadas[$i % count($completadas)]->id : null;
            Pago::create([
                'paciente_id' => $pacientes[$i % count($pacientes)]->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'user_id' => $admin->id,
                'cita_id' => $citaId,
                'monto' => $montos[$i % count($montos)],
                'metodo_pago' => $metodos[$i % count($metodos)],
                'referencia' => $citaId ? 'DEMO-CITA-' . $citaId : 'DEMO-SIN-CITA',
                'concepto' => $conceptos[$i % count($conceptos)],
                'notas' => 'Pago demostración caja',
                'firma_paciente' => null,
                'fecha_pago' => now()->subDays($i % 20)->toDateString(),
            ]);
        }
    }

    protected function createEgresos(Clinica $clinica, Sucursal $sucursal, User $admin): void
    {
        Egreso::create([
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $admin->id,
            'monto' => 3200.50,
            'tipo_egreso' => 'compra_material',
            'concepto' => 'Material de rehabilitación y consumibles',
            'proveedor' => 'MedSupply Demo SA',
            'factura' => 'FAC-DEMO-001',
            'notas' => 'Egreso de demostración',
        ]);
        Egreso::create([
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $admin->id,
            'monto' => 1500.00,
            'tipo_egreso' => 'servicio',
            'concepto' => 'Mantenimiento de equipos cardiopulmonares',
            'proveedor' => 'Servicio Técnico Demo',
            'factura' => null,
            'notas' => null,
        ]);
    }

    protected function createAllExpedientesForPaciente(
        Paciente $paciente,
        Clinica $clinica,
        Sucursal $sucursal,
        User $doctor,
        User $nutri,
        User $psico,
        User $fisio
    ): void {
        $fecha = now()->subDays(10)->format('Y-m-d');

        $this->insertDemoClinico($paciente, $doctor, $clinica, $sucursal, $fecha);
        $this->insertDemoEsfuerzo($paciente, $doctor, $clinica, $sucursal, $fecha);
        $this->insertDemoEstratificacionInch($paciente, $doctor, $clinica, $sucursal, $fecha);

        EstratiAacvpr::create([
            'paciente_id' => $paciente->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'fecha_estratificacion' => $fecha,
        ]);

        $this->insertDemoReporteFinalCardio($paciente, $doctor, $clinica, $sucursal, $fecha);

        $this->insertDemoReporteNutri($paciente, $nutri, $clinica, $sucursal);
        $this->insertDemoReportePsico($paciente, $psico, $clinica, $sucursal);
        $this->insertDemoReporteFisio($paciente, $fisio, $clinica, $sucursal);

        $this->createFisioNotas($paciente, $fisio, $clinica, $sucursal, $fecha);

        $pulmonar = ExpedientePulmonar::create([
            'paciente_id' => $paciente->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'fecha_consulta' => $fecha,
            'hora_consulta' => '09:30',
            'fc' => 78,
            'ta' => '120/80',
            'sat_aa' => 96,
        ]);

        NotaSeguimientoPulmonar::create([
            'paciente_id' => $paciente->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'expediente_pulmonar_id' => $pulmonar->id,
            'tipo_exp' => 19,
            'fecha_consulta' => $fecha,
            'hora_consulta' => '10:00:00',
            's_subjetivo' => 'Mejor tolerancia al esfuerzo (demo).',
            'o_objetivo' => 'SpO2 estable, FC en rango.',
            'a_apreciacion' => 'Evolución favorable.',
            'p_plan' => 'Continuar programa; control en 1 semana.',
        ]);

        PruebaEsfuerzoPulmonar::create([
            'paciente_id' => $paciente->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'fecha_realizacion' => $fecha,
            'diagnosticos' => 'EPOC leve (demo)',
            'basal_fc' => 72,
            'basal_tas' => 120,
            'basal_tad' => 78,
            'basal_saturacion' => 95,
        ]);

        ReporteFinalPulmonar::create([
            'paciente_id' => $paciente->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'tipo_exp' => 15,
            'fecha_inicio' => now()->subMonths(2)->toDateString(),
            'fecha_termino' => $fecha,
            'diagnostico' => 'Rehabilitación pulmonar — alta demo',
            'descripcion_programa' => 'Programa de 8 semanas (datos ficticios).',
        ]);

        CualidadFisica::create([
            'paciente_id' => $paciente->id,
            'user_id' => $fisio->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'fecha_prueba_inicial' => now()->subWeeks(6)->toDateString(),
            'fecha_prueba_final' => $fecha,
            'ta_inicial' => '118/76',
            'fc_inicial' => '74',
            'sato2_inicial' => '97',
        ]);
    }

    protected function createFisioNotas(Paciente $paciente, User $fisio, Clinica $clinica, Sucursal $sucursal, string $fecha): void
    {
        $h = HistoriaClinicaFisioterapia::create([
            'paciente_id' => $paciente->id,
            'user_id' => $fisio->id,
            'fecha' => $fecha,
            'hora' => '10:00',
            'ocupacion' => 'Empleado administrativo',
            'motivo_consulta' => 'Dolor lumbar mecánico (demo)',
            'padecimiento_actual' => 'Lumbalgia',
            'antecedentes_heredofamiliares' => 'Ninguno',
            'antecedentes_personales_patologicos' => 'Ninguno',
            'antecedentes_personales_no_patologicos' => 'Sedentarismo',
            'antecedentes_quirurgicos_traumaticos' => 'Ninguno',
            'signos_vitales' => 'TA 120/80',
            'inspeccion' => 'Normal',
            'palpacion' => 'Contractura paravertebral',
            'rango_movimiento' => 'Flexión limitada',
            'fuerza_muscular' => '5/5',
            'pruebas_especiales' => 'Negativas',
            'diagnostico_medico' => 'Lumbalgia',
            'diagnostico_fisioterapeutico' => 'Disfunción articular',
            'objetivos_tratamiento' => 'Reducir dolor',
            'modalidades_terapeuticas' => 'Ejercicio terapéutico',
            'educacion_paciente' => 'Higiene postural',
            'pronostico' => 'Favorable',
        ]);
        $h->clinica_id = $clinica->id;
        $h->sucursal_id = $sucursal->id;
        $h->save();

        $e = NotaEvolucionFisioterapia::create([
            'paciente_id' => $paciente->id,
            'user_id' => $fisio->id,
            'fecha' => $fecha,
            'hora' => '11:00',
            'diagnostico_fisioterapeutico' => 'Disfunción lumbar',
            'observaciones_subjetivas' => 'Menor dolor',
            'dolor_eva' => 3,
            'funcionalidad' => 'Mejor',
            'observaciones_objetivas' => 'Mayor ROM',
            'tecnicas_modalidades_aplicadas' => 'Ejercicio, calor',
            'ejercicio_terapeutico' => 'Core',
            'respuesta_tratamiento' => 'Buena',
            'plan' => '3 sesiones más',
        ]);
        $e->clinica_id = $clinica->id;
        $e->sucursal_id = $sucursal->id;
        $e->save();

        $a = NotaAltaFisioterapia::create([
            'paciente_id' => $paciente->id,
            'user_id' => $fisio->id,
            'fecha' => $fecha,
            'diagnostico_medico' => 'Lumbalgia',
            'diagnostico_fisioterapeutico_inicial' => 'Disfunción lumbar',
            'fecha_inicio_atencion' => Carbon::parse($fecha)->subDays(21),
            'fecha_termino' => $fecha,
            'numero_sesiones' => 8,
            'tratamiento_otorgado' => 'Ejercicio terapéutico',
            'evolucion_resultados' => 'Alta satisfactoria',
            'dolor_alta_eva' => 1,
            'mejoria_funcional' => 'Sí',
            'objetivos_alcanzados' => 'Sí',
            'estado_funcional_alta' => 'Independiente',
            'recomendaciones_seguimiento' => 'Continuar ejercicio en casa',
            'pronostico_funcional' => 'Favorable',
        ]);
        $a->clinica_id = $clinica->id;
        $a->sucursal_id = $sucursal->id;
        $a->save();
    }

    /**
     * @param  list<Paciente>  $pacientes
     */
    protected function createSparseExpedientesForOthers(array $pacientes, User $doctor, Clinica $clinica, Sucursal $sucursal, User $fisio): void
    {
        $fecha = now()->subDays(6)->format('Y-m-d');

        foreach ($pacientes as $idx => $paciente) {
            $tipo = $paciente->tipo_paciente;

            if (in_array($tipo, ['cardiaca', 'ambos'], true)) {
                $this->insertDemoClinico($paciente, $doctor, $clinica, $sucursal, $fecha);
            }
            if ($tipo === 'pulmonar' || $tipo === 'ambos') {
                ExpedientePulmonar::create([
                    'paciente_id' => $paciente->id,
                    'user_id' => $doctor->id,
                    'clinica_id' => $clinica->id,
                    'sucursal_id' => $sucursal->id,
                    'fecha_consulta' => $fecha,
                    'hora_consulta' => '08:00',
                    'sat_aa' => 94,
                ]);
            }
            if ($tipo === 'fisioterapia' || $idx % 3 === 0) {
                $this->insertDemoReporteFisio($paciente, $fisio, $clinica, $sucursal);
            }
        }
    }

    protected function createRecetas(Paciente $paciente, User $doctor, Clinica $clinica, Sucursal $sucursal): void
    {
        $base = [
            'paciente_id' => $paciente->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'fecha' => now()->subDays(2)->toDateString(),
            'diagnostico_principal' => 'HTA en control; dislipidemia (demo)',
            'indicaciones_generales' => 'Dieta mediterránea; actividad física moderada.',
        ];

        $r1 = Receta::create(array_merge($base, ['folio' => 90001]));
        RecetaMedicamento::create([
            'receta_id' => $r1->id,
            'medicamento' => 'Losartán',
            'presentacion' => '50 mg',
            'dosis' => '1 tableta',
            'frecuencia' => 'cada 24 h',
            'duracion' => '30 días',
            'indicaciones_especificas' => 'Tomar en ayunas o con alimentos.',
            'orden' => 1,
        ]);

        $r2 = Receta::create(array_merge($base, [
            'folio' => 90002,
            'fecha' => now()->subDays(5)->toDateString(),
            'diagnostico_principal' => 'Dolor musculoesquelético (demo)',
        ]));
        RecetaMedicamento::create([
            'receta_id' => $r2->id,
            'medicamento' => 'Paracetamol',
            'presentacion' => '500 mg',
            'dosis' => '1-2 tabletas',
            'frecuencia' => 'cada 8 h si dolor',
            'duracion' => '5 días',
            'indicaciones_especificas' => 'No exceder 4 g/día.',
            'orden' => 1,
        ]);

        $r3 = Receta::create(array_merge($base, [
            'folio' => 90003,
            'fecha' => now()->subDays(8)->toDateString(),
            'diagnostico_principal' => 'Seguimiento post-rehab (demo)',
            'indicaciones_generales' => 'Acudir a control en 4 semanas.',
        ]));
        RecetaMedicamento::create([
            'receta_id' => $r3->id,
            'medicamento' => 'Atorvastatina',
            'presentacion' => '20 mg',
            'dosis' => '1 tableta',
            'frecuencia' => 'por la noche',
            'duracion' => '90 días',
            'indicaciones_especificas' => null,
            'orden' => 1,
        ]);
    }

    protected function insertDemoReporteNutri(Paciente $p, User $nutri, Clinica $clinica, Sucursal $sucursal): void
    {
        $ts = now();
        DB::table('reporte_nutris')->insert([
            'paciente_id' => $p->id,
            'user_id' => $nutri->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'tipo_exp' => 6,
            'diagnostico' => 'Sobrepeso grado I (demo)',
            'recomendaciones' => 'Plan alimentario hipocalórico; hidratación adecuada.',
            'nutriologo' => 'Lic. Nutrición Demo',
            'cedula_nutriologo' => '12345678',
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);
    }

    protected function insertDemoReportePsico(Paciente $p, User $psico, Clinica $clinica, Sucursal $sucursal): void
    {
        $ts = now();
        DB::table('reporte_psicos')->insert([
            'paciente_id' => $p->id,
            'user_id' => $psico->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'tipo_exp' => 5,
            'motivo_consulta' => 'Ansiedad relacionada con enfermedad crónica (demo)',
            'plan_tratamiento' => 'Psicoeducación y técnicas de relajación.',
            'psicologo' => 'Psic. Demo',
            'cedula_psicologo' => '87654321',
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);
    }

    protected function insertDemoReporteFisio(Paciente $p, User $fisio, Clinica $clinica, Sucursal $sucursal): void
    {
        if (Schema::hasColumn('reporte_fisios', 'contenido')) {
            $attrs = [
                'paciente_id' => $p->id,
                'user_id' => $fisio->id,
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'tipo_exp' => 7,
                'contenido' => 'Valoración fisioterapéutica global (demo).',
            ];
            if (Schema::hasColumn('reporte_fisios', 'fecha')) {
                $attrs['fecha'] = now()->toDateString();
            }
            ReporteFisio::create($attrs);

            return;
        }

        $ts = now();
        $row = [
            'paciente_id' => $p->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'tipo_exp' => 7,
            'archivo' => null,
            'created_at' => $ts,
            'updated_at' => $ts,
        ];
        if (Schema::hasColumn('reporte_fisios', 'user_id')) {
            $row['user_id'] = $fisio->id;
        }
        DB::table('reporte_fisios')->insert($row);
    }

    protected function insertDemoClinico(Paciente $p, User $doctor, Clinica $clinica, Sucursal $sucursal, string $fecha): void
    {
        $ts = now();
        DB::table('clinicos')->insert([
            'paciente_id' => $p->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'fecha' => $fecha,
            'tipo_exp' => 3,
            'diagnostico_general' => 'Evaluación clínica inicial (datos de demostración).',
            'plan' => 'Continuar programa de rehabilitación cardíaca según criterios médicos.',
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);
    }

    protected function insertDemoEsfuerzo(Paciente $p, User $doctor, Clinica $clinica, Sucursal $sucursal, string $fecha): void
    {
        $ts = now();
        DB::table('esfuerzos')->insert([
            'paciente_id' => $p->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'fecha' => $fecha,
            'tipo_exp' => 1,
            'metodo' => 'Ergometría (demo)',
            'conclusiones' => 'Registro ficticio para demostración del sistema.',
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);
    }

    protected function insertDemoEstratificacionInch(Paciente $p, User $doctor, Clinica $clinica, Sucursal $sucursal, string $fecha): void
    {
        $ts = now();
        DB::table('estratificacions')->insert([
            'paciente_id' => $p->id,
            'user_id' => $doctor->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'estrati_fecha' => $fecha,
            'tipo_exp' => 2,
            'comentarios' => 'Estratificación INCH — registro demo.',
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);
    }

    protected function insertDemoReporteFinalCardio(Paciente $p, User $doctor, Clinica $clinica, Sucursal $sucursal, string $fecha): void
    {
        $ts = now();
        DB::table('reporte_finals')->insert([
            'fecha_inicio' => $fecha,
            'fecha_final' => $fecha,
            'fecha' => 0,
            'fc_basal' => 0,
            'doble_pr_bas' => 0,
            'fc_maxima' => 0,
            'doble_pr_max' => 0,
            'fc_borg12' => 0,
            'doble_pr_b12' => 0,
            'carga_max' => 0,
            'mets_por' => 0,
            'tiempo_ejer' => 0,
            'recup_fc' => 0,
            'umbral_isq' => 0,
            'umbral_isq_fc' => 0,
            'max_des_st' => 0,
            'indice_ta_es' => 0,
            'recup_tas' => 0,
            'resp_crono' => 0,
            'iem' => 0,
            'pod_car_eje' => 0,
            'duke' => 0,
            'veteranos' => 0,
            'score_ang' => 0,
            'pe_1' => 0,
            'pe_2' => 0,
            'tipo_exp' => 4,
            'user_id' => $doctor->id,
            'paciente_id' => $p->id,
            'clinica_id' => $clinica->id,
            'sucursal_id' => $sucursal->id,
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);
    }

    protected function purgeDemoClinica(Clinica $clinica): void
    {
        $cid = $clinica->id;
        $pids = Paciente::where('clinica_id', $cid)->pluck('id')->all();
        $userIds = User::where('clinica_id', $cid)->pluck('id')->all();

        DB::transaction(function () use ($cid, $pids, $userIds) {
            $recetaIds = Receta::where('clinica_id', $cid)->pluck('id');
            if ($recetaIds->isNotEmpty()) {
                RecetaMedicamento::whereIn('receta_id', $recetaIds)->delete();
            }
            Receta::where('clinica_id', $cid)->delete();

            Pago::where('clinica_id', $cid)->delete();
            Cita::where('clinica_id', $cid)->delete();
            Egreso::where('clinica_id', $cid)->delete();

            if ($pids !== []) {
                PruebaEsfuerzoPulmonar::whereIn('paciente_id', $pids)->delete();
                NotaSeguimientoPulmonar::whereIn('paciente_id', $pids)->delete();
                ReporteFinalPulmonar::whereIn('paciente_id', $pids)->delete();
                ExpedientePulmonar::whereIn('paciente_id', $pids)->delete();
                CualidadFisica::whereIn('paciente_id', $pids)->delete();
                EstratiAacvpr::whereIn('paciente_id', $pids)->delete();
                Estratificacion::whereIn('paciente_id', $pids)->delete();
                Esfuerzo::whereIn('paciente_id', $pids)->delete();
                Clinico::whereIn('paciente_id', $pids)->delete();
                ReporteFinal::whereIn('paciente_id', $pids)->delete();
                ReporteNutri::whereIn('paciente_id', $pids)->delete();
                ReportePsico::whereIn('paciente_id', $pids)->delete();
                ReporteFisio::whereIn('paciente_id', $pids)->delete();
                HistoriaClinicaFisioterapia::whereIn('paciente_id', $pids)->delete();
                NotaEvolucionFisioterapia::whereIn('paciente_id', $pids)->delete();
                NotaAltaFisioterapia::whereIn('paciente_id', $pids)->delete();

                if (Schema::hasTable('paciente_portal_otps')) {
                    DB::table('paciente_portal_otps')->whereIn('paciente_id', $pids)->delete();
                }
                User::whereIn('paciente_id', $pids)->whereNull('clinica_id')->delete();
            }

            if (Schema::hasTable('portal_expediente_compartidos')) {
                DB::table('portal_expediente_compartidos')->where('clinica_id', $cid)->delete();
            }

            DB::table('clinica_paciente')->where('clinica_id', $cid)->delete();

            if (Schema::hasTable('eventos')) {
                DB::table('eventos')->where('clinica_id', $cid)->delete();
            }

            if ($userIds !== []) {
                UserPermission::whereIn('user_id', $userIds)->delete();
            }
            if (Schema::hasTable('user_sucursal') && $userIds !== []) {
                DB::table('user_sucursal')->whereIn('user_id', $userIds)->delete();
            }

            DB::table('user_clinicas')->where('clinica_id', $cid)->delete();

            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->where('clinica_id', $cid)->delete();
            }
            if (Schema::hasTable('ai_usage')) {
                DB::table('ai_usage')->where('clinica_id', $cid)->delete();
            }

            Paciente::where('clinica_id', $cid)->delete();
            User::where('clinica_id', $cid)->delete();
            Sucursal::where('clinica_id', $cid)->delete();
            Clinica::where('id', $cid)->delete();
        });
    }
}
