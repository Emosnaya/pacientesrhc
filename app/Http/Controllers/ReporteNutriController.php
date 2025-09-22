<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReporteNutriCollection;
use App\Models\Paciente;
use App\Models\ReporteNutri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteNutriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // Los administradores solo ven sus propios reportes nutricionales
            $reportes = ReporteNutri::where('user_id', $user->id)->with('paciente')->get();
        } else {
            // Los usuarios no admin solo ven los reportes a los que tienen acceso
            $accessibleReportes = $user->getAccessibleResources('reporte_nutris');
            $reporteIds = $accessibleReportes->pluck('permissionable_id')->toArray();
            $reportes = ReporteNutri::whereIn('id', $reporteIds)->with('paciente')->get();
        }
        
        return new ReporteNutriCollection($reportes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $reporteNutri = new ReporteNutri();
        $data = $request->input('datos');
        $id = intval($request->input('id'));
        $paciente = Paciente::find($id);
        
        // Verificar permisos de escritura para usuarios no admin
        if (!$user->isAdmin()) {
            $permission = $user->permissions()->where('can_write', true)->first();
            if (!$permission) {
                return response()->json(['error' => 'No tienes permisos para crear reportes nutricionales'], 403);
            }
        }
        
        $reporteNutri->paciente_id = $paciente->id;

        $reporteNutri->sistolica = $data['sistolica'];
        $reporteNutri->diastolica = $data['diastolica'];
        $reporteNutri->trigliceridos = $data['trigliceridos'];
        $reporteNutri->hdl = $data['hdl'];
        $reporteNutri->ldl = $data['ldl'];
        $reporteNutri->colesterol = $data['colesterol'];
        $reporteNutri->aguaSimple = $data['aguaSimple'];
        $reporteNutri->cafe_te = $data['cafe_te'];
        $reporteNutri->bebidas = $data['bebidas'];
        $reporteNutri->light = $data['light'];
        $reporteNutri->comidas = $data['comidas'];
        $reporteNutri->actividad = $data['actividad'];
        $reporteNutri->actividadDias = $data['actividadDias'];
        $reporteNutri->minutosDia = $data['minutosDia'];
        $reporteNutri->formula = $data['formula'];
        $reporteNutri->lipidos = $data['lipidos']=== 'true' ? true : false;
        $reporteNutri->Controlglucosa = $data['Controlglucosa']=== 'true' ? true : false;
        $reporteNutri->controlPeso = $data['controlPeso']=== 'true' ? true : false;
        $reporteNutri->otro = $data['otro'];
        $reporteNutri->mmHg = null;
        $reporteNutri->estado = $data['estado'];
        $reporteNutri->glucosa = $data['glucosa'];
        $reporteNutri->recomendaciones = json_encode($data['recomendaciones']);
        $reporteNutri->diagnostico = $data['diagnostico'];
        $reporteNutri->recomendacion= $data['recomendacion'];
        $reporteNutri->observaciones= $data['observaciones'];
        $reporteNutri->controlPresion= $data['controlPresion'] === 'true' ? true : false;;
        $reporteNutri->tipo_exp = 6;
        // Asignar user_id según el tipo de usuario
        if (!$user->isAdmin()) {
            // Usar el user_id del admin que otorgó los permisos
            $reporteNutri->user_id = $permission->granted_by;
        } else {
            // Si es admin, usar su propio user_id
            $reporteNutri->user_id = $user->id;
        }
        $reporteNutri->nutriologo = $data['nutriologo'];
        $reporteNutri->cedula_nutriologo = $data['cedula_nutriologo'];
        $reporteNutri->save();





    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function show(ReporteNutri $reporteNutri)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden ver sus propios reportes nutricionales
        if ($user->isAdmin() && $reporteNutri->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para ver este reporte nutricional'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($reporteNutri, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver este reporte nutricional'], 403);
        }

        // Verificar si se encontró el reporte
        if (!$reporteNutri) {
            return response()->json(['error' => 'Reporte nutricional no encontrado'], 404);
        }

        return response()->json($reporteNutri->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function update(Request $data, ReporteNutri $reporte)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden editar sus propios reportes nutricionales
        if ($user->isAdmin() && $reporte->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para editar este reporte nutricional'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($reporte, 'can_edit')) {
            return response()->json(['error' => 'No tienes permisos para editar este reporte nutricional'], 403);
        }
        $reporteNutri = ReporteNutri::find($data->id);
        $reporteNutri->sistolica = $data['sistolica'];
        $reporteNutri->diastolica = $data['diastolica'];
        $reporteNutri->trigliceridos = $data['trigliceridos'];
        $reporteNutri->hdl = $data['hdl'];
        $reporteNutri->ldl = $data['ldl'];
        $reporteNutri->colesterol = $data['colesterol'];
        $reporteNutri->aguaSimple = $data['aguaSimple'];
        $reporteNutri->cafe_te = $data['cafe_te'];
        $reporteNutri->bebidas = $data['bebidas'];
        $reporteNutri->light = $data['light'];
        $reporteNutri->comidas = $data['comidas'];
        $reporteNutri->actividad = $data['actividad'];
        $reporteNutri->actividadDias = $data['actividadDias'];
        $reporteNutri->minutosDia = $data['minutosDia'];
        $reporteNutri->formula = $data['formula'];
        $reporteNutri->lipidos = $data['lipidos']=== 'true' || $data['lipidos']=== "1"  ? true : false;
        $reporteNutri->Controlglucosa = $data['Controlglucosa']=== 'true'  || $data['Controlglucosa']=== "1" ? true : false;
        $reporteNutri->controlPeso = $data['controlPeso']=== 'true'  || $data['controlPeso']=== "1" ? true : false;
        $reporteNutri->otro = $data['otro'];
        $reporteNutri->mmHg = null;
        $reporteNutri->estado = $data['estado'];
        $reporteNutri->glucosa = $data['glucosa'];
        $reporteNutri->recomendaciones = json_encode($data['recomendaciones']);
        $reporteNutri->diagnostico = $data['diagnostico'];
        $reporteNutri->recomendacion= $data['recomendacion'];
        $reporteNutri->observaciones= $data['observaciones'];
        $reporteNutri->controlPresion= $data['controlPresion'] === 'true'  || $data['controlPresion']=== "1" ? true : false;
        $reporteNutri->tipo_exp = 6;
        $reporteNutri->nutriologo = $data['nutriologo'];
        $reporteNutri->cedula_nutriologo = $data['cedula_nutriologo'];
        $reporteNutri->save();

        return response('Actualizado',204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteNutri  $reporteNutri
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReporteNutri $reporteNutri)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden eliminar sus propios reportes nutricionales
        if ($user->isAdmin() && $reporteNutri->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este reporte nutricional'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($reporteNutri, 'can_delete')) {
            return response()->json(['error' => 'No tienes permisos para eliminar este reporte nutricional'], 403);
        }
        
        $reporteNutri->delete();
        return response()->json(['message' => 'Reporte nutricional eliminado exitosamente'], 204);
    }
}
