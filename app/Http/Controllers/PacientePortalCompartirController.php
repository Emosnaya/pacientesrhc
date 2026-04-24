<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteArchivo;
use App\Models\PacienteArchivoCompartido;
use App\Models\PacienteLinkCompartido;
use App\Models\PortalExpedienteCompartido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gestiona el compartir de documentos desde el portal del paciente:
 *  - Compartir con otra clínica vinculada (dentro de la plataforma)
 *  - Generar / revocar enlace seguro externo
 */
class PacientePortalCompartirController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) return null;
        return Paciente::find($user->paciente_id);
    }

    // ══════════════════════════════════════════════════════════════
    // COMPARTIR CON OTRA CLÍNICA (dentro de plataforma)
    // ══════════════════════════════════════════════════════════════

    /**
     * GET /api/paciente-portal/archivos/{archivoId}/compartidos
     * Devuelve con qué clínicas está compartido el archivo.
     */
    public function compartidosArchivo(int $archivoId)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $archivo = PacienteArchivo::where('paciente_id', $paciente->id)
            ->where('subido_por_paciente', true)
            ->findOrFail($archivoId);

        $compartidos = $archivo->clinicasCompartidas()
            ->get(['clinicas.id', 'clinicas.nombre'])
            ->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre]);

        return response()->json(['data' => $compartidos]);
    }

    /**
     * POST /api/paciente-portal/archivos/{archivoId}/compartir-clinica
     * Comparte (o descomparte) el archivo con una clínica.
     * Body: { clinica_id, compartir: true|false }
     */
    public function compartirConClinica(Request $request, int $archivoId)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'compartir'  => 'required|boolean',
        ]);

        $archivo = PacienteArchivo::where('paciente_id', $paciente->id)
            ->where('subido_por_paciente', true)
            ->findOrFail($archivoId);

        // Verificar que el paciente pertenece a esa clínica destino
        if (! $paciente->clinicas()->where('clinicas.id', $request->clinica_id)->exists()) {
            return response()->json(['message' => 'No estás vinculado a esa clínica'], 403);
        }

        if ($request->compartir) {
            PacienteArchivoCompartido::firstOrCreate([
                'paciente_archivo_id' => $archivo->id,
                'clinica_id'          => $request->clinica_id,
            ], [
                'compartido_at' => now(),
            ]);
            $msg = 'Archivo compartido con la clínica';
        } else {
            PacienteArchivoCompartido::where('paciente_archivo_id', $archivo->id)
                ->where('clinica_id', $request->clinica_id)
                ->delete();
            $msg = 'Archivo ya no está compartido con esa clínica';
        }

        return response()->json(['message' => $msg]);
    }

    // ══════════════════════════════════════════════════════════════
    // ENLACES SEGUROS EXTERNOS
    // ══════════════════════════════════════════════════════════════

    /**
     * GET /api/paciente-portal/links
     * Lista todos los links del paciente.
     */
    public function indexLinks()
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $links = PacienteLinkCompartido::where('paciente_id', $paciente->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($l) => $this->formatLink($l));

        return response()->json(['data' => $links]);
    }

    /**
     * POST /api/paciente-portal/links
     * Genera un enlace seguro para un archivo o expediente compartido.
     * Body: { tipo: 'archivo'|'expediente', referencia_id, horas_expiracion?, max_vistas?, nota? }
     */
    public function crearLink(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $request->validate([
            'tipo'              => 'required|in:archivo,expediente',
            'referencia_id'     => 'required|integer',
            'horas_expiracion'  => 'nullable|integer|min:1|max:720', // máx 30 días
            'max_vistas'        => 'nullable|integer|min:1|max:100',
            'nota'              => 'nullable|string|max:255',
        ]);

        // Validar que el recurso pertenece al paciente
        $nombre = '';
        if ($request->tipo === 'archivo') {
            $archivo = PacienteArchivo::where('paciente_id', $paciente->id)
                ->findOrFail($request->referencia_id);
            $nombre = $archivo->nombre_original;
        } else {
            $exp = PortalExpedienteCompartido::where('paciente_id', $paciente->id)
                ->findOrFail($request->referencia_id);
            $nombre = $exp->tipo_nombre ?? 'Expediente';
        }

        $horas = $request->horas_expiracion ?? 72; // 72h por defecto
        $token = Str::random(48);

        $link = PacienteLinkCompartido::create([
            'paciente_id'       => $paciente->id,
            'tipo'              => $request->tipo,
            'referencia_id'     => $request->referencia_id,
            'referencia_nombre' => $nombre,
            'token'             => $token,
            'expires_at'        => now()->addHours($horas),
            'max_vistas'        => $request->max_vistas,
            'nota'              => $request->nota,
            'created_by_user_id' => Auth::id(),
        ]);

        return response()->json(['data' => $this->formatLink($link)], 201);
    }

    /**
     * DELETE /api/paciente-portal/links/{id}
     * Revoca (elimina) un enlace.
     */
    public function revocarLink(int $id)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        PacienteLinkCompartido::where('paciente_id', $paciente->id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Enlace revocado']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatLink(PacienteLinkCompartido $l): array
    {
        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        return [
            'id'                => $l->id,
            'tipo'              => $l->tipo,
            'referencia_id'     => $l->referencia_id,
            'referencia_nombre' => $l->referencia_nombre,
            'nota'              => $l->nota,
            'url'               => "{$frontendUrl}/ver-documento/{$l->token}",
            'expires_at'        => $l->expires_at?->format('d/m/Y H:i'),
            'vistas'            => $l->vistas,
            'max_vistas'        => $l->max_vistas,
            'activo'            => $l->isValido(),
            'created_at'        => $l->created_at?->format('d/m/Y H:i'),
        ];
    }
}
