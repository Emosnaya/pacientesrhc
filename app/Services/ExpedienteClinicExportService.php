<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\ClinicaExport;
use App\Models\Clinico;
use App\Models\ControlPrenatal;
use App\Models\CualidadFisica;
use App\Models\Ecocardiograma;
use App\Models\Electrocardiograma;
use App\Models\Esfuerzo;
use App\Models\EstratiAacvpr;
use App\Models\Estratificacion;
use App\Models\ExpedientePulmonar;
use App\Models\HistoriaClinicaCardiologia;
use App\Models\HistoriaClinicaDental;
use App\Models\HistoriaClinicaFisioterapia;
use App\Models\HistoriaClinicaNutricion;
use App\Models\HistoriaGinecologica;
use App\Models\HistoriaObstetrica;
use App\Models\Incapacidad;
use App\Models\NotaAltaFisioterapia;
use App\Models\NotaClinicaSoapNutricional;
use App\Models\NotaEvolucionFisioterapia;
use App\Models\NotaSeguimientoPulmonar;
use App\Models\NotaSubsecuenteCardiologia;
use App\Models\Odontograma;
use App\Models\Paciente;
use App\Models\PacienteArchivo;
use App\Models\PruebaEsfuerzoPulmonar;
use App\Models\RadiografiaDental;
use App\Models\ReporteFinal;
use App\Models\ReporteFinalPulmonar;
use App\Models\ReporteFisio;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\SeguimientoNutricional;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Exportación portable de pacientes y expedientes clínicos de una clínica.
 * Excluye citas, recetas y finanzas.
 */
class ExpedienteClinicExportService
{
    public static function userIsClinicaOwner(User $user, Clinica $clinica): bool
    {
        if ((int) $clinica->propietario_user_id === (int) $user->id) {
            return true;
        }

        return $user->clinicas()
            ->where('clinicas.id', $clinica->id)
            ->wherePivot('rol_en_clinica', 'propietario')
            ->exists();
    }

    public function build(ClinicaExport $export): void
    {
        $export->refresh();
        $export->update([
            'status' => ClinicaExport::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        $clinicaId = (int) $export->clinica_id;
        $clinica = Clinica::findOrFail($clinicaId);

        $pacientes = Paciente::forClinicaWorkspace($clinicaId)
            ->orderBy('id')
            ->get();

        $export->update([
            'pacientes_total' => $pacientes->count(),
            'pacientes_done' => 0,
        ]);

        $tmpDir = storage_path('app/temp/exports/'.$export->id.'_'.Str::uuid());
        if (! is_dir($tmpDir) && ! mkdir($tmpDir, 0755, true) && ! is_dir($tmpDir)) {
            throw new \RuntimeException('No se pudo crear el directorio temporal de exportación');
        }

        $zipPath = $tmpDir.'/export.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP');
        }

        $index = [
            'clinica' => [
                'id' => $clinica->id,
                'nombre' => $clinica->nombre,
                'email' => $clinica->email,
            ],
            'generado_at' => now()->toIso8601String(),
            'alcance' => 'pacientes_y_expedientes_clinicos',
            'excluye' => ['citas', 'recetas', 'finanzas', 'facturacion', 'archivos_compartidos_por_otras_clinicas'],
            'pacientes' => [],
        ];

        $zip->addFromString('LEEME.txt', $this->readmeText($clinica));
        $this->writePacientesCsv($zip, $pacientes);

        $expedientesTotal = 0;
        $archivosTotal = 0;
        $done = 0;

        foreach ($pacientes as $paciente) {
            $folder = 'pacientes/paciente_'.$paciente->id.'/';
            $patientMeta = $this->pacientePublicArray($paciente);
            $zip->addFromString($folder.'paciente.json', json_encode($patientMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $expedientes = $this->collectExpedientesForPaciente((int) $paciente->id, $clinicaId);
            $expedientesTotal += $expedientes->count();
            $zip->addFromString(
                $folder.'expedientes.json',
                json_encode($expedientes->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $filesManifest = [];
            $filesAdded = $this->addPatientFiles($zip, $folder, (int) $paciente->id, $clinicaId, $filesManifest);
            $archivosTotal += $filesAdded;
            $zip->addFromString(
                $folder.'archivos_manifest.json',
                json_encode($filesManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $index['pacientes'][] = [
                'id' => $paciente->id,
                'registro' => $paciente->registro,
                'nombre' => trim(($paciente->nombre ?? '').' '.($paciente->apellidoPat ?? '').' '.($paciente->apellidoMat ?? '')),
                'expedientes' => $expedientes->count(),
                'archivos' => $filesAdded,
            ];

            $done++;
            $export->update([
                'pacientes_done' => $done,
                'expedientes_total' => $expedientesTotal,
                'archivos_total' => $archivosTotal,
            ]);
        }

        $zip->addFromString('indice.json', json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        $relativePath = 'exports/'.$clinicaId.'/export_'.$export->id.'_'.Str::uuid().'.zip';
        $stream = fopen($zipPath, 'r');
        Storage::disk('private')->put($relativePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $size = filesize($zipPath) ?: null;
        @unlink($zipPath);
        $this->rrmdir($tmpDir);

        $export->update([
            'status' => ClinicaExport::STATUS_COMPLETED,
            'ruta_zip' => $relativePath,
            'tamanio_bytes' => $size,
            'expedientes_total' => $expedientesTotal,
            'archivos_total' => $archivosTotal,
            'pacientes_done' => $done,
            'completed_at' => now(),
            'expires_at' => now()->addDays(7),
            'error_message' => null,
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collectExpedientesForPaciente(int $pacienteId, int $clinicaId): Collection
    {
        $items = collect();

        $modules = [
            [Esfuerzo::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 1, 'Prueba de Esfuerzo', $m->fecha)],
            [Clinico::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 3, 'Historia Clínica Cardíaca', $m->fecha)],
            [ReporteFinal::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 4, 'Reporte Final', optional($m->created_at)?->format('Y-m-d'))],
            [ReportePsico::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 5, 'Nota de Psicología', $m->fecha)],
            [ReporteNutri::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 6, 'Nota Nutricional', $m->fecha)],
            [ReporteFisio::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 7, 'Nota Fisiológica', $m->fecha)],
            [ExpedientePulmonar::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 8, 'Historia Clínica Pulmonar', $m->fecha_consulta)],
            [CualidadFisica::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 14, 'Cualidades Físicas No Aeróbicas', $m->fecha_prueba_inicial)],
            [ReporteFinalPulmonar::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 15, 'Reporte Final Pulmonar', $m->fecha_termino ?? optional($m->created_at)?->format('Y-m-d'))],
            [PruebaEsfuerzoPulmonar::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 16, 'Prueba de Esfuerzo Pulmonar', $m->fecha_realizacion)],
            [HistoriaClinicaDental::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 17, 'Historia Clínica Dental', $m->fecha)],
            [Odontograma::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 18, 'Odontograma', $m->fecha)],
            [NotaSeguimientoPulmonar::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 19, 'Nota de Seguimiento Pulmonar', $m->fecha_consulta)],
            [EstratiAacvpr::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 20, 'Estratificación AACVPR/EAPC', $m->fecha_estratificacion)],
            [HistoriaClinicaCardiologia::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 30, 'Historia Clínica Cardiológica', $m->fecha_consulta)],
            [Ecocardiograma::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 31, 'Ecocardiograma', $m->fecha_estudio)],
            [Electrocardiograma::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 32, 'Electrocardiograma', $m->fecha_estudio)],
            [HistoriaGinecologica::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 33, 'Historia Ginecológica', $m->fecha_consulta ?? optional($m->created_at)?->format('Y-m-d'))],
            [HistoriaObstetrica::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 34, 'Historia Obstétrica', optional($m->created_at)?->format('Y-m-d'))],
            [ControlPrenatal::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 35, 'Control Prenatal', $m->fecha_control)],
            [HistoriaClinicaNutricion::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 36, 'Historia Clínica Nutriológica', $m->fecha_elaboracion)],
            [SeguimientoNutricional::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 37, 'Seguimiento Nutricional', $m->fecha_elaboracion)],
            [NotaClinicaSoapNutricional::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 38, 'Nota Clínica SOAP Nutricional', $m->fecha_elaboracion)],
            [NotaSubsecuenteCardiologia::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 39, 'Nota de Subsecuente', $m->fecha_consulta)],
            [Incapacidad::class, 'clinica_id', fn ($m) => $this->mapRecord($m, 40, 'Incapacidad', $m->fecha_inicio)],
        ];

        foreach ($modules as [$class, $clinicColumn, $mapper]) {
            if (! class_exists($class)) {
                continue;
            }
            /** @var Model $model */
            $model = new $class;
            if (! Schema::hasTable($model->getTable())) {
                continue;
            }
            $query = $class::query()->where('paciente_id', $pacienteId);
            if (Schema::hasColumn($model->getTable(), $clinicColumn)) {
                $query->where($clinicColumn, $clinicaId);
            } else {
                continue;
            }
            foreach ($query->get() as $row) {
                $items->push($mapper($row));
            }
        }

        // Estratificación: clinica_id o fallback al creador de la misma clínica
        if (Schema::hasTable((new Estratificacion)->getTable())) {
            $estratis = Estratificacion::where('paciente_id', $pacienteId)
                ->where(function ($q) use ($clinicaId) {
                    $q->where('clinica_id', $clinicaId)
                        ->orWhere(function ($q2) use ($clinicaId) {
                            $q2->whereNull('clinica_id')
                                ->whereHas('user', fn ($uq) => $uq->where('clinica_id', $clinicaId));
                        });
                })
                ->get();
            foreach ($estratis as $row) {
                $items->push($this->mapRecord($row, 2, 'Estratificación', $row->estrati_fecha));
            }
        }

        // Fisioterapia: tablas históricas sin clinica_id → autor de la clínica
        $fisio = [
            [HistoriaClinicaFisioterapia::class, 11, 'Historia Clínica Fisioterapia', 'fecha'],
            [NotaEvolucionFisioterapia::class, 12, 'Nota de Evolución Fisioterapia', 'fecha'],
            [NotaAltaFisioterapia::class, 13, 'Nota de Alta Fisioterapia', 'fecha'],
        ];
        foreach ($fisio as [$class, $tipo, $nombre, $fechaField]) {
            if (! class_exists($class) || ! Schema::hasTable((new $class)->getTable())) {
                continue;
            }
            $table = (new $class)->getTable();
            $query = $class::query()->where('paciente_id', $pacienteId);
            if (Schema::hasColumn($table, 'clinica_id')) {
                $query->where('clinica_id', $clinicaId);
            } else {
                $query->whereHas('user', fn ($uq) => $uq->where('clinica_id', $clinicaId));
            }
            foreach ($query->get() as $row) {
                $items->push($this->mapRecord($row, $tipo, $nombre, $row->{$fechaField} ?? null));
            }
        }

        return $items->sortByDesc(fn ($i) => (string) ($i['created_at'] ?? ''))->values();
    }

    protected function mapRecord(Model $model, int $tipoExp, string $tipoNombre, $fecha): array
    {
        $attrs = $model->attributesToArray();
        unset($attrs['consentimiento_token_hash'], $attrs['telefono_search_hash']);

        return [
            'id' => $model->getKey(),
            'tipo_exp' => $tipoExp,
            'tipo_nombre' => $tipoNombre,
            'fecha' => $fecha,
            'created_at' => optional($model->created_at)?->toIso8601String(),
            'updated_at' => optional($model->updated_at)?->toIso8601String(),
            'modelo' => class_basename($model),
            'datos' => $attrs,
        ];
    }

    protected function pacientePublicArray(Paciente $paciente): array
    {
        return [
            'id' => $paciente->id,
            'uuid_publico' => $paciente->uuid_publico,
            'registro' => $paciente->registro,
            'nombre' => $paciente->nombre,
            'apellidoPat' => $paciente->apellidoPat,
            'apellidoMat' => $paciente->apellidoMat,
            'telefono' => $paciente->telefono,
            'email' => $paciente->email,
            'fechaNacimiento' => optional($paciente->fechaNacimiento)?->format('Y-m-d'),
            'edad' => $paciente->edad,
            'genero' => $paciente->genero,
            'estadoCivil' => $paciente->estadoCivil,
            'profesion' => $paciente->profesion,
            'domicilio' => $paciente->domicilio,
            'calle' => $paciente->calle,
            'num_ext' => $paciente->num_ext,
            'num_int' => $paciente->num_int,
            'colonia' => $paciente->colonia,
            'codigo_postal' => $paciente->codigo_postal,
            'ciudad' => $paciente->ciudad,
            'estado_dir' => $paciente->estado_dir,
            'talla' => $paciente->talla,
            'peso' => $paciente->peso,
            'cintura' => $paciente->cintura,
            'imc' => $paciente->imc,
            'diagnostico' => $paciente->diagnostico,
            'medicamentos' => $paciente->medicamentos,
            'motivo_consulta' => $paciente->motivo_consulta,
            'alergias' => $paciente->alergias,
            'tipo_paciente' => $paciente->tipo_paciente,
            'archivo_muerto' => $paciente->archivo_muerto,
        ];
    }

    protected function writePacientesCsv(ZipArchive $zip, Collection $pacientes): void
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, [
            'id', 'registro', 'nombre', 'apellidoPat', 'apellidoMat', 'telefono', 'email',
            'fechaNacimiento', 'genero', 'ciudad', 'estado_dir', 'diagnostico', 'alergias',
        ]);
        foreach ($pacientes as $p) {
            fputcsv($fh, [
                $p->id,
                $p->registro,
                $p->nombre,
                $p->apellidoPat,
                $p->apellidoMat,
                $p->telefono,
                $p->email,
                optional($p->fechaNacimiento)?->format('Y-m-d'),
                $p->genero,
                $p->ciudad,
                $p->estado_dir,
                $p->diagnostico,
                $p->alergias,
            ]);
        }
        rewind($fh);
        $zip->addFromString('pacientes.csv', stream_get_contents($fh) ?: '');
        fclose($fh);
    }

    /**
     * @param  array<int, array<string, mixed>>  $manifest
     */
    protected function addPatientFiles(ZipArchive $zip, string $folder, int $pacienteId, int $clinicaId, array &$manifest): int
    {
        $count = 0;

        $archivos = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->get();

        foreach ($archivos as $archivo) {
            $entry = [
                'tipo' => 'paciente_archivo',
                'id' => $archivo->id,
                'nombre_original' => $archivo->nombre_original,
                'mime_type' => $archivo->mime_type,
                'tamanio' => $archivo->tamanio,
                'ruta_origen' => $archivo->ruta,
            ];
            $dest = $folder.'archivos/'.$archivo->id.'_'.$this->safeFilename($archivo->nombre_original ?: ('archivo_'.$archivo->id));
            if ($archivo->ruta && Storage::disk('private')->exists($archivo->ruta)) {
                $zip->addFromString($dest, Storage::disk('private')->get($archivo->ruta));
                $entry['incluido'] = true;
                $entry['ruta_en_zip'] = $dest;
                $count++;
            } else {
                $entry['incluido'] = false;
                $entry['motivo'] = 'archivo_no_encontrado';
            }
            $manifest[] = $entry;
        }

        if (Schema::hasTable((new RadiografiaDental)->getTable())) {
            $radios = RadiografiaDental::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->get();
            foreach ($radios as $radio) {
                $entry = [
                    'tipo' => 'radiografia_dental',
                    'id' => $radio->id,
                    'titulo' => $radio->titulo,
                    'ruta_origen' => $radio->ruta_archivo,
                ];
                $dest = $folder.'radiografias/'.$radio->id.'_'.$this->safeFilename(basename((string) $radio->ruta_archivo) ?: ('radio_'.$radio->id));
                if ($radio->ruta_archivo && Storage::disk('public')->exists($radio->ruta_archivo)) {
                    $zip->addFromString($dest, Storage::disk('public')->get($radio->ruta_archivo));
                    $entry['incluido'] = true;
                    $entry['ruta_en_zip'] = $dest;
                    $count++;
                } else {
                    $entry['incluido'] = false;
                    $entry['motivo'] = 'archivo_no_encontrado';
                }
                $manifest[] = $entry;
            }
        }

        if (Schema::hasTable((new Electrocardiograma)->getTable())) {
            $ecgs = Electrocardiograma::where('paciente_id', $pacienteId)
                ->where('clinica_id', $clinicaId)
                ->whereNotNull('imagen_path')
                ->get();
            foreach ($ecgs as $ecg) {
                $entry = [
                    'tipo' => 'electrocardiograma',
                    'id' => $ecg->id,
                    'ruta_origen' => $ecg->imagen_path,
                ];
                $dest = $folder.'ecg/'.$ecg->id.'_'.$this->safeFilename(basename((string) $ecg->imagen_path) ?: ('ecg_'.$ecg->id));
                if ($ecg->imagen_path && Storage::disk('public')->exists($ecg->imagen_path)) {
                    $zip->addFromString($dest, Storage::disk('public')->get($ecg->imagen_path));
                    $entry['incluido'] = true;
                    $entry['ruta_en_zip'] = $dest;
                    $count++;
                } else {
                    $entry['incluido'] = false;
                    $entry['motivo'] = 'archivo_no_encontrado';
                }
                $manifest[] = $entry;
            }
        }

        return $count;
    }

    protected function safeFilename(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9._\-áéíóúÁÉÍÓÚñÑ ]+/u', '_', $name) ?: 'archivo';

        return Str::limit($name, 120, '');
    }

    protected function readmeText(Clinica $clinica): string
    {
        return implode("\n", [
            'Exportación de pacientes y expedientes clínicos — LynkaMed',
            'Clínica: '.$clinica->nombre,
            'Generado: '.now()->toDateTimeString(),
            '',
            'Contenido:',
            '- pacientes.csv: listado de pacientes de la clínica',
            '- indice.json: resumen de la exportación',
            '- pacientes/paciente_{id}/paciente.json: datos del paciente',
            '- pacientes/paciente_{id}/expedientes.json: expedientes clínicos de esta clínica',
            '- pacientes/paciente_{id}/archivos/: documentos clínicos originales de esta clínica',
            '',
            'NO incluye: citas, recetas (pertenecen al médico), finanzas ni facturación.',
            'NO incluye archivos que otras clínicas compartieron con esta clínica.',
            '',
            'Este paquete es confidencial. Consérvalo de forma segura.',
        ]);
    }

    protected function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    public function fail(ClinicaExport $export, \Throwable $e): void
    {
        Log::error('Exportación clínica fallida', [
            'export_id' => $export->id,
            'error' => $e->getMessage(),
        ]);
        $export->update([
            'status' => ClinicaExport::STATUS_FAILED,
            'error_message' => Str::limit($e->getMessage(), 1000),
        ]);
    }
}
