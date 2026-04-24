<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacienteArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PacienteArchivoController extends Controller
{
    /** GET /api/pacientes/{pacienteId}/archivos
     *  Devuelve:
     *   - archivos subidos por esta clínica para el paciente
     *   - archivos subidos por el paciente y compartidos con esta clínica
     */
    public function index(int $pacienteId)
    {
        $user     = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        if (! $this->pertenece($paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $clinicaId = $user->clinica_efectiva_id;

        // Archivos propios de esta clínica
        $propios = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => $this->format($a));

        // Archivos subidos por el paciente y compartidos explícitamente con esta clínica
        $compartidosPorPaciente = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('subido_por_paciente', true)
            ->whereHas('compartidos', fn ($q) => $q->where('clinica_id', $clinicaId))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => $this->format($a, compartidoPorPaciente: true));

        return response()->json([
            'data'                   => $propios,
            'compartidos_por_paciente' => $compartidosPorPaciente,
        ]);
    }

    /** POST /api/pacientes/{pacienteId}/archivos */
    public function store(Request $request, int $pacienteId)
    {
        $user     = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        if (! $this->pertenece($paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'archivo'     => 'required|file|max:20480',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $file      = $request->file('archivo');
        $clinicaId = $user->clinica_efectiva_id;

        $path = $file->storeAs(
            "pacientes/{$pacienteId}/archivos",
            time() . '_' . $file->getClientOriginalName(),
            'private'
        );

        $archivo = PacienteArchivo::create([
            'paciente_id'          => $pacienteId,
            'clinica_id'           => $clinicaId,
            'nombre_original'      => $file->getClientOriginalName(),
            'ruta'                 => $path,
            'mime_type'            => $file->getMimeType(),
            'tamanio'              => $file->getSize(),
            'descripcion'          => $request->input('descripcion'),
            'subido_por_paciente'  => false,
            'visible_en_portal'    => false,
            'subido_por_user_id'   => $user->id,
        ]);

        return response()->json(['data' => $this->format($archivo)], 201);
    }

    /** PATCH /api/pacientes/{pacienteId}/archivos/{id}/portal
     *  Alterna la visibilidad del archivo en el portal del paciente.
     */
    public function togglePortal(int $pacienteId, int $id)
    {
        $user    = Auth::user();
        $archivo = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        if (! $this->pertenece($archivo->paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $archivo->update(['visible_en_portal' => ! $archivo->visible_en_portal]);

        return response()->json(['data' => $this->format($archivo)]);
    }

    /** DELETE /api/pacientes/{pacienteId}/archivos/{id} */
    public function destroy(int $pacienteId, int $id)
    {
        $user    = Auth::user();
        $archivo = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        if (! $this->pertenece($archivo->paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        Storage::disk('private')->delete($archivo->ruta);
        $archivo->delete();

        return response()->json(['message' => 'Archivo eliminado']);
    }

    /** GET /api/pacientes/{pacienteId}/archivos/{id}/ver */
    public function view(int $pacienteId, int $id)
    {
        $user    = Auth::user();
        $archivo = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        if (! $this->pertenece($archivo->paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (! Storage::disk('private')->exists($archivo->ruta)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return response(
            Storage::disk('private')->get($archivo->ruta),
            200,
            [
                'Content-Type'        => $archivo->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $archivo->nombre_original . '"',
            ]
        );
    }

    /** GET /api/pacientes/{pacienteId}/archivos/{id}/descargar */
    public function download(int $pacienteId, int $id)
    {
        $user    = Auth::user();
        $archivo = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        if (! $this->pertenece($archivo->paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (! Storage::disk('private')->exists($archivo->ruta)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return Storage::disk('private')->download($archivo->ruta, $archivo->nombre_original);
    }

    /** GET /api/pacientes/{pacienteId}/archivos/{id}/ver-compartido
     *  Permite a la clínica ver un archivo que el paciente le compartió.
     */
    public function viewShared(int $pacienteId, int $id)
    {
        $user      = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;

        $archivo = PacienteArchivo::where('paciente_id', $pacienteId)
            ->where('subido_por_paciente', true)
            ->whereHas('compartidos', fn ($q) => $q->where('clinica_id', $clinicaId))
            ->findOrFail($id);

        if (! $this->pertenece($archivo->paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (! Storage::disk('private')->exists($archivo->ruta)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return response(
            Storage::disk('private')->get($archivo->ruta),
            200,
            [
                'Content-Type'        => $archivo->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $archivo->nombre_original . '"',
            ]
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function pertenece(Paciente $paciente, $user): bool
    {
        $cid = $user->clinica_efectiva_id ?? null;
        return $paciente->belongsToClinicaWorkspace($cid ? (int) $cid : null);
    }

    private function format(PacienteArchivo $a, bool $compartidoPorPaciente = false): array
    {
        return [
            'id'                      => $a->id,
            'nombre_original'         => $a->nombre_original,
            'descripcion'             => $a->descripcion,
            'mime_type'               => $a->mime_type,
            'tamanio'                 => $a->tamanio,
            'tamanio_legible'         => $a->tamanio_legible,
            'visible_en_portal'       => $a->visible_en_portal,
            'subido_por_paciente'     => $a->subido_por_paciente,
            'compartido_por_paciente' => $compartidoPorPaciente,
            'subido_por'              => $a->subidoPorUser?->nombre_completo,
            'created_at'              => $a->created_at?->format('d/m/Y H:i'),
        ];
    }
}
