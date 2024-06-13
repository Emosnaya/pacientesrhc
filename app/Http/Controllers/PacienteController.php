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

        if ($genero === 'masculino'){
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
        $paciente->talla = $request->talla;
        $paciente->peso = $request->peso;
        $paciente->fechaNacimiento = $fechaNacimiento;
        $paciente->edad = $edad;
        $paciente->imc = $imc;

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
        $pacientefind = Paciente::find($request->id);

        $peso = $request->peso;
        $talla = $request->talla;
        $imc = ($peso)/($talla*$talla);
        $fechaNacimiento = $request->fechaNacimiento;
        $edad = Carbon::parse($fechaNacimiento)->age;
        $genero = $request->genero;

        if ($genero === 'masculino'){
            $paciente->genero = 1;
        }else{
            $paciente->genero = 0;
        }

        $pacientefind->registro = $request->registro;
        $pacientefind->nombre = $request->nombre;
        $pacientefind->apellidoPat = $request->apellidoPat;
        $pacientefind->apellidoMat = $request->apellidoMat;
        $pacientefind->telefono = $request->telefono;
        $pacientefind->domicilio = $request->domicilio;
        $pacientefind->profesion = $request->profesion;
        $pacientefind->cintura = $request->cintura;
        $pacientefind->estadoCivil = $request->estadoCivil;
        $pacientefind->talla = $request->talla;
        $pacientefind->peso = $request->peso;
        $pacientefind->fechaNacimiento = $fechaNacimiento;
        $pacientefind->edad = $edad;
        $pacientefind->imc = $imc;

        $pacientefind->save();

        // Devolver una respuesta de éxito con el paciente actualizado
        return response()->json('', 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();
        return response()->json('',204);

    }
}
