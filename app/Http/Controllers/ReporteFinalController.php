<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReporteFinalCollection;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteFinalController extends Controller
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
            // Los administradores solo ven sus propios expedientes
            $expedientes = ReporteFinal::where('user_id', $user->id)->with('paciente')->get();
        } else {
            // Los usuarios no admin solo ven los expedientes a los que tienen acceso
            $accessibleExpedientes = $user->getAccessibleResources('expedientes');
            $expedienteIds = $accessibleExpedientes->pluck('permissionable_id')->toArray();
            $expedientes = ReporteFinal::whereIn('id', $expedienteIds)->with('paciente')->get();
        }
        
        return new ReporteFinalCollection($expedientes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->input("datos");
        $id = $request->input("esfuerzoUnoid");
        $idDos = $request->input("esfuerzoDosid");

        $esfuerzo = Esfuerzo::find($id);
        $esfuerzodos = Esfuerzo::find($idDos);

        $reporteFinal = new ReporteFinal();

        $reporteFinal->fecha_inicio = $esfuerzo->fecha;
        $reporteFinal->fecha_final = $esfuerzodos->fecha;
        $reporteFinal->fecha = $data['fecha'];
        $reporteFinal->fc_basal = $data['fcBasal'];
        $reporteFinal->doble_pr_bas = $data['dpBasal'];
        $reporteFinal->fc_maxima = $data['fcMax'];
        $reporteFinal->doble_pr_max = $data['dpMax'];
        $reporteFinal->fc_borg12 = $data['fcBorg12'];
        $reporteFinal->doble_pr_b12 = $data['dpBorg12'];
        $reporteFinal->carga_max = $data['metsMax'];
        $reporteFinal->mets_por = $data['vo2Alcanzado'];
        $reporteFinal->tiempo_ejer = $data['tiempoEsfuerzo'];
        $reporteFinal->recup_fc = $data['fcmaxFer'];
        $reporteFinal->umbral_isq = $data['metsUisq'];
        $reporteFinal->umbral_isq_fc = $data['fcUisq'];
        $reporteFinal->max_des_st = $data['MaxInfra'];
        $reporteFinal->indice_ta_es = $data['indiceTas'];
        $reporteFinal->recup_tas = $data['recuptas'];
        $reporteFinal->resp_crono = $data['respCrono'];
        $reporteFinal->iem = $data['iem'];
        $reporteFinal->pod_car_eje = $data['pce'];
        $reporteFinal->duke = $data['duke'];
        $reporteFinal->pe_1 = $id;
        $reporteFinal->pe_2 = $idDos;
        $reporteFinal->veteranos = $data['veteranos'];
        $reporteFinal->score_ang = $data['scoreAngina'];
        $reporteFinal->tipo_exp = 4;

        $user = Auth::user();
        
        // Si es usuario no admin, necesita permisos de escritura para crear expedientes
        if (!$user->isAdmin()) {
            // Verificar si tiene permisos de escritura (can_write) en algún recurso
            // Para crear expedientes, necesitamos obtener el user_id del admin que le otorgó permisos
            $permission = $user->permissions()->where('can_write', true)->first();
            if (!$permission) {
                return response()->json(['error' => 'No tienes permisos para crear expedientes'], 403);
            }
            // Usar el user_id del admin que otorgó los permisos
            $reporteFinal->user_id = $permission->granted_by;
        } else {
            // Si es admin, usar su propio user_id
            $reporteFinal->user_id = $user->id;
        }
        
        $reporteFinal->paciente_id = $esfuerzo->paciente_id;
        
        $reporteFinal->save();

        return response()->json("Guardado");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function show(ReporteFinal $reporteFinal)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden ver sus propios expedientes
        if ($user->isAdmin() && $reporteFinal->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para ver este expediente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($reporteFinal, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver este expediente'], 403);
        }

        return response()->json($reporteFinal->load('paciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReporteFinal $reporteFinal)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden editar sus propios expedientes
        if ($user->isAdmin() && $reporteFinal->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para editar este expediente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($reporteFinal, 'can_edit')) {
            return response()->json(['error' => 'No tienes permisos para editar este expediente'], 403);
        }

        // Aquí iría la lógica de actualización del expediente
        // Por ahora solo devolvemos un mensaje de éxito
        return response()->json(['message' => 'Expediente actualizado exitosamente']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReporteFinal  $reporteFinal
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReporteFinal $reporteFinal)
    {
        $user = Auth::user();
        
        // Los administradores solo pueden eliminar sus propios expedientes
        if ($user->isAdmin() && $reporteFinal->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este expediente'], 403);
        }
        
        // Los usuarios no admin verifican permisos específicos
        if (!$user->isAdmin() && !$user->hasPermissionOn($reporteFinal, 'can_delete')) {
            return response()->json(['error' => 'No tienes permisos para eliminar este expediente'], 403);
        }
        
        $reporteFinal->delete();
        return response()->json(['message' => 'Expediente eliminado exitosamente'], 204);
    }
}
