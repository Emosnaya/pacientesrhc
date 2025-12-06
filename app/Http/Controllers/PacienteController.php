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
    public function index(){
        $user = Auth::user();
        
        // Obtener todos los pacientes de la misma clínica
        $pacientes = Paciente::whereHas('user', function($query) use ($user) {
            $query->where('clinica_id', $user->clinica_id);
        })->get();
        
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

        $paciente->registro = $request->registro;
        $paciente->nombre = $request->nombre;
        $paciente->apellidoPat = $request->apellidoPat;
        $paciente->apellidoMat = $request->apellidoMat ?? null;
        $paciente->telefono = $request->telefono;
        $paciente->domicilio = $request->domicilio ?? null;
        $paciente->profesion = $request->profesion ?? null;
        $paciente->cintura = $request->cintura ?? 0;
        $paciente->estadoCivil = $request->estadoCivil ?? null;
        $paciente->diagnostico = $request->diagnostico ?? null;
        $paciente->medicamentos = $request->medicamentos ?? null;
        $paciente->envio = $request->envio;
        $paciente->talla = $request->talla;
        $paciente->peso = $request->peso;
        $paciente->fechaNacimiento = $fechaNacimiento;
        $paciente->edad = $edad;
        $paciente->imc = $imc;
        $paciente->email = $request->email;
        $paciente->tipo_paciente = $request->tipo_paciente ?? 'cardiaca';

        $user = Auth::user();
        
        // Determinar el dueño del paciente
        if ($user->isAdmin()) {
            // Si es admin, el paciente es suyo
            $paciente->user_id = $user->id;
        } else {
            // Si no es admin, debe proporcionar el user_id del doctor
            if ($request->has('user_id') && $request->user_id) {
                // Verificar que el doctor pertenece a la misma clínica
                $doctor = \App\Models\User::where('id', $request->user_id)
                    ->where('clinica_id', $user->clinica_id)
                    ->first();
                
                if (!$doctor) {
                    return response()->json(['error' => 'El doctor seleccionado no es válido'], 400);
                }
                
                $paciente->user_id = $doctor->id;
            } else {
                return response()->json(['error' => 'Debe seleccionar un doctor para asignar el paciente'], 400);
            }
        }

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
            'profesion' => $request->profesion,
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
            'genero' => ($request->genero == 1 || $request->genero === '1') ? 1 : 0,
            'tipo_paciente' => $request->tipo_paciente ?? $paciente->tipo_paciente
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
}
