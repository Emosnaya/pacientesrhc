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
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = Auth::user();
        
        // Usar el nuevo método que incluye pacientes asociados a reportes con permisos
        $pacientes = $user->getAccessiblePacientes();
        
        return new PacienteCollection($pacientes);
    }

    /**
     * Store a newly created resource in storage.
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
        $edad = Carbon::parse($fechaNacimiento)->age;
        $genero = $request->genero;

        if ($genero === 1){
            $paciente->genero = 1;
        }else{
            $paciente->genero = 0;
        }



        $paciente->registro = $request->registro;
        $paciente->nombre = $request->nombre;
        $paciente->apellidoPat = $request->apellidoPat;
        $paciente->apellidoMat = $request->apellidoMat ?? null;
        $paciente->telefono = $request->telefono;
        $paciente->domicilio = $request->domicilio?? null;
        $paciente->profesion = $request->profesion?? null;
        $paciente->cintura = $request->cintura?? 0;
        $paciente->estadoCivil = $request->estadoCivil?? null;
        $paciente->diagnostico = $request->diagnostico?? null;
        $paciente->medicamentos = $request->medicamentos?? null;
        $paciente->envio = $request->envio;
        $paciente->talla = $request->talla;
        $paciente->peso = $request->peso;
        $paciente->fechaNacimiento = $fechaNacimiento;
        $paciente->edad = $edad;
        $paciente->imc = $imc;
        $paciente->email = $request->email;
        $paciente->tipo_paciente = $request->tipo_paciente ?? 'cardiaca'; // Valor por defecto

        $user = Auth::user();
        
        // Si es usuario no admin, necesita permisos de escritura para crear pacientes
        if (!$user->isAdmin()) {
            // Verificar si tiene permisos de escritura (can_write) en algún recurso
            // Para crear pacientes, necesitamos obtener el user_id del admin que le otorgó permisos
            $permission = $user->permissions()->where('can_write', true)->first();
            if (!$permission) {
                return response()->json(['error' => 'No tienes permisos para crear pacientes'], 403);
            }
            // Usar el user_id del admin que otorgó los permisos
            $paciente->user_id = $permission->granted_by;
        } else {
            // Si es admin, usar su propio user_id
            $paciente->user_id = $user->id;
        }

        $paciente->save();



        return [
            'mesage' => 'Paciente Guardado'
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function show(Paciente $paciente)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden ver sus propios pacientes
        if ($user->isAdmin() && $paciente->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para ver este paciente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver este paciente'], 403);
        }

        // Verificar si se encontró el paciente
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        return response()->json($paciente);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paciente $paciente)
{
    $user = Auth::user();
    
    // Los administradores solo pueden editar sus propios pacientes
    if ($user->isAdmin() && $paciente->user_id !== $user->id) {
        return response()->json(['error' => 'No tienes permisos para editar este paciente'], 403);
    }
    
    // Los usuarios no admin verifican permisos específicos
    if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_edit')) {
        return response()->json(['error' => 'No tienes permisos para editar este paciente'], 403);
    }

    // Calcular valores derivados
    $peso = $request->peso;
    $talla = $request->talla;
    $imc = ($peso)/($talla*$talla);
    $fechaNacimiento = $request->fechaNacimiento;
    $edad = Carbon::parse($fechaNacimiento)->age;

    // Actualizar todos los campos del paciente
    $paciente->update([
        'registro' => $request->registro,
        'nombre' => $request->nombre,
        'apellidoPat' => $request->apellidoPat,
        'apellidoMat' => $request->apellidoMat,
        'telefono' => $request->telefono,
        'domicilio' => $request->domicilio,
        'profesion' => $request->profesion, // Corregí "profesion" (antes tenía typo)
        'cintura' => $request->cintura,
        'estadoCivil' => $request->estadoCivil,
        'diagnostico' => $request->diagnostico,
        'medicamentos' => $request->medicamentos,
        'envio' => $request->envio,
        'talla' => $talla,
        'peso' => $peso,
        'fechaNacimiento' => $fechaNacimiento,
        'edad' => $edad,
        'imc' => $imc,
        'email' => $request->email,
        'genero' => $request->genero == 1 ? 1 : 0, // Simplificada la asignación de género
        'tipo_paciente' => $request->tipo_paciente ?? $paciente->tipo_paciente // Mantener valor actual si no se proporciona
    ]);

    return response()->json($paciente, 200);
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Paciente $paciente)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden eliminar sus propios pacientes
        if ($user->isAdmin() && $paciente->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este paciente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_delete')) {
            return response()->json(['error' => 'No tienes permisos para eliminar este paciente'], 403);
        }
        
        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado exitosamente'], 204);
    }
}
