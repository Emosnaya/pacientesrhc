<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Models\OrdenLaboratorio;
use App\Models\OrdenLaboratorioToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenLaboratorioController extends Controller
{
    // ─── helpers ───────────────────────────────────────────────────────────────

    private function clinicaId(): int
    {
        return (int) Auth::user()->clinica_efectiva_id;
    }

    private function requireAdmin(): void
    {
        $u = Auth::user();
        if (!$u->is_admin && !$u->isSuperAdmin()) {
            abort(403, 'Solo administradores pueden realizar esta acción');
        }
    }

    private function nextFolio(int $clinicaId): int
    {
        return (OrdenLaboratorio::where('clinica_id', $clinicaId)->max('folio') ?? 0) + 1;
    }

    // ─── Laboratorios (catálogo) ────────────────────────────────────────────

    /** GET /api/laboratorios */
    public function indexLaboratorios(): JsonResponse
    {
        $labs = Laboratorio::where('clinica_id', $this->clinicaId())
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        return response()->json($labs);
    }

    /** POST /api/laboratorios */
    public function storeLaboratorio(Request $request): JsonResponse
    {
        $this->requireAdmin();
        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:30',
            'contacto' => 'nullable|string|max:255',
        ]);
        $lab = Laboratorio::create(array_merge($data, ['clinica_id' => $this->clinicaId()]));
        return response()->json($lab, 201);
    }

    /** PUT /api/laboratorios/{lab} */
    public function updateLaboratorio(Request $request, Laboratorio $laboratorio): JsonResponse
    {
        $this->requireAdmin();
        if ($laboratorio->clinica_id !== $this->clinicaId()) abort(403);
        $laboratorio->update($request->validate([
            'nombre'   => 'sometimes|required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:30',
            'contacto' => 'nullable|string|max:255',
            'activo'   => 'sometimes|boolean',
        ]));
        return response()->json($laboratorio->fresh());
    }

    /** DELETE /api/laboratorios/{lab} */
    public function destroyLaboratorio(Laboratorio $laboratorio): JsonResponse
    {
        $this->requireAdmin();
        if ($laboratorio->clinica_id !== $this->clinicaId()) abort(403);
        $laboratorio->update(['activo' => false]);
        return response()->json(['message' => 'Laboratorio desactivado']);
    }

    // ─── Órdenes ────────────────────────────────────────────────────────────

    /** GET /api/ordenes-laboratorio?paciente_id= */
    public function index(Request $request): JsonResponse
    {
        $clinicaId = $this->clinicaId();
        $ordenes = OrdenLaboratorio::where('clinica_id', $clinicaId)
            ->when($request->paciente_id, fn($q) => $q->where('paciente_id', $request->paciente_id))
            ->with(['laboratorio:id,nombre,email', 'user:id,nombre,apellidoPat', 'portalToken:id,orden_id,token'])
            ->orderByDesc('folio')
            ->get()
            ->map(fn($o) => array_merge($o->toArray(), ['status_label' => $o->status_label]));

        return response()->json($ordenes);
    }

    /** POST /api/ordenes-laboratorio */
    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin();
        $clinicaId = $this->clinicaId();

        $data = $request->validate([
            'paciente_id'         => 'required|exists:pacientes,id',
            'laboratorio_id'      => 'nullable|exists:laboratorios,id',
            'estudios'            => 'required|string',
            'indicaciones'        => 'nullable|string',
            'diagnostico_clinico' => 'nullable|string',
            'notas_laboratorio'   => 'nullable|string',
            'email_laboratorio'   => 'nullable|email|max:255',
        ]);

        $orden = DB::transaction(function () use ($data, $clinicaId) {
            return OrdenLaboratorio::create(array_merge($data, [
                'clinica_id' => $clinicaId,
                'user_id'    => Auth::id(),
                'folio'      => $this->nextFolio($clinicaId),
                'status'     => 'pendiente',
            ]));
        });

        // Si se proporcionó un correo, enviar automáticamente el portal link
        if (!empty($data['email_laboratorio'])) {
            try {
                $orden->load(['paciente', 'laboratorio', 'clinica', 'user']);

                $tokenModel = OrdenLaboratorioToken::create([
                    'orden_id' => $orden->id,
                    'token'    => Str::random(48),
                ]);

                $portalUrl = config('app.url') . '/portal-laboratorio/' . $tokenModel->token;

                $pdf = Pdf::loadView('pdfs.orden-laboratorio', [
                    'orden'     => $orden,
                    'portalUrl' => $portalUrl,
                ])->setPaper('letter', 'portrait');

                Mail::send([], [], function ($msg) use ($orden, $data, $pdf, $portalUrl) {
                    $clinicaNombre = $orden->clinica?->nombre ?? 'LynkaMed';
                    $paciente      = $orden->paciente;
                    $nombre        = trim(($paciente->nombre ?? '') . ' ' . ($paciente->apellidoPat ?? ''));

                    $msg->to($data['email_laboratorio'])
                        ->subject("Orden de Laboratorio #{$orden->folio} — {$nombre}")
                        ->html(
                            view('emails.orden-laboratorio', [
                                'orden'         => $orden,
                                'portalUrl'     => $portalUrl,
                                'clinicaNombre' => $clinicaNombre,
                                'mensaje'       => null,
                            ])->render()
                        )
                        ->attachData($pdf->output(), "orden-lab-{$orden->folio}.pdf", [
                            'mime' => 'application/pdf',
                        ]);
                });

                $orden->update(['correo_enviado' => true, 'correo_enviado_at' => now()]);
            } catch (\Throwable $e) {
                // No bloquear la creación si el correo falla
                \Log::warning('No se pudo enviar correo de orden de laboratorio: ' . $e->getMessage());
            }
        }

        return response()->json(
            $orden->load(['laboratorio:id,nombre,email', 'user:id,nombre,apellidoPat'])->append('status_label'),
            201
        );
    }

    /** PUT /api/ordenes-laboratorio/{orden} */
    public function update(Request $request, OrdenLaboratorio $orden): JsonResponse
    {
        $this->requireAdmin();
        if ($orden->clinica_id !== $this->clinicaId()) abort(403);

        $orden->update($request->validate([
            'laboratorio_id'      => 'nullable|exists:laboratorios,id',
            'estudios'            => 'sometimes|required|string',
            'indicaciones'        => 'nullable|string',
            'diagnostico_clinico' => 'nullable|string',
            'notas_laboratorio'   => 'nullable|string',
            'email_laboratorio'   => 'nullable|email|max:255',
            'status'              => 'sometimes|in:pendiente,recibida,en_proceso,lista,entregada,cancelada',
            'fecha_recoleccion'        => 'nullable|date',
            'fecha_entrega_estimada'   => 'nullable|date',
            'fecha_entrega_real'       => 'nullable|date',
        ]));

        return response()->json(
            $orden->fresh()->load(['laboratorio:id,nombre,email', 'user:id,nombre,apellidoPat'])->append('status_label')
        );
    }

    /** DELETE /api/ordenes-laboratorio/{orden} */
    public function destroy(OrdenLaboratorio $orden): JsonResponse
    {
        $this->requireAdmin();
        if ($orden->clinica_id !== $this->clinicaId()) abort(403);
        $orden->delete();
        return response()->json(['message' => 'Orden eliminada']);
    }

    // ─── Subir modelo dental ─────────────────────────────────────────────────

    /** POST /api/ordenes-laboratorio/{orden}/modelo-dental */
    public function subirModeloDental(Request $request, OrdenLaboratorio $orden): JsonResponse
    {
        $this->requireAdmin();
        if ($orden->clinica_id !== $this->clinicaId()) abort(403);

        $request->validate(['archivo' => 'required|file|max:20480']); // 20 MB máx

        // Eliminar archivo anterior si existe
        if ($orden->modelo_dental && Storage::disk('public')->exists($orden->modelo_dental)) {
            Storage::disk('public')->delete($orden->modelo_dental);
        }

        $path = $request->file('archivo')->store("modelos-dentales/{$orden->clinica_id}", 'public');
        $orden->update(['modelo_dental' => $path]);

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    // ─── Enviar por correo ───────────────────────────────────────────────────

    /** POST /api/ordenes-laboratorio/{orden}/enviar-correo */
    public function enviarCorreo(Request $request, OrdenLaboratorio $orden): JsonResponse
    {
        $this->requireAdmin();
        if ($orden->clinica_id !== $this->clinicaId()) abort(403);

        $data = $request->validate([
            'email'   => 'required|email',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        $orden->load(['paciente', 'laboratorio', 'user']);

        // Generar / recuperar token del portal del laboratorio
        $tokenModel = $orden->portalToken ?? OrdenLaboratorioToken::create([
            'orden_id' => $orden->id,
            'token'    => Str::random(48),
        ]);

        $portalUrl = config('app.url') . '/portal-laboratorio/' . $tokenModel->token;

        // Generar PDF de la orden
        $pdf = Pdf::loadView('pdfs.orden-laboratorio', [
            'orden'     => $orden,
            'portalUrl' => $portalUrl,
        ])->setPaper('letter', 'portrait');

        // Enviar correo con el PDF adjunto
        Mail::send([], [], function ($msg) use ($orden, $data, $pdf, $portalUrl) {
            $clinicaNombre = $orden->clinica?->nombre ?? 'Lynkamed';
            $paciente      = $orden->paciente;
            $nombre        = trim(($paciente->nombre ?? '') . ' ' . ($paciente->apellidoPat ?? ''));

            $msg->to($data['email'])
                ->subject("Orden de Laboratorio #{$orden->folio} — {$nombre}")
                ->html(
                    view('emails.orden-laboratorio', [
                        'orden'        => $orden,
                        'portalUrl'    => $portalUrl,
                        'clinicaNombre'=> $clinicaNombre,
                        'mensaje'      => $data['mensaje'] ?? null,
                    ])->render()
                )
                ->attachData($pdf->output(), "orden-lab-{$orden->folio}.pdf", [
                    'mime' => 'application/pdf',
                ]);
        });

        $orden->update(['correo_enviado' => true, 'correo_enviado_at' => now(), 'email_laboratorio' => $data['email']]);

        return response()->json(['message' => 'Correo enviado correctamente', 'portal_url' => $portalUrl]);
    }

    // ─── PDF (imprimir sin enviar) ───────────────────────────────────────────

    /** GET /api/ordenes-laboratorio/{orden}/pdf */
    public function pdf(OrdenLaboratorio $orden): mixed
    {
        if ($orden->clinica_id !== $this->clinicaId()) abort(403);
        $orden->load(['paciente', 'laboratorio', 'clinica', 'user']);

        $firmaUser   = $orden->user;
        $firmaBase64 = null;
        if ($firmaUser && $firmaUser->firma_digital && file_exists(public_path('storage/' . $firmaUser->firma_digital))) {
            $imageData   = file_get_contents(public_path('storage/' . $firmaUser->firma_digital));
            $imageType   = mime_content_type(public_path('storage/' . $firmaUser->firma_digital));
            $firmaBase64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
        }

        $clinicaObj  = $orden->clinica;
        $clinicaLogo = null;
        if ($clinicaObj && $clinicaObj->logo && file_exists(public_path('storage/' . $clinicaObj->logo))) {
            $logoData    = file_get_contents(public_path('storage/' . $clinicaObj->logo));
            $logoType    = mime_content_type(public_path('storage/' . $clinicaObj->logo));
            $clinicaLogo = 'data:' . $logoType . ';base64,' . base64_encode($logoData);
        }

        $isDental = ($clinicaObj?->tipo_clinica === 'dental');

        $token     = $orden->portalToken?->token;
        $portalUrl = $token ? config('app.url') . '/portal-laboratorio/' . $token : null;

        return Pdf::loadView('pdfs.orden-laboratorio', [
            'orden'       => $orden,
            'clinica'     => $clinicaObj,
            'user'        => $firmaUser,
            'firmaBase64' => $firmaBase64,
            'clinicaLogo' => $clinicaLogo,
            'isDental'    => $isDental,
            'portalUrl'   => $portalUrl,
        ])->setPaper('letter', 'portrait')
          ->stream("orden-lab-{$orden->folio}.pdf");
    }

    // ─── Generar/refrescar token de portal ───────────────────────────────────

    /** POST /api/ordenes-laboratorio/{orden}/portal-token */
    public function generarToken(OrdenLaboratorio $orden): JsonResponse
    {
        $this->requireAdmin();
        if ($orden->clinica_id !== $this->clinicaId()) abort(403);

        $token = $orden->portalToken;
        if ($token) {
            $token->update(['token' => Str::random(48)]);
        } else {
            $token = OrdenLaboratorioToken::create([
                'orden_id' => $orden->id,
                'token'    => Str::random(48),
            ]);
        }

        return response()->json([
            'token'     => $token->token,
            'portal_url'=> config('app.url') . '/portal-laboratorio/' . $token->token,
        ]);
    }

    // ─── Portal público del laboratorio ─────────────────────────────────────

    /** GET /api/portal-laboratorio/{token}  — sin auth */
    public function portalShow(string $token): JsonResponse
    {
        $tokenModel = OrdenLaboratorioToken::where('token', $token)->with([
            'orden.paciente:id,nombre,apellidoPat',
            'orden.clinica:id,nombre,logo',
            'orden.laboratorio:id,nombre',
            'orden.user:id,nombre,apellidoPat',
        ])->firstOrFail();

        if ($tokenModel->isExpired()) abort(410, 'Este enlace ha expirado');

        $orden = $tokenModel->orden;

        return response()->json([
            'folio'                  => $orden->folio,
            'estudios'               => $orden->estudios,
            'indicaciones'           => $orden->indicaciones,
            'notas_laboratorio'      => $orden->notas_laboratorio,
            'status'                 => $orden->status,
            'status_label'           => $orden->status_label,
            'fecha_recoleccion'      => $orden->fecha_recoleccion,
            'fecha_entrega_estimada' => $orden->fecha_entrega_estimada,
            'fecha_entrega_real'     => $orden->fecha_entrega_real,
            'paciente'               => [
                'nombre' => trim(($orden->paciente->nombre ?? '') . ' ' . ($orden->paciente->apellidoPat ?? '')),
            ],
            'clinica' => [
                'nombre' => $orden->clinica?->nombre,
                'logo'   => $orden->clinica?->logo ? Storage::url($orden->clinica->logo) : null,
            ],
            'created_at' => $orden->created_at->toDateString(),
        ]);
    }

    /** PATCH /api/portal-laboratorio/{token}  — sin auth, solo campos permitidos */
    public function portalUpdate(Request $request, string $token): JsonResponse
    {
        $tokenModel = OrdenLaboratorioToken::where('token', $token)->with('orden')->firstOrFail();
        if ($tokenModel->isExpired()) abort(410, 'Este enlace ha expirado');

        $data = $request->validate([
            'status'                 => 'required|in:recibida,en_proceso,lista,entregada',
            'notas_laboratorio'      => 'nullable|string|max:2000',
            'fecha_recoleccion'      => 'nullable|date',
            'fecha_entrega_estimada' => 'nullable|date',
            'fecha_entrega_real'     => 'nullable|date',
        ]);

        $tokenModel->orden->update($data);

        return response()->json(['message' => 'Estado actualizado correctamente']);
    }
}
