<?php

namespace App\Http\Controllers;

use App\Models\PacienteLinkCompartido;
use App\Models\PacienteArchivo;
use App\Models\PortalExpedienteCompartido;
use Illuminate\Support\Facades\Storage;

/**
 * Endpoint PÚBLICO: sirve el archivo/PDF asociado a un token de compartir.
 * No requiere autenticación.
 */
class DocumentoCompartidoPublicoController extends Controller
{
    /** GET /documento-compartido/{token} */
    public function ver(string $token)
    {
        $link = PacienteLinkCompartido::where('token', $token)->firstOrFail();

        if (! $link->isValido()) {
            return response()->json([
                'message' => 'Este enlace ha expirado o alcanzó el límite de vistas.',
                'expirado' => true,
            ], 410);
        }

        $link->incrementarVistas();

        if ($link->tipo === 'archivo') {
            return $this->servirArchivo($link);
        }

        return $this->servirExpediente($link);
    }

    /** GET /documento-compartido/{token}/info — metadata del link (sin servir el archivo) */
    public function info(string $token)
    {
        $link = PacienteLinkCompartido::where('token', $token)->first();

        if (! $link) {
            return response()->json(['message' => 'Enlace no encontrado'], 404);
        }

        if (! $link->isValido()) {
            return response()->json(['message' => 'Este enlace ha expirado o alcanzó el límite de vistas.', 'expirado' => true], 410);
        }

        $extra = [];
        if ($link->tipo === 'archivo') {
            $archivo = PacienteArchivo::find($link->referencia_id);
            $extra = [
                'mime_type'      => $archivo?->mime_type,
                'tamanio_legible'=> $archivo?->tamanio_legible,
                'clinica_id'     => $archivo?->clinica_id,
            ];
        }

        return response()->json(array_merge([
            'valido'    => true,
            'tipo'      => $link->tipo,
            'nombre'    => $link->referencia_nombre,
            'nota'      => $link->nota,
            'expira_en' => $link->expires_at?->format('d/m/Y H:i'),
            'vistas'    => $link->vistas,
            'max_vistas'=> $link->max_vistas,
        ], $extra));
    }

    private function servirArchivo(PacienteLinkCompartido $link)
    {
        $archivo = PacienteArchivo::findOrFail($link->referencia_id);

        if (! Storage::disk('private')->exists($archivo->ruta)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return response(
            Storage::disk('private')->get($archivo->ruta),
            200,
            [
                'Content-Type'        => $archivo->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $archivo->nombre_original . '"',
                'Cache-Control'       => 'no-store',
            ]
        );
    }

    private function servirExpediente(PacienteLinkCompartido $link)
    {
        // Redirigimos a la misma ruta del portal PDF ya existente,
        // pero usando el token para autenticar sin sesión.
        // En este caso retornamos la metadata para que el frontend
        // llame al endpoint correcto de generación de PDF.
        $exp = PortalExpedienteCompartido::find($link->referencia_id);

        if (! $exp) {
            return response()->json(['message' => 'Expediente no encontrado'], 404);
        }

        return response()->json([
            'tipo'             => 'expediente',
            'portal_row_id'    => $exp->id,
            'tipo_exp'         => $exp->tipo_exp,
            'tipo_nombre'      => $exp->tipo_nombre,
            'expediente_id'    => $exp->expediente_id,
        ]);
    }
}
