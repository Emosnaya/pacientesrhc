<?php

namespace App\Http\Controllers;

use App\Http\Resources\PacienteCollection;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\PortalExpedienteCompartido;
use App\Services\ClinicalAuditService;
use App\Services\PacienteConsentimientoService;
use App\Services\PhoneHashService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    /**
     * El paciente pertenece al workspace activo (columna legacy o pivot clinica_paciente).
     */
    protected function pacienteBelongsToWorkspace(Paciente $paciente, $user): bool
    {
        $cid = $user->clinica_efectiva_id ?? null;

        return $paciente->belongsToClinicaWorkspace($cid ? (int) $cid : null);
    }

    /**
     * Localiza un paciente antes de abrir el formulario de alta.
     * El QR usa un UUID no secuencial; correo y teléfono se normalizan.
     */
    public function buscarParaVinculacion(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email|required_without_all:telefono,uuid_publico',
            'telefono' => 'nullable|string|max:30|required_without_all:email,uuid_publico',
            'uuid_publico' => 'nullable|uuid|required_without_all:email,telefono',
        ]);

        $pacientes = collect();
        $coincidencias = [];

        if (! empty($validated['email'])) {
            $email = strtolower(trim($validated['email']));
            if ($paciente = Paciente::where('email', $email)->first()) {
                $pacientes->put($paciente->id, $paciente);
                $coincidencias[] = 'correo';
            }
        }

        if (! empty($validated['telefono'])) {
            $hash = app(PhoneHashService::class)->hash($validated['telefono']);
            if ($hash && ($paciente = Paciente::where('telefono_search_hash', $hash)->first())) {
                $pacientes->put($paciente->id, $paciente);
                $coincidencias[] = 'telefono';
            }
        }

        if (! empty($validated['uuid_publico'])) {
            if ($paciente = Paciente::where('uuid_publico', $validated['uuid_publico'])->first()) {
                $pacientes->put($paciente->id, $paciente);
                $coincidencias[] = 'qr';
            }
        }

        if ($pacientes->count() > 1) {
            return response()->json([
                'message' => 'Los identificadores ingresados pertenecen a pacientes distintos.',
            ], 422);
        }

        /** @var Paciente|null $paciente */
        $paciente = $pacientes->first();
        if (! $paciente) {
            return response()->json([
                'message' => 'No encontramos un paciente con esos datos.',
            ], 404);
        }

        $clinicaId = Auth::user()?->clinica_efectiva_id;
        if ($clinicaId && $paciente->belongsToClinicaWorkspace((int) $clinicaId)) {
            return response()->json([
                'message' => 'Este paciente ya está vinculado a tu clínica o consultorio.',
                'already_linked' => true,
                'paciente_id' => $paciente->id,
            ], 409);
        }

        $g = (int) (bool) $paciente->genero;
        $coincidencia = count($coincidencias) > 1 ? implode('_y_', $coincidencias) : $coincidencias[0];

        return response()->json([
            'success' => true,
            'paciente_existente' => [
                'id' => $paciente->id,
                'nombre' => $paciente->nombre,
                'apellidoPat' => $paciente->apellidoPat,
                'apellidoMat' => $paciente->apellidoMat,
                'email' => $paciente->email,
                'telefono' => $paciente->telefono,
                'fechaNacimiento' => $paciente->fechaNacimiento?->format('Y-m-d'),
                'genero' => $g,
                'genero_label' => $g === 1 ? 'Masculino' : 'Femenino',
                'domicilio_formateado' => $paciente->domicilio_formateado,
                'coincidencia' => $coincidencia,
            ],
        ]);
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

        $sucursalId = $this->resolveEffectiveSucursalId($user, $request, $clinicaId);

        // Solo pacientes con fila en clinica_paciente (sucursal opcional en el pivot)
        $query = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null);

        if ($request->filled('tipo_paciente')) {
            $tipoFiltro = $request->tipo_paciente;
            $query->whereHas('clinicas', function ($q) use ($clinicaId, $sucursalId, $tipoFiltro) {
                $q->where('clinicas.id', $clinicaId)
                    ->where('clinica_paciente.tipo_paciente', $tipoFiltro);
                if ($sucursalId) {
                    $q->where('clinica_paciente.sucursal_id', $sucursalId);
                }
            });
        }

        $pacientes = $query->with(['clinicas' => function ($q) use ($clinicaId) {
            $q->where('clinicas.id', $clinicaId);
        }])->get();

        foreach ($pacientes as $paciente) {
            $paciente->mergeClinicaPivotAttributes($user->clinica_efectiva_id);
        }

        return new PacienteCollection($pacientes);
    }

    /**
     * Store a newly created resource in storage.
     * - Si es admin: se asigna a sí mismo
     * - Si no es admin: debe enviar user_id del doctor a asignar
     * - Si el correo o teléfono ya existe, ofrece vincular al paciente sin duplicarlo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Evitar duplicados globales: buscar por correo y por hash normalizado del teléfono.
        $email = $request->filled('email') ? strtolower(trim((string) $request->email)) : null;
        $telefonoHash = $request->filled('telefono')
            ? app(PhoneHashService::class)->hash((string) $request->telefono)
            : null;
        $pacientePorEmail = $email ? Paciente::where('email', $email)->first() : null;
        $pacientePorTelefono = $telefonoHash
            ? Paciente::where('telefono_search_hash', $telefonoHash)->first()
            : null;

        if ($pacientePorEmail && $pacientePorTelefono && $pacientePorEmail->id !== $pacientePorTelefono->id) {
            return response()->json([
                'message' => 'El correo y el teléfono pertenecen a pacientes distintos. Verifica los datos antes de continuar.',
                'error' => 'Los datos de contacto no corresponden a la misma persona.',
            ], 422);
        }

        $existingPaciente = $pacientePorEmail ?: $pacientePorTelefono;
        if ($existingPaciente) {
            $g = (int) (bool) $existingPaciente->genero;
            $coincidencia = $pacientePorEmail && $pacientePorTelefono
                ? 'correo_y_telefono'
                : ($pacientePorEmail ? 'correo' : 'telefono');

            return response()->json([
                'message' => 'Este paciente ya existe. Puedes vincularlo a tu clínica o consultorio sin crear un duplicado.',
                'error' => 'Paciente ya registrado en el sistema',
                'coincidencia' => $coincidencia,
                'paciente_existente' => [
                    'id' => $existingPaciente->id,
                    'nombre' => $existingPaciente->nombre,
                    'apellidoPat' => $existingPaciente->apellidoPat,
                    'apellidoMat' => $existingPaciente->apellidoMat,
                    'email' => $existingPaciente->email,
                    'telefono' => $existingPaciente->telefono,
                    'fechaNacimiento' => $existingPaciente->fechaNacimiento?->format('Y-m-d'),
                    'genero' => $g,
                    'genero_label' => $g === 1 ? 'Masculino' : 'Femenino',
                    'domicilio_formateado' => $existingPaciente->domicilio_formateado,
                    'coincidencia' => $coincidencia,
                ],
            ], 409);
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

        // Si no se captura un registro, generar el consecutivo desde 1.
        // Esto aplica también a la clínica original.
        if ($request->filled('registro')) {
            $paciente->registro = trim((string) $request->registro);
        } else {
            $paciente->registro = Paciente::siguienteRegistroParaClinica(
                (int) $clinicaId,
                $sucursalId ? (int) $sucursalId : null
            );
        }
        
        $paciente->nombre = $request->nombre;
        $paciente->apellidoPat = $request->apellidoPat;
        $paciente->apellidoMat = $request->apellidoMat ?? null;
        $paciente->telefono = $request->telefono;
        $paciente->whatsapp_notificaciones = (bool) $request->boolean('whatsapp_notificaciones');
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
        $paciente->alergias = $request->alergias ?? null;
        $paciente->envio = $request->envio ?? null;
        $paciente->talla = $request->talla ?? 0;
        $paciente->peso = $request->peso ?? 0;
        $paciente->fechaNacimiento = $fechaNacimiento;
        $paciente->edad = $edad;
        $paciente->imc = $imc;
        $paciente->email = $email;
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
        
        $tipoPivot = $request->tipo_paciente ?? $paciente->tipo_paciente ?? 'cardiaca';

        // Generar numero_expediente local para esta clínica (usa el mismo que el registro global al crear)
        $numeroExpediente = $request->numero_expediente ?? $paciente->registro ?? null;

        // Crear la vinculación en la tabla pivot (motivo y tipo por clínica)
        $paciente->clinicas()->attach($clinicaId, [
            'sucursal_id' => $sucursalId,
            'user_id' => $user->id,
            'vinculado_at' => now(),
            'motivo_consulta' => $request->motivo_consulta ?? null,
            'tipo_paciente' => $tipoPivot,
            'numero_expediente' => $numeroExpediente,
            'portal_visible_citas' => true,
            'portal_visible_datos_basicos' => true,
        ]);

        // LFPDPPP: invitación por correo para aceptar aviso y términos (si hay email)
        $paciente->refresh();
        $paciente->load('clinicas');
        $paciente->mergeClinicaPivotAttributes($user->clinica_efectiva_id);
        app(PacienteConsentimientoService::class)->enviarInvitacion($paciente, $paciente->clinica);

        return [
            'message' => 'Paciente Guardado',
            'paciente' => $paciente
        ];
    }

    /**
     * Vincula un paciente existente al workspace activo.
     * Requiere que el correo o el teléfono coincidan para evitar vincular por ID arbitrario.
     */
    public function vincularClinica(Request $request, Paciente $paciente)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_efectiva_id;
        if (! $clinicaId) {
            return response()->json(['message' => 'No hay clínica activa en el workspace.'], 400);
        }

        $validated = $request->validate([
            'email' => 'nullable|email|required_without_all:telefono,uuid_publico',
            'telefono' => 'nullable|string|max:30|required_without_all:email,uuid_publico',
            'uuid_publico' => 'nullable|uuid|required_without_all:email,telefono',
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
            'tipo_paciente' => 'nullable|string|max:255',
        ]);

        $emailCoincide = false;
        if (! empty($validated['email'])) {
            $email = strtolower(trim($validated['email']));
            $emailCoincide = strtolower(trim((string) ($paciente->email ?? ''))) === $email;
        }

        $telefonoCoincide = false;
        if (! empty($validated['telefono'])) {
            $hashEnviado = app(PhoneHashService::class)->hash($validated['telefono']);
            $telefonoCoincide = $hashEnviado
                && hash_equals((string) $paciente->telefono_search_hash, $hashEnviado);
        }

        $uuidCoincide = ! empty($validated['uuid_publico'])
            && hash_equals((string) $paciente->uuid_publico, (string) $validated['uuid_publico']);

        if (! $emailCoincide && ! $telefonoCoincide && ! $uuidCoincide) {
            return response()->json([
                'message' => 'El correo o teléfono no coincide con este paciente.',
            ], 422);
        }

        if ($paciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
            return response()->json([
                'message' => 'Este paciente ya está vinculado a tu clínica o consultorio.',
            ], 422);
        }

        if ($user->isSuperAdmin() && $request->filled('sucursal_id')) {
            $sucursal = \App\Models\Sucursal::where('id', $request->sucursal_id)
                ->where('clinica_id', $clinicaId)
                ->first();

            if (! $sucursal) {
                return response()->json(['error' => 'La sucursal seleccionada no es válida'], 400);
            }
            $sucursalId = $sucursal->id;
        } else {
            $sucursalId = $this->resolveEffectiveSucursalId($user, $request, $clinicaId);
        }

        $tipoNuevoVinculo = $validated['tipo_paciente'] ?? null;
        if ($tipoNuevoVinculo === null || $tipoNuevoVinculo === '') {
            $tipoNuevoVinculo = $paciente->tipo_paciente ?: 'general';
        }

        // Generar numero_expediente secuencial para esta clínica
        $nuevoExpediente = $this->generarNumeroExpedienteClinica($clinicaId);

        $paciente->clinicas()->attach($clinicaId, [
            'sucursal_id' => $sucursalId,
            'user_id' => $user->id,
            'vinculado_at' => now(),
            'motivo_consulta' => null,
            'tipo_paciente' => $tipoNuevoVinculo,
            'numero_expediente' => $nuevoExpediente,
            'portal_visible_citas' => true,
            'portal_visible_datos_basicos' => true,
        ]);

        $paciente->refresh();
        $clinica = Clinica::query()->find($clinicaId);
        $consentimientoEmailEnviado = false;
        if ($clinica) {
            try {
                $consentimientoEmailEnviado = app(PacienteConsentimientoService::class)->enviarInvitacion(
                    $paciente,
                    $clinica,
                    PacienteConsentimientoService::CONTEXTO_NUEVA_VINCULACION
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $paciente->load('clinicas');
        $paciente->mergeClinicaPivotAttributes($user->clinica_efectiva_id);

        return response()->json([
            'message' => 'Paciente vinculado a tu clínica',
            'paciente' => $paciente,
            'consentimiento_email_enviado' => $consentimientoEmailEnviado,
        ], 201);
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
        $paciente->mergeClinicaPivotAttributes($user->clinica_efectiva_id);

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
            'whatsapp_notificaciones' => $request->has('whatsapp_notificaciones')
                ? $request->boolean('whatsapp_notificaciones')
                : $paciente->whatsapp_notificaciones,
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
            'alergias' => $request->alergias,
            'envio' => $request->envio,
            'talla' => $talla,
            'peso' => $peso,
            'fechaNacimiento' => $fechaNacimiento,
            'edad' => $edad,
            'imc' => $imc,
            'email' => $request->email,
            'genero' => ($request->genero == 1 || $request->genero === '1') ? 1 : 0,
            'categoria_pago' => $request->categoria_pago ?? $paciente->categoria_pago,
            'aseguradora' => $request->categoria_pago === 'aseguradora' ? ($request->aseguradora ?? $paciente->aseguradora) : null,
            'color' => $request->color ?? $paciente->color
        ]);

        $efectiva = $user->clinica_efectiva_id;
        if ($efectiva && $paciente->clinicas()->where('clinicas.id', $efectiva)->exists()) {
            $pivotUpdates = [];
            if ($request->has('motivo_consulta')) {
                $pivotUpdates['motivo_consulta'] = $request->motivo_consulta;
            }
            if ($request->has('tipo_paciente')) {
                $pivotUpdates['tipo_paciente'] = $request->tipo_paciente;
            }
            // numero_expediente local de la clínica (si se envía)
            if ($request->has('numero_expediente')) {
                $pivotUpdates['numero_expediente'] = $request->numero_expediente;
            }
            if ($pivotUpdates !== []) {
                $paciente->clinicas()->updateExistingPivot($efectiva, $pivotUpdates);
            }
        }

        $paciente->refresh();
        $paciente->load('clinicas');
        $paciente->mergeClinicaPivotAttributes($user->clinica_efectiva_id);

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
        $ultimoRegistro = Paciente::forClinicaWorkspace((int) $clinicaId, $sucursalId ?: null)
            ->max('registro');
        $nuevoRegistro = $ultimoRegistro ? ((int) $ultimoRegistro + 1) : 1;
        
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
            'motivo_consulta' => $request->motivo_consulta,
            'tipo_paciente' => $paciente->tipo_paciente ?? 'dental',
            'numero_expediente' => $paciente->registro,
            'portal_visible_citas' => true,
            'portal_visible_datos_basicos' => true,
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

        $paciente->load('clinicas');
        $paciente->mergeClinicaPivotAttributes($user->clinica_efectiva_id);

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

    /**
     * Lista de expedientes marcados para el portal del paciente (clínica activa).
     */
    public function portalExpedientesCompartidos(Request $request, Paciente $paciente)
    {
        $user = Auth::user();
        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $clinicaId = (int) $request->query('clinica_id', $user->clinica_efectiva_id);
        if ($clinicaId !== (int) $user->clinica_efectiva_id) {
            return response()->json(['message' => 'Usa la clínica/consultorio activo en el selector'], 403);
        }

        if (! $paciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
            return response()->json(['data' => []]);
        }

        $rows = PortalExpedienteCompartido::query()
            ->where('paciente_id', $paciente->id)
            ->where('clinica_id', $clinicaId)
            ->get(['tipo_exp', 'expediente_id', 'tipo_nombre_snapshot', 'fecha_snapshot']);

        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'tipo_exp' => (int) $r->tipo_exp,
                'expediente_id' => (int) $r->expediente_id,
                'tipo_nombre' => $r->tipo_nombre_snapshot,
                'fecha' => $r->fecha_snapshot?->format('Y-m-d'),
            ]),
        ]);
    }

    /**
     * Sustituye la lista de expedientes visibles en el portal para esta clínica y paciente.
     */
    public function syncPortalExpedientesCompartidos(Request $request, Paciente $paciente)
    {
        $user = Auth::user();
        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'items' => 'present|array',
            'items.*.tipo_exp' => 'required|integer|min:1|max:50',
            'items.*.expediente_id' => 'required|integer|min:1',
            'items.*.tipo_nombre' => 'nullable|string|max:191',
            'items.*.fecha' => 'nullable|date',
        ]);

        $clinicaId = (int) $validated['clinica_id'];
        if ($clinicaId !== (int) $user->clinica_efectiva_id) {
            return response()->json(['message' => 'Solo puedes editar desde tu clínica/consultorio activo'], 403);
        }

        if (! $paciente->clinicas()->where('clinicas.id', $clinicaId)->exists()) {
            return response()->json(['message' => 'Paciente no vinculado a esta clínica'], 422);
        }

        DB::transaction(function () use ($paciente, $clinicaId, $validated) {
            PortalExpedienteCompartido::query()
                ->where('paciente_id', $paciente->id)
                ->where('clinica_id', $clinicaId)
                ->delete();

            foreach ($validated['items'] as $row) {
                PortalExpedienteCompartido::create([
                    'paciente_id' => $paciente->id,
                    'clinica_id' => $clinicaId,
                    'tipo_exp' => (int) $row['tipo_exp'],
                    'expediente_id' => (int) $row['expediente_id'],
                    'tipo_nombre_snapshot' => $row['tipo_nombre'] ?? null,
                    'fecha_snapshot' => isset($row['fecha']) && $row['fecha'] !== '' ? $row['fecha'] : null,
                ]);
            }
        });

        return response()->json(['message' => 'Documentos visibles en el portal actualizados']);
    }

    /**
     * Genera un número de expediente secuencial para una clínica específica.
     * Formato: número entero secuencial (1, 2, 3...) 
     * Es único por clínica en la tabla clinica_paciente.
     */
    private function generarNumeroExpedienteClinica(int $clinicaId): string
    {
        $ultimoExpediente = DB::table('clinica_paciente')
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('numero_expediente')
            ->where('numero_expediente', '!=', '')
            ->orderByRaw('CAST(numero_expediente AS UNSIGNED) DESC')
            ->value('numero_expediente');

        // Si hay expediente anterior numérico, incrementar
        if ($ultimoExpediente && is_numeric($ultimoExpediente)) {
            return (string) ((int) $ultimoExpediente + 1);
        }

        // Si no hay expedientes o el último no es numérico, buscar el máximo numérico
        $maxNumerico = DB::table('clinica_paciente')
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('numero_expediente')
            ->where('numero_expediente', '!=', '')
            ->whereRaw("numero_expediente REGEXP '^[0-9]+$'")
            ->max(DB::raw('CAST(numero_expediente AS UNSIGNED)'));

        return (string) (($maxNumerico ?? 0) + 1);
    }

    /**
     * Estado del portal del paciente: si ya tiene usuario y si ya configuró contraseña.
     */
    public function portalStatus(Paciente $paciente)
    {
        $user = Auth::user();
        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $portalUser = \App\Models\User::where('paciente_id', $paciente->id)
            ->where('rol', 'paciente')
            ->first();

        return response()->json([
            'tiene_email'          => (bool) filter_var($paciente->email, FILTER_VALIDATE_EMAIL),
            'email'                => $paciente->email,
            'tiene_cuenta'         => (bool) $portalUser,
            'acceso_configurado'   => $portalUser ? (bool) $portalUser->password_set_at : false,
            'invitacion_enviada_at' => $paciente->consentimiento_email_enviado_at,
        ]);
    }

    /**
     * Reenvía el correo de invitación/acceso al portal del paciente.
     */
    public function reenviarInvitacionPortal(Paciente $paciente)
    {
        $user = Auth::user();
        if (! $this->pacienteBelongsToWorkspace($paciente, $user)) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        if (! filter_var($paciente->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'El paciente no tiene un email válido registrado.'], 422);
        }

        $clinica = \App\Models\Clinica::find($user->clinica_efectiva_id);
        $servicio = app(PacienteConsentimientoService::class);
        $ok = $servicio->enviarInvitacion($paciente, $clinica, PacienteConsentimientoService::CONTEXTO_REGISTRO);

        if (! $ok) {
            return response()->json(['error' => 'No se pudo enviar el correo. Revisa la configuración de correo.'], 500);
        }

        return response()->json(['message' => 'Invitación enviada correctamente.']);
    }
}

