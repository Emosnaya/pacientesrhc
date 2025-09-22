<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicoCollection;
use App\Http\Resources\EsfuerzoCollection;
use App\Http\Resources\EstratificacionCollection;
use App\Http\Resources\ReporteFinalCollection;
use App\Http\Resources\ReporteNutriCollection;
use App\Http\Resources\ReportePsicoCollection;
use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfoController extends Controller
{
    public function clinicos($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, mostrar todos los reportes
        if ($user->isAdmin()) {
            return new ClinicoCollection(Clinico::where('paciente_id', $id)->get());
        }

        // Si es admin del paciente, mostrar todos los reportes
        if ($paciente->user_id == $user->id) {
            return new ClinicoCollection(Clinico::where('paciente_id', $id)->get());
        }

        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', Clinico::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();

        $reportes = Clinico::where('paciente_id', $id)
            ->whereIn('id', $accessibleReportIds)
            ->get();

        return new ClinicoCollection($reportes);
    }

    public function esfuerzos($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, mostrar todos los reportes
        if ($user->isAdmin()) {
            return new EsfuerzoCollection(Esfuerzo::where('paciente_id', $id)->get());
        }

        // Si es admin del paciente, mostrar todos los reportes
        if ($paciente->user_id == $user->id) {
            return new EsfuerzoCollection(Esfuerzo::where('paciente_id', $id)->get());
        }

        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', Esfuerzo::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();

        $reportes = Esfuerzo::where('paciente_id', $id)
            ->whereIn('id', $accessibleReportIds)
            ->get();

        return new EsfuerzoCollection($reportes);
    }

    public function estratificaciones($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, mostrar todos los reportes
        if ($user->isAdmin()) {
            return new EstratificacionCollection(Estratificacion::where('paciente_id', $id)->get());
        }

        // Si es admin del paciente, mostrar todos los reportes
        if ($paciente->user_id == $user->id) {
            return new EstratificacionCollection(Estratificacion::where('paciente_id', $id)->get());
        }

        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', Estratificacion::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();

        $reportes = Estratificacion::where('paciente_id', $id)
            ->whereIn('id', $accessibleReportIds)
            ->get();

        return new EstratificacionCollection($reportes);
    }

    public function reportes($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, mostrar todos los reportes
        if ($user->isAdmin()) {
            return new ReporteFinalCollection(ReporteFinal::where('paciente_id', $id)->get());
        }

        // Si es admin del paciente, mostrar todos los reportes
        if ($paciente->user_id == $user->id) {
            return new ReporteFinalCollection(ReporteFinal::where('paciente_id', $id)->get());
        }

        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', ReporteFinal::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();

        $reportes = ReporteFinal::where('paciente_id', $id)
            ->whereIn('id', $accessibleReportIds)
            ->get();

        return new ReporteFinalCollection($reportes);
    }

    public function nutricional($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, mostrar todos los reportes
        if ($user->isAdmin()) {
            return new ReporteNutriCollection(ReporteNutri::where('paciente_id', $id)->get());
        }

        // Si es admin del paciente, mostrar todos los reportes
        if ($paciente->user_id == $user->id) {
            return new ReporteNutriCollection(ReporteNutri::where('paciente_id', $id)->get());
        }

        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', ReporteNutri::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();

        $reportes = ReporteNutri::where('paciente_id', $id)
            ->whereIn('id', $accessibleReportIds)
            ->get();

        return new ReporteNutriCollection($reportes);
    }

    public function psicologico($id)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, mostrar todos los reportes
        if ($user->isAdmin()) {
            return new ReportePsicoCollection(ReportePsico::where('paciente_id', $id)->get());
        }

        // Si es admin del paciente, mostrar todos los reportes
        if ($paciente->user_id == $user->id) {
            return new ReportePsicoCollection(ReportePsico::where('paciente_id', $id)->get());
        }

        // Si no es admin, solo mostrar reportes específicos con permisos
        $accessibleReportIds = $user->permissions()
            ->where('permissionable_type', ReportePsico::class)
            ->where('can_read', true)
            ->pluck('permissionable_id')
            ->toArray();

        $reportes = ReportePsico::where('paciente_id', $id)
            ->whereIn('id', $accessibleReportIds)
            ->get();

        return new ReportePsicoCollection($reportes);
    }

    /**
     * Verificar permisos específicos de un expediente
     */
    public function checkExpedientePermissions($id, $expedienteType, $expedienteId)
    {
        $user = Auth::user();
        $paciente = Paciente::find($id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Verificar permisos básicos del paciente
        if (!$user->isAdmin() && !$user->hasPermissionOn($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los reportes de este paciente'], 403);
        }

        // Si es admin, tiene todos los permisos
        if ($user->isAdmin()) {
            return response()->json([
                'can_read' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_create' => true,
                'can_compare' => true
            ]);
        }

        // Si es admin del paciente, tiene todos los permisos
        if ($paciente->user_id == $user->id) {
            return response()->json([
                'can_read' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_create' => true,
                'can_compare' => true
            ]);
        }

        // Buscar el expediente específico
        $expediente = null;
        switch ($expedienteType) {
            case 'clinico':
                $expediente = Clinico::find($expedienteId);
                break;
            case 'esfuerzo':
                $expediente = Esfuerzo::find($expedienteId);
                break;
            case 'estratificacion':
                $expediente = Estratificacion::find($expedienteId);
                break;
            case 'reporte_final':
                $expediente = ReporteFinal::find($expedienteId);
                break;
            case 'reporte_nutri':
                $expediente = ReporteNutri::find($expedienteId);
                break;
            case 'reporte_psico':
                $expediente = ReportePsico::find($expedienteId);
                break;
            case 'reporte_fisio':
                $expediente = \App\Models\ReporteFisio::find($expedienteId);
                break;
        }

        if (!$expediente) {
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Verificar que el expediente pertenece al paciente
        if ($expediente->paciente_id != $id) {
            return response()->json(['error' => 'El expediente no pertenece a este paciente'], 403);
        }

        // Buscar permisos específicos del expediente
        $permission = $user->permissions()
            ->where('permissionable_type', get_class($expediente))
            ->where('permissionable_id', $expedienteId)
            ->first();

        if (!$permission) {
            return response()->json([
                'can_read' => false,
                'can_edit' => false,
                'can_delete' => false,
                'can_create' => false,
                'can_compare' => false
            ]);
        }

        return response()->json([
            'can_read' => $permission->can_read,
            'can_edit' => $permission->can_edit,
            'can_delete' => $permission->can_delete,
            'can_create' => $permission->can_edit, // Si puede editar, puede crear
            'can_compare' => $permission->can_edit // Si puede editar, puede comparar
        ]);
    }
    public function destroyPsico($id)
    {
        $reporte = ReportePsico::find($id);

        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function destroyNutri($id)
    {
        $reporte = ReporteNutri::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function nutriIndex($id)
    {
        $reporteNutriResponse = ReporteNutri::find($id);

        if($reporteNutriResponse->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$reporteNutriResponse) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($reporteNutriResponse);
    }
    public function psicoIndex($id)
    {
        $reportePsicoResponse = ReportePsico::find($id);

        if($reportePsicoResponse->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }

        // Verificar si se encontró el paciente
        if (!$reportePsicoResponse) {
            // Si no se encontró, devolver una respuesta de error
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Si se encontró el paciente, devolverlo como respuesta
        return response()->json($reportePsicoResponse);
    }
    public function destroyFinal($id)
    {
        $reporte = ReporteFinal::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function destroyEsfuer($id)
    {
        $reporte = Esfuerzo::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function destroyEstrati($id)
    {
        $reporte = Estratificacion::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
    public function destroyClinico($id)
    {
        $reporte = Clinico::find($id);
        
        if($reporte->user_id != Auth::user()->id){
            return response()->json('Error de permisos', 404);
        }
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
}
