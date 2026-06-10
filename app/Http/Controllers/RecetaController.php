<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RecetaController extends Controller
{
    /**
     * Roles permitidos para acceder a recetas
     */
    private const ROLES_RECETAS = ['doctor', 'doctora'];

    /**
     * Verificar si el usuario puede acceder a recetas
     */
    private function canAccessRecetas($user): bool
    {
        return $user && $user->rol && in_array(strtolower($user->rol), self::ROLES_RECETAS);
    }

    /**
     * Listar recetas (todas de la sucursal o por paciente).
     * Solo accesible para doctores.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea doctor/doctora
        if (!$this->canAccessRecetas($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los doctores pueden acceder a las recetas médicas'
            ], 403);
        }
        
        $clinicaId = $user->clinica_efectiva_id;
        // Validar que la sucursal pertenezca a la clínica efectiva
        $sucursalId = null;
        if ($request->has('sucursal_id') && $request->sucursal_id) {
            $valida = \App\Models\Sucursal::where('id', $request->sucursal_id)
                ->where('clinica_id', $clinicaId)->exists();
            $sucursalId = $valida ? $request->sucursal_id : null;
        } elseif ($user->sucursal_id) {
            $valida = \App\Models\Sucursal::where('id', $user->sucursal_id)
                ->where('clinica_id', $clinicaId)->exists();
            $sucursalId = $valida ? $user->sucursal_id : null;
        }

        $query = Receta::where('clinica_id', $clinicaId)
            ->where('user_id', $user->id) // Solo recetas creadas por el usuario autenticado
            ->with(['paciente', 'user', 'medicamentos'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        $recetas = $query->get();
        return response()->json($recetas);
    }

    /**
     * Recetas de un paciente.
     * Solo accesible para doctores.
     */
    public function getByPaciente(Request $request, $pacienteId)
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea doctor/doctora
        if (!$this->canAccessRecetas($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los doctores pueden acceder a las recetas médicas'
            ], 403);
        }
        
        $clinicaId = $user->clinica_efectiva_id;
        $sucursalId = null;
        if ($request->has('sucursal_id') && $request->sucursal_id) {
            $valida = \App\Models\Sucursal::where('id', $request->sucursal_id)
                ->where('clinica_id', $clinicaId)->exists();
            $sucursalId = $valida ? $request->sucursal_id : null;
        } elseif ($user->sucursal_id) {
            $valida = \App\Models\Sucursal::where('id', $user->sucursal_id)
                ->where('clinica_id', $clinicaId)->exists();
            $sucursalId = $valida ? $user->sucursal_id : null;
        }

        $query = Receta::where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->with(['user', 'medicamentos'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $recetas = $query->get();
        return response()->json($recetas);
    }

    /**
     * Ver una receta.
     * Solo accesible para doctores.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea doctor/doctora
        if (!$this->canAccessRecetas($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los doctores pueden acceder a las recetas médicas'
            ], 403);
        }
        
        $receta = Receta::where('clinica_id', $user->clinica_efectiva_id)
            ->with(['paciente', 'user', 'sucursal', 'medicamentos'])
            ->findOrFail($id);
        return response()->json($receta);
    }

    /**
     * Crear receta.
     * Solo usuarios con rol "doctor" o "doctora" pueden crear recetas.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Verificar que el usuario sea doctor/doctora
        if (!$this->canAccessRecetas($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los doctores pueden crear recetas médicas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'nota_subsecuente_id' => 'nullable|exists:nota_subsecuente_cardiologias,id',
            'historia_clinica_id' => 'nullable|exists:historia_clinica_cardiologias,id',
            'fecha' => 'required|date',
            'diagnostico_principal' => 'nullable|string|max:500',
            'indicaciones_generales' => 'nullable|string',
            'medicamentos' => 'nullable|array',
            'medicamentos.*.medicamento' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.presentacion' => 'nullable|string|max:255',
            'medicamentos.*.via' => 'nullable|string|max:100',
            'medicamentos.*.dosis' => 'nullable|string|max:255',
            'medicamentos.*.frecuencia' => 'nullable|string|max:255',
            'medicamentos.*.duracion' => 'nullable|string|max:255',
            'medicamentos.*.indicaciones_especificas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        $paciente = \App\Models\Paciente::findOrFail($request->paciente_id);

        // Generar folio automáticamente por clínica/sucursal
        $clinicaId = $user->clinica_efectiva_id;
        $folio = $this->generarFolio($clinicaId, $sucursalId);

        $receta = Receta::create([
            'folio' => $folio,
            'paciente_id' => $request->paciente_id,
            'nota_subsecuente_id' => $request->nota_subsecuente_id,
            'historia_clinica_id' => $request->historia_clinica_id,
            'user_id' => $user->id,
            'sucursal_id' => $sucursalId,
            'clinica_id' => $clinicaId,
            'fecha' => $request->fecha,
            'diagnostico_principal' => $request->diagnostico_principal,
            'indicaciones_generales' => $request->indicaciones_generales,
        ]);

        $this->syncMedicamentos($receta, $request->medicamentos ?? []);

        $receta->load(['paciente', 'user', 'medicamentos']);
        return response()->json([
            'success' => true,
            'message' => 'Receta guardada correctamente',
            'data' => $receta
        ], 201);
    }

    /**
     * Actualizar receta vinculada a nota de subsecuente.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$this->canAccessRecetas($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los doctores pueden actualizar recetas médicas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'fecha' => 'sometimes|required|date',
            'diagnostico_principal' => 'nullable|string|max:500',
            'indicaciones_generales' => 'nullable|string',
            'medicamentos' => 'nullable|array',
            'medicamentos.*.medicamento' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.presentacion' => 'nullable|string|max:255',
            'medicamentos.*.via' => 'nullable|string|max:100',
            'medicamentos.*.dosis' => 'nullable|string|max:255',
            'medicamentos.*.frecuencia' => 'nullable|string|max:255',
            'medicamentos.*.duracion' => 'nullable|string|max:255',
            'medicamentos.*.indicaciones_especificas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $receta = Receta::where('clinica_id', $user->clinica_efectiva_id)
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $receta->update($request->only([
            'fecha',
            'diagnostico_principal',
            'indicaciones_generales',
        ]));

        if ($request->has('medicamentos')) {
            $this->syncMedicamentos($receta, $request->medicamentos ?? []);
        }

        $receta->load(['paciente', 'user', 'medicamentos']);
        return response()->json([
            'success' => true,
            'message' => 'Receta actualizada correctamente',
            'data' => $receta
        ]);
    }

    private function syncMedicamentos(Receta $receta, array $medicamentos): void
    {
        $receta->medicamentos()->delete();
        foreach ($medicamentos as $orden => $item) {
            if (empty($item['medicamento'])) {
                continue;
            }
            $receta->medicamentos()->create([
                'medicamento' => $item['medicamento'],
                'presentacion' => $item['presentacion'] ?? null,
                'via' => $item['via'] ?? null,
                'dosis' => $item['dosis'] ?? null,
                'frecuencia' => $item['frecuencia'] ?? null,
                'duracion' => $item['duracion'] ?? null,
                'indicaciones_especificas' => $item['indicaciones_especificas'] ?? null,
                'orden' => $orden,
            ]);
        }
    }

    /**
     * Enviar receta por correo con PDF adjunto.
     */
    public function enviarCorreo(Request $request, $id)
    {
        $user = Auth::user();

        if (!$this->canAccessRecetas($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los doctores pueden enviar recetas'
            ], 403);
        }

        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $receta = Receta::where('clinica_id', $user->clinica_efectiva_id)
            ->where('user_id', $user->id)
            ->with('paciente')
            ->findOrFail($id);

        $pdfBinary = app(PDFController::class)->recetaPdfBinary($request, $id);
        $paciente = $receta->paciente;
        $nombre = trim(($paciente->nombre ?? '') . ' ' . ($paciente->apellidoPat ?? ''));

        Mail::send([], [], function ($msg) use ($data, $receta, $pdfBinary, $nombre) {
            $msg->to($data['email'])
                ->subject("Receta médica #{$receta->folio} — {$nombre}")
                ->html('<p>Adjunto encontrará la receta médica solicitada.</p>')
                ->attachData($pdfBinary, "receta-{$receta->folio}.pdf", [
                    'mime' => 'application/pdf',
                ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Receta enviada por correo correctamente',
        ]);
    }

    /**
     * Obtener configuración del diseño PDF de recetas de la clínica.
     */
    public function getPdfConfig(Request $request)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_efectiva_id);
        if (!$clinica) {
            return response()->json([
                'orden_secciones' => ['header', 'titulo', 'paciente', 'diagnostico', 'medicamentos', 'indicaciones', 'firma']
            ]);
        }
        $config = $clinica->receta_pdf_config ?? [];
        $orden = $config['orden_secciones'] ?? ['header', 'titulo', 'paciente', 'diagnostico', 'medicamentos', 'indicaciones', 'firma'];
        return response()->json([
            'orden_secciones' => $orden,
            'secciones_disponibles' => [
                ['id' => 'header', 'nombre' => 'Encabezado (logo y datos clínica)'],
                ['id' => 'titulo', 'nombre' => 'Título y fecha'],
                ['id' => 'paciente', 'nombre' => 'Datos del paciente'],
                ['id' => 'diagnostico', 'nombre' => 'Diagnóstico'],
                ['id' => 'medicamentos', 'nombre' => 'Medicamentos prescritos'],
                ['id' => 'indicaciones', 'nombre' => 'Indicaciones generales'],
                ['id' => 'firma', 'nombre' => 'Firma del médico'],
            ]
        ]);
    }

    /**
     * Actualizar configuración del diseño PDF de recetas de la clínica.
     */
    public function updatePdfConfig(Request $request)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_efectiva_id);
        if (!$clinica) {
            return response()->json(['message' => 'Clínica no encontrada'], 404);
        }
        $validator = Validator::make($request->all(), [
            'orden_secciones' => 'required|array',
            'orden_secciones.*' => 'string|in:header,titulo,paciente,diagnostico,medicamentos,indicaciones,firma',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $orden = $request->orden_secciones;
        $clinica->receta_pdf_config = ['orden_secciones' => $orden];
        $clinica->save();
        return response()->json([
            'success' => true,
            'message' => 'Diseño del PDF actualizado',
            'orden_secciones' => $orden
        ]);
    }

    /**
     * Generar folio secuencial por clínica y sucursal.
     */
    private function generarFolio($clinicaId, $sucursalId)
    {
        // Obtener el último folio de la combinación clínica/sucursal
        $ultimoFolio = Receta::where('clinica_id', $clinicaId)
            ->where('sucursal_id', $sucursalId)
            ->max('folio');

        // Incrementar el folio, similar al registro de pacientes
        return $ultimoFolio ? ((int)$ultimoFolio + 1) : 1;
    }
}
