<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorFirmaController extends Controller
{
    /**
     * Obtener lista de doctores con firma digital de la misma clínica
     * Para que los operadores/ayudantes puedan seleccionar quién firma
     */
    public function getDoctoresConFirma()
    {
        $user = Auth::user();
        
        $doctores = User::where('clinica_id', $user->clinica_id)
            ->whereNotNull('firma_digital')
            ->select('id', 'nombre', 'apellidoPat', 'apellidoMat', 'cedula', 'firma_digital')
            ->orderBy('nombre')
            ->get();
        
        return response()->json([
            'doctores' => $doctores,
            'usuario_actual' => [
                'id' => $user->id,
                'nombre_completo' => $user->nombre . ' ' . $user->apellidoPat . ' ' . $user->apellidoMat,
                'tiene_firma' => !empty($user->firma_digital),
                'rol' => $user->rol,
                'requiere_firma' => $user->isFirmante(),
                'es_administrativo' => $user->isAdministrativo(),
            ]
        ]);
    }
}
