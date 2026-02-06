<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Paciente;
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
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'referencia' => 'nullable|string|max:255',
            'concepto' => 'nullable|string|max:500',
            'notas' => 'nullable|string',
            'firma_paciente' => 'required|string',
            'cita_id' => 'nullable|exists:citas,id',
        ]);

        $user = Auth::user();

        // Verificar que el paciente pertenece a la misma clínica
        $paciente = Paciente::findOrFail($request->paciente_id);
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json([
                'message' => 'No tienes permiso para registrar pagos de este paciente'
            ], 403);
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

        // Crear el pago
        $pago = Pago::create([
            'paciente_id' => $request->paciente_id,
            'clinica_id' => $user->clinica_id,
            'sucursal_id' => $user->sucursal_id,
            'user_id' => $user->id,
            'cita_id' => $request->cita_id,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'concepto' => $request->concepto,
            'notas' => $request->notas,
            'firma_paciente' => $request->firma_paciente,
        ]);

        // Cargar relaciones
        $pago->load(['paciente', 'usuario', 'cita']);

        return response()->json([
            'message' => 'Pago registrado exitosamente',
            'pago' => $pago
        ], 201);
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

        // Total por método de pago
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

        // Total general
        $todosPagos = Pago::where('clinica_id', $user->clinica_id)
            ->byDate($fecha)
            ->when($request->filled('sucursal_id'), function($query) use ($request) {
                $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $totalGeneral = $todosPagos->sum(function($pago) {
            return (float) $pago->monto;
        });

        $cantidadTotal = $todosPagos->count();

        // Últimos pagos del día
        $ultimosPagos = Pago::with(['paciente', 'usuario'])
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
            'total_general' => $totalGeneral,
            'cantidad_total' => $cantidadTotal,
            'ultimos_pagos' => $ultimosPagos
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

        $totalPeriodo = $pagos->sum(function($pago) {
            return (float) $pago->monto;
        });

        $dias = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
        $promedioDiario = $dias > 0 ? ($totalPeriodo / $dias) : 0;

        // Distribución por método de pago
        $porMetodo = $pagos->groupBy('metodo_pago')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum(function($pago) {
                    return (float) $pago->monto;
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

        return response()->json([
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'total_periodo' => $totalPeriodo,
            'promedio_diario' => round($promedioDiario, 2),
            'cantidad_pagos' => $pagos->count(),
            'por_metodo' => $porMetodo,
            'top_pacientes' => $topPacientes
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

        $pagos = Pago::with(['usuario', 'cita'])
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
        $nombrePaciente = trim(($pago->paciente->nombre ?? '') . ' ' . ($pago->paciente->apellidoPat ?? '') . ' ' . ($pago->paciente->apellidoMat ?? ''));
        $subject = $request->subject ?: ('Recibo de pago #' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . ' - ' . $nombrePaciente . ' - ' . ($clinica->nombre ?? 'CERCAP'));
        $mensaje = $request->message ?: 'Se adjunta el recibo de pago en PDF.';

        try {
            $pdf = Pdf::loadView('finanzas.recibo', compact('pago', 'clinica'));
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
}
