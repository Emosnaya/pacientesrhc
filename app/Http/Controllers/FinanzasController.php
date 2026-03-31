<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Egreso;
use App\Models\Paciente;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class FinanzasController extends Controller
{
    /**
     * Registrar un nuevo pago
     */
    public function registrarPago(Request $request)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_efectiva_id);
        // Para rehabilitación cardiopulmonar la firma del paciente no es obligatoria en el recibo de pago
        $firmaObligatoria = !$clinica || $clinica->tipo_clinica !== 'rehabilitacion_cardiopulmonar';

        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'referencia' => 'nullable|string|max:255',
            'concepto' => 'nullable|string|max:500',
            'notas' => 'nullable|string',
            'firma_paciente' => ['nullable', 'string'],
            'cita_id' => 'nullable|exists:citas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'fecha_pago' => 'nullable|date|before_or_equal:today',
        ]);

        $paciente = null;
        if ($request->filled('paciente_id')) {
            // Verificar que el paciente pertenece a la misma clínica
            $paciente = Paciente::findOrFail($request->paciente_id);
            if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
                return response()->json([
                    'message' => 'No tienes permiso para registrar pagos de este paciente'
                ], 403);
            }
        }

        // Si es pago sin paciente, no se requiere firma
        $firmaObligatoria = $paciente && ($clinica && $clinica->tipo_clinica !== 'rehabilitacion_cardiopulmonar');
        if ($firmaObligatoria && !$request->firma_paciente) {
            return response()->json([
                'errors' => ['firma_paciente' => ['La firma del paciente es obligatoria']]
            ], 422);
        }

        // Validar firma si se proporciona
        if ($request->firma_paciente) {
            // Verificar que sea una imagen base64 válida
            if (!preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $request->firma_paciente)) {
                return response()->json([
                    'message' => 'La firma debe ser una imagen válida en formato base64'
                ], 422);
            }
        }

        $clinicaId = (int) $user->clinica_efectiva_id;
        $sucursalId = $paciente
            ? $paciente->sucursal_id
            : ($user->isSuperAdmin() && $request->filled('sucursal_id') ? $request->sucursal_id : $user->sucursal_id);

        // Crear el pago
        $pago = Pago::create([
            'paciente_id' => $paciente?->id,
            'clinica_id' => $clinicaId,
            'sucursal_id' => $sucursalId,
            'user_id' => $user->id,
            'cita_id' => $request->cita_id,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'concepto' => $request->concepto,
            'notas' => $request->notas,
            'firma_paciente' => $request->firma_paciente ?: null,
            'fecha_pago' => $request->fecha_pago ? Carbon::parse($request->fecha_pago)->toDateString() : now()->toDateString(),
        ]);

        // Cargar relaciones
        $pago->load(['paciente', 'usuario', 'cita']);

        return response()->json([
            'message' => 'Pago registrado exitosamente',
            'pago' => $pago
        ], 201);
    }

    /**
     * Actualizar un pago existente
     */
    public function updatePago(Request $request, $id)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_efectiva_id);
        $firmaObligatoria = !$clinica || $clinica->tipo_clinica !== 'rehabilitacion_cardiopulmonar';

        $pago = Pago::findOrFail($id);

        // Verificar permisos: solo el usuario que creó el pago o superadmin puede editar
        if ($pago->user_id !== $user->id && !$user->isSuperAdmin()) {
            return response()->json([
                'message' => 'No tienes permiso para editar este pago'
            ], 403);
        }

        // Verificar que el pago pertenece a la misma clínica
        if ($pago->clinica_id !== $user->clinica_efectiva_id) {
            return response()->json([
                'message' => 'No tienes permiso para editar pagos de esta clínica'
            ], 403);
        }

        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'referencia' => 'nullable|string|max:255',
            'concepto' => 'nullable|string|max:500',
            'notas' => 'nullable|string',
            'firma_paciente' => ['nullable', 'string'],
            'cita_id' => 'nullable|exists:citas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'fecha_pago' => 'nullable|date|before_or_equal:today',
        ]);

        $paciente = null;
        if ($request->filled('paciente_id')) {
            $paciente = Paciente::findOrFail($request->paciente_id);
            if (! $paciente->belongsToClinicaWorkspace((int) $user->clinica_efectiva_id)) {
                return response()->json([
                    'message' => 'No tienes permiso para asignar pagos a este paciente'
                ], 403);
            }
        }

        // Si es pago sin paciente, no se requiere firma
        $firmaObligatoria = $paciente && ($clinica && $clinica->tipo_clinica !== 'rehabilitacion_cardiopulmonar');
        if ($firmaObligatoria && !$request->firma_paciente && !$pago->firma_paciente) {
            return response()->json([
                'errors' => ['firma_paciente' => ['La firma del paciente es obligatoria']]
            ], 422);
        }

        // Validar firma si se proporciona
        if ($request->firma_paciente) {
            if (!preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $request->firma_paciente)) {
                return response()->json([
                    'message' => 'La firma debe ser una imagen válida en formato base64'
                ], 422);
            }
        }

        $clinicaId = (int) $user->clinica_efectiva_id;
        $sucursalId = $paciente
            ? $paciente->sucursal_id
            : ($user->isSuperAdmin() && $request->filled('sucursal_id') ? $request->sucursal_id : $pago->sucursal_id);

        // Actualizar el pago
        $pago->update([
            'paciente_id' => $paciente?->id,
            'clinica_id' => $clinicaId,
            'sucursal_id' => $sucursalId,
            'cita_id' => $request->cita_id,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'concepto' => $request->concepto,
            'notas' => $request->notas,
            'firma_paciente' => $request->firma_paciente ?: $pago->firma_paciente,
            'fecha_pago' => $request->fecha_pago ? Carbon::parse($request->fecha_pago)->toDateString() : $pago->fecha_pago,
        ]);

        // Cargar relaciones
        $pago->load(['paciente', 'usuario', 'cita']);

        return response()->json([
            'message' => 'Pago actualizado exitosamente',
            'pago' => $pago
        ]);
    }

    /**
     * Eliminar un pago
     */
    public function destroyPago(Request $request, $id)
    {
        $user = Auth::user();
        $pago = Pago::findOrFail($id);

        // Verificar permisos: solo superadmin puede eliminar pagos
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'message' => 'Solo los administradores pueden eliminar pagos'
            ], 403);
        }

        // Verificar que el pago pertenece a la misma clínica
        if ($pago->clinica_id !== $user->clinica_efectiva_id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar pagos de esta clínica'
            ], 403);
        }

        $pago->delete();

        return response()->json([
            'message' => 'Pago eliminado exitosamente'
        ]);
    }

    /**
     * Registrar un nuevo egreso
     */
    public function registrarEgreso(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'tipo_egreso' => 'required|in:compra_material,servicio,mantenimiento,nomina,renta,servicios_publicos,otro',
            'concepto' => 'required|string|max:500',
            'proveedor' => 'nullable|string|max:255',
            'factura' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $sucursalId = $user->isSuperAdmin() && $request->filled('sucursal_id')
            ? $request->sucursal_id
            : $user->sucursal_id;

        // Crear el egreso
        $egreso = Egreso::create([
            'clinica_id' => $user->clinica_efectiva_id,
            'sucursal_id' => $sucursalId,
            'user_id' => $user->id,
            'monto' => $request->monto,
            'tipo_egreso' => $request->tipo_egreso,
            'concepto' => $request->concepto,
            'proveedor' => $request->proveedor,
            'factura' => $request->factura,
            'notas' => $request->notas,
        ]);

        // Cargar relaciones
        $egreso->load(['usuario', 'clinica', 'sucursal']);

        return response()->json([
            'message' => 'Egreso registrado exitosamente',
            'egreso' => $egreso
        ], 201);
    }

    /**
     * Listar egresos con filtros
     */
    public function indexEgresos(Request $request)
    {
        try {
            $user = $request->user();
            $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
            
            $query = Egreso::where('clinica_id', $user->clinica_efectiva_id)
                ->where('sucursal_id', $user->sucursal_id)
                ->with(['usuario', 'sucursal']);

            // Filtrar por fecha
            if ($request->has('fecha')) {
                $query->whereDate('created_at', $request->fecha);
            }

            // Filtrar por rango de fechas
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('created_at', [
                    $request->fecha_inicio . ' 00:00:00',
                    $request->fecha_fin . ' 23:59:59'
                ]);
            }

            // Filtrar por tipo de egreso
            if ($request->has('tipo_egreso')) {
                $query->where('tipo_egreso', $request->tipo_egreso);
            }

            $egresos = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 50);

            return response()->json($egresos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener egresos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un egreso específico
     */
    public function showEgreso(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $egreso = Egreso::where('id', $id)
                ->where('clinica_id', $user->clinica_efectiva_id)
                ->where('sucursal_id', $user->sucursal_id)
                ->with(['usuario', 'clinica', 'sucursal'])
                ->firstOrFail();

            return response()->json($egreso);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Egreso no encontrado',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Eliminar un egreso (solo admin/superadmin)
     */
    public function destroyEgreso(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Solo admin o superadmin pueden eliminar egresos
            if (!in_array($user->role, ['admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'No tienes permisos para eliminar egresos'
                ], 403);
            }
            
            $egreso = Egreso::where('id', $id)
                ->where('clinica_id', $user->clinica_efectiva_id)
                ->where('sucursal_id', $user->sucursal_id)
                ->firstOrFail();

            $egreso->delete();

            return response()->json([
                'message' => 'Egreso eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar egreso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener listado de pagos con filtros
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;

        $query = Pago::with(['paciente', 'usuario', 'cita'])
            ->where('clinica_id', $user->clinica_efectiva_id);

        // Filtros
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->betweenDates($request->fecha_inicio, $request->fecha_fin);
        } elseif ($request->filled('fecha')) {
            $query->byDate($request->fecha);
        }

        if ($request->filled('metodo_pago')) {
            $query->byMetodoPago($request->metodo_pago);
        }

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Ordenar por fecha descendente
        $query->orderBy('created_at', 'desc');

        // Paginación
        $perPage = $request->input('per_page', 15);
        $pagos = $query->paginate($perPage);

        return response()->json($pagos);
    }

    /**
     * Obtener detalles de un pago específico
     */
    public function show($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;

        $pago = Pago::with(['paciente', 'usuario', 'cita', 'sucursal'])
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        return response()->json($pago);
    }

    /**
     * Corte de caja diario
     */
    public function corteCajaDiario(Request $request)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_efectiva_id;
        $periodo = $request->input('periodo', 'dia');
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $mes = $request->input('mes');
        $anio = $request->input('anio');

        // Calcular fechas según el período
        if ($periodo === 'mes' && $mes) {
            // Formato esperado: YYYY-MM
            $fechaInicio = Carbon::parse($mes . '-01')->startOfMonth()->toDateString();
            // Si es el mes actual, limitar hasta hoy; si es mes pasado, hasta fin de mes
            $mesParsed = Carbon::parse($mes . '-01');
            if ($mesParsed->isSameMonth(Carbon::now())) {
                $fechaFin = Carbon::now()->toDateString();
            } else {
                $fechaFin = $mesParsed->endOfMonth()->toDateString();
            }
            $etiquetaPeriodo = Carbon::parse($mes)->locale('es')->isoFormat('MMMM YYYY');
        } elseif ($periodo === 'anio' && $anio) {
            $fechaInicio = Carbon::create($anio, 1, 1)->startOfYear()->toDateString();
            $fechaFin = Carbon::create($anio, 12, 31)->endOfYear()->toDateString();
            $etiquetaPeriodo = $anio;
        } else {
            // Por defecto: día
            $fechaInicio = $fecha;
            $fechaFin = $fecha;
            $etiquetaPeriodo = Carbon::parse($fecha)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        }

        // Total por método de pago (ingresos)
        $totalesPorMetodo = Pago::where('clinica_id', $clinicaId)
            ->betweenDates($fechaInicio, $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->select('metodo_pago', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('metodo_pago')
            ->get()
            ->map(function($item) use ($clinicaId, $request, $fechaInicio, $fechaFin) {
                // Obtener el monto total desencriptado para este método
                $pagos = Pago::where('metodo_pago', $item->metodo_pago)
                    ->betweenDates($fechaInicio, $fechaFin)
                    ->where('clinica_id', $clinicaId)
                    ->when($request->filled('sucursal_id'), function($q) use ($request) {
                        $q->where('sucursal_id', $request->sucursal_id);
                    })
                    ->get();
                
                $total = $pagos->sum(function($pago) {
                    return (float) $pago->monto;
                });

                return [
                    'metodo_pago' => $item->metodo_pago,
                    'cantidad' => $item->cantidad,
                    'total' => $total
                ];
            });

        // Total general de ingresos (pagos)
        $todosPagos = Pago::where('clinica_id', $clinicaId)
            ->betweenDates($fechaInicio, $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalIngresos = $todosPagos->sum(function($pago) {
            return (float) $pago->monto;
        });

        $cantidadPagos = $todosPagos->count();

        // Total de egresos del período
        $todosEgresos = Egreso::where('clinica_id', $clinicaId)
            ->betweenDates($fechaInicio, $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalEgresos = $todosEgresos->sum(function($egreso) {
            return (float) $egreso->monto;
        });

        $cantidadEgresos = $todosEgresos->count();

        // Balance del período
        $balance = $totalIngresos - $totalEgresos;

        // Últimos pagos del período
        $ultimosPagos = Pago::with(['paciente', 'usuario', 'clinica', 'sucursal'])
            ->where('clinica_id', $clinicaId)
            ->betweenDates($fechaInicio, $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->latest()
            ->limit(50)
            ->get();

        // Últimos egresos del período
        $ultimosEgresos = Egreso::with(['usuario', 'clinica', 'sucursal'])
            ->where('clinica_id', $clinicaId)
            ->betweenDates($fechaInicio, $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'fecha' => $fecha,
            'periodo' => $periodo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'etiqueta_periodo' => $etiquetaPeriodo,
            'totales_por_metodo' => $totalesPorMetodo,
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'balance' => $balance,
            'cantidad_pagos' => $cantidadPagos,
            'cantidad_egresos' => $cantidadEgresos,
            'ultimos_pagos' => $ultimosPagos,
            'ultimos_egresos' => $ultimosEgresos
        ]);
    }

    /**
     * Estadísticas de pagos
     */
    public function estadisticas(Request $request)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        
        // Período (por defecto mes actual)
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

        $pagos = Pago::with('paciente')
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalIngresos = $pagos->sum(function($pago) {
            return (float) $pago->monto;
        });

        // Obtener egresos del mismo período
        $egresos = Egreso::where('clinica_id', $user->clinica_efectiva_id)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalEgresos = $egresos->sum(function($egreso) {
            return (float) $egreso->monto;
        });

        $balance = $totalIngresos - $totalEgresos;

        $dias = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
        $promedioIngresoDiario = $dias > 0 ? ($totalIngresos / $dias) : 0;
        $promedioEgresoDiario = $dias > 0 ? ($totalEgresos / $dias) : 0;
        $promedioBalanceDiario = $dias > 0 ? ($balance / $dias) : 0;

        // Distribución por método de pago
        $porMetodo = $pagos->groupBy('metodo_pago')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum(function($pago) {
                    return (float) $pago->monto;
                })
            ];
        });

        // Distribución de egresos por tipo
        $egresosPorTipo = $egresos->groupBy('tipo_egreso')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum(function($egreso) {
                    return (float) $egreso->monto;
                })
            ];
        });

        // Top 5 pacientes con más pagos (solo si tienen paciente cargado)
        $topPacientes = $pagos->groupBy('paciente_id')
            ->map(function($grupo) {
                $paciente = $grupo->first()->paciente;
                if (!$paciente) {
                    return null;
                }
                return [
                    'paciente_id' => $paciente->id,
                    'paciente_nombre' => trim($paciente->nombre . ' ' . ($paciente->apellidoPat ?? '')),
                    'cantidad_pagos' => $grupo->count(),
                    'total_pagado' => $grupo->sum(function($pago) {
                        return (float) $pago->monto;
                    })
                ];
            })
            ->filter()
            ->sortByDesc('total_pagado')
            ->take(5)
            ->values();

        // Top 5 categorías de egresos
        $topEgresos = $egresosPorTipo->sortByDesc('total')->take(5)->map(function($data, $tipo) {
            return [
                'tipo' => $tipo,
                'cantidad' => $data['cantidad'],
                'total' => $data['total']
            ];
        })->values();

        return response()->json([
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            // Totales
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'balance' => $balance,
            // Promedios diarios
            'promedio_ingreso_diario' => round($promedioIngresoDiario, 2),
            'promedio_egreso_diario' => round($promedioEgresoDiario, 2),
            'promedio_balance_diario' => round($promedioBalanceDiario, 2),
            // Contadores
            'cantidad_pagos' => $pagos->count(),
            'cantidad_egresos' => $egresos->count(),
            // Distribuciones
            'por_metodo' => $porMetodo,
            'egresos_por_tipo' => $egresosPorTipo,
            // Top lists
            'top_pacientes' => $topPacientes,
            'top_egresos' => $topEgresos,
            // Legacy (mantener compatibilidad)
            'total_periodo' => $totalIngresos,
            'promedio_diario' => round($promedioIngresoDiario, 2)
        ]);
    }

    /**
     * Obtener historial de pagos de un paciente
     */
    public function historialPaciente($pacienteId)
    {
        $user = Auth::user();

        // Verificar que el paciente pertenece a la clínica efectiva (puede ser consultorio activo)
        $paciente = Paciente::forClinicaWorkspace((int) $user->clinica_efectiva_id)
            ->findOrFail($pacienteId);

        $pagos = Pago::with(['usuario', 'cita', 'clinica', 'sucursal'])
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPagado = $pagos->sum(function($pago) {
            return (float) $pago->monto;
        });

        return response()->json([
            'paciente' => $paciente,
            'pagos' => $pagos,
            'total_pagado' => $totalPagado,
            'cantidad_pagos' => $pagos->count()
        ]);
    }

    /**
     * Enviar recibo de pago por correo
     */
    public function sendReciboByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pago_id' => 'required|integer|exists:pagos,id',
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        $pago = Pago::with(['paciente', 'usuario', 'sucursal', 'clinica'])
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($request->pago_id);

        $clinica = $pago->clinica ?? $user->clinica;
        $sucursal = $pago->sucursal ?? $user->sucursal;
        $clinicaLogo = $this->getClinicaLogoBase64($user);
        
        // Log para debugging
        \Log::info('Recibo PDF Data:', [
            'clinica_id' => $clinica?->id,
            'clinica_nombre' => $clinica?->nombre,
            'sucursal_id' => $sucursal?->id,
            'sucursal_nombre' => $sucursal?->nombre,
            'logo_existe' => !empty($clinicaLogo)
        ]);
        
        $nombrePaciente = trim(($pago->paciente->nombre ?? '') . ' ' . ($pago->paciente->apellidoPat ?? '') . ' ' . ($pago->paciente->apellidoMat ?? ''));
        $subject = $request->subject ?: ('Recibo de pago #' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . ' - ' . $nombrePaciente . ' - ' . ($clinica->nombre ?? 'CERCAP'));
        $mensaje = $request->message ?: 'Se adjunta el recibo de pago en PDF.';

        try {
            $pdf = Pdf::loadView('finanzas.recibo', compact('pago', 'clinica', 'sucursal', 'clinicaLogo'));
            $pdfFileName = 'Recibo_' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . '_' . \Str::slug($nombrePaciente) . '.pdf';

            Mail::send('emails.recibo-pago', [
                'mensaje' => $mensaje,
                'clinica' => $clinica,
                'pago' => $pago,
                'nombrePaciente' => $nombrePaciente,
            ], function ($mail) use ($request, $subject, $pdf, $pdfFileName) {
                $mail->to($request->email)
                    ->subject($subject)
                    ->attachData($pdf->output(), $pdfFileName, ['mime' => 'application/pdf']);
            });

            return response()->json([
                'message' => 'Recibo enviado por correo (PDF adjunto) exitosamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error enviando recibo por email: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al enviar el correo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ver el recibo en PDF directamente
     */
    public function verReciboPdf($id)
    {
        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        $pago = Pago::with(['paciente', 'usuario', 'sucursal', 'clinica'])
            ->where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        $clinica = $pago->clinica ?? $user->clinica;
        $sucursal = $pago->sucursal ?? $user->sucursal;
        $clinicaLogo = $this->getClinicaLogoBase64($user);
        
        // Log para debugging
        \Log::info('Ver Recibo PDF:', [
            'clinica' => $clinica?->nombre,
            'sucursal' => $sucursal?->nombre,
            'logo' => substr($clinicaLogo ?? 'null', 0, 50) . '...'
        ]);

        $pdf = Pdf::loadView('finanzas.recibo', compact('pago', 'clinica', 'sucursal', 'clinicaLogo'));
        return $pdf->stream('Recibo_' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    /**
     * Obtener el logo de la clínica en formato base64
     */
    private function getClinicaLogoBase64($user, $targetHeight = 36)
    {
        try {
            // Validar que el usuario exista
            if (!$user) {
                return null;
            }

            $user->loadMissing(['clinicaActiva', 'clinica']);
            $clinica = $user->clinicaActiva ?? $user->clinica;
            $logoPath = null;
            
            if ($clinica && $clinica->logo) {
                $logoPath = storage_path('app/public/' . $clinica->logo);
            }
            
            if (!$logoPath || !file_exists($logoPath)) {
                return null;
            }
            
            // Verificar si GD está disponible
            if (!function_exists('imagecreatefrompng')) {
                \Log::warning('GD no está disponible, usando imagen original');
                $imageData = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            
            // Redimensionar la imagen para que dompdf respete el tamaño
            $imageInfo = @getimagesize($logoPath);
            if (!$imageInfo) {
                \Log::error('No se pudo obtener información de la imagen: ' . $logoPath);
                $imageData = file_get_contents($logoPath);
                $mimeType = mime_content_type($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }

            list($width, $height) = $imageInfo;
            $aspectRatio = $width / $height;
            $newHeight = $targetHeight;
            $newWidth = (int)($newHeight * $aspectRatio);

            // Determinar el tipo de imagen y crear el recurso correspondiente
            $mimeType = $imageInfo['mime'];
            $source = null;

            switch ($mimeType) {
                case 'image/jpeg':
                    $source = @imagecreatefromjpeg($logoPath);
                    break;
                case 'image/png':
                    $source = @imagecreatefrompng($logoPath);
                    break;
                case 'image/gif':
                    $source = @imagecreatefromgif($logoPath);
                    break;
                default:
                    \Log::error('Tipo de imagen no soportado: ' . $mimeType);
                    $imageData = file_get_contents($logoPath);
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }

            if (!$source) {
                \Log::error('No se pudo crear imagen desde: ' . $logoPath);
                $imageData = file_get_contents($logoPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }

            // Crear nueva imagen redimensionada
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preservar transparencia para PNG
            if ($mimeType === 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Redimensionar
            imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Guardar en buffer
            ob_start();
            if ($mimeType === 'image/png') {
                imagepng($newImage, null, 9);
            } else {
                imagejpeg($newImage, null, 90);
            }
            $imageData = ob_get_clean();

            // Liberar memoria
            imagedestroy($source);
            imagedestroy($newImage);

            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        } catch (\Exception $e) {
            \Log::error('Error al obtener logo de clínica: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Eliminar un pago (solo admin)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Verificar que sea admin o super admin
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar pagos'
            ], 403);
        }

        $pago = Pago::where('clinica_id', $user->clinica_efectiva_id)
            ->findOrFail($id);

        $pago->delete();

        return response()->json([
            'message' => 'Pago eliminado exitosamente'
        ]);
    }

    /**
     * Corte del día/mes/año
     */
    public function corte(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:dia,mes,año',
            'fecha' => 'required|date',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        $tipo = $request->tipo;
        $fecha = Carbon::parse($request->fecha);
        $sucursalId = $request->sucursal_id ?? $user->sucursal_id;

        // Construir la consulta base
        $query = Pago::where('clinica_id', $user->clinica_efectiva_id)
            ->with(['paciente', 'usuario', 'sucursal']);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        // Filtrar por rango de fechas según el tipo de corte
        switch ($tipo) {
            case 'dia':
                $query->byDate($fecha->toDateString());
                break;
            case 'mes':
                $inicioMes = $fecha->copy()->startOfMonth()->toDateString();
                $finMes = $fecha->copy()->endOfMonth()->toDateString();
                
                // Si es el mes actual, limitar hasta hoy
                if ($fecha->month === now()->month && $fecha->year === now()->year) {
                    $finMes = now()->toDateString();
                }
                
                $query->betweenDates($inicioMes, $finMes);
                break;
            case 'año':
                $inicioAño = $fecha->copy()->startOfYear()->toDateString();
                $finAño = $fecha->copy()->endOfYear()->toDateString();
                
                // Si es el año actual, limitar hasta hoy
                if ($fecha->year === now()->year) {
                    $finAño = now()->toDateString();
                }
                
                $query->betweenDates($inicioAño, $finAño);
                break;
        }

        $pagos = $query->orderBy('created_at', 'asc')->get();

        // Construir consulta de egresos
        $queryEgresos = Egreso::where('clinica_id', $user->clinica_efectiva_id)
            ->with(['usuario', 'sucursal']);

        if ($sucursalId) {
            $queryEgresos->where('sucursal_id', $sucursalId);
        }

        // Filtrar egresos por el mismo rango de fechas
        switch ($tipo) {
            case 'dia':
                $queryEgresos->whereDate('created_at', $fecha->toDateString());
                break;
            case 'mes':
                $inicioMes = $fecha->copy()->startOfMonth();
                $finMes = $fecha->copy()->endOfMonth();
                if ($fecha->month === now()->month && $fecha->year === now()->year) {
                    $finMes = now();
                }
                $queryEgresos->whereBetween('created_at', [$inicioMes, $finMes]);
                break;
            case 'año':
                $inicioAño = $fecha->copy()->startOfYear();
                $finAño = $fecha->copy()->endOfYear();
                if ($fecha->year === now()->year) {
                    $finAño = now();
                }
                $queryEgresos->whereBetween('created_at', [$inicioAño, $finAño]);
                break;
        }

        $egresos = $queryEgresos->orderBy('created_at', 'asc')->get();

        // Calcular totales de ingresos por método de pago
        $totalIngresos = $pagos->sum('monto');
        $totalEgresos = $egresos->sum('monto');
        
        $totales = [
            'efectivo' => $pagos->where('metodo_pago', 'efectivo')->sum('monto'),
            'tarjeta' => $pagos->where('metodo_pago', 'tarjeta')->sum('monto'),
            'transferencia' => $pagos->where('metodo_pago', 'transferencia')->sum('monto'),
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'balance' => $totalIngresos - $totalEgresos,
            'cantidad_pagos' => $pagos->count(),
            'cantidad_egresos' => $egresos->count(),
            // Legacy
            'total' => $totalIngresos,
        ];

        return response()->json([
            'tipo_corte' => $tipo,
            'fecha' => $fecha->toDateString(),
            'sucursal_id' => $sucursalId,
            'pagos' => $pagos,
            'egresos' => $egresos,
            'totales' => $totales,
        ]);
    }

    /**
     * Exportar corte a Excel (CSV)
     */
    public function exportarCorte(Request $request)
    {
        // Normalizar el tipo de manera muy agresiva
        $allInputs = $request->all();
        if (isset($allInputs['tipo'])) {
            if (is_array($allInputs['tipo'])) {
                $allInputs['tipo'] = !empty($allInputs['tipo']) ? (string) $allInputs['tipo'][0] : 'dia';
            } else {
                $allInputs['tipo'] = (string) $allInputs['tipo'];
            }
        }
        $request->replace($allInputs);
        
        $request->validate([
            'tipo' => 'required|in:dia,mes,año',
            'fecha' => 'required|date',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        if (! extension_loaded('zip')) {
            Log::error('exportarCorte: extensión PHP zip no cargada');

            return response()->json([
                'message' => 'El servidor no tiene habilitada la extensión PHP "zip", necesaria para generar archivos Excel. En Ubuntu/Debian: sudo apt install php-zip y reiniciar PHP-FPM.',
            ], 503);
        }

        $user = Auth::user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_efectiva_id;
        
        $tipo = $request->input('tipo');
        $fecha = Carbon::parse($request->fecha);
        $sucursalId = $request->sucursal_id ?? $user->sucursal_id;

        // Construir la consulta base
        $query = Pago::where('clinica_id', $clinicaId)
            ->with(['paciente', 'usuario', 'sucursal']);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        // Filtrar por rango de fechas según el tipo de corte
        switch ($tipo) {
            case 'dia':
                $query->byDate($fecha->toDateString());
                $etiquetaPeriodo = $fecha->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
                break;
            case 'mes':
                $inicioMes = $fecha->copy()->startOfMonth()->toDateString();
                $finMes = $fecha->copy()->endOfMonth()->toDateString();
                
                // Si es el mes actual, limitar hasta hoy
                if ($fecha->month === now()->month && $fecha->year === now()->year) {
                    $finMes = now()->toDateString();
                }
                
                $query->betweenDates($inicioMes, $finMes);
                $etiquetaPeriodo = $fecha->locale('es')->isoFormat('MMMM YYYY');
                break;
            case 'año':
                $inicioAño = $fecha->copy()->startOfYear()->toDateString();
                $finAño = $fecha->copy()->endOfYear()->toDateString();
                
                // Si es el año actual, limitar hasta hoy
                if ($fecha->year === now()->year) {
                    $finAño = now()->toDateString();
                }
                
                $query->betweenDates($inicioAño, $finAño);
                $etiquetaPeriodo = $fecha->year;
                break;
        }

        $pagos = $query->orderBy('created_at', 'asc')->get();

        // Obtener egresos del mismo período
        $queryEgresos = Egreso::where('clinica_id', $clinicaId);
        
        if ($sucursalId) {
            $queryEgresos->where('sucursal_id', $sucursalId);
        }
        
        switch ($tipo) {
            case 'dia':
                $queryEgresos->whereDate('created_at', $fecha->toDateString());
                break;
            case 'mes':
                $queryEgresos->whereBetween('created_at', [$inicioMes, $finMes]);
                break;
            case 'año':
                $queryEgresos->whereBetween('created_at', [$inicioAño, $finAño]);
                break;
        }
        
        $egresos = $queryEgresos->with(['usuario', 'sucursal'])->orderBy('created_at', 'asc')->get();

        // Crear spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Corte de Caja');

        // Obtener info de la clínica
        $clinica = Clinica::find($clinicaId);
        
        // === ENCABEZADO ===
        $row = 1;
        
        // Título principal con diseño corporativo
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", strtoupper((string) ($clinica->nombre ?? 'CORTE DE CAJA')));
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(35);
        $row++;
        
        // Período
        $sheet->mergeCells("A{$row}:L{$row}");
        $tipoDisplay = is_array($tipo) ? (isset($tipo[0]) ? $tipo[0] : 'dia') : $tipo;
        $sheet->setCellValue("A{$row}", 'Mes: ' . $etiquetaPeriodo);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '34495E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECF0F1']]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;
        
        // Fecha de generación
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'Generado: ' . now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm'));
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '7F8C8D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row += 2;

        // === SECCIÓN DE INGRESOS ===
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'INGRESOS (PAGOS)');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E5D8C']]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        // Encabezados de ingresos
        $headers = ['No. Recibo', 'Fecha', 'Hora', 'Paciente', 'Registro', 'Concepto', 'Método', 'Referencia', 'Monto', 'Recibió', 'Sucursal', 'Notas'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2874A6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '95A5A6']]]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Datos de ingresos
        $startDataRow = $row;
        foreach ($pagos as $pago) {
            $sheet->setCellValue('A' . $row, str_pad($pago->id, 6, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $pago->fecha_pago ?? Carbon::parse($pago->created_at)->format('Y-m-d'));
            $sheet->setCellValue('C' . $row, Carbon::parse($pago->created_at)->format('H:i:s'));
            $sheet->setCellValue('D' . $row, $this->excelSafeCellValue($pago->paciente ? "{$pago->paciente->nombre} {$pago->paciente->apellidoPat} {$pago->paciente->apellidoMat}" : 'N/A'));
            $sheet->setCellValue('E' . $row, $this->excelSafeCellValue($pago->paciente ? ($pago->paciente->registro ?? 'N/A') : 'N/A'));
            $sheet->setCellValue('F' . $row, $this->excelSafeCellValue($pago->concepto));
            $sheet->setCellValue('G' . $row, ucfirst((string) ($pago->metodo_pago ?? '')));
            $sheet->setCellValue('H' . $row, $this->excelSafeCellValue($pago->referencia));
            $sheet->setCellValue('I' . $row, (float) $pago->monto);
            $sheet->setCellValue('J' . $row, $this->excelSafeCellValue($pago->usuario ? "{$pago->usuario->nombre} {$pago->usuario->apellidoPat}" : 'N/A'));
            $sheet->setCellValue('K' . $row, $this->excelSafeCellValue($pago->sucursal ? $pago->sucursal->nombre : 'N/A'));
            $sheet->setCellValue('L' . $row, $this->excelSafeCellValue($pago->notas));
            
            // Formato moneda
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            $sheet->getStyle('I' . $row)->getFont()->setBold(true);
            
            // Alternar colores de fila
            if (($row - $startDataRow) % 2 == 0) {
                $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7F9FA']]
                ]);
            } else {
                $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
                ]);
            }
            
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5DBDB']]
                ],
                'font' => ['size' => 10]
            ]);
            
            $row++;
        }

        $row++;

        // Resumen de ingresos
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'RESUMEN DE INGRESOS');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3498DB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $metodosResumen = [
            ['metodo' => 'Efectivo', 'tipo' => 'efectivo'],
            ['metodo' => 'Tarjeta', 'tipo' => 'tarjeta'],
            ['metodo' => 'Transferencia', 'tipo' => 'transferencia']
        ];

        foreach ($metodosResumen as $metodo) {
            $cantidad = $pagos->where('metodo_pago', $metodo['tipo'])->count();
            $total = $pagos->where('metodo_pago', $metodo['tipo'])->sum(function($p) { return (float) $p->monto; });
            
            $sheet->setCellValue("A{$row}", $metodo['metodo']);
            $sheet->setCellValue("B{$row}", $cantidad);
            $sheet->setCellValue("C{$row}", (float) $total);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        $totalIngresos = $pagos->sum(function($p) { return (float) $p->monto; });
        $sheet->setCellValue("A{$row}", 'TOTAL INGRESOS');
        $sheet->setCellValue("B{$row}", $pagos->count());
        $sheet->setCellValue("C{$row}", (float) $totalIngresos);
        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E5D8C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '154360']]]
        ]);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $row += 2;

        // === SECCIÓN DE EGRESOS ===
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'EGRESOS (GASTOS/RETIROS)');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E74C3C']]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        // Encabezados de egresos
        $headersEgresos = ['No.', 'Fecha', 'Hora', 'Tipo', 'Concepto', 'Proveedor', 'Factura', '', 'Monto', 'Registró', 'Sucursal', 'Notas'];
        $col = 'A';
        foreach ($headersEgresos as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E67E22']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '95A5A6']]]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Datos de egresos
        $startDataRowEgresos = $row;
        foreach ($egresos as $egreso) {
            $sheet->setCellValue('A' . $row, str_pad($egreso->id, 6, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, Carbon::parse($egreso->created_at)->format('Y-m-d'));
            $sheet->setCellValue('C' . $row, Carbon::parse($egreso->created_at)->format('H:i:s'));
            $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', (string) ($egreso->tipo_egreso ?? ''))));
            $sheet->setCellValue('E' . $row, $this->excelSafeCellValue($egreso->concepto));
            $sheet->setCellValue('F' . $row, $this->excelSafeCellValue($egreso->proveedor));
            $sheet->setCellValue('G' . $row, $this->excelSafeCellValue($egreso->factura));
            $sheet->setCellValue('I' . $row, (float) $egreso->monto);
            $sheet->setCellValue('J' . $row, $this->excelSafeCellValue($egreso->usuario ? "{$egreso->usuario->nombre} {$egreso->usuario->apellidoPat}" : 'N/A'));
            $sheet->setCellValue('K' . $row, $this->excelSafeCellValue($egreso->sucursal ? $egreso->sucursal->nombre : 'N/A'));
            $sheet->setCellValue('L' . $row, $this->excelSafeCellValue($egreso->notas));
            
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            $sheet->getStyle('I' . $row)->getFont()->setBold(true);
            
            if (($row - $startDataRowEgresos) % 2 == 0) {
                $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDF2E9']]
                ]);
            } else {
                $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
                ]);
            }
            
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5DBDB']]],
                'font' => ['size' => 10]
            ]);
            
            $row++;
        }

        $row++;

        // Resumen de egresos
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'RESUMEN DE EGRESOS');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E67E22']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $tiposEgreso = [
            ['tipo' => 'Compra de Material', 'valor' => 'compra_material'],
            ['tipo' => 'Servicio', 'valor' => 'servicio'],
            ['tipo' => 'Mantenimiento', 'valor' => 'mantenimiento'],
            ['tipo' => 'Nómina', 'valor' => 'nomina'],
            ['tipo' => 'Renta', 'valor' => 'renta'],
            ['tipo' => 'Servicios Públicos', 'valor' => 'servicios_publicos'],
            ['tipo' => 'Otro', 'valor' => 'otro']
        ];

        foreach ($tiposEgreso as $tipoEgresoItem) {
            $cantidad = $egresos->where('tipo_egreso', $tipoEgresoItem['valor'])->count();
            $total = $egresos->where('tipo_egreso', $tipoEgresoItem['valor'])->sum(function($e) { return (float) $e->monto; });
            
            $sheet->setCellValue("A{$row}", $tipoEgresoItem['tipo']);
            $sheet->setCellValue("B{$row}", $cantidad);
            $sheet->setCellValue("C{$row}", (float) $total);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        $totalEgresos = $egresos->sum(function($e) { return (float) $e->monto; });
        $sheet->setCellValue("A{$row}", 'TOTAL EGRESOS');
        $sheet->setCellValue("B{$row}", $egresos->count());
        $sheet->setCellValue("C{$row}", (float) $totalEgresos);
        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E74C3C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'C0392B']]]
        ]);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $row += 2;

        // === BALANCE GENERAL ===
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'BALANCE GENERAL');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Ingresos (+)');
        $sheet->setCellValue("B{$row}", (float) $totalIngresos);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
            'font' => ['size' => 11, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'AED6F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Egresos (-)');
        $sheet->setCellValue("B{$row}", (float) $totalEgresos);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
            'font' => ['size' => 11, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5B7B1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);
        $row++;

        $balanceNeto = $totalIngresos - $totalEgresos;
        $sheet->setCellValue("A{$row}", 'BALANCE NETO');
        $sheet->setCellValue("B{$row}", (float) $balanceNeto);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $colorBalance = $balanceNeto >= 0 ? '1E5D8C' : 'E74C3C';
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorBalance]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '2C3E50']]]
        ]);

        // Ajustar anchos de columna
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(30);

        // Generar archivo (stream evita tempnam/sys_get_temp_dir en servidores con permisos u open_basedir restrictivos)
        $tipoStr = is_array($tipo) ? (isset($tipo[0]) ? $tipo[0] : 'dia') : $tipo;
        $filename = 'corte_'.$tipoStr.'_'.$fecha->format('Y-m-d').'.xlsx';

        try {
            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Throwable $e) {
            Log::error('exportarCorte: fallo al generar XLSX', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'No se pudo generar el archivo Excel. Comprueba que PHP tenga la extensión zip y espacio en disco.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Quita caracteres de control no válidos en XML (Excel falla en prod con datos binarios o NUL).
     */
    private function excelSafeCellValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value);

        return is_string($clean) ? $clean : '';
    }
}
