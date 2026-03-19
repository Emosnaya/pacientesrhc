<?php

namespace App\Http\Controllers;

use App\Http\Resources\PacienteCollection;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     * Todos los usuarios de la misma clínica pueden ver los pacientes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user = Auth::user();
        
        // Obtener pacientes de la misma clínica
        $query = Paciente::whereHas('user', function($q) use ($user) {
            $q->where('clinica_id', $user->clinica_id);
        });
        
        // Priorizar sucursal_id del request (para super admins cambiando de sucursal)
        // Si no viene en el request, usar la del usuario
        $sucursalId = $request->has('sucursal_id') ? $request->sucursal_id : $user->sucursal_id;
        
        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }
        
        $pacientes = $query->get();
        
        return new PacienteCollection($pacientes);
    }

    /**
     * Store a newly created resource in storage.
     * - Si es admin: se asigna a sí mismo
     * - Si no es admin: debe enviar user_id del doctor a asignar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        
        // Determinar sucursal_id
        if ($user->isSuperAdmin && $request->has('sucursal_id') && $request->sucursal_id) {
            // SuperAdmin puede asignar a cualquier sucursal de su clínica
            $sucursal = \App\Models\Sucursal::where('id', $request->sucursal_id)
                ->where('clinica_id', $user->clinica_id)
                ->first();
            
            if (!$sucursal) {
                return response()->json(['error' => 'La sucursal seleccionada no es válida'], 400);
            }
            
            $sucursalId = $sucursal->id;
        } else {
            // Usuarios normales y cuando no viene sucursal_id
            $sucursalId = $user->sucursal_id;
        }

        // Calcular el siguiente registro según la clínica
        if ($user->clinica_id == 1) {
            // Clínica original: mantener comportamiento actual (recibe registro del request)
            $paciente->registro = $request->registro;
        } else {
            // Otras clínicas: registro secuencial por sucursal
            if (!$request->registro) {
                $ultimoRegistro = Paciente::where('clinica_id', $user->clinica_id)
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
        // Si envían user_id específico, validar que sea de la misma clínica
        if ($request->has('user_id') && $request->user_id) {
            $doctor = \App\Models\User::where('id', $request->user_id)
                ->where('clinica_id', $user->clinica_id)
                ->first();
            
            if (!$doctor) {
                return response()->json(['error' => 'El doctor seleccionado no es válido'], 400);
            }
            
            $paciente->user_id = $doctor->id;
        } else {
            // Si no envían user_id, asignar al usuario actual
            $paciente->user_id = $user->id;
        }

        $paciente->clinica_id = $user->clinica_id;
        $paciente->sucursal_id = $sucursalId;

        $paciente->save();

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
        
        // Verificar que el paciente pertenece a la misma clínica
        $pacienteOwner = $paciente->user;
        if (!$pacienteOwner || $pacienteOwner->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos para ver este paciente'], 403);
        }

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
        
        // Verificar que el paciente pertenece a la misma clínica
        $pacienteOwner = $paciente->user;
        if (!$pacienteOwner || $pacienteOwner->clinica_id !== $user->clinica_id) {
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
        
        // Verificar que el paciente pertenece a la misma clínica
        $pacienteOwner = $paciente->user;
        if (!$pacienteOwner || $pacienteOwner->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este paciente'], 403);
        }
        
        // Solo admin o dueño puede eliminar
        if (!$user->isAdmin() && $paciente->user_id !== $user->id) {
            return response()->json(['error' => 'Solo el dueño del paciente o un administrador puede eliminarlo'], 403);
        }
        
        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado exitosamente'], 204);
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
        
        // Validar que sea una clínica dental
        $clinica = $user->clinica;
        if ($clinica->tipo_clinica !== 'dental') {
            return response()->json([
                'message' => 'El flujo Express solo está disponible para clínicas dentales'
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'motivo_consulta' => 'required|string|max:500',
            'tipo_paciente' => 'sometimes|string|in:cardiaca,pulmonar,dental,fisioterapia,nutricion,psicologia',
        ]);
        
        // Determinar sucursal_id
        $sucursalId = $request->has('sucursal_id') && $user->isSuperAdmin 
            ? $request->sucursal_id 
            : $user->sucursal_id;
        
        // Generar registro secuencial por sucursal
        $ultimoRegistro = Paciente::where('clinica_id', $user->clinica_id)
            ->where('sucursal_id', $sucursalId)
            ->max('registro');
        $nuevoRegistro = $ultimoRegistro ? ((int)$ultimoRegistro + 1) : 1;
        
        // Crear paciente express (datos mínimos + aceptación de aviso de privacidad)
        $paciente = Paciente::create([
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
            'clinica_id' => $user->clinica_id,
            'sucursal_id' => $sucursalId,
            // Cumplimiento LFPDPPP: Aviso de privacidad aceptado implícitamente en urgencias
            'aviso_privacidad_aceptado_at' => now(),
            'version_aviso' => '1.0-EXPRESS',
        ]);

        // Crear cita automática marcada como completada (ya está en consulta)
        $cita = \App\Models\Cita::create([
            'paciente_id' => $paciente->id,
            'admin_id' => $user->id,
            'user_id' => $user->id,
            'clinica_id' => $user->clinica_id,
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
}

