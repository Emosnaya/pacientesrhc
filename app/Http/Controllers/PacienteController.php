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
        return new PacienteCollection(Paciente::where('user_id',Auth::user()->id)->get());
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
        $peso = $request->peso;
        $talla = $request->talla;
        $imc = ($peso)/($talla*$talla);
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
        $paciente->apellidoMat = $request->apellidoMat;
        $paciente->telefono = $request->telefono;
        $paciente->domicilio = $request->domicilio;
        $paciente->profesion = $request->profesion;
        $paciente->cintura = $request->cintura;
        $paciente->estadoCivil = $request->estadoCivil;
        $paciente->diagnostico = $request->diagnostico;
        $paciente->medicamentos = $request->medicamentos;
        $paciente->envio = $request->envio;
        $paciente->talla = $request->talla;
        $paciente->peso = $request->peso;
        $paciente->fechaNacimiento = $fechaNacimiento;
        $paciente->edad = $edad;
        $paciente->imc = $imc;
        $paciente->email = $request->email;

        $paciente->user_id = Auth::user()->id;

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
        $pacienteRespose = Paciente::find($paciente->id);

        if($pacienteRespose->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$pacienteRespose) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($pacienteRespose);
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
    // Verificar permisos primero
    if($paciente->user_id != Auth::user()->id){
        return response()->json('Error de permisos', 403);
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
        'genero' => $request->genero == 1 ? 1 : 0 // Simplificada la asignación de género
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
        if($paciente->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $paciente->delete();
        return response()->json('',204);

    }
}
