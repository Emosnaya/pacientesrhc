<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\CitaAvailabilityService;
use App\Services\SucursalHorarioService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PacientePortalDirectorioController extends Controller
{
    private const AGENDA_DOCTOR_ROLES = ['doctor', 'doctora', 'licenciado', 'enfermero', 'enfermera', 'fisioterapeuta'];

    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) {
            return null;
        }

        return Paciente::query()->find($user->paciente_id);
    }

    /**
     * Directorio de sucursales activas visibles para descubrir centros nuevos.
     */
    public function index(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $q = trim((string) $request->query('q', ''));
        $especialidad = trim((string) $request->query('especialidad', ''));
        $fecha = $request->query('fecha');
        $soloConHorarios = filter_var($request->query('con_horarios', false), FILTER_VALIDATE_BOOLEAN);
        $limit = min(80, max(10, (int) $request->query('limit', 40)));

        if ($fecha && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
            return response()->json(['message' => 'Fecha inválida'], 422);
        }

        $linkedIds = $paciente->clinicas()->pluck('clinicas.id')->all();
        $horarioService = app(SucursalHorarioService::class);
        $availability = app(CitaAvailabilityService::class);

        $query = Sucursal::query()
            ->visiblesDirectorio()
            ->whereHas('clinica', fn ($q) => $q->where('activa', true))
            ->with(['clinica:id,nombre,tipo_clinica,logo,color_principal,direccion,citas_solapamiento_modo,cita_estado_inicial,portal_permite_multiples_citas_mismo_horario,modulos_habilitados']);

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('nombre', 'like', $like)
                    ->orWhere('direccion', 'like', $like)
                    ->orWhere('ciudad', 'like', $like)
                    ->orWhere('estado', 'like', $like)
                    ->orWhere('tipo_clinica', 'like', $like)
                    ->orWhereHas('clinica', function ($c) use ($like) {
                        $c->where('nombre', 'like', $like)
                            ->orWhere('direccion', 'like', $like)
                            ->orWhere('tipo_clinica', 'like', $like);
                    });
            });
        }

        if ($especialidad !== '') {
            $needle = $this->especialidadClave($especialidad);
            $query->where(function ($sub) use ($needle, $especialidad) {
                $sub->where('tipo_clinica', 'like', '%'.$especialidad.'%')
                    ->orWhere('tipo_clinica', 'like', '%'.$needle.'%')
                    ->orWhereHas('clinica', function ($c) use ($needle, $especialidad) {
                        $c->where('tipo_clinica', 'like', '%'.$especialidad.'%')
                            ->orWhere('tipo_clinica', 'like', '%'.$needle.'%');
                    });
            });
        }

        $rows = $query
            ->orderByRaw('CASE WHEN latitud IS NULL OR longitud IS NULL THEN 1 ELSE 0 END')
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(function (Sucursal $sucursal) use ($paciente, $linkedIds, $fecha, $soloConHorarios, $horarioService, $availability) {
                $clinica = $sucursal->clinica;
                if (! $clinica) {
                    return null;
                }

                $vinculada = in_array($clinica->id, $linkedIds, true);
                $especialidades = $this->especialidadesDeSucursal($sucursal, $clinica);

                $slotsAbiertos = null;
                if ($fecha) {
                    $candidateSlots = $horarioService->slotsParaFecha($sucursal, (string) $fecha);
                    $slotsAbiertos = 0;
                    foreach ($candidateSlots as $hora) {
                        $check = $availability->canBook(
                            $clinica,
                            (string) $fecha,
                            $hora,
                            $sucursal->id,
                            null,
                            $paciente->id
                        );
                        if ($check['ok']) {
                            $slotsAbiertos++;
                        }
                    }

                    if ($soloConHorarios && $slotsAbiertos === 0) {
                        return null;
                    }
                }

                return [
                    'id' => $sucursal->id,
                    'nombre' => $sucursal->nombre,
                    'direccion' => $sucursal->direccion,
                    'ciudad' => $sucursal->ciudad,
                    'estado' => $sucursal->estado,
                    'codigo_postal' => $sucursal->codigo_postal,
                    'direccion_completa' => $sucursal->direccion_completa,
                    'telefono' => $sucursal->telefono,
                    'latitud' => $sucursal->latitud,
                    'longitud' => $sucursal->longitud,
                    'tiene_coordenadas' => $sucursal->tiene_coordenadas,
                    'horarios_atencion' => $sucursal->horariosNormalizados(),
                    'tipo_clinica' => $sucursal->tipo_clinica ?: $clinica->tipo_clinica,
                    'especialidades' => $especialidades,
                    'slots_abiertos' => $slotsAbiertos,
                    'vinculada' => $vinculada,
                    'clinica' => [
                        'id' => $clinica->id,
                        'nombre' => $clinica->nombre,
                        'tipo_clinica' => $clinica->tipo_clinica,
                        'logo' => $clinica->logo,
                        'logo_url' => $clinica->logo_url,
                        'color_principal' => $clinica->color_principal,
                        'citas_solapamiento_modo' => $availability->modoSolapamiento($clinica),
                    ],
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'count' => $rows->count(),
                'q' => $q !== '' ? $q : null,
                'especialidad' => $especialidad !== '' ? $especialidad : null,
                'fecha' => $fecha,
            ],
        ]);
    }

    /**
     * Detalle de una sucursal del directorio + doctores.
     */
    public function show(int $sucursalId): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $sucursal = Sucursal::query()
            ->visiblesDirectorio()
            ->with(['clinica'])
            ->find($sucursalId);

        if (! $sucursal || ! $sucursal->clinica || ! $sucursal->clinica->activa) {
            return response()->json(['message' => 'Sucursal no encontrada'], 404);
        }

        $clinica = $sucursal->clinica;
        $vinculada = $paciente->clinicas()->where('clinicas.id', $clinica->id)->exists();
        $availability = app(CitaAvailabilityService::class);

        $doctores = User::query()
            ->where('clinica_id', $clinica->id)
            ->where(function ($q) use ($sucursal) {
                $q->whereNull('sucursal_id')->orWhere('sucursal_id', $sucursal->id);
            })
            ->whereIn('rol', self::AGENDA_DOCTOR_ROLES)
            ->select(['id', 'nombre', 'apellidoPat', 'apellidoMat', 'rol'])
            ->limit(30)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'nombre' => trim(($u->nombre ?? '').' '.($u->apellidoPat ?? '').' '.($u->apellidoMat ?? '')),
                'rol' => $u->rol,
            ])
            ->values();

        return response()->json([
            'data' => [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'direccion' => $sucursal->direccion,
                'ciudad' => $sucursal->ciudad,
                'estado' => $sucursal->estado,
                'codigo_postal' => $sucursal->codigo_postal,
                'direccion_completa' => $sucursal->direccion_completa,
                'telefono' => $sucursal->telefono,
                'latitud' => $sucursal->latitud,
                'longitud' => $sucursal->longitud,
                'tiene_coordenadas' => $sucursal->tiene_coordenadas,
                'horarios_atencion' => $sucursal->horariosNormalizados(),
                'especialidades' => $this->especialidadesDeSucursal($sucursal, $clinica),
                'doctores' => $doctores,
                'vinculada' => $vinculada,
                'clinica' => [
                    'id' => $clinica->id,
                    'nombre' => $clinica->nombre,
                    'tipo_clinica' => $clinica->tipo_clinica,
                    'logo' => $clinica->logo,
                    'logo_url' => $clinica->logo_url,
                    'color_principal' => $clinica->color_principal,
                    'citas_solapamiento_modo' => $availability->modoSolapamiento($clinica),
                    'cita_estado_inicial' => $availability->estadoInicial($clinica),
                ],
            ],
        ]);
    }

    /**
     * Disponibilidad por sucursal usando horarios_atencion + reglas de solapamiento.
     */
    public function disponibilidad(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursales,id',
            'fecha' => 'required|date_format:Y-m-d',
            'doctor_id' => 'nullable|integer|exists:users,id',
            'especialidad' => 'nullable|string|max:120',
        ]);

        $sucursal = Sucursal::query()->with('clinica')->findOrFail((int) $validated['sucursal_id']);
        $clinica = $sucursal->clinica;
        if (! $clinica || ! $clinica->activa || ! $sucursal->activa || ! $sucursal->visible_directorio) {
            return response()->json(['message' => 'La sucursal no está disponible'], 422);
        }

        $fecha = (string) $validated['fecha'];
        $doctorId = isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null;
        $horarioService = app(SucursalHorarioService::class);
        $availability = app(CitaAvailabilityService::class);

        $candidateSlots = $horarioService->slotsParaFecha($sucursal, $fecha);
        if (empty($candidateSlots)) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'cerrado' => true,
                    'message' => 'La sucursal no atiende este día',
                ],
            ]);
        }

        $slots = collect($candidateSlots)->map(function (string $slot) use ($availability, $clinica, $paciente, $fecha, $doctorId, $sucursal) {
            if ($availability->isPastSlot($fecha, $slot)) {
                return [
                    'hora' => $slot,
                    'disponible' => false,
                    'motivo' => 'Ese horario ya no está disponible',
                ];
            }

            $check = $availability->canBook(
                $clinica,
                $fecha,
                $slot,
                $sucursal->id,
                $doctorId,
                $paciente->id
            );

            return [
                'hora' => $slot,
                'disponible' => $check['ok'],
                'motivo' => $check['ok'] ? null : $check['message'],
            ];
        })->values();

        return response()->json([
            'data' => $slots,
            'meta' => [
                'sucursal_id' => $sucursal->id,
                'clinica_id' => $clinica->id,
                'fecha' => $fecha,
                'cerrado' => false,
                'citas_solapamiento_modo' => $availability->modoSolapamiento($clinica),
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function especialidadesDeSucursal(Sucursal $sucursal, Clinica $clinica): array
    {
        $fromSucursal = collect($sucursal->modulos_habilitados ?? [])
            ->filter(fn ($m) => is_string($m) && trim($m) !== '')
            ->map(fn ($m) => $this->especialidadEtiqueta($this->especialidadClave($m)))
            ->values();

        if ($fromSucursal->isNotEmpty()) {
            return $fromSucursal->all();
        }

        if ($sucursal->tipo_clinica) {
            return [$this->especialidadEtiqueta($this->especialidadClave($sucursal->tipo_clinica))];
        }

        $fromClinica = collect($clinica->modulos_efectivos ?? [])
            ->filter(fn ($m) => is_string($m) && trim($m) !== '')
            ->map(fn ($m) => $this->especialidadEtiqueta($this->especialidadClave($m)))
            ->values();

        if ($fromClinica->isNotEmpty()) {
            return $fromClinica->all();
        }

        if ($clinica->tipo_clinica) {
            return [$this->especialidadEtiqueta($this->especialidadClave($clinica->tipo_clinica))];
        }

        return [];
    }

    private function especialidadClave(string $value): string
    {
        $slug = (string) Str::of($value)
            ->lower()
            ->ascii()
            ->replace('-', '_')
            ->replace(' ', '_')
            ->value();

        $slug = preg_replace('/[^a-z0-9_]/', '', $slug) ?? '';

        return trim($slug, '_');
    }

    private function especialidadEtiqueta(string $key): string
    {
        $tipos = config('clinica_tipos.tipos', []);
        if (is_array($tipos) && isset($tipos[$key]['nombre'])) {
            return (string) $tipos[$key]['nombre'];
        }

        return ucfirst(str_replace('_', ' ', $key));
    }
}
