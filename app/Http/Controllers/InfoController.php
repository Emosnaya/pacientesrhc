<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicoCollection;
use App\Http\Resources\EsfuerzoCollection;
use App\Http\Resources\EstratificacionCollection;
use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function clinicos($id)
    {
        return new ClinicoCollection(Clinico::where('paciente_id', $id)->get());
    }

    public function esfuerzos($id)
    {
        return new EsfuerzoCollection(Esfuerzo::where('paciente_id', $id)->get());
    }

    public function estratificaciones($id)
    {
        return new EstratificacionCollection(Estratificacion::where('paciente_id', $id)->get());
    }
}
