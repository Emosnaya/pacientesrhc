<?php

namespace Tests\Feature;

use App\Jobs\BuildClinicaExpedienteExport;
use App\Models\Clinica;
use App\Models\ClinicaExport;
use App\Models\Clinico;
use App\Models\Paciente;
use App\Models\User;
use App\Services\ExpedienteClinicExportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use ZipArchive;

class ClinicaExportTest extends TestCase
{
    use DatabaseTransactions;

    private function makeClinica(array $overrides = []): Clinica
    {
        return Clinica::create(array_merge([
            'nombre' => 'Clínica Export Test '.uniqid(),
            'email' => 'export-'.uniqid().'@test.local',
            'activa' => true,
            'pagado' => true,
            'fecha_vencimiento' => now()->addMonth()->toDateString(),
        ], $overrides));
    }

    private function makeUser(Clinica $clinica, array $overrides = []): User
    {
        return User::create(array_merge([
            'nombre' => 'User',
            'apellidoPat' => 'Test',
            'apellidoMat' => 'Export',
            'email' => 'user-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
            'clinica_id' => $clinica->id,
            'email_verified' => true,
        ], $overrides));
    }

    private function attachRole(User $user, Clinica $clinica, string $rol): void
    {
        DB::table('user_clinicas')->insert([
            'user_id' => $user->id,
            'clinica_id' => $clinica->id,
            'rol_en_clinica' => $rol,
            'activa' => 1,
            'isAdmin' => $rol === 'propietario' ? 1 : 0,
            'isSuperAdmin' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makePacienteForClinica(Clinica $clinica, User $user): Paciente
    {
        $paciente = Paciente::create([
            'nombre' => 'Paciente',
            'apellidoPat' => 'Export',
            'apellidoMat' => 'Test',
            'registro' => (string) random_int(100000, 999999),
            'email' => 'pac-'.uniqid().'@test.local',
            'fechaNacimiento' => '1990-01-15',
            'edad' => '36',
            'genero' => 1,
            'user_id' => $user->id,
            'clinica_id' => $clinica->id,
        ]);

        DB::table('clinica_paciente')->insert([
            'clinica_id' => $clinica->id,
            'paciente_id' => $paciente->id,
            'user_id' => $user->id,
            'vinculado_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $paciente->fresh();
    }

    public function test_solo_propietario_puede_solicitar_exportacion(): void
    {
        Queue::fake();

        $clinica = $this->makeClinica();
        $owner = $this->makeUser($clinica);
        $clinica->update(['propietario_user_id' => $owner->id]);
        $this->attachRole($owner, $clinica, 'propietario');

        $colab = $this->makeUser($clinica, ['nombre' => 'Colab']);
        $this->attachRole($colab, $clinica, 'colaborador');

        $visor = $this->makeUser($clinica, ['nombre' => 'Visor']);
        $this->attachRole($visor, $clinica, 'visor');

        Sanctum::actingAs($colab);
        $this->postJson('/api/clinica/exports')->assertStatus(403);

        Sanctum::actingAs($visor);
        $this->postJson('/api/clinica/exports')->assertStatus(403);

        Sanctum::actingAs($owner);
        $this->postJson('/api/clinica/exports')
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        Queue::assertPushed(BuildClinicaExpedienteExport::class);
    }

    public function test_otra_clinica_no_puede_ver_ni_descargar_export(): void
    {
        Storage::fake('private');

        $clinicaA = $this->makeClinica();
        $ownerA = $this->makeUser($clinicaA);
        $clinicaA->update(['propietario_user_id' => $ownerA->id]);
        $this->attachRole($ownerA, $clinicaA, 'propietario');

        $clinicaB = $this->makeClinica();
        $ownerB = $this->makeUser($clinicaB);
        $clinicaB->update(['propietario_user_id' => $ownerB->id]);
        $this->attachRole($ownerB, $clinicaB, 'propietario');

        $path = 'exports/'.$clinicaA->id.'/test.zip';
        Storage::disk('private')->put($path, 'zip-bytes');

        $export = ClinicaExport::create([
            'clinica_id' => $clinicaA->id,
            'user_id' => $ownerA->id,
            'status' => ClinicaExport::STATUS_COMPLETED,
            'ruta_zip' => $path,
            'expires_at' => now()->addDays(7),
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($ownerB);
        $this->getJson('/api/clinica/exports/'.$export->id)->assertStatus(403);
        $this->getJson('/api/clinica/exports/'.$export->id.'/download')->assertStatus(403);
    }

    public function test_exportacion_vacia_y_aislamiento_de_expedientes_por_clinica(): void
    {
        Storage::fake('private');

        $clinicaA = $this->makeClinica();
        $ownerA = $this->makeUser($clinicaA);
        $clinicaA->update(['propietario_user_id' => $ownerA->id]);
        $this->attachRole($ownerA, $clinicaA, 'propietario');

        $clinicaB = $this->makeClinica();
        $ownerB = $this->makeUser($clinicaB);
        $clinicaB->update(['propietario_user_id' => $ownerB->id]);
        $this->attachRole($ownerB, $clinicaB, 'propietario');

        // Exportación vacía
        $emptyExport = ClinicaExport::create([
            'clinica_id' => $clinicaA->id,
            'user_id' => $ownerA->id,
            'status' => ClinicaExport::STATUS_PENDING,
        ]);
        app(ExpedienteClinicExportService::class)->build($emptyExport);
        $emptyExport->refresh();
        $this->assertSame(ClinicaExport::STATUS_COMPLETED, $emptyExport->status);
        $this->assertSame(0, $emptyExport->pacientes_total);
        $this->assertTrue(Storage::disk('private')->exists($emptyExport->ruta_zip));

        $paciente = $this->makePacienteForClinica($clinicaA, $ownerA);

        if (Schema::hasTable('clinicos') && Schema::hasColumn('clinicos', 'clinica_id')) {
            Clinico::create([
                'paciente_id' => $paciente->id,
                'user_id' => $ownerA->id,
                'clinica_id' => $clinicaA->id,
                'fecha' => now()->toDateString(),
            ]);

            // Expediente de otra clínica del mismo paciente (si estuviera vinculado)
            DB::table('clinica_paciente')->insertOrIgnore([
                'clinica_id' => $clinicaB->id,
                'paciente_id' => $paciente->id,
                'user_id' => $ownerB->id,
                'vinculado_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Clinico::create([
                'paciente_id' => $paciente->id,
                'user_id' => $ownerB->id,
                'clinica_id' => $clinicaB->id,
                'fecha' => now()->toDateString(),
            ]);
        }

        $export = ClinicaExport::create([
            'clinica_id' => $clinicaA->id,
            'user_id' => $ownerA->id,
            'status' => ClinicaExport::STATUS_PENDING,
        ]);
        app(ExpedienteClinicExportService::class)->build($export);
        $export->refresh();

        $this->assertSame(ClinicaExport::STATUS_COMPLETED, $export->status);
        $this->assertSame(1, $export->pacientes_total);

        $tmp = tempnam(sys_get_temp_dir(), 'exp');
        file_put_contents($tmp, Storage::disk('private')->get($export->ruta_zip));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tmp) === true);
        $this->assertNotFalse($zip->locateName('LEEME.txt'));
        $this->assertNotFalse($zip->locateName('pacientes.csv'));
        $this->assertNotFalse($zip->locateName('indice.json'));

        $expedientesJson = $zip->getFromName('pacientes/paciente_'.$paciente->id.'/expedientes.json');
        $this->assertNotFalse($expedientesJson);
        $expedientes = json_decode($expedientesJson, true) ?: [];
        foreach ($expedientes as $item) {
            if (($item['modelo'] ?? '') === 'Clinico') {
                $this->assertSame($clinicaA->id, (int) ($item['datos']['clinica_id'] ?? 0));
            }
        }
        $zip->close();
        @unlink($tmp);
    }

    public function test_job_fallido_marca_export_failed(): void
    {
        $clinica = $this->makeClinica();
        $owner = $this->makeUser($clinica);
        $clinica->update(['propietario_user_id' => $owner->id]);
        $this->attachRole($owner, $clinica, 'propietario');

        $export = ClinicaExport::create([
            'clinica_id' => $clinica->id,
            'user_id' => $owner->id,
            'status' => ClinicaExport::STATUS_PENDING,
        ]);

        $service = $this->mock(ExpedienteClinicExportService::class);
        $service->shouldReceive('build')->once()->andThrow(new \RuntimeException('boom-export'));
        $service->shouldReceive('fail')->once()->andReturnUsing(function (ClinicaExport $e, \Throwable $err) {
            $e->update([
                'status' => ClinicaExport::STATUS_FAILED,
                'error_message' => $err->getMessage(),
            ]);
        });

        try {
            (new BuildClinicaExpedienteExport($export->id))->handle($service);
            $this->fail('Se esperaba excepción del job');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom-export', $e->getMessage());
        }

        $export->refresh();
        $this->assertSame(ClinicaExport::STATUS_FAILED, $export->status);
        $this->assertSame('boom-export', $export->error_message);
    }

    public function test_exportacion_expirada_no_se_descarga(): void
    {
        Storage::fake('private');

        $clinica = $this->makeClinica();
        $owner = $this->makeUser($clinica);
        $clinica->update(['propietario_user_id' => $owner->id]);
        $this->attachRole($owner, $clinica, 'propietario');

        $path = 'exports/'.$clinica->id.'/expired.zip';
        Storage::disk('private')->put($path, 'zip');

        $export = ClinicaExport::create([
            'clinica_id' => $clinica->id,
            'user_id' => $owner->id,
            'status' => ClinicaExport::STATUS_COMPLETED,
            'ruta_zip' => $path,
            'expires_at' => now()->subDay(),
            'completed_at' => now()->subDays(8),
        ]);

        Sanctum::actingAs($owner);
        $this->getJson('/api/clinica/exports/'.$export->id.'/download')
            ->assertStatus(409)
            ->assertJsonFragment(['message' => 'La exportación expiró. Solicita una nueva.']);
    }

    public function test_disponible_con_suscripcion_vencida(): void
    {
        Queue::fake();

        $clinica = $this->makeClinica([
            'pagado' => false,
            'fecha_vencimiento' => now()->subDays(10)->toDateString(),
        ]);
        $owner = $this->makeUser($clinica);
        $clinica->update(['propietario_user_id' => $owner->id]);
        $this->attachRole($owner, $clinica, 'propietario');

        Sanctum::actingAs($owner);
        $this->postJson('/api/clinica/exports')
            ->assertStatus(202)
            ->assertJsonPath('success', true);

        $this->getJson('/api/clinica/exports/latest')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
