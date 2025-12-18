<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Esfuerzo;
use App\Models\ReporteFinal;
use App\Models\ReporteFisio;
use App\Models\Estratificacion;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', 30); // Por defecto 30 días
            
            // Si el período es "all", no aplicar filtro de fechas
            $allTime = $period === 'all' || $period === 'todas';
            
            if ($allTime) {
                $startDate = null;
                $endDate = null;
            } else {
                $startDate = Carbon::now()->subDays($period)->format('Y-m-d');
                $endDate = Carbon::now()->addDay()->format('Y-m-d');
            }

            // Cualquier usuario puede ver las analíticas de su clínica
            // Filtrar todas las consultas por clínica del usuario
            $clinicaId = $user->clinica_id;

            // Métricas básicas - filtradas por clínica
            if ($allTime) {
                $totalCitas = Cita::where('clinica_id', $clinicaId)->count();
            } else {
                $totalCitas = Cita::where('clinica_id', $clinicaId)
                    ->whereBetween('fecha', [$startDate, $endDate])->count();
            }
            $totalPacientes = Paciente::where('clinica_id', $clinicaId)->count();
            
            if ($allTime) {
                $citasConfirmadas = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'confirmada')->count();
                $citasPendientes = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'pendiente')->count();
                $citasCanceladas = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'cancelada')->count();
                $citasCompletadas = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'completada')->count();
            } else {
                $citasConfirmadas = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'confirmada')->whereBetween('fecha', [$startDate, $endDate])->count();
                $citasPendientes = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'pendiente')->whereBetween('fecha', [$startDate, $endDate])->count();
                $citasCanceladas = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'cancelada')->whereBetween('fecha', [$startDate, $endDate])->count();
                $citasCompletadas = Cita::where('clinica_id', $clinicaId)
                    ->where('estado', 'completada')->whereBetween('fecha', [$startDate, $endDate])->count();
            }

            // Citas por estado
            $citasPorEstado = [
                $citasPendientes,
                $citasConfirmadas,
                $citasCompletadas,
                $citasCanceladas
            ];

            // Citas por día (últimos 7 días)
            $citasPorDia = $this->getCitasPorDia($startDate, $endDate, $clinicaId, $allTime);

            // Citas por semana
            $citasPorSemana = $this->getCitasPorSemana($startDate, $endDate, $clinicaId, $allTime);

            // Citas por mes
            $citasPorMes = $this->getCitasPorMes($startDate, $endDate, $clinicaId, $allTime);

            // Pacientes por edad
            $pacientesPorEdad = $this->getPacientesPorEdad($clinicaId);

            // Pacientes por género
            $pacientesPorGenero = $this->getPacientesPorGenero($clinicaId);

            // Pacientes pulmonares y ambos
            $pacientesPulmonares = $this->getPacientesPulmonares($clinicaId);

            // Estadísticas adicionales
            $tasaConfirmacion = $this->getTasaConfirmacion($startDate, $endDate, $clinicaId, $allTime);
            $citasPrimeraVez = $this->getCitasPrimeraVez($startDate, $endDate, $clinicaId, $allTime);
            $promedioCitasDia = $this->getPromedioCitasDia($startDate, $endDate, $clinicaId, $allTime);
            $diaMasActivo = $this->getDiaMasActivo($startDate, $endDate, $clinicaId, $allTime);
            $horaPico = $this->getHoraPico($startDate, $endDate, $clinicaId, $allTime);
            
            // Tasa de término (pacientes con expediente final)
            $tasaTermino = $this->getTasaTermino($clinicaId);
            
            // Pacientes que terminaron el tratamiento
            $pacientesTerminados = $this->getPacientesConExpedienteFinal($clinicaId);
            
            // Tasa promedio de incremento en METs
            $promedioIncrementoMets = $this->getPromedioIncrementoMets($clinicaId);
            
            // Duración de programa (distribución por sesiones)
            $duracionPrograma = $this->getDuracionPrograma($clinicaId);
            
            // Distribución de medicamentos
            $medicamentos = $this->getMedicamentos($clinicaId);

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
                'tasa_termino' => $tasaTermino,
                'pacientes_terminados' => $pacientesTerminados,
                'promedio_incremento_mets' => $promedioIncrementoMets,
                'duracion_programa' => $duracionPrograma,
                'medicamentos' => $medicamentos,
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

    private function getTotalCitas($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $query = Cita::where('clinica_id', $clinicaId);
        
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        return $query->count();
    }

    private function getTotalPacientes($clinicaId)
    {
        return Paciente::where('clinica_id', $clinicaId)->count();
    }

    private function getCitasByStatus($status, $startDate, $endDate, $clinicaId, $allTime = false)
    {
        $query = Cita::where('clinica_id', $clinicaId)
                   ->where('estado', $status);
                   
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        return $query->count();
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

    private function getCitasPorDia($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $query = Cita::select(
                DB::raw('DATE(fecha) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId);
            
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        $citas = $query->groupBy('fecha')
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

        if ($allTime) {
            // Para "todas las citas", mostrar solo las fechas con citas (últimos 30 días de datos)
            $citasArray = array_slice($citasMap, -30, 30, true);
            foreach ($citasArray as $fecha => $total) {
                $labels[] = Carbon::parse($fecha)->format('d/m');
                $data[] = $total;
            }
        } else {
            // Crear array con todos los días del período
            $current = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            while ($current->lte($end)) {
                $fechaStr = $current->format('Y-m-d');
                $labels[] = $current->format('d/m');
                $data[] = isset($citasMap[$fechaStr]) ? $citasMap[$fechaStr] : 0;
                $current->addDay();
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getCitasPorMes($startDate, $endDate, $clinicaId, $allTime = false)
    {
        // Obtener todas las citas del año actual, no solo del período
        $añoActual = Carbon::now()->year;
        $inicioAño = Carbon::create($añoActual, 1, 1)->format('Y-m-d');
        $finAño = Carbon::create($añoActual, 12, 31)->format('Y-m-d');
        
        $query = Cita::select(
                DB::raw('YEAR(fecha) as año'),
                DB::raw('MONTH(fecha) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId);
            
        // Para el filtro por mes, siempre mostramos el año actual completo, pero si no es allTime
        // podemos filtrar por el rango específico
        if (!$allTime) {
            $query->whereBetween('fecha', [$inicioAño, $finAño]);
        }
        
        $citas = $query->groupBy('año', 'mes')
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

    private function getCitasPorSemana($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $query = Cita::select(
                DB::raw('YEAR(fecha) as año'),
                DB::raw('WEEK(fecha, 1) as semana'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId);
            
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        $citas = $query->groupBy('año', 'semana')
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
        $fisioterapia = Paciente::where('clinica_id', $clinicaId)->where('tipo_paciente', 'fisioterapia')->count();

        return [
            'labels' => ['Cardíaca', 'Pulmonar', 'Ambos', 'Fisioterapia'],
            'data' => [$cardiaca, $pulmonar, $ambos, $fisioterapia]
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

    private function getTasaConfirmacion($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $totalCitas = $this->getTotalCitas($startDate, $endDate, $clinicaId, $allTime);
        
        if ($totalCitas == 0) {
            return 0;
        }

        $citasConfirmadas = $this->getCitasByStatus('confirmada', $startDate, $endDate, $clinicaId, $allTime);
        
        return round(($citasConfirmadas / $totalCitas) * 100, 1);
    }

    private function getCitasPrimeraVez($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $query = Cita::where('clinica_id', $clinicaId)
                   ->where('primera_vez', true);
                   
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        return $query->count();
    }

    private function getPromedioCitasDia($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $totalCitas = $this->getTotalCitas($startDate, $endDate, $clinicaId, $allTime);
        
        if ($allTime) {
            // Para todas las citas, calcular desde la primera cita hasta hoy
            $primeraCita = Cita::where('clinica_id', $clinicaId)->orderBy('fecha')->first();
            if (!$primeraCita) {
                return 0;
            }
            $dias = Carbon::parse($primeraCita->fecha)->diffInDays(Carbon::now()) + 1;
        } else {
            $dias = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        }
        
        return $dias > 0 ? round($totalCitas / $dias, 1) : 0;
    }

    private function getDiaMasActivo($startDate, $endDate, $clinicaId, $allTime = false)
    {
        $query = Cita::select(
                DB::raw('DAYNAME(fecha) as dia_semana'),
                DB::raw('COUNT(*) as total')
            )
            ->where('clinica_id', $clinicaId);
            
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        $diaMasActivo = $query->groupBy('dia_semana')
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

    private function getHoraPico($startDate, $endDate, $clinicaId, $allTime = false)
    {
        // Obtener todas las citas con hora (sin aplicar el cast de datetime)
        $query = Cita::where('clinica_id', $clinicaId);
        
        if (!$allTime) {
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }
        
        $citas = $query->whereNotNull('hora')
            ->get(['hora']);

        if ($citas->isEmpty()) {
            return 'N/A';
        }

        $horasCount = [];
        foreach ($citas as $cita) {
            $hora = $cita->hora;
            $horaInt = null;
            
            // Si es un objeto Carbon/DateTime, extraer la hora directamente
            if ($hora instanceof \Carbon\Carbon || $hora instanceof \DateTime) {
                $horaInt = (int) $hora->format('H');
            } elseif (is_string($hora)) {
                // Si es string con formato "HH:MM:SS" o "HH:MM"
                if (strpos($hora, ':') !== false) {
                    $horaParts = explode(':', $hora);
                    if (isset($horaParts[0]) && is_numeric($horaParts[0])) {
                        $horaInt = (int) $horaParts[0];
                    }
                } elseif (is_numeric($hora)) {
                    $horaInt = (int) $hora;
                }
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

    /**
     * Obtener la tasa de término
     * Fórmula: (Px con reporte final / total de px cardíacos) * 100
     * Representa el porcentaje de pacientes que completaron el programa
     */
    private function getTasaTermino($clinicaId)
    {
        $totalPacientes = Paciente::where('clinica_id', $clinicaId)
        ->where('tipo_paciente', 'cardiaca')
        ->count();
        
        if ($totalPacientes == 0) {
            return 0;
        }

        $pacientesConExpFinal = $this->getPacientesConExpedienteFinal($clinicaId);
        $pacientesSinExpFinal = $totalPacientes-$pacientesConExpFinal;
        
        return round(($pacientesConExpFinal / $pacientesSinExpFinal) * 100, 1);
    }

    /**
     * Obtener cantidad de pacientes con reporte final (terminaron el programa)
     * Solo cuenta pacientes cardíacos que tienen reporte final
     */
    private function getPacientesConExpedienteFinal($clinicaId)
    {
        // Obtener IDs de pacientes cardíacos de la clínica
        $pacientesCardiacos = Paciente::where('clinica_id', $clinicaId)
            ->where('tipo_paciente', 'cardiaca')
            ->pluck('id');
        
        // Contar pacientes cardíacos que tienen reporte final
        return ReporteFinal::where('clinica_id', $clinicaId)
            ->whereIn('paciente_id', $pacientesCardiacos)
            ->select('paciente_id')
            ->distinct()
            ->count();
    }

    /**
     * Obtener el promedio de incremento en METs desde reporte_finals
     */
    private function getPromedioIncrementoMets($clinicaId)
    {
        // Obtener el promedio del campo mets_por de la tabla reporte_finals
        $promedio = ReporteFinal::where('clinica_id', $clinicaId)
            ->whereNotNull('mets_por')
            ->avg('mets_por');

        return $promedio ? round(floatval($promedio), 1) : 0;
    }

    /**
     * Obtener distribución de pacientes por duración de programa (sesiones)
     * Rubros: 10, 20, 30, 36 y 56 sesiones
     */
    private function getDuracionPrograma($clinicaId)
    {
        // Obtener la última estratificación de cada paciente (para tener el valor más reciente)
        $estratificaciones = Estratificacion::where('clinica_id', $clinicaId)
            ->whereNotNull('sesiones')
            ->select('paciente_id', 'sesiones')
            ->get()
            ->unique('paciente_id'); // Tomar un registro único por paciente

        // Definir los rubros de sesiones
        $rubros = [
            '10' => 0,
            '20' => 0,
            '30' => 0,
            '36' => 0,
            '56' => 0
        ];

        foreach ($estratificaciones as $estrat) {
            $sesiones = (int) $estrat->sesiones;
            
            // Asignar al rubro correspondiente
            if ($sesiones == 10) {
                $rubros['10']++;
            } elseif ($sesiones == 20) {
                $rubros['20']++;
            } elseif ($sesiones == 30) {
                $rubros['30']++;
            } elseif ($sesiones == 36) {
                $rubros['36']++;
            } elseif ($sesiones == 56) {
                $rubros['56']++;
            }
        }

        return [
            'labels' => ['10 sesiones', '20 sesiones', '30 sesiones', '36 sesiones', '56 sesiones'],
            'data' => array_values($rubros)
        ];
    }

    private function getSemanasTerminadas($clinicaId)
    {
        return Esfuerzo::where('clinica_id', $clinicaId)
            ->whereNotNull('semana')
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        $semanas = [];
        foreach ($esfuerzos as $esfuerzo) {
            $semanas[] = $esfuerzo->semana;
        }

        return $semanas;
    }

    /**
     * Obtener distribución de medicamentos (cuántos pacientes toman cada medicamento)
     */
    private function getMedicamentos($clinicaId)
    {
        // Obtener todos los clínicos de la clínica
        $clinicos = DB::table('clinicos')
            ->join('pacientes', 'clinicos.paciente_id', '=', 'pacientes.id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->select([
                DB::raw('SUM(CASE WHEN betabloqueador = 1 THEN 1 ELSE 0 END) as betabloqueador'),
                DB::raw('SUM(CASE WHEN nitratos = 1 THEN 1 ELSE 0 END) as nitratos'),
                DB::raw('SUM(CASE WHEN calcioantagonista = 1 THEN 1 ELSE 0 END) as calcioantagonista'),
                DB::raw('SUM(CASE WHEN aspirina = 1 THEN 1 ELSE 0 END) as aspirina'),
                DB::raw('SUM(CASE WHEN anticoagulacion = 1 THEN 1 ELSE 0 END) as anticoagulacion'),
                DB::raw('SUM(CASE WHEN iecas = 1 THEN 1 ELSE 0 END) as iecas'),
                DB::raw('SUM(CASE WHEN atii = 1 THEN 1 ELSE 0 END) as atii'),
                DB::raw('SUM(CASE WHEN diureticos = 1 THEN 1 ELSE 0 END) as diureticos'),
                DB::raw('SUM(CASE WHEN estatinas = 1 THEN 1 ELSE 0 END) as estatinas'),
                DB::raw('SUM(CASE WHEN fibratos = 1 THEN 1 ELSE 0 END) as fibratos'),
                DB::raw('SUM(CASE WHEN digoxina = 1 THEN 1 ELSE 0 END) as digoxina'),
                DB::raw('SUM(CASE WHEN antiarritmicos = 1 THEN 1 ELSE 0 END) as antiarritmicos'),
                DB::raw('SUM(CASE WHEN arni = 1 THEN 1 ELSE 0 END) as arni'),
                DB::raw('SUM(CASE WHEN sglt2 = 1 THEN 1 ELSE 0 END) as sglt2'),
                DB::raw('SUM(CASE WHEN mra = 1 THEN 1 ELSE 0 END) as mra'),
                DB::raw('SUM(CASE WHEN ivabradina = 1 THEN 1 ELSE 0 END) as ivabradina'),
                DB::raw('SUM(CASE WHEN pcsk0 = 1 THEN 1 ELSE 0 END) as pcsk0'),
                DB::raw('SUM(CASE WHEN ranalozina = 1 THEN 1 ELSE 0 END) as ranalozina'),
                DB::raw('SUM(CASE WHEN timetrazidina = 1 THEN 1 ELSE 0 END) as timetrazidina'),
                DB::raw('SUM(CASE WHEN inhibidor_adp = 1 THEN 1 ELSE 0 END) as inhibidor_adp'),
            ])
            ->first();

        // Convertir el resultado a arrays
        $labels = [
            'Betabloqueador', 'Nitratos', 'Calcioantagonista', 'Aspirina',
            'Anticoagulación', 'IECAs', 'AT-II', 'Diuréticos',
            'Estatinas', 'Fibratos', 'Digoxina', 'Antiarrítmicos',
            'ARNI', 'SGLT2', 'MRA', 'Ivabradina',
            'PCSK9', 'Ranolazina', 'Trimetazidina', 'Inhibidor ADP'
        ];

        $data = [
            (int) $clinicos->betabloqueador,
            (int) $clinicos->nitratos,
            (int) $clinicos->calcioantagonista,
            (int) $clinicos->aspirina,
            (int) $clinicos->anticoagulacion,
            (int) $clinicos->iecas,
            (int) $clinicos->atii,
            (int) $clinicos->diureticos,
            (int) $clinicos->estatinas,
            (int) $clinicos->fibratos,
            (int) $clinicos->digoxina,
            (int) $clinicos->antiarritmicos,
            (int) $clinicos->arni,
            (int) $clinicos->sglt2,
            (int) $clinicos->mra,
            (int) $clinicos->ivabradina,
            (int) $clinicos->pcsk0,
            (int) $clinicos->ranalozina,
            (int) $clinicos->timetrazidina,
            (int) $clinicos->inhibidor_adp,
        ];

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
