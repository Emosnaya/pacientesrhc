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

    /**
     * Variantes que apuntan a la misma especialidad del catálogo.
     */
    private const ESPECIALIDAD_CANONICA = [
        'cardiaco' => 'cardiologia',
        'rehabilitacion_cardiaca' => 'rehabilitacion_cardiopulmonar',
        'rehabilitacion_pulmonar' => 'rehabilitacion_cardiopulmonar',
        'rehabilitacion' => 'rehabilitacion_cardiopulmonar',
        'pulmonar' => 'neumologia',
        'odontologia' => 'dental',
        'dentista' => 'dental',
        'obstetricia' => 'ginecologia',
        'gineco' => 'ginecologia',
        'ginecologia_obstetricia' => 'ginecologia',
        'ginecologia_y_obstetricia' => 'ginecologia',
        'medicina_general' => 'general',
        'interna' => 'medicina_interna',
        'traumatologia' => 'ortopedia',
        'ortopedia_traumatologia' => 'ortopedia',
        'ortopedia_y_traumatologia' => 'ortopedia',
        'orl' => 'otorrinolaringologia',
        'otorrino' => 'otorrinolaringologia',
        'nutriologia' => 'nutricion',
        'psicoterapia' => 'psicologia',
        'salud_mental' => 'psicologia',
        'cirugia' => 'cirugia_general',
        'rehabilitacion_fisica' => 'fisioterapia',
        'fisio' => 'fisioterapia',
    ];

    /**
     * Claves que debe buscar cada especialidad canónica (tipo principal y módulos).
     */
    private const ESPECIALIDAD_GRUPOS = [
        'cardiologia' => ['cardiologia', 'cardiaco'],
        'neumologia' => ['neumologia', 'pulmonar'],
        'dental' => ['dental', 'odontologia'],
        'ginecologia' => ['ginecologia', 'obstetricia'],
        'general' => ['general', 'medicina_general'],
        'medicina_interna' => ['medicina_interna'],
        'ortopedia' => ['ortopedia', 'traumatologia'],
        'otorrinolaringologia' => ['otorrinolaringologia', 'orl'],
        'nutricion' => ['nutricion', 'nutriologia'],
        'psicologia' => ['psicologia', 'psicoterapia'],
        'cirugia_general' => ['cirugia_general', 'cirugia'],
        'fisioterapia' => ['fisioterapia', 'rehabilitacion_fisica'],
        'rehabilitacion_cardiopulmonar' => ['rehabilitacion_cardiopulmonar', 'rehabilitacion', 'cardiaco', 'pulmonar', 'fisioterapia'],
    ];

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
        $landmarkId = $request->query('landmark_id');
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
            ->whereHas('clinica', fn ($q) => $q->publicableEnDirectorio())
            ->with([
                'clinica:id,nombre,tipo_clinica,logo,color_principal,direccion,citas_solapamiento_modo,cita_estado_inicial,portal_permite_multiples_citas_mismo_horario,modulos_habilitados',
                'landmark:id,nombre,tipo,ciudad,alcaldia,latitud,longitud',
            ]);

        if ($landmarkId && is_numeric($landmarkId)) {
            $query->where('landmark_id', (int) $landmarkId);
        }

        if ($q !== '') {
            $like = '%'.$q.'%';
            $clavesTexto = $this->especialidadCandidatos($q);
            $query->where(function ($sub) use ($like, $clavesTexto) {
                $sub->where('nombre', 'like', $like)
                    ->orWhere('direccion', 'like', $like)
                    ->orWhere('ciudad', 'like', $like)
                    ->orWhere('estado', 'like', $like)
                    ->orWhere('tipo_clinica', 'like', $like)
                    ->orWhere('landmark_detalle', 'like', $like)
                    ->orWhereHas('landmark', function ($l) use ($like) {
                        $l->where('nombre', 'like', $like)
                            ->orWhere('alcaldia', 'like', $like);
                    })
                    ->orWhereHas('clinica', function ($c) use ($like) {
                        $c->where('nombre', 'like', $like)
                            ->orWhere('direccion', 'like', $like)
                            ->orWhere('tipo_clinica', 'like', $like);
                    });

                // Texto que coincide con un módulo habilitado (ej. "nutricion").
                foreach ($clavesTexto as $clave) {
                    $sub->orWhereJsonContains('modulos_habilitados', $clave)
                        ->orWhereHas('clinica', fn ($c) => $c->whereJsonContains('modulos_habilitados', $clave));
                }
            });
        }

        if ($especialidad !== '') {
            $this->aplicarFiltroEspecialidad($query, $especialidad);
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
                    'landmark_id' => $sucursal->landmark_id,
                    'landmark_detalle' => $sucursal->landmark_detalle,
                    'landmark' => $sucursal->landmark ? [
                        'id' => $sucursal->landmark->id,
                        'nombre' => $sucursal->landmark->nombre,
                        'tipo' => $sucursal->landmark->tipo,
                        'alcaldia' => $sucursal->landmark->alcaldia,
                    ] : null,
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
                'landmark_id' => $landmarkId && is_numeric($landmarkId) ? (int) $landmarkId : null,
                'fecha' => $fecha,
                'filtros_especialidad' => $this->catalogoEspecialidadesConConteo(),
            ],
        ]);
    }

    /**
     * Catálogo global de especialidades/módulos para filtros del mapa.
     */
    public function catalogoEspecialidades(): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $catalogo = $this->catalogoEspecialidadesConConteo();

        return response()->json([
            'data' => $catalogo,
            'meta' => [
                'total' => count($catalogo),
                'disponibles' => count(array_filter($catalogo, fn ($item) => $item['disponible'])),
            ],
        ]);
    }

    /**
     * Hospitales/plazas con al menos un consultorio visible (descubrimiento paciente).
     */
    public function landmarks(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $q = trim((string) $request->query('q', ''));
        $limit = min(80, max(10, (int) $request->query('limit', 40)));

        $query = \App\Models\Landmark::query()
            ->activos()
            ->whereHas('sucursales', function ($s) {
                $s->visiblesDirectorio()->whereHas('clinica', fn ($c) => $c->publicableEnDirectorio());
            })
            ->withCount(['sucursales as consultorios_count' => function ($s) {
                $s->visiblesDirectorio()->whereHas('clinica', fn ($c) => $c->publicableEnDirectorio());
            }]);

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('nombre', 'like', $like)
                    ->orWhere('alcaldia', 'like', $like);
            });
        }

        $rows = $query->orderByDesc('consultorios_count')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'nombre' => $l->nombre,
                'tipo' => $l->tipo,
                'alcaldia' => $l->alcaldia,
                'ciudad' => $l->ciudad,
                'latitud' => $l->latitud,
                'longitud' => $l->longitud,
                'consultorios_count' => (int) $l->consultorios_count,
            ]);

        return response()->json([
            'data' => $rows,
            'meta' => ['count' => $rows->count()],
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
            ->with(['clinica', 'landmark'])
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
                'landmark_id' => $sucursal->landmark_id,
                'landmark_detalle' => $sucursal->landmark_detalle,
                'landmark' => $sucursal->landmark ? [
                    'id' => $sucursal->landmark->id,
                    'nombre' => $sucursal->landmark->nombre,
                    'tipo' => $sucursal->landmark->tipo,
                    'alcaldia' => $sucursal->landmark->alcaldia,
                ] : null,
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
     * Un centro atiende una especialidad si es su tipo principal
     * o si tiene el módulo habilitado (sucursal o clínica).
     */
    private function aplicarFiltroEspecialidad($query, string $especialidad): void
    {
        $candidatos = $this->especialidadCandidatos($especialidad);

        $query->where(function ($sub) use ($candidatos) {
            foreach ($candidatos as $clave) {
                // Prefijo (no substring) para que "general" no arrastre "cirugia_general".
                $sub->orWhere('tipo_clinica', 'like', $clave.'%')
                    ->orWhereJsonContains('modulos_habilitados', $clave)
                    ->orWhereHas('clinica', function ($c) use ($clave) {
                        $c->where(function ($inner) use ($clave) {
                            $inner->where('tipo_clinica', 'like', $clave.'%')
                                ->orWhereJsonContains('modulos_habilitados', $clave);

                            $tiposPorDefecto = Clinica::tiposConModuloPorDefecto($clave);
                            if ($tiposPorDefecto !== []) {
                                // Clínicas sin módulos configurados heredan los de su tipo.
                                $inner->orWhere(function ($legacy) use ($tiposPorDefecto) {
                                    $legacy->whereIn('tipo_clinica', $tiposPorDefecto)
                                        ->where(function ($vacios) {
                                            $vacios->whereNull('modulos_habilitados')
                                                ->orWhereJsonLength('modulos_habilitados', 0);
                                        });
                                });
                            }
                        });
                    });
            }
        });
    }

    /**
     * Claves equivalentes entre especialidades y módulos internos.
     */
    private function especialidadCandidatos(string $especialidad): array
    {
        $clave = $this->especialidadClave($especialidad);
        $canonica = $this->especialidadCanonica($clave);

        return collect([$clave, $canonica])
            ->merge(self::ESPECIALIDAD_GRUPOS[$canonica] ?? [])
            ->filter(fn ($c) => is_string($c) && trim($c) !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Clave del catálogo a la que pertenece una especialidad o módulo.
     */
    private function especialidadCanonica(string $valor): string
    {
        $clave = $this->especialidadClave($valor);

        return self::ESPECIALIDAD_CANONICA[$clave] ?? $clave;
    }

    private function especialidadesDeSucursal(Sucursal $sucursal, Clinica $clinica): array
    {
        $principal = $sucursal->tipo_clinica ?: $clinica->tipo_clinica;

        $modulos = collect($sucursal->modulos_habilitados ?? []);
        if ($modulos->isEmpty()) {
            $modulos = collect($clinica->modulos_efectivos ?? []);
        }

        // Un tipo paraguas (ej. rehab cardiopulmonar) se omite si ya se listan sus submódulos.
        $submodulos = Clinica::MODULOS_POR_TIPO[$this->especialidadClave((string) $principal)] ?? [];
        if ($submodulos !== [] && $modulos->intersect($submodulos)->isNotEmpty()) {
            $principal = null;
        }

        return collect([$principal])
            ->merge($modulos)
            ->filter(fn ($m) => is_string($m) && trim($m) !== '')
            ->map(fn ($m) => $this->especialidadEtiqueta($this->especialidadClave($m)))
            ->unique()
            ->values()
            ->all();
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

        $modulos = config('clinica_tipos.modulos_seleccionables', []);
        if (is_array($modulos) && isset($modulos[$key]['nombre'])) {
            return (string) $modulos[$key]['nombre'];
        }

        return ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Catálogo unificado: cada especialidad puede venir del tipo principal del centro
     * o de un módulo habilitado, y trae el conteo de centros visibles que la atienden.
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalogoEspecialidadesConConteo(): array
    {
        $items = $this->catalogoBase();

        $sucursales = Sucursal::query()
            ->visiblesDirectorio()
            ->whereHas('clinica', fn ($q) => $q->publicableEnDirectorio())
            ->with(['clinica:id,tipo_clinica,modulos_habilitados'])
            ->get(['id', 'clinica_id', 'tipo_clinica', 'modulos_habilitados']);

        foreach ($sucursales as $sucursal) {
            $clinica = $sucursal->clinica;
            if (! $clinica) {
                continue;
            }

            $principal = $this->especialidadCanonica((string) ($sucursal->tipo_clinica ?: $clinica->tipo_clinica));
            $modulos = collect($sucursal->modulos_habilitados ?? []);
            if ($modulos->isEmpty()) {
                $modulos = collect($clinica->modulos_efectivos ?? []);
            }

            $modulos = $modulos
                ->filter(fn ($m) => is_string($m) && trim($m) !== '')
                ->map(fn ($m) => $this->especialidadCanonica($m))
                ->unique();

            if ($principal !== '' && isset($items[$principal])) {
                $items[$principal]['count_principal']++;
            }

            foreach ($modulos as $modulo) {
                if ($modulo !== $principal && isset($items[$modulo])) {
                    $items[$modulo]['count_modulo']++;
                }
            }

            $atendidas = $modulos->push($principal)->filter()->unique();
            foreach ($atendidas as $clave) {
                if (isset($items[$clave])) {
                    $items[$clave]['count']++;
                }
            }
        }

        return collect($items)
            ->map(function (array $item) {
                $item['disponible'] = $item['count'] > 0;

                return $item;
            })
            ->sort(function (array $a, array $b) {
                // Primero lo que hoy sí tiene centros publicados, luego el resto A-Z.
                if (($a['count'] > 0) !== ($b['count'] > 0)) {
                    return $a['count'] > 0 ? -1 : 1;
                }
                if ($a['count'] !== $b['count']) {
                    return $b['count'] <=> $a['count'];
                }

                return strcmp($a['label'], $b['label']);
            })
            ->values()
            ->all();
    }

    /**
     * Especialidades posibles del producto: tipos principales + módulos habilitables.
     *
     * @return array<string, array<string, mixed>>
     */
    private function catalogoBase(): array
    {
        $items = [];

        $registrar = function (string $key, array $meta, string $fuente) use (&$items): void {
            $canonica = $this->especialidadCanonica($key);
            if ($canonica === '') {
                return;
            }

            if (! isset($items[$canonica])) {
                $items[$canonica] = [
                    'key' => $canonica,
                    'label' => $this->especialidadEtiqueta($canonica),
                    'color' => null,
                    'icon' => null,
                    'count' => 0,
                    'count_principal' => 0,
                    'count_modulo' => 0,
                    'es_tipo_principal' => false,
                    'es_modulo' => false,
                ];
            }

            $items[$canonica]['color'] = $items[$canonica]['color'] ?: ($meta['color'] ?? null);
            $items[$canonica]['icon'] = $items[$canonica]['icon'] ?: ($meta['icon'] ?? null);

            if ($fuente === 'tipo') {
                $items[$canonica]['es_tipo_principal'] = true;
                // La etiqueta del tipo principal es la más descriptiva para el paciente.
                if ($canonica === $this->especialidadClave($key) && ! empty($meta['nombre'])) {
                    $items[$canonica]['label'] = (string) $meta['nombre'];
                }
            } else {
                $items[$canonica]['es_modulo'] = true;
            }
        };

        foreach ((array) config('clinica_tipos.tipos', []) as $key => $meta) {
            if (is_string($key) && $key !== '' && is_array($meta)) {
                $registrar($key, $meta, 'tipo');
            }
        }

        foreach ((array) config('clinica_tipos.modulos_seleccionables', []) as $key => $meta) {
            if (is_string($key) && $key !== '' && is_array($meta)) {
                $registrar($key, $meta, 'modulo');
            }
        }

        return $items;
    }
}
