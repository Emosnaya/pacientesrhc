<?php

namespace App\Http\Controllers;

use App\Http\Resources\PacienteCollection;
use App\Models\Paciente;
use App\Services\ClinicalAuditService;
use App\Services\PacienteConsentimientoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    /**
     * El paciente pertenece al workspace activo (columna legacy o pivot clinica_paciente).
     */
    protected function pacienteBelongsToWorkspace(Paciente $paciente, $user): bool
    {
        $cid = $user->clinica_efectiva_id;
        if (! $cid) {
            return false;
        }
        if ((int) $paciente->clinica_id === (int) $cid) {
            return true;
        }

        return $paciente->clinicas()->where('clinica_id', $cid)->exists();
    }

    /**
     * Display a listing of the resource.
     * Todos los usuarios de la misma clínica pueden ver los pacientes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;
        
        // Obtener pacientes vinculados a la clínica/workspace activo mediante la tabla pivot
        $query = Paciente::whereHas('clinicas', function($q) use ($clinicaId) {
            $q->where('clinica_id', $clinicaId);
        });
        
        // Resolver sucursal: validar que pertenezca a la clínica efectiva
        $sucursalId = $this->resolveEffectiveSucursalId($user, $request, $clinicaId);
        
        if ($sucursalId) {
            $query->whereHas('clinicas', function($q) use ($clinicaId, $sucursalId) {
                $q->where('clinica_id', $clinicaId)
                  ->where('sucursal_id', $sucursalId);
            });
        }
        
        $pacientes = $query->get();
        
        return new PacienteCollection($pacientes);
    }

    /**
     * Store a newly created resource in storage.
     * - Si es admin: se asigna a sí mismo
     * - Si no es admin: debe enviar user_id del doctor a asignar
     * - Si el email ya existe, responde con error indicando que debe usar verificación OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Verificar si el email ya existe (si se proporcionó)
        if ($request->email) {
            $email = strtolower(trim($request->email));
            $existingPaciente = Paciente::where('email', $email)->first();
            
            if ($existingPaciente) {
                return response()->json([
                    'error' => 'Este email ya está registrado en el sistema',
                    'paciente_existente' => [
                        'id' => $existingPaciente->id,
                        'nombre' => $existingPaciente->nombre,
                        'apellidoPat' => $existingPaciente->apellidoPat,
                    ]
                ], 409);
            }
        }

        $paciente = new Paciente;
        $peso = $request->peso ?? 0;
        $talla = $request->talla ?? 0;
        if($talla > 0 && $peso > 0){
            $imc = ($peso)/($talla*$talla);
        }else{
            $imc = 0;
        }
        $fechaNacimiento = $request->fechaNacimiento;
        $edad = $fechaNacimiento ? Carbon::parse($fechaNacimiento)->age : 0;
        $genero = $request->genero;

        if ($genero === 1 || $genero === '1'){
            $paciente->genero = 1;
        }else{
            $paciente->genero = 0;
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;
        
        // Determinar sucursal_id
        if ($user->isSuperAdmin() && $request->has('sucursal_id') && $request->sucursal_id) {
            // Validar que la sucursal pertenezca a la clínica efectiva (no a la original)
            $sucursal = \App\Models\Sucursal::where('id', $request->sucursal_id)
                ->where('clinica_id', $clinicaId)
                ->first();
            
            if (!$sucursal) {
                return response()->json(['error' => 'La sucursal seleccionada no es válida'], 400);
            }
            
            $sucursalId = $sucursal->id;
        } else {
            // Usuarios normales: usar sucursal del usuario si pertenece a la clínica efectiva
            $sucursalId = $this->resolveEffectiveSucursalId($user, $request, $clinicaId);
        }

        // Calcular el siguiente registro según la clínica
        if ($clinicaId == 1) {
            // Clínica original: mantener comportamiento actual (recibe registro del request)
            $paciente->registro = $request->registro;
        } else {
            // Otras clínicas: registro secuencial por sucursal
            if (!$request->registro) {
                $ultimoRegistro = Paciente::where('clinica_id', $clinicaId)
                    ->where('sucursal_id', $sucursalId)
                    ->max('registro');
                $paciente->registro = $ultimoRegistro ? ((int)$ultimoRegistro + 1) : 1;
            } else {
                $paciente->registro = $request->registro;
            }
        }
        
        $paciente->nombre = $request->nombre;
        $paciente->apellidoPat = $request->apellidoPat;
        $paciente->apellidoMat = $request->apellidoMat ?? null;
        $paciente->telefono = $request->telefono;
        $paciente->domicilio = $request->domicilio ?? null;
        $paciente->calle = $request->calle ?? null;
        $paciente->num_ext = $request->num_ext ?? null;
        $paciente->num_int = $request->num_int ?? null;
        $paciente->colonia = $request->colonia ?? null;
        $paciente->codigo_postal = $request->codigo_postal ?? null;
        $paciente->ciudad = $request->ciudad ?? null;
        $paciente->estado_dir = $request->estado_dir ?? null;
        $paciente->profesion = $request->profesion ?? null;
        $paciente->cintura = $request->cintura ?? 0;
        $paciente->estadoCivil = $request->estadoCivil ?? null;
        $paciente->diagnostico = $request->diagnostico ?? null;
        $paciente->medicamentos = $request->medicamentos ?? null;
        $paciente->motivo_consulta = $request->motivo_consulta ?? null;
        $paciente->alergias = $request->alergias ?? null;
        $paciente->envio = $request->envio ?? null;
        $paciente->talla = $request->talla ?? 0;
        $paciente->peso = $request->peso ?? 0;
        $paciente->fechaNacimiento = $fechaNacimiento;
        $paciente->edad = $edad;
        $paciente->imc = $imc;
        $paciente->email = $request->email;
        $paciente->tipo_paciente = $request->tipo_paciente ?? 'cardiaca';
        $paciente->categoria_pago = $request->categoria_pago ?? null;
        $paciente->aseguradora = $request->categoria_pago === 'aseguradora' ? ($request->aseguradora ?? null) : null;
        $paciente->color = $request->color ?? null;
        
        // Determinar el dueño del paciente (simplificado)
        // Si envían user_id específico, validar que sea de la misma clínica efectiva
        if ($request->has('user_id') && $request->user_id) {
            $doctor = \App\Models\User::where('id', $request->user_id)
                ->where(function($q) use ($clinicaId, $user) {
                    // Aceptar si pertenece a la clínica efectiva directamente o por pivot
                    $q->where('clinica_id', $clinicaId)
                      ->orWhere('id', $user->id);
                })
                ->first();
            
            if (!$doctor) {
                return response()->json(['error' => 'El doctor seleccionado no es válido'], 400);
            }
            
            $paciente->user_id = $doctor->id;
        } else {
            // Si no envían user_id, asignar al usuario actual
            $paciente->user_id = $user->id;
        }

        $paciente->clinica_id = $clinicaId;
        $paciente->sucursal_id = $sucursalId;

        $paciente->save();
        
        // Crear la vinculación en la tabla pivot
        $paciente->clinicas()->attach($clinicaId, [
            'sucursal_id' => $sucursalId,
            'user_id' => $user->id,
            'vinculado_at' => now()
        ]);

        // LFPDPPP: invitación por correo para aceptar aviso y términos (si hay email)
        $paciente->refresh();
        app(PacienteConsentimientoService::class)->enviarInvitacion($paciente, $paciente->clinica);

        return [
            'message' => 'Paciente Guardado',
            'paciente' => $paciente
        ];
    }

    /**
     * Display the specified resource.
     * Todos de la misma clínica pueden ver.
     *
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function show(Paciente $paciente)
    {
        $user = Auth::user();

        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['error' => 'No tienes permisos para ver este paciente'], 403);
        }

        ClinicalAuditService::logAccess(
            $user,
            Paciente::class,
            $paciente->id,
            'Visualización de paciente en expediente',
            'viewed'
        );

        $paciente->load('clinicas');

        return response()->json($paciente);
    }

    /**
     * Update the specified resource in storage.
     * Todos de la misma clínica pueden editar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paciente $paciente)
    {
        $user = Auth::user();

        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['error' => 'No tienes permisos para editar este paciente'], 403);
        }

        // Calcular valores derivados
        $peso = $request->peso ?? 0;
        $talla = $request->talla ?? 0;
        $imc = ($talla > 0 && $peso > 0) ? ($peso)/($talla*$talla) : 0;
        $fechaNacimiento = $request->fechaNacimiento;
        $edad = $fechaNacimiento ? Carbon::parse($fechaNacimiento)->age : 0;

        // Actualizar todos los campos del paciente
        $paciente->update([
            'registro' => $request->registro,
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat,
            'apellidoMat' => $request->apellidoMat,
            'telefono' => $request->telefono,
            'domicilio' => $request->domicilio,
            'calle' => $request->calle,
            'num_ext' => $request->num_ext,
            'num_int' => $request->num_int,
            'colonia' => $request->colonia,
            'codigo_postal' => $request->codigo_postal,
            'ciudad' => $request->ciudad,
            'estado_dir' => $request->estado_dir,
            'profesion' => $request->profesion,
            'cintura' => $request->cintura,
            'estadoCivil' => $request->estadoCivil,
            'diagnostico' => $request->diagnostico,
            'medicamentos' => $request->medicamentos,
            'motivo_consulta' => $request->motivo_consulta,
            'alergias' => $request->alergias,
            'envio' => $request->envio,
            'talla' => $talla,
            'peso' => $peso,
            'fechaNacimiento' => $fechaNacimiento,
            'edad' => $edad,
            'imc' => $imc,
            'email' => $request->email,
            'genero' => ($request->genero == 1 || $request->genero === '1') ? 1 : 0,
            'tipo_paciente' => $request->tipo_paciente ?? $paciente->tipo_paciente,
            'categoria_pago' => $request->categoria_pago ?? $paciente->categoria_pago,
            'aseguradora' => $request->categoria_pago === 'aseguradora' ? ($request->aseguradora ?? $paciente->aseguradora) : null,
            'color' => $request->color ?? $paciente->color
        ]);

        return response()->json($paciente, 200);
    }

    /**
     * Remove the specified resource from storage.
     * Solo admin o dueño del paciente puede eliminar.
     *
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Paciente $paciente)
    {
        $user = Auth::user();
        
        // Solo super admin o admin pueden desvincular pacientes
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json(['error' => 'Solo administradores pueden desvincular pacientes'], 403);
        }
        
        $clinicaId = $user->clinica_efectiva_id;

        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['error' => 'No tienes permisos para desvincular este paciente'], 403);
        }
        
        // Desvincular paciente de la clínica (eliminar registro pivot, NO eliminar paciente)
        $paciente->clinicas()->detach($clinicaId);
        
        return response()->json(['message' => 'Paciente desvinculado exitosamente de la clínica'], 200);
    }

    /**
     * Flujo Express: Crear paciente de emergencia con cita automática
     * Para urgencias dentales u otras emergencias médicas
     * Cumplimiento: NOM-024-SSA3-2012 (Aceptación de aviso de privacidad)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createExpress(Request $request)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;
        
        // Validar que sea una clínica dental
        $clinica = \App\Models\Clinica::find($clinicaId);
        if (!$clinica || $clinica->tipo_clinica !== 'dental') {
            return response()->json([
                'message' => 'El flujo Express solo está disponible para clínicas dentales'
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'motivo_consulta' => 'required|string|max:500',
            'tipo_paciente' => 'sometimes|string|in:cardiaca,pulmonar,dental,fisioterapia,nutricion,psicologia',
            'email' => 'nullable|email|max:255',
        ]);
        
        // Determinar sucursal_id
        $sucursalId = $request->has('sucursal_id') && $user->isSuperAdmin() 
            ? $request->sucursal_id 
            : $this->resolveEffectiveSucursalId($user, $request, $clinicaId);
        
        // Generar registro secuencial por sucursal
        $ultimoRegistro = Paciente::where('clinica_id', $clinicaId)
            ->where('sucursal_id', $sucursalId)
            ->max('registro');
        $nuevoRegistro = $ultimoRegistro ? ((int)$ultimoRegistro + 1) : 1;
        
        $emailExpress = $request->filled('email') ? strtolower(trim($request->email)) : null;

        $datosPaciente = [
            'registro' => $nuevoRegistro,
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat ?? '',
            'apellidoMat' => $request->apellidoMat ?? '',
            'telefono' => $request->telefono,
            'fechaNacimiento' => '1900-01-01', // Fecha temporal para emergencias - se actualiza después
            'edad' => 0,
            'genero' => false,
            'talla' => 0,
            'peso' => 0,
            'cintura' => 0,
            'imc' => 0,
            'motivo_consulta' => $request->motivo_consulta,
            'tipo_paciente' => $request->tipo_paciente ?? 'dental',
            'user_id' => $user->id,
            'clinica_id' => $clinicaId,
            'sucursal_id' => $sucursalId,
        ];

        if ($emailExpress) {
            $datosPaciente['email'] = $emailExpress;
            // LFPDPPP: con email se envía invitación; la aceptación queda en el enlace del correo
            $datosPaciente['aviso_privacidad_aceptado_at'] = null;
            $datosPaciente['version_aviso'] = null;
        } else {
            // Urgencia sin correo: aceptación implícita documentada en expediente (NOM express)
            $datosPaciente['aviso_privacidad_aceptado_at'] = now();
            $datosPaciente['version_aviso'] = '1.0-EXPRESS';
        }

        $paciente = Paciente::create($datosPaciente);

        if ($emailExpress) {
            app(PacienteConsentimientoService::class)->enviarInvitacion($paciente->fresh(), $clinica);
        }

        $paciente->clinicas()->attach($clinicaId, [
            'sucursal_id' => $sucursalId,
            'user_id' => $user->id,
            'vinculado_at' => now(),
        ]);

        // Crear cita automática marcada como completada (ya está en consulta)
        $cita = \App\Models\Cita::create([
            'paciente_id' => $paciente->id,
            'admin_id' => $user->id,
            'user_id' => $user->id,
            'clinica_id' => $clinicaId,
            'sucursal_id' => $sucursalId,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
            'estado' => 'completada', // La urgencia ya está siendo atendida
            'primera_vez' => true,
            'notas' => 'Urgencia Express: ' . $request->motivo_consulta,
        ]);

        return response()->json([
            'message' => 'Paciente express creado exitosamente',
            'paciente' => $paciente,
            'cita' => $cita,
        ], 201);
    }

    /**
     * Resolver sucursal_id validando que pertenezca a la clínica efectiva.
     * Evita que el frontend pase una sucursal del workspace anterior (caché stale).
     */
    private function resolveEffectiveSucursalId($user, $request, int $clinicaId): ?int
    {
        if ($request->has('sucursal_id') && $request->sucursal_id) {
            $valida = \App\Models\Sucursal::where('id', $request->sucursal_id)
                ->where('clinica_id', $clinicaId)
                ->exists();
            if ($valida) return (int) $request->sucursal_id;
            // sucursal_id no pertenece al workspace activo — ignorar
        }

        // Usar user->sucursal_id solo si pertenece a la clínica efectiva
        if ($user->sucursal_id) {
            $valida = \App\Models\Sucursal::where('id', $user->sucursal_id)
                ->where('clinica_id', $clinicaId)
                ->exists();
            if ($valida) return $user->sucursal_id;
        }

        return null; // Sin filtro de sucursal = todos los de la clínica efectiva
    }

    /**
     * Visibilidad en portal del paciente por clínica (LFPDPPP: solo lo que la clínica autoriza).
     */
    public function updatePortalVisibilidad(Request $request, Paciente $paciente)
    {
        $user = Auth::user();
        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'portal_visible_citas' => 'sometimes|boolean',
            'portal_visible_datos_basicos' => 'sometimes|boolean',
            'portal_visible_expediente_resumen' => 'sometimes|boolean',
        ]);

        $clinicaId = (int) $validated['clinica_id'];
        if (! $paciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
            return response()->json(['message' => 'La clínica no está vinculada a este paciente'], 422);
        }

        $updates = [];
        foreach (['portal_visible_citas', 'portal_visible_datos_basicos', 'portal_visible_expediente_resumen'] as $key) {
            if (array_key_exists($key, $validated)) {
                $updates[$key] = (bool) $validated[$key];
            }
        }

        if ($updates === []) {
            return response()->json(['message' => 'Sin cambios'], 200);
        }

        $paciente->clinicas()->updateExistingPivot($clinicaId, $updates);

        return response()->json(['message' => 'Visibilidad actualizada']);
    }
}

