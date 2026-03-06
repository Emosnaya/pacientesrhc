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
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanzasController extends Controller
{
    /**
     * Registrar un nuevo pago
     */
    public function registrarPago(Request $request)
    {
        $user = Auth::user();
        $clinica = Clinica::find($user->clinica_id);
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
            if ($paciente->clinica_id !== $user->clinica_id) {
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

        $clinicaId = $paciente ? $paciente->clinica_id : $user->clinica_id;
        $sucursalId = $paciente
            ? $paciente->sucursal_id
            : ($user->isSuperAdmin && $request->filled('sucursal_id') ? $request->sucursal_id : $user->sucursal_id);

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
        $clinica = Clinica::find($user->clinica_id);
        $firmaObligatoria = !$clinica || $clinica->tipo_clinica !== 'rehabilitacion_cardiopulmonar';

        $pago = Pago::findOrFail($id);

        // Verificar permisos: solo el usuario que creó el pago o superadmin puede editar
        if ($pago->user_id !== $user->id && !$user->isSuperAdmin) {
            return response()->json([
                'message' => 'No tienes permiso para editar este pago'
            ], 403);
        }

        // Verificar que el pago pertenece a la misma clínica
        if ($pago->clinica_id !== $user->clinica_id) {
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
            if ($paciente->clinica_id !== $user->clinica_id) {
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

        $clinicaId = $paciente ? $paciente->clinica_id : $pago->clinica_id;
        $sucursalId = $paciente
            ? $paciente->sucursal_id
            : ($user->isSuperAdmin && $request->filled('sucursal_id') ? $request->sucursal_id : $pago->sucursal_id);

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
        if (!$user->isSuperAdmin) {
            return response()->json([
                'message' => 'Solo los administradores pueden eliminar pagos'
            ], 403);
        }

        // Verificar que el pago pertenece a la misma clínica
        if ($pago->clinica_id !== $user->clinica_id) {
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

        $sucursalId = $user->isSuperAdmin && $request->filled('sucursal_id')
            ? $request->sucursal_id
            : $user->sucursal_id;

        // Crear el egreso
        $egreso = Egreso::create([
            'clinica_id' => $user->clinica_id,
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
            
            $query = Egreso::where('clinica_id', $user->clinica_id)
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
                ->where('clinica_id', $user->clinica_id)
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
                ->where('clinica_id', $user->clinica_id)
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

        $query = Pago::with(['paciente', 'usuario', 'cita'])
            ->where('clinica_id', $user->clinica_id);

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

        $pago = Pago::with(['paciente', 'usuario', 'cita', 'sucursal'])
            ->where('clinica_id', $user->clinica_id)
            ->findOrFail($id);

        return response()->json($pago);
    }

    /**
     * Corte de caja diario
     */
    public function corteCajaDiario(Request $request)
    {
        $user = Auth::user();
        $fecha = $request->input('fecha', Carbon::today()->toDateString());

        // Total por método de pago (ingresos)
        $totalesPorMetodo = Pago::where('clinica_id', $user->clinica_id)
            ->byDate($fecha)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->select('metodo_pago', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('metodo_pago')
            ->get()
            ->map(function($item) {
                // Obtener el monto total desencriptado para este método
                $pagos = Pago::where('metodo_pago', $item->metodo_pago)
                    ->byDate(request()->input('fecha', Carbon::today()->toDateString()))
                    ->where('clinica_id', auth()->user()->clinica_id)
                    ->when(request()->filled('sucursal_id'), function($q) {
                        $q->where('sucursal_id', request()->sucursal_id);
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
        $todosPagos = Pago::where('clinica_id', $user->clinica_id)
            ->byDate($fecha)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalIngresos = $todosPagos->sum(function($pago) {
            return (float) $pago->monto;
        });

        $cantidadPagos = $todosPagos->count();

        // Total de egresos del día
        $todosEgresos = Egreso::where('clinica_id', $user->clinica_id)
            ->byDate($fecha)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalEgresos = $todosEgresos->sum(function($egreso) {
            return (float) $egreso->monto;
        });

        $cantidadEgresos = $todosEgresos->count();

        // Balance del día
        $balance = $totalIngresos - $totalEgresos;

        // Últimos pagos del día
        $ultimosPagos = Pago::with(['paciente', 'usuario', 'clinica', 'sucursal'])
            ->where('clinica_id', $user->clinica_id)
            ->byDate($fecha)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->latest()
            ->limit(10)
            ->get();

        // Últimos egresos del día
        $ultimosEgresos = Egreso::with(['usuario', 'clinica', 'sucursal'])
            ->where('clinica_id', $user->clinica_id)
            ->byDate($fecha)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'fecha' => $fecha,
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
        
        // Período (por defecto mes actual)
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

        $pagos = Pago::with('paciente')
            ->where('clinica_id', $user->clinica_id)
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
        $egresos = Egreso::where('clinica_id', $user->clinica_id)
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

        // Verificar que el paciente pertenece a la misma clínica
        $paciente = Paciente::where('clinica_id', $user->clinica_id)
            ->findOrFail($pacienteId);

        $pagos = Pago::with(['usuario', 'cita', 'clinica', 'sucursal'])
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
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
        $pago = Pago::with(['paciente', 'usuario', 'sucursal', 'clinica'])
            ->where('clinica_id', $user->clinica_id)
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
        $pago = Pago::with(['paciente', 'usuario', 'sucursal', 'clinica'])
            ->where('clinica_id', $user->clinica_id)
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
                $logoPath = public_path('img/logo.png');
                if (file_exists($logoPath)) {
                    $imageData = file_get_contents($logoPath);
                    $mimeType = mime_content_type($logoPath);
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
                return null;
            }
            
            $clinica = $user->clinica;
            $logoPath = null;
            
            if ($clinica && $clinica->logo) {
                $logoPath = storage_path('app/public/' . $clinica->logo);
            }
            
            // Si no existe el logo de la clínica, usar el logo por defecto
            if (!$logoPath || !file_exists($logoPath)) {
                $logoPath = public_path('img/logo.png');
            }
            
            // Si tampoco existe el logo por defecto, retornar null
            if (!file_exists($logoPath)) {
                \Log::error('Logo no encontrado: ' . $logoPath);
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

        $pago = Pago::where('clinica_id', $user->clinica_id)
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
        $tipo = $request->tipo;
        $fecha = Carbon::parse($request->fecha);
        $sucursalId = $request->sucursal_id ?? $user->sucursal_id;

        // Construir la consulta base
        $query = Pago::where('clinica_id', $user->clinica_id)
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
        $queryEgresos = Egreso::where('clinica_id', $user->clinica_id)
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
        $request->validate([
            'tipo' => 'required|in:dia,mes,año',
            'fecha' => 'required|date',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $user = Auth::user();
        $tipo = $request->tipo;
        $fecha = Carbon::parse($request->fecha);
        $sucursalId = $request->sucursal_id ?? $user->sucursal_id;

        // Construir la consulta base
        $query = Pago::where('clinica_id', $user->clinica_id)
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

        // Generar CSV
        $filename = "corte_{$tipo}_{$fecha->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($pagos, $tipo, $fecha, $user, $sucursalId) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'No. Recibo',
                'Fecha',
                'Hora',
                'Paciente',
                'Registro Paciente',
                'Concepto',
                'Método de Pago',
                'Referencia',
                'Monto',
                'Recibió',
                'Sucursal',
                'Notas'
            ]);

            // Datos
            foreach ($pagos as $pago) {
                fputcsv($file, [
                    str_pad($pago->id, 6, '0', STR_PAD_LEFT),
                    $pago->fecha_pago ?? Carbon::parse($pago->created_at)->format('Y-m-d'),
                    Carbon::parse($pago->created_at)->format('H:i:s'),
                    $pago->paciente ? "{$pago->paciente->nombre} {$pago->paciente->apellidoPat} {$pago->paciente->apellidoMat}" : 'N/A',
                    $pago->paciente->registro ?? 'N/A',
                    $pago->concepto ?? '',
                    ucfirst($pago->metodo_pago),
                    $pago->referencia ?? '',
                    number_format($pago->monto, 2, '.', ''),
                    $pago->usuario ? "{$pago->usuario->nombre} {$pago->usuario->apellidoPat}" : 'N/A',
                    $pago->sucursal->nombre ?? 'N/A',
                    $pago->notas ?? ''
                ]);
            }

            // Separador y totales de Ingresos
            fputcsv($file, []);
            fputcsv($file, []);
            fputcsv($file, ['=== RESUMEN DE INGRESOS ===']);
            fputcsv($file, []);
            fputcsv($file, ['Método de Pago', 'Cantidad', 'Total']);
            fputcsv($file, ['Efectivo', $pagos->where('metodo_pago', 'efectivo')->count(), number_format($pagos->where('metodo_pago', 'efectivo')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Tarjeta', $pagos->where('metodo_pago', 'tarjeta')->count(), number_format($pagos->where('metodo_pago', 'tarjeta')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Transferencia', $pagos->where('metodo_pago', 'transferencia')->count(), number_format($pagos->where('metodo_pago', 'transferencia')->sum('monto'), 2, '.', '')]);
            fputcsv($file, []);
            $totalIngresos = $pagos->sum('monto');
            fputcsv($file, ['TOTAL INGRESOS', $pagos->count(), number_format($totalIngresos, 2, '.', '')]);

            // Obtener egresos del mismo período
            $queryEgresos = Egreso::where('clinica_id', $user->clinica_id);
            
            if ($sucursalId) {
                $queryEgresos->where('sucursal_id', $sucursalId);
            }
            
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
            
            $egresos = $queryEgresos->with(['usuario', 'sucursal'])->orderBy('created_at', 'asc')->get();

            // Sección de Egresos
            fputcsv($file, []);
            fputcsv($file, []);
            fputcsv($file, ['=== EGRESOS (GASTOS/RETIROS) ===']);
            fputcsv($file, []);
            fputcsv($file, ['No.', 'Fecha', 'Hora', 'Tipo', 'Concepto', 'Proveedor', 'Factura', '', 'Monto', 'Registró', 'Sucursal', 'Notas']);
            
            foreach ($egresos as $egreso) {
                fputcsv($file, [
                    str_pad($egreso->id, 6, '0', STR_PAD_LEFT),
                    Carbon::parse($egreso->created_at)->format('Y-m-d'),
                    Carbon::parse($egreso->created_at)->format('H:i:s'),
                    ucfirst(str_replace('_', ' ', $egreso->tipo_egreso)),
                    $egreso->concepto ?? '',
                    $egreso->proveedor ?? '',
                    $egreso->factura ?? '',
                    '', // Columna vacía para alinear con estructura de ingresos
                    number_format($egreso->monto, 2, '.', ''),
                    $egreso->usuario ? "{$egreso->usuario->nombre} {$egreso->usuario->apellidoPat}" : 'N/A',
                    $egreso->sucursal->nombre ?? 'N/A',
                    $egreso->notas ?? ''
                ]);
            }

            // Totales de Egresos por tipo
            fputcsv($file, []);
            fputcsv($file, []);
            fputcsv($file, ['=== RESUMEN DE EGRESOS ===']);
            fputcsv($file, []);
            fputcsv($file, ['Tipo de Egreso', 'Cantidad', 'Total']);
            fputcsv($file, ['Compra de Material', $egresos->where('tipo_egreso', 'compra_material')->count(), number_format($egresos->where('tipo_egreso', 'compra_material')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Servicio', $egresos->where('tipo_egreso', 'servicio')->count(), number_format($egresos->where('tipo_egreso', 'servicio')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Mantenimiento', $egresos->where('tipo_egreso', 'mantenimiento')->count(), number_format($egresos->where('tipo_egreso', 'mantenimiento')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Nómina', $egresos->where('tipo_egreso', 'nomina')->count(), number_format($egresos->where('tipo_egreso', 'nomina')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Renta', $egresos->where('tipo_egreso', 'renta')->count(), number_format($egresos->where('tipo_egreso', 'renta')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Servicios Públicos', $egresos->where('tipo_egreso', 'servicios_publicos')->count(), number_format($egresos->where('tipo_egreso', 'servicios_publicos')->sum('monto'), 2, '.', '')]);
            fputcsv($file, ['Otro', $egresos->where('tipo_egreso', 'otro')->count(), number_format($egresos->where('tipo_egreso', 'otro')->sum('monto'), 2, '.', '')]);
            fputcsv($file, []);
            $totalEgresos = $egresos->sum('monto');
            fputcsv($file, ['TOTAL EGRESOS', $egresos->count(), number_format($totalEgresos, 2, '.', '')]);

            // Balance General
            fputcsv($file, []);
            fputcsv($file, []);
            fputcsv($file, ['=== BALANCE GENERAL ===']);
            fputcsv($file, []);
            fputcsv($file, ['Concepto', 'Monto']);
            fputcsv($file, ['Total Ingresos (+)', number_format($totalIngresos, 2, '.', '')]);
            fputcsv($file, ['Total Egresos (-)', number_format($totalEgresos, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['BALANCE NETO', number_format($totalIngresos - $totalEgresos, 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
