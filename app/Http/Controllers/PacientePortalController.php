<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\PortalExpedienteCompartido;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacientePortalController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) {
            return null;
        }

        return Paciente::query()->find($user->paciente_id);
    }

    private function pivotParaClinica(Paciente $paciente, int $clinicaId): ?object
    {
        $row = $paciente->clinicas()->where('clinicas.id', $clinicaId)->first();

        return $row?->pivot;
    }

    public function clinicas(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $rows = $paciente->clinicas()
            ->get()
            ->map(function ($c) {
                $p = $c->pivot;

                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'tipo_clinica' => $c->tipo_clinica,
                    'logo' => $c->logo,
                    'logo_url' => $c->logo_url ?? null,
                    'vinculado_at' => $p->vinculado_at,
                    'portal_visible_citas' => (bool) ($p->portal_visible_citas ?? false),
                    'portal_visible_datos_basicos' => (bool) ($p->portal_visible_datos_basicos ?? false),
                    'portal_visible_expediente_resumen' => (bool) ($p->portal_visible_expediente_resumen ?? false),
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function clinicaResumen(Request $request, int $clinicaId): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pivot = $this->pivotParaClinica($paciente, $clinicaId);
        if (! $pivot) {
            return response()->json(['message' => 'Clínica no vinculada'], 404);
        }

        $clinica = Clinica::query()->find($clinicaId);
        if (! $clinica) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        $datos = null;
        if ($pivot->portal_visible_datos_basicos ?? false) {
            $g = (int) (bool) $paciente->genero;
            $datos = [
                'nombre' => $paciente->nombre,
                'apellidoPat' => $paciente->apellidoPat,
                'apellidoMat' => $paciente->apellidoMat,
                'telefono' => $paciente->telefono,
                'email' => $paciente->email,
                'fechaNacimiento' => $paciente->fechaNacimiento?->format('Y-m-d'),
                'genero' => $g,
                'genero_label' => $g === 1 ? 'Masculino' : 'Femenino',
                'domicilio_formateado' => $paciente->domicilio_formateado,
            ];
        }

        return response()->json([
            'clinica' => [
                'id' => $clinica->id,
                'nombre' => $clinica->nombre,
                'tipo_clinica' => $clinica->tipo_clinica,
                'logo' => $clinica->logo,
                'logo_url' => $clinica->logo_url ?? null,
            ],
            'visibilidad' => [
                'citas' => (bool) ($pivot->portal_visible_citas ?? false),
                'datos_basicos' => (bool) ($pivot->portal_visible_datos_basicos ?? false),
                'expediente_resumen' => (bool) ($pivot->portal_visible_expediente_resumen ?? false),
            ],
            'datos_paciente' => $datos,
        ]);
    }

    public function citas(Request $request, int $clinicaId): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pivot = $this->pivotParaClinica($paciente, $clinicaId);
        if (! $pivot || ! ($pivot->portal_visible_citas ?? false)) {
            return response()->json(['message' => 'Las citas no están habilitadas para esta clínica.'], 403);
        }

        $query = Cita::query()
            ->forClinica($clinicaId)
            ->forPaciente($paciente->id)
            ->with(['sucursal'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc');

        $citas = $query->limit(100)->get()->map(function (Cita $c) {
            return [
                'id' => $c->id,
                'fecha' => $c->fecha?->format('Y-m-d'),
                'hora' => $c->hora ? \Carbon\Carbon::parse($c->hora)->format('H:i') : null,
                'estado' => $c->estado,
                'sucursal' => $c->sucursal ? ['id' => $c->sucursal->id, 'nombre' => $c->sucursal->nombre] : null,
            ];
        });

        return response()->json(['data' => $citas]);
    }

    /**
     * Datos del paciente para su perfil en el portal (información propia).
     */
    public function perfil(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($this->perfilPayload($paciente->load('clinicas')));
    }

    /**
     * Actualiza el mismo registro Paciente que usa la clínica (lista blanca de campos).
     */
    public function updatePerfil(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($request->input('fechaNacimiento') === '') {
            $request->merge(['fechaNacimiento' => null]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidoPat' => 'required|string|max:100',
            'apellidoMat' => 'nullable|string|max:100',
            'email' => 'required|email|max:191',
            'telefono' => 'nullable|string|max:50',
            'fechaNacimiento' => 'nullable|date',
            'genero' => 'required|boolean',
            'calle' => 'nullable|string|max:255',
            'num_ext' => 'nullable|string|max:50',
            'num_int' => 'nullable|string|max:50',
            'colonia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:255',
            'estado_dir' => 'nullable|string|max:255',
            'alergias' => 'nullable|string',
        ]);

        $email = strtolower(trim($validated['email']));
        $emailTaken = User::query()
            ->where('email', $email)
            ->where('id', '!=', $user->id)
            ->exists();
        if ($emailTaken) {
            return response()->json([
                'message' => 'Ese correo ya está registrado en otra cuenta.',
                'errors' => ['email' => ['Ese correo ya está registrado en otra cuenta.']],
            ], 422);
        }

        $paciente->nombre = $validated['nombre'];
        $paciente->apellidoPat = $validated['apellidoPat'];
        $paciente->apellidoMat = $validated['apellidoMat'] ?? null;
        $paciente->email = $email;
        $paciente->telefono = $validated['telefono'] ?? null;
        $paciente->genero = ($validated['genero'] === true || $validated['genero'] === 1 || $validated['genero'] === '1') ? 1 : 0;

        if (array_key_exists('fechaNacimiento', $validated)) {
            if ($validated['fechaNacimiento'] === null) {
                $paciente->fechaNacimiento = null;
                $paciente->edad = 0;
            } else {
                $paciente->fechaNacimiento = $validated['fechaNacimiento'];
                $paciente->edad = Carbon::parse($validated['fechaNacimiento'])->age;
            }
        }

        $paciente->calle = $validated['calle'] ?? null;
        $paciente->num_ext = $validated['num_ext'] ?? null;
        $paciente->num_int = $validated['num_int'] ?? null;
        $paciente->colonia = $validated['colonia'] ?? null;
        $paciente->codigo_postal = $validated['codigo_postal'] ?? null;
        $paciente->ciudad = $validated['ciudad'] ?? null;
        $paciente->estado_dir = $validated['estado_dir'] ?? null;
        $paciente->alergias = $validated['alergias'] ?? null;

        $paciente->save();

        $user->email = $email;
        $user->nombre = $validated['nombre'];
        $user->apellidoPat = $validated['apellidoPat'];
        $user->apellidoMat = $validated['apellidoMat'] ?? null;
        $user->save();

        return response()->json($this->perfilPayload($paciente->fresh()->load('clinicas')));
    }

    /**
     * @return array<string, mixed>
     */
    private function perfilPayload(Paciente $paciente): array
    {
        $g = (int) (bool) $paciente->genero;

        $motivos = $paciente->clinicas
            ->map(function ($c) {
                return [
                    'clinica_id' => $c->id,
                    'nombre' => $c->nombre,
                    'motivo_consulta' => $c->pivot->motivo_consulta,
                ];
            })
            ->values()
            ->all();

        $payload = [
            'nombre' => $paciente->nombre,
            'apellidoPat' => $paciente->apellidoPat,
            'apellidoMat' => $paciente->apellidoMat,
            'email' => $paciente->email,
            'telefono' => $paciente->telefono,
            'fechaNacimiento' => $paciente->fechaNacimiento?->format('Y-m-d'),
            'genero' => $g,
            'genero_label' => $g === 1 ? 'Masculino' : 'Femenino',
            'calle' => $paciente->calle,
            'num_ext' => $paciente->num_ext,
            'num_int' => $paciente->num_int,
            'colonia' => $paciente->colonia,
            'codigo_postal' => $paciente->codigo_postal,
            'ciudad' => $paciente->ciudad,
            'estado_dir' => $paciente->estado_dir,
            'domicilio_formateado' => $paciente->domicilio_formateado,
            'motivos_consulta_clinicas' => $motivos,
            'alergias' => $paciente->alergias,
        ];

        $dom = $paciente->domicilio;
        if ($dom !== null && trim((string) $dom) !== '') {
            $payload['domicilio'] = $dom;
        }

        return $payload;
    }

    /**
     * Citas en rango para calendario: solo clínicas/consultorios con portal_visible_citas.
     */
    public function citasCalendario(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $clinicaIds = $paciente->clinicas()
            ->wherePivot('portal_visible_citas', true)
            ->pluck('clinicas.id');

        if ($clinicaIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $from = $request->query('from', Carbon::now()->subMonths(1)->toDateString());
        $to = $request->query('to', Carbon::now()->addMonths(6)->toDateString());

        $citas = Cita::query()
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $clinicaIds)
            ->whereBetween('fecha', [$from, $to])
            ->with(['sucursal', 'clinica'])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(function (Cita $c) {
                return [
                    'id' => $c->id,
                    'fecha' => $c->fecha?->format('Y-m-d'),
                    'hora' => $c->hora ? Carbon::parse($c->hora)->format('H:i') : null,
                    'estado' => $c->estado,
                    'clinica' => $c->clinica ? [
                        'id' => $c->clinica->id,
                        'nombre' => $c->clinica->nombre,
                    ] : null,
                    'sucursal' => $c->sucursal ? [
                        'id' => $c->sucursal->id,
                        'nombre' => $c->sucursal->nombre,
                    ] : null,
                ];
            });

        return response()->json(['data' => $citas]);
    }

    /**
     * Expedientes que el personal marcó como visibles para este paciente en el portal.
     */
    public function expedientesCompartidos(Request $request): JsonResponse
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allowedClinicaIds = $paciente->clinicas()->pluck('clinicas.id');

        $rows = PortalExpedienteCompartido::query()
            ->where('paciente_id', $paciente->id)
            ->whereIn('clinica_id', $allowedClinicaIds)
            ->with(['clinica:id,nombre'])
            ->orderByDesc('fecha_snapshot')
            ->orderByDesc('id')
            ->get()
            ->map(function (PortalExpedienteCompartido $r) {
                return [
                    'id' => $r->id,
                    'tipo_exp' => (int) $r->tipo_exp,
                    'expediente_id' => (int) $r->expediente_id,
                    'tipo_nombre' => $r->tipo_nombre_snapshot,
                    'fecha' => $r->fecha_snapshot?->format('Y-m-d'),
                    'clinica' => $r->clinica ? [
                        'id' => $r->clinica->id,
                        'nombre' => $r->clinica->nombre,
                    ] : null,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    /**
     * PDF de un expediente que el personal compartió al portal (LFPDPPP: solo filas en portal_expediente_compartidos).
     * Tipos soportados se amplían según match abajo.
     */
    public function documentoCompartidoPdf(Request $request, int $id)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $row = PortalExpedienteCompartido::query()
            ->where('paciente_id', $paciente->id)
            ->whereKey($id)
            ->first();

        if (! $row) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        if (! $paciente->clinicas()->where('clinicas.id', $row->clinica_id)->exists()) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $tipo = (int) $row->tipo_exp;
        $eid = (int) $row->expediente_id;

        if ($tipo === 6) {
            $nutri = \App\Models\ReporteNutri::query()->find($eid);
            if (! $nutri || (int) $nutri->paciente_id !== (int) $paciente->id) {
                return response()->json(['message' => 'Documento no encontrado'], 404);
            }
        }

        $sub = Request::create($request->url(), 'GET', ['id' => $eid]);
        $sub->merge(['id' => $eid]);

        return match ($tipo) {
            6 => app(PDFController::class)->nutriPdf($sub),
            default => response()->json([
                'message' => 'La vista en PDF para este tipo de expediente aún no está habilitada en el portal.',
            ], 501),
        };
    }
}
