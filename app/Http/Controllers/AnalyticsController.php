<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', 30); // Por defecto 30 días
            
            $startDate = Carbon::now()->subDays($period)->format('Y-m-d');
            $endDate = Carbon::now()->addDay()->format('Y-m-d');

            // Verificar permisos - solo admins pueden ver todas las analíticas
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver las analíticas'
                ], 403);
            }

            // Métricas básicas
            $totalCitas = Cita::whereBetween('fecha', [$startDate, $endDate])->count();
            $totalPacientes = Paciente::count();
            $citasConfirmadas = Cita::where('estado', 'confirmada')->whereBetween('fecha', [$startDate, $endDate])->count();
            $citasPendientes = Cita::where('estado', 'pendiente')->whereBetween('fecha', [$startDate, $endDate])->count();
            $citasCanceladas = Cita::where('estado', 'cancelada')->whereBetween('fecha', [$startDate, $endDate])->count();
            $citasCompletadas = Cita::where('estado', 'completada')->whereBetween('fecha', [$startDate, $endDate])->count();

            // Citas por estado
            $citasPorEstado = [
                $citasPendientes,
                $citasConfirmadas,
                $citasCompletadas,
                $citasCanceladas
            ];

            // Citas por día (últimos 7 días)
            $citasPorDia = $this->getCitasPorDia($startDate, $endDate);

            // Pacientes por edad
            $pacientesPorEdad = $this->getPacientesPorEdad();

            // Estadísticas adicionales
            $tasaConfirmacion = $totalCitas > 0 ? round(($citasConfirmadas / $totalCitas) * 100, 1) : 0;
            $citasPrimeraVez = Cita::where('primera_vez', true)->whereBetween('fecha', [$startDate, $endDate])->count();
            $promedioCitasDia = $period > 0 ? round($totalCitas / $period, 1) : 0;

            $analytics = [
                'total_citas' => $totalCitas,
                'total_pacientes' => $totalPacientes,
                'citas_confirmadas' => $citasConfirmadas,
                'citas_pendientes' => $citasPendientes,
                'citas_canceladas' => $citasCanceladas,
                'citas_completadas' => $citasCompletadas,
                'citas_por_estado' => $citasPorEstado,
                'citas_por_dia' => $citasPorDia,
                'citas_por_mes' => ['labels' => [], 'data' => []],
                'pacientes_por_edad' => $pacientesPorEdad,
                'tasa_confirmacion' => $tasaConfirmacion,
                'citas_primera_vez' => $citasPrimeraVez,
                'promedio_citas_dia' => $promedioCitasDia,
                'dia_mas_activo' => 'N/A',
                'hora_pico' => 'N/A',
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las analíticas: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getTotalCitas($startDate, $endDate)
    {
        return Cita::whereBetween('fecha', [$startDate, $endDate])->count();
    }

    private function getTotalPacientes()
    {
        return Paciente::count();
    }

    private function getCitasByStatus($status, $startDate, $endDate)
    {
        return Cita::where('estado', $status)
                   ->whereBetween('fecha', [$startDate, $endDate])
                   ->count();
    }

    private function getCitasPorEstado($startDate, $endDate)
    {
        $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
        $data = [];

        foreach ($estados as $estado) {
            $data[] = Cita::where('estado', $estado)
                          ->whereBetween('fecha', [$startDate, $endDate])
                          ->count();
        }

        return $data;
    }

    private function getCitasPorDia($startDate, $endDate)
    {
        $citas = Cita::select(
                DB::raw('DATE(fecha) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $labels = [];
        $data = [];

        // Crear array con todos los días del período
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $fechaStr = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');
            $citaDelDia = $citas->where('fecha', $fechaStr)->first();
            $data[] = $citaDelDia ? $citaDelDia->total : 0;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getCitasPorMes($startDate, $endDate)
    {
        $citas = Cita::select(
                DB::raw('YEAR(fecha) as año'),
                DB::raw('MONTH(fecha) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();

        $labels = [];
        $data = [];

        foreach ($citas as $cita) {
            $fecha = Carbon::create($cita->año, $cita->mes, 1);
            $labels[] = $fecha->format('M Y');
            $data[] = $cita->total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getPacientesPorEdad()
    {
        $rangos = [
            [0, 18],
            [19, 30],
            [31, 50],
            [51, 70],
            [71, 120]
        ];

        $data = [];

        foreach ($rangos as $rango) {
            $count = Paciente::whereBetween('edad', $rango)->count();
            $data[] = $count;
        }

        return $data;
    }

    private function getTasaConfirmacion($startDate, $endDate)
    {
        $totalCitas = $this->getTotalCitas($startDate, $endDate);
        
        if ($totalCitas == 0) {
            return 0;
        }

        $citasConfirmadas = $this->getCitasByStatus('confirmada', $startDate, $endDate);
        
        return round(($citasConfirmadas / $totalCitas) * 100, 1);
    }

    private function getCitasPrimeraVez($startDate, $endDate)
    {
        return Cita::where('primera_vez', true)
                   ->whereBetween('fecha', [$startDate, $endDate])
                   ->count();
    }

    private function getPromedioCitasDia($startDate, $endDate)
    {
        $totalCitas = $this->getTotalCitas($startDate, $endDate);
        $dias = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        
        return $dias > 0 ? round($totalCitas / $dias, 1) : 0;
    }

    private function getDiaMasActivo($startDate, $endDate)
    {
        $diaMasActivo = Cita::select(
                DB::raw('DAYNAME(fecha) as dia_semana'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('dia_semana')
            ->orderBy('total', 'desc')
            ->first();

        if ($diaMasActivo) {
            $dias = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];
            
            return $dias[$diaMasActivo->dia_semana] ?? $diaMasActivo->dia_semana;
        }

        return 'N/A';
    }

    private function getHoraPico($startDate, $endDate)
    {
        $horaPico = Cita::select(
                DB::raw('HOUR(hora) as hora'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('hora')
            ->orderBy('total', 'desc')
            ->first();

        if ($horaPico) {
            return sprintf('%02d:00', $horaPico->hora);
        }

        return 'N/A';
    }
}
