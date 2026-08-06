<?php

namespace Tests\Feature;

use App\Http\Controllers\ClinicoController;
use App\Models\Clinica;
use App\Models\Clinico;
use App\Models\ControlPrenatal;
use App\Models\CualidadFisica;
use App\Models\Ecocardiograma;
use App\Models\Electrocardiograma;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\EstratiAacvpr;
use App\Models\ExpedientePulmonar;
use App\Models\HistoriaClinicaCardiologia;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\HistoriaClinicaNutricion;
use App\Models\HistoriaGinecologica;
use App\Models\HistoriaObstetrica;
use App\Models\NotaAltaFisioterapia;
use App\Models\NotaClinicaSoapNutricional;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaSubsecuenteCardiologia;
use App\Models\Paciente;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\SeguimientoNutricional;
use App\Models\User;
use App\Support\FormValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Regresión: '' en date/time/double no debe romper update de expedientes.
 */
class ExpedienteEmptyValuesUpdateTest extends TestCase
{
    use DatabaseTransactions;

    private Clinica $clinica;
    private User $user;
    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinica::create([
            'nombre' => 'Clínica Exp Empty '.uniqid(),
            'email' => 'exp-empty-'.uniqid().'@test.local',
            'activa' => true,
            'pagado' => true,
            'fecha_vencimiento' => now()->addMonth()->toDateString(),
        ]);

        $this->user = User::create([
            'nombre' => 'Doc',
            'apellidoPat' => 'Empty',
            'apellidoMat' => 'Test',
            'email' => 'doc-empty-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
            'clinica_id' => $this->clinica->id,
            'email_verified' => true,
        ]);

        DB::table('user_clinicas')->insert([
            'user_id' => $this->user->id,
            'clinica_id' => $this->clinica->id,
            'rol_en_clinica' => 'propietario',
            'activa' => 1,
            'isAdmin' => 1,
            'isSuperAdmin' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->clinica->update(['propietario_user_id' => $this->user->id]);

        $this->paciente = Paciente::create([
            'nombre' => 'Paciente',
            'apellidoPat' => 'Empty',
            'apellidoMat' => 'Vals',
            'registro' => (string) random_int(100000, 999999),
            'email' => 'pac-empty-'.uniqid().'@test.local',
            'fechaNacimiento' => '1990-01-15',
            'edad' => '36',
            'genero' => 1,
            'user_id' => $this->user->id,
            'clinica_id' => $this->clinica->id,
        ]);

        DB::table('clinica_paciente')->insert([
            'clinica_id' => $this->clinica->id,
            'paciente_id' => $this->paciente->id,
            'user_id' => $this->user->id,
            'vinculado_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function emptyPayloadForTable(string $table, array $keep = []): array
    {
        $payload = $keep;
        $cols = DB::select("SHOW COLUMNS FROM `{$table}`");

        foreach ($cols as $col) {
            $field = $col->Field;
            if (array_key_exists($field, $payload)) {
                continue;
            }
            if (in_array($field, [
                'id', 'user_id', 'paciente_id', 'clinica_id', 'sucursal_id',
                'created_at', 'updated_at', 'deleted_at', 'historia_obstetrica_id',
            ], true)) {
                continue;
            }

            $type = strtolower($col->Type);
            $isRisky = (bool) preg_match(
                '/^(date|time|datetime|timestamp|double|float|decimal|int|bigint|smallint|tinyint|mediumint)/',
                $type
            );

            if (! $isRisky) {
                continue;
            }

            // No tocar columnas NOT NULL (dejar valor existente).
            if ($col->Null === 'NO') {
                continue;
            }

            $payload[$field] = '';
        }

        return FormValue::sanitize($payload, false);
    }

    private function assertModelSurvivesEmptyUpdate(string $modelClass, array $createAttrs, string $table): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $modelClass::create($createAttrs);
        $payload = $this->emptyPayloadForTable($table);

        try {
            $model->forceFill($payload)->save();
        } catch (\Throwable $e) {
            $this->fail("{$modelClass} update with empty risky fields failed: {$e->getMessage()}");
        }

        $this->assertTrue($model->fresh()->exists);
    }

    public function test_form_value_sanitize_makes_empty_dates_safe_for_mysql(): void
    {
        $sanitized = FormValue::sanitize([
            'fecha' => '',
            'hora' => '',
            'fc' => '',
            'peso' => '  ',
        ], false);

        $this->assertNull($sanitized['fecha']);
        $this->assertNull($sanitized['hora']);
        $this->assertNull($sanitized['fc']);
        $this->assertNull($sanitized['peso']);
    }

    public function test_clinico_update_with_empty_dates_and_zero_fc_does_not_500(): void
    {
        Sanctum::actingAs($this->user);

        $clinico = new Clinico();
        $clinico->paciente_id = $this->paciente->id;
        $clinico->user_id = $this->user->id;
        $clinico->clinica_id = $this->clinica->id;
        $clinico->fecha = '2026-01-15';
        $clinico->save();

        $datos = [
            'fecha' => '2026-01-15',
            'fecha_1vez' => '',
            'hora' => '',
            'imAnterior' => '',
            'imSeptal' => '',
            'imApical' => '',
            'inicioFase2' => '',
            'finFase2' => '',
            'fechaTra' => '',
            'ecoFecha' => '',
            'mnFecha' => '',
            'vrieFecha' => '',
            'holterFecha' => '',
            'catetFecha' => '',
            'Vez1aFecha' => '',
            'claseCcs' => '',
            'cfNyha' => '',
            'cigxDia' => '',
            'FC' => '',
            'qtm' => '',
            'fc_ecog' => '',
            'fcEcog' => '',
            'diagnosticoGeneral' => str_repeat('nota clinica ', 30),
            'plan' => str_repeat('plan clinico ', 30),
            'exploracionFisica' => '',
            'estudios' => '',
        ];

        $response = $this->putJson('/api/clinico/'.$clinico->id, ['datos' => $datos]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Actualizado correctamente']);

        $fresh = $clinico->fresh();
        $this->assertNull($fresh->fecha_1vez);
        $this->assertNull($fresh->qtc);
    }

    public function test_clinico_fill_from_form_datos_handles_empty_qtc_inputs(): void
    {
        $clinico = new Clinico();
        $clinico->paciente_id = $this->paciente->id;
        $clinico->user_id = $this->user->id;
        $clinico->clinica_id = $this->clinica->id;
        $clinico->fecha = '2026-02-01';
        $clinico->save();

        Auth::login($this->user);

        $controller = app(ClinicoController::class);
        $method = new ReflectionMethod(ClinicoController::class, 'fillFromFormDatos');
        $method->setAccessible(true);
        $method->invoke($controller, $clinico, [
            'fecha' => '2026-02-01',
            'qtm' => '',
            'fcEcog' => '',
            'FC' => '',
            'inicioFase2' => '',
        ]);
        $clinico->save();

        $this->assertNull($clinico->fresh()->qtc);
        $this->assertNull($clinico->fresh()->inicio_fase_2);
    }

    public function test_seguimiento_nutricional_update_empty_optional_fields(): void
    {
        Sanctum::actingAs($this->user);

        $row = SeguimientoNutricional::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_elaboracion' => '2026-03-01',
            'numero_seguimiento' => 1,
            'observaciones' => 'inicial',
        ]);

        $response = $this->putJson('/api/nutricion/seguimiento/'.$row->id, [
            'fecha_elaboracion' => '2026-03-02',
            'observaciones' => '',
            'valoracion_bioquimica' => '',
        ]);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue(in_array($response->status(), [200, 201], true), $response->getContent());
    }

    public function test_historia_cardio_update_empty_proxima_cita(): void
    {
        Sanctum::actingAs($this->user);

        $row = HistoriaClinicaCardiologia::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_consulta' => '2026-03-01',
            'hora' => '10:00:00',
            'motivo_consulta' => 'dolor',
        ]);

        $response = $this->putJson('/api/cardiologia/historia/'.$row->id, [
            'fecha_consulta' => '2026-03-01',
            'hora' => '10:00',
            'proxima_cita' => '',
            'motivo_consulta' => 'dolor',
        ]);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertNull($row->fresh()->proxima_cita);
    }

    public function test_soap_nutricional_update_empty_strings(): void
    {
        Sanctum::actingAs($this->user);

        $row = NotaClinicaSoapNutricional::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_elaboracion' => '2026-03-01',
            'subjetivo' => 's',
            'objetivo' => 'o',
            'analisis' => 'a',
            'plan' => 'p',
        ]);

        $response = $this->putJson('/api/nutricion/soap/'.$row->id, [
            'fecha_elaboracion' => '2026-03-01',
            'subjetivo' => '',
            'objetivo' => '',
            'analisis' => '',
            'plan' => '',
        ]);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
    }

    public function test_pulmonar_update_empty_optional_numeric_and_dates(): void
    {
        Sanctum::actingAs($this->user);

        $row = ExpedientePulmonar::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'fecha_consulta' => '2026-03-01',
            'hora_consulta' => '09:00:00',
        ]);

        $response = $this->putJson('/api/pulmonar/'.$row->id, [
            'fecha_consulta' => '2026-03-01',
            'hora_consulta' => '09:00',
            'covid19_fecha_ultima_dosis' => '',
            'covid19_numero_dosis' => '',
            'influenza_ano' => '',
            'neumococo_ano' => '',
            'actividad_fisica_dias_semana' => '',
            'actividad_fisica_tiempo_dia' => '',
        ]);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
    }

    public function test_estratificacion_update_empty_dates_and_numbers(): void
    {
        Sanctum::actingAs($this->user);

        $row = new Estratificacion();
        $row->paciente_id = $this->paciente->id;
        $row->user_id = $this->user->id;
        $row->clinica_id = $this->clinica->id;
        $row->tipo_exp = 2;
        $row->primeravez_rhc = '2026-01-01';
        $row->save();

        $payload = [
            'rhc_1_fecha' => '',
            'pe' => '',
            'estrati' => '',
            'cIsquemia' => '',
            'sesiones' => '',
            'im' => false,
            'ima' => false,
            'imas' => false,
            'imaa' => false,
            'imal' => false,
            'imae' => false,
            'imInf' => false,
            'impi' => false,
            'impiVd' => false,
            'imLat' => false,
            'imSesst' => false,
            'imComplicado' => false,
            'valvular' => '',
            'otro' => false,
            'mcd' => false,
            'icc' => false,
            'reanimacion' => false,
            'fallaEntrenar' => false,
            'tabaquismo' => false,
            'dislipidemia' => false,
            'dm' => false,
            'has' => false,
            'obesidad' => false,
            'estres' => false,
            'sedentarismo' => false,
            'otroFactor' => '',
            'depresion' => false,
            'ansiedad' => false,
            'sintomatologia' => '',
            'puntuacionAtp' => '',
            'heartScore' => '',
            'colTotal' => '',
            'ldl' => '',
            'hdl' => '',
            'tg' => '',
            'fevi' => '',
            'pcr' => '',
            'enfCoronaria' => '',
            'isquemia' => '',
            'isquemiaIrm' => '',
            'eco' => '',
            'holter' => '',
            'capacidadPe' => false,
            'fcBasal' => '',
            'fcMax' => '',
            'fcBorg12' => '',
            'dpBorg12' => '',
            'metsBorg12' => '',
            'carga_maxima' => '',
            'tolerancia_esfuerzo' => '',
            'respuestaPre' => '',
            'indiceTa' => '',
            'porcentajeFC' => '',
            'cronotr' => '',
            'poderCardiaco' => '',
            'recuperacionTas' => '',
            'recuperacionFc' => '',
            'duke' => '',
            'veteranos' => '',
            'ectopiaVen' => false,
            'umbralIs' => false,
            'supradesnivel' => false,
            'infra135' => false,
            'infra5' => false,
            'riesgoGlobal' => '',
            'grupo' => '',
            'semanas' => '',
            'borg' => '',
            'fcDiana' => '',
            'dpDiana' => '',
            'comentarios' => '',
            'fcdianaNumber' => '',
        ];

        $response = $this->putJson('/api/estratificacion/'.$row->id, $payload);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertNull($row->fresh()->primeravez_rhc);
    }

    public function test_electrocardiograma_update_empty_hora(): void
    {
        Sanctum::actingAs($this->user);

        $row = Electrocardiograma::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_estudio' => '2026-03-01',
            'hora' => '10:00:00',
            'urgente' => 0,
            'comparado_previo' => 0,
            'interpretacion' => 'normal',
        ]);

        $response = $this->putJson('/api/cardiologia/ecg/'.$row->id, [
            'fecha_estudio' => '2026-03-01',
            'hora' => '',
            'interpretacion' => 'normal',
            'paciente' => ['id' => $this->paciente->id],
            'user' => ['id' => $this->user->id],
        ]);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertNull($row->fresh()->hora);
    }

    public function test_ecocardiograma_update_empty_hora(): void
    {
        Sanctum::actingAs($this->user);

        $row = Ecocardiograma::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_estudio' => '2026-03-01',
            'hora' => '11:00:00',
        ]);

        $response = $this->putJson('/api/cardiologia/ecocardiograma/'.$row->id, [
            'fecha_estudio' => '2026-03-01',
            'hora' => '',
            'conclusiones' => '',
        ]);

        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertNull($row->fresh()->hora);
    }

    public function test_gine_obste_prenatal_updates_with_empty_hora(): void
    {
        Sanctum::actingAs($this->user);

        $gine = HistoriaGinecologica::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_consulta' => '2026-03-01',
            'hora' => '09:00:00',
            'motivo_consulta' => 'control',
        ]);

        $r1 = $this->putJson('/api/ginecologia/historia/'.$gine->id, [
            'fecha_consulta' => '2026-03-01',
            'hora' => '',
            'motivo_consulta' => 'control',
        ]);
        $this->assertNotEquals(500, $r1->status(), $r1->getContent());
        $this->assertTrue($r1->isSuccessful(), $r1->getContent());

        $obste = HistoriaObstetrica::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'tipo_exp' => 1,
            'fecha_consulta' => '2026-03-01',
            'hora' => '09:00:00',
            'motivo_consulta' => 'embarazo',
        ]);

        $r2 = $this->putJson('/api/obstetricia/historia/'.$obste->id, [
            'fecha_consulta' => '2026-03-01',
            'hora' => '',
            'motivo_consulta' => 'embarazo',
        ]);
        $this->assertNotEquals(500, $r2->status(), $r2->getContent());
        $this->assertTrue($r2->isSuccessful(), $r2->getContent());

        $control = ControlPrenatal::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'historia_obstetrica_id' => $obste->id,
            'tipo_exp' => 1,
            'fecha_control' => '2026-03-01',
            'hora' => '09:00:00',
            'numero_control' => 1,
        ]);

        $r3 = $this->putJson('/api/obstetricia/control-prenatal/'.$control->id, [
            'fecha_control' => '2026-03-01',
            'hora' => '',
            'numero_control' => 1,
            'fecha_proxima_cita' => '',
        ]);
        $this->assertNotEquals(500, $r3->status(), $r3->getContent());
        $this->assertTrue($r3->isSuccessful(), $r3->getContent());
        $this->assertNull($control->fresh()->fecha_proxima_cita);
    }

    public function test_fisio_updates_with_empty_optional_fields(): void
    {
        Sanctum::actingAs($this->user);

        $historia = HistoriaClinicaFisioterapia::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'clinica_id' => $this->clinica->id,
            'fecha' => '2026-03-01',
            'hora' => '10:00',
        ]);

        $r1 = $this->putJson('/api/fisioterapia/historia/'.$historia->id, [
            'fecha' => '2026-03-01',
            'hora' => '10:00',
            'diagnostico' => '',
            'objetivo' => '',
        ]);
        $this->assertNotEquals(500, $r1->status(), $r1->getContent());
        $this->assertTrue($r1->isSuccessful(), $r1->getContent());

        $evo = NotaEvolucionFisioterapia::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'fecha' => '2026-03-01',
            'hora' => '10:00',
        ]);
        $r2 = $this->putJson('/api/fisioterapia/evolucion/'.$evo->id, [
            'fecha' => '2026-03-01',
            'hora' => '10:00',
            'evolucion' => '',
        ]);
        $this->assertNotEquals(500, $r2->status(), $r2->getContent());
        $this->assertTrue($r2->isSuccessful(), $r2->getContent());

        $alta = NotaAltaFisioterapia::create([
            'user_id' => $this->user->id,
            'paciente_id' => $this->paciente->id,
            'fecha' => '2026-03-01',
            'hora' => '10:00',
        ]);
        $r3 = $this->putJson('/api/fisioterapia/alta/'.$alta->id, [
            'fecha' => '2026-03-01',
            'hora' => '10:00',
            'recomendaciones' => '',
        ]);
        $this->assertNotEquals(500, $r3->status(), $r3->getContent());
        $this->assertTrue($r3->isSuccessful(), $r3->getContent());
    }

    public function test_model_level_empty_risky_columns_for_remaining_expedientes(): void
    {
        $cases = [
            [HistoriaClinicaNutricion::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
                'tipo_exp' => 1,
                'fecha_elaboracion' => '2026-03-01',
            ], 'historia_clinica_nutricion'],
            [NotaSubsecuenteCardiologia::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
                'tipo_exp' => 1,
                'fecha_consulta' => '2026-03-01',
            ], 'nota_subsecuente_cardiologias'],
            [CualidadFisica::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
                'fecha_prueba_inicial' => '2026-03-01',
            ], 'cualidades_fisicas'],
            [Esfuerzo::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
                'fecha' => '2026-03-01',
            ], 'esfuerzos'],
            [ReportePsico::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
            ], 'reporte_psicos'],
            [ReporteNutri::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
            ], 'reporte_nutris'],
        ];

        if (Schema::hasTable('estrati_aacvprs')) {
            $cases[] = [EstratiAacvpr::class, [
                'user_id' => $this->user->id,
                'paciente_id' => $this->paciente->id,
                'clinica_id' => $this->clinica->id,
                'fecha_estratificacion' => '2026-03-01',
            ], 'estrati_aacvprs'];
        }

        foreach ($cases as [$class, $attrs, $table]) {
            $this->assertModelSurvivesEmptyUpdate($class, $attrs, $table);
        }
    }

    public function test_esfuerzo_http_update_with_empty_numeric_fields_does_not_500(): void
    {
        Sanctum::actingAs($this->user);

        $row = new Esfuerzo();
        $row->paciente_id = $this->paciente->id;
        $row->user_id = $this->user->id;
        $row->clinica_id = $this->clinica->id;
        $row->fecha = '2026-03-01';
        $row->save();

        $payload = [
            'fecha' => '2026-03-01',
            'confusor' => '',
            'fcBasal' => '',
            'tasBasal' => '',
            'tadBasal' => '',
            'fcBorg12' => '',
            'tasBorg12' => '',
            'tadBorg12' => '',
            'fcw50' => '',
            'tasw50' => '',
            'tasMax' => '',
            'fcMax' => '',
            'fc1erMin' => '',
            'tas1erMin' => '',
            'fc3erMin' => '',
            'tas3erMin' => '',
            'icc' => '',
            'maxInfradesnivel' => '',
            'borgUisq' => '',
            'fcUisq' => '',
            'tasUisq' => '',
            'banda' => '',
            'ciclo' => '',
            'medicionGases' => '',
            'paciente' => ['id' => $this->paciente->id, 'edad' => 36, 'genero' => 1],
        ];

        $response = $this->putJson('/api/esfuerzo/'.$row->id, $payload);
        $this->assertNotEquals(500, $response->status(), $response->getContent());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
    }
}
