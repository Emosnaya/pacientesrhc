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

            // Cualquier usuario puede ver las analíticas de su clínica
            // Filtrar todas las consultas por clínica del usuario
            $clinicaId = $user->clinica_id;

            // Métricas básicas - filtradas por clínica
            $totalCitas = Cita::where('clinica_id', $clinicaId)
                ->whereBetween('fecha', [$startDate, $endDate])->count();
            $totalPacientes = Paciente::where('clinica_id', $clinicaId)->count();
            $citasConfirmadas = Cita::where('clinica_id', $clinicaId)
                ->where('estado', 'confirmada')->whereBetween('fecha', [$startDate, $endDate])->count();
            $citasPendientes = Cita::where('clinica_id', $clinicaId)
                ->where('estado', 'pendiente')->whereBetween('fecha', [$startDate, $endDate])->count();
            $citasCanceladas = Cita::where('clinica_id', $clinicaId)
                ->where('estado', 'cancelada')->whereBetween('fecha', [$startDate, $endDate])->count();
            $citasCompletadas = Cita::where('clinica_id', $clinicaId)
                ->where('estado', 'completada')->whereBetween('fecha', [$startDate, $endDate])->count();

            // Citas por estado
            $citasPorEstado = [
                $citasPendientes,
                $citasConfirmadas,
                $citasCompletadas,
                $citasCanceladas
            ];

            // Citas por día (últimos 7 días)
            $citasPorDia = $this->getCitasPorDia($startDate, $endDate, $clinicaId);

            // Citas por semana
            $citasPorSemana = $this->getCitasPorSemana($startDate, $endDate, $clinicaId);

            // Citas por mes
            $citasPorMes = $this->getCitasPorMes($startDate, $endDate, $clinicaId);

            // Pacientes por edad
            $pacientesPorEdad = $this->getPacientesPorEdad($clinicaId);

            // Pacientes por género
            $pacientesPorGenero = $this->getPacientesPorGenero($clinicaId);

            // Pacientes pulmonares y ambos
            $pacientesPulmonares = $this->getPacientesPulmonares($clinicaId);

            // Estadísticas adicionales
            $tasaConfirmacion = $this->getTasaConfirmacion($startDate, $endDate, $clinicaId);
            $citasPrimeraVez = $this->getCitasPrimeraVez($startDate, $endDate, $clinicaId);
            $promedioCitasDia = $this->getPromedioCitasDia($startDate, $endDate, $clinicaId);
            $diaMasActivo = $this->getDiaMasActivo($startDate, $endDate, $clinicaId);
            $horaPico = $this->getHoraPico($startDate, $endDate, $clinicaId);

            $analytics = [
                'total_citas' => $totalCitas,
                'total_pacientes' => $totalPacientes,
                'citas_confirmadas' => $citasConfirmadas,
                'citas_pendientes' => $citasPendientes,
                'citas_canceladas' => $citasCanceladas,
                'citas_completadas' => $citasCompletadas,
                'citas_por_estado' => $citasPorEstado,
                'citas_por_dia' => $citasPorDia,
                'citas_por_semana' => $citasPorSemana,
                'citas_por_mes' => $citasPorMes,
                'pacientes_por_edad' => $pacientesPorEdad,
                'pacientes_por_genero' => $pacientesPorGenero,
                'pacientes_pulmonares' => $pacientesPulmonares,
                'tasa_confirmacion' => $tasaConfirmacion,
                'citas_primera_vez' => $citasPrimeraVez,
                'promedio_citas_dia' => $promedioCitasDia,
                'dia_mas_activo' => $diaMasActivo,
                'hora_pico' => $horaPico,
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

    private function getTotalCitas($startDate, $endDate, $clinicaId)
    {
        return Cita::where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$startDate, $endDate])->count();
    }

    private function getTotalPacientes($clinicaId)
    {
        return Paciente::where('clinica_id', $clinicaId)->count();
    }

    private function getCitasByStatus($status, $startDate, $endDate, $clinicaId)
    {
        return Cita::where('clinica_id', $clinicaId)
                   ->where('estado', $status)
                   ->whereBetween('fecha', [$startDate, $endDate])
                   ->count();
    }

    private function getCitasPorEstado($startDate, $endDate, $clinicaId)
    {
        $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
        $data = [];

        foreach ($estados as $estado) {
            $data[] = Cita::where('clinica_id', $clinicaId)
                          ->where('estado', $estado)
                          ->whereBetween('fecha', [$startDate, $endDate])
                          ->count();
        }

        return $data;
    }

    private function getCitasPorDia($startDate, $endDate, $clinicaId)
    {
        $citas = Cita::select(
                DB::raw('DATE(fecha) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Convertir a array asociativo para búsqueda rápida
        $citasMap = [];
        foreach ($citas as $cita) {
            $fecha = $cita->fecha;
            // Convertir fecha a string si es Carbon
            if ($fecha instanceof \Carbon\Carbon) {
                $fecha = $fecha->format('Y-m-d');
            } else {
                $fecha = (string) $fecha;
            }
            $total = is_object($cita->total) ? (int) (string) $cita->total : (int) $cita->total;
            $citasMap[$fecha] = $total;
        }

        $labels = [];
        $data = [];

        // Crear array con todos los días del período
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $fechaStr = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');
            $data[] = isset($citasMap[$fechaStr]) ? $citasMap[$fechaStr] : 0;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getCitasPorMes($startDate, $endDate, $clinicaId)
    {
        // Obtener todas las citas del año actual, no solo del período
        $añoActual = Carbon::now()->year;
        $inicioAño = Carbon::create($añoActual, 1, 1)->format('Y-m-d');
        $finAño = Carbon::create($añoActual, 12, 31)->format('Y-m-d');
        
        $citas = Cita::select(
                DB::raw('YEAR(fecha) as año'),
                DB::raw('MONTH(fecha) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$inicioAño, $finAño])
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();

        // Crear un mapa de citas por año-mes para búsqueda rápida
        $citasMap = [];
        foreach ($citas as $cita) {
            $año = is_object($cita->año) ? (int) (string) $cita->año : (int) $cita->año;
            $mes = is_object($cita->mes) ? (int) (string) $cita->mes : (int) $cita->mes;
            $total = is_object($cita->total) ? (int) (string) $cita->total : (int) $cita->total;
            $citasMap["{$año}-{$mes}"] = $total;
        }
        
        // Crear array con todos los meses del año actual
        $labels = [];
        $data = [];
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        for ($mes = 1; $mes <= 12; $mes++) {
            $key = "{$añoActual}-{$mes}";
            $labels[] = $meses[$mes] . ' ' . $añoActual;
            $data[] = isset($citasMap[$key]) ? $citasMap[$key] : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getCitasPorSemana($startDate, $endDate, $clinicaId)
    {
        $citas = Cita::select(
                DB::raw('YEAR(fecha) as año'),
                DB::raw('WEEK(fecha, 1) as semana'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('año', 'semana')
            ->orderBy('año')
            ->orderBy('semana')
            ->get();

        $labels = [];
        $data = [];

        foreach ($citas as $cita) {
            // Asegurar que los valores sean enteros
            $año = is_object($cita->año) ? (int) (string) $cita->año : (int) $cita->año;
            $semana = is_object($cita->semana) ? (int) (string) $cita->semana : (int) $cita->semana;
            $total = is_object($cita->total) ? (int) (string) $cita->total : (int) $cita->total;
            
            $labels[] = 'Sem ' . $semana . ' ' . $año;
            $data[] = $total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getPacientesPorGenero($clinicaId)
    {
        $masculino = Paciente::where('clinica_id', $clinicaId)->where('genero', 1)->count();
        $femenino = Paciente::where('clinica_id', $clinicaId)->where('genero', 0)->count();

        return [
            'labels' => ['Masculino', 'Femenino'],
            'data' => [$masculino, $femenino]
        ];
    }

    private function getPacientesPulmonares($clinicaId)
    {
        $cardiaca = Paciente::where('clinica_id', $clinicaId)->where('tipo_paciente', 'cardiaca')->count();
        $pulmonar = Paciente::where('clinica_id', $clinicaId)->where('tipo_paciente', 'pulmonar')->count();
        $ambos = Paciente::where('clinica_id', $clinicaId)->where('tipo_paciente', 'ambos')->count();

        return [
            'labels' => ['Cardíaca', 'Pulmonar', 'Ambos'],
            'data' => [$cardiaca, $pulmonar, $ambos]
        ];
    }

    private function getPacientesPorEdad($clinicaId)
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
            $count = Paciente::where('clinica_id', $clinicaId)
                ->whereBetween('edad', $rango)->count();
            $data[] = $count;
        }

        return $data;
    }

    private function getTasaConfirmacion($startDate, $endDate, $clinicaId)
    {
        $totalCitas = $this->getTotalCitas($startDate, $endDate, $clinicaId);
        
        if ($totalCitas == 0) {
            return 0;
        }

        $citasConfirmadas = $this->getCitasByStatus('confirmada', $startDate, $endDate, $clinicaId);
        
        return round(($citasConfirmadas / $totalCitas) * 100, 1);
    }

    private function getCitasPrimeraVez($startDate, $endDate, $clinicaId)
    {
        return Cita::where('clinica_id', $clinicaId)
                   ->where('primera_vez', true)
                   ->whereBetween('fecha', [$startDate, $endDate])
                   ->count();
    }

    private function getPromedioCitasDia($startDate, $endDate, $clinicaId)
    {
        $totalCitas = $this->getTotalCitas($startDate, $endDate, $clinicaId);
        $dias = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        
        return $dias > 0 ? round($totalCitas / $dias, 1) : 0;
    }

    private function getDiaMasActivo($startDate, $endDate, $clinicaId)
    {
        $diaMasActivo = Cita::select(
                DB::raw('DAYNAME(fecha) as dia_semana'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('dia_semana')
            ->orderBy('total', 'desc')
            ->first();

        if ($diaMasActivo && $diaMasActivo->dia_semana) {
            $dias = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];
            
            $diaSemana = (string) $diaMasActivo->dia_semana;
            return $dias[$diaSemana] ?? $diaSemana;
        }

        return 'N/A';
    }

    private function getHoraPico($startDate, $endDate, $clinicaId)
    {
        // Usar SQL para extraer la hora directamente y contar
        $resultado = Cita::select(
                DB::raw('HOUR(TIME(hora)) as hora'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$startDate, $endDate])
            ->whereNotNull('hora')
            ->where('hora', '!=', '')
            ->whereRaw('HOUR(TIME(hora)) BETWEEN 0 AND 23')
            ->groupBy('hora')
            ->orderBy('total', 'desc')
            ->first();

        if ($resultado && $resultado->hora !== null) {
            $hora = is_object($resultado->hora) ? (int) (string) $resultado->hora : (int) $resultado->hora;
            
            // Validar que esté en el rango válido (0-23)
            if ($hora >= 0 && $hora <= 23) {
                return sprintf('%02d:00', $hora);
            }
        }

        // Si la consulta SQL falla, intentar con procesamiento manual
        $citas = Cita::where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [$startDate, $endDate])
            ->whereNotNull('hora')
            ->where('hora', '!=', '')
            ->get(['hora']);

        if ($citas->isEmpty()) {
            return 'N/A';
        }

        $horasCount = [];
        foreach ($citas as $cita) {
            $hora = $cita->hora;
            $horaInt = null;
            
            // Si la hora viene en formato "HH:MM:SS" o "HH:MM", extraer solo la hora
            if (is_string($hora) && strpos($hora, ':') !== false) {
                $horaParts = explode(':', $hora);
                if (isset($horaParts[0]) && is_numeric($horaParts[0])) {
                    $horaInt = (int) $horaParts[0];
                }
            } elseif (is_numeric($hora)) {
                $horaInt = (int) $hora;
            }
            
            // Validar que esté en el rango válido (0-23)
            if ($horaInt !== null && $horaInt >= 0 && $horaInt <= 23) {
                if (!isset($horasCount[$horaInt])) {
                    $horasCount[$horaInt] = 0;
                }
                $horasCount[$horaInt]++;
            }
        }

        if (empty($horasCount)) {
            return 'N/A';
        }

        // Encontrar la hora con más citas
        arsort($horasCount);
        $horaPico = (int) array_key_first($horasCount);

        return sprintf('%02d:00', $horaPico);
    }
}
