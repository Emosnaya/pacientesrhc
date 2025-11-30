<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Clinico;
use App\Models\ReporteFinal;
use App\Models\ReporteNutri;
use App\Models\ReportePsico;
use App\Models\ReporteFisio;
use App\Models\ExpedientePulmonar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpedienteUnificadoController extends Controller
{
    /**
     * Obtener todos los expedientes de un paciente respetando permisos
     */
    public function getExpedientesByPaciente($pacienteId)
    {
        $user = Auth::user();
        $paciente = Paciente::findOrFail($pacienteId);

        // Verificar permisos sobre el paciente
        if (!$user->canAccessPaciente($paciente, 'can_read')) {
            return response()->json(['error' => 'No tienes permisos para ver los expedientes de este paciente'], 403);
        }

        $expedientes = collect();

        // 1. Esfuerzos
        $esfuerzos = Esfuerzo::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    // Verificar permisos específicos sobre esfuerzos
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 1,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Prueba de Esfuerzo'
                ];
            });

        // 2. Estratificaciones
        $estratificaciones = Estratificacion::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 2,
                    'fecha' => $item->estrati_fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Estratificación'
                ];
            });

        // 3. Clínicos
        $clinicos = Clinico::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 3,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Expediente Clínico'
                ];
            });

        // 4. Reportes Finales
        $reportes = ReporteFinal::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 4,
                    'fecha' => $item->created_at->format('Y-m-d'),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Reporte Final'
                ];
            });

        // 5. Reportes Nutricionales
        $nutricionales = ReporteNutri::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 6,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota Nutricional'
                ];
            });

        // 6. Reportes Psicológicos
        $psicologicos = ReportePsico::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 5,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota Psicológica'
                ];
            });

        // 7. Reportes Fisiológicos
        $fisiologicos = ReporteFisio::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 7,
                    'fecha' => $item->fecha,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Nota Fisiológica'
                ];
            });

        // 8. Expedientes Pulmonares
        $pulmonares = ExpedientePulmonar::where('paciente_id', $pacienteId)
            ->where(function($query) use ($user) {
                if ($user->isAdmin()) {
                    $query->whereHas('paciente', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereHas('permissions', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('can_read', true);
                    });
                }
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tipo_exp' => 8,
                    'fecha' => $item->fecha_consulta,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'tipo_nombre' => 'Expediente Pulmonar'
                ];
            });

        // Combinar todos los expedientes
        $expedientes = $expedientes
            ->merge($esfuerzos)
            ->merge($estratificaciones)
            ->merge($clinicos)
            ->merge($reportes)
            ->merge($nutricionales)
            ->merge($psicologicos)
            ->merge($fisiologicos)
            ->merge($pulmonares);

        // Ordenar por fecha de creación (más recientes primero)
        $expedientes = $expedientes->sortByDesc('created_at')->values();

        return response()->json([
            'data' => $expedientes,
            'total' => $expedientes->count()
        ]);
    }
}