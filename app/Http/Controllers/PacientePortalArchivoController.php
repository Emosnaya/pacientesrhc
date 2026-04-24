<?php

namespace App\Http\Controllers;

use App\Models\PacienteArchivo;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para que el PACIENTE gestione sus propios archivos desde el portal.
 */
class PacientePortalArchivoController extends Controller
{
    private function pacienteAutorizado(): ?Paciente
    {
        $user = Auth::user();
        if (! $user || ! $user->paciente_id) return null;
        return Paciente::find($user->paciente_id);
    }

    /** GET /api/paciente-portal/archivos
     *  Solo los archivos que el propio paciente subió.
     */
    public function index()
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $archivos = PacienteArchivo::where('paciente_id', $paciente->id)
            ->where('subido_por_paciente', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => $this->format($a));

        return response()->json(['data' => $archivos]);
    }

    /** GET /api/paciente-portal/archivos-clinica
     *  Archivos subidos por la clínica/consultorio y marcados como visibles en portal.
     *  Agrupados por clínica.
     */
    public function indexClinica()
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $archivos = PacienteArchivo::with('clinica:id,nombre')
            ->where('paciente_id', $paciente->id)
            ->where('subido_por_paciente', false)
            ->where('visible_en_portal', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => $this->format($a, includeClinica: true));

        return response()->json(['data' => $archivos]);
    }

    /** POST /api/paciente-portal/archivos
     *  El paciente sube un archivo vinculado a una clínica específica.
     */
    public function store(Request $request)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $request->validate([
            'archivo'     => 'required|file|max:20480',
            'descripcion' => 'nullable|string|max:255',
            'clinica_id'  => 'required|integer|exists:clinicas,id',
        ]);

        // Verificar que el paciente pertenece a esa clínica
        $pertenece = $paciente->clinicas()->where('clinicas.id', $request->clinica_id)->exists();
        if (! $pertenece) {
            return response()->json(['message' => 'No perteneces a esa clínica'], 403);
        }

        $file = $request->file('archivo');

        $path = $file->storeAs(
            "pacientes/{$paciente->id}/archivos",
            time() . '_' . $file->getClientOriginalName(),
            'private'
        );

        $archivo = PacienteArchivo::create([
            'paciente_id'         => $paciente->id,
            'clinica_id'          => $request->clinica_id,
            'nombre_original'     => $file->getClientOriginalName(),
            'ruta'                => $path,
            'mime_type'           => $file->getMimeType(),
            'tamanio'             => $file->getSize(),
            'descripcion'         => $request->input('descripcion'),
            'subido_por_paciente' => true,
            'visible_en_portal'   => true,   // el paciente siempre ve sus propios archivos
            'subido_por_user_id'  => Auth::id(),
        ]);

        return response()->json(['data' => $this->format($archivo)], 201);
    }

    /** DELETE /api/paciente-portal/archivos/{id}  (solo los suyos) */
    public function destroy(int $id)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        $archivo = PacienteArchivo::where('paciente_id', $paciente->id)
            ->where('subido_por_paciente', true)
            ->findOrFail($id);

        Storage::disk('private')->delete($archivo->ruta);
        $archivo->delete();

        return response()->json(['message' => 'Archivo eliminado']);
    }

    /** GET /api/paciente-portal/archivos/{id}/ver */
    public function view(int $id)
    {
        $paciente = $this->pacienteAutorizado();
        if (! $paciente) return response()->json(['message' => 'No autorizado'], 403);

        // Puede ver: sus propios archivos O los de la clínica que estén visibles en portal
        $archivo = PacienteArchivo::where('paciente_id', $paciente->id)
            ->where(function ($q) {
                $q->where('subido_por_paciente', true)
                  ->orWhere('visible_en_portal', true);
            })
            ->findOrFail($id);

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

    private function format(PacienteArchivo $a, bool $includeClinica = false): array
    {
        $data = [
            'id'                  => $a->id,
            'nombre_original'     => $a->nombre_original,
            'descripcion'         => $a->descripcion,
            'mime_type'           => $a->mime_type,
            'tamanio'             => $a->tamanio,
            'tamanio_legible'     => $a->tamanio_legible,
            'clinica_id'          => $a->clinica_id,
            'subido_por_paciente' => $a->subido_por_paciente,
            'created_at'          => $a->created_at?->format('d/m/Y H:i'),
        ];
        if ($includeClinica) {
            $data['clinica_nombre'] = $a->clinica?->nombre;
        }
        return $data;
    }
}
