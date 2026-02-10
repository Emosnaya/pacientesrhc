<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Transcribir audio a texto
     */
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,webm,m4a,ogg|max:10240', // Max 10MB
        ]);

        try {
            $audio = $request->file('audio');
            $path = $audio->store('temp-audio', 'local');
            $fullPath = Storage::path($path);

            Log::info('🎤 Transcribiendo audio', ['path' => $fullPath]);

            $result = $this->aiService->transcribeAudio($fullPath);

            // Eliminar archivo temporal
            Storage::delete($path);

            if ($result['success']) {
                Log::info('✅ Transcripción exitosa');
                return response()->json([
                    'success' => true,
                    'text' => $result['text']
                ]);
            } else {
                Log::error('❌ Error en transcripción');
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en transcripción: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar el audio'
            ], 500);
        }
    }

    /**
     * Autocompletar texto
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:3',
            'context' => 'nullable|string',
            'tipo_reporte' => 'nullable|string|in:nutri,psico,fisio,general'
        ]);

        try {
            $text = $request->input('text');
            $context = $request->input('context', '');
            $tipoReporte = $request->input('tipo_reporte', 'general');

            Log::info('✨ Generando autocompletado', [
                'tipo' => $tipoReporte,
                'text_length' => strlen($text)
            ]);

            $result = $this->aiService->autocompleteText($text, $context, $tipoReporte);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'completion' => $result['completion']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en autocompletado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al generar autocompletado'
            ], 500);
        }
    }

    /**
     * Resumir texto
     */
    public function summarize(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:50',
            'tipo_reporte' => 'nullable|string|in:nutri,psico,fisio,general'
        ]);

        try {
            $text = $request->input('text');
            $tipoReporte = $request->input('tipo_reporte', 'general');

            Log::info('📝 Generando resumen', [
                'tipo' => $tipoReporte,
                'text_length' => strlen($text)
            ]);

            $result = $this->aiService->summarizeText($text, $tipoReporte);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'summary' => $result['summary']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en resumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al generar resumen'
            ], 500);
        }
    }

    /**
     * Chat médico - Asistente virtual
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_history' => 'nullable|array|max:10'
        ]);

        $user = Auth::user();
        
        // Verificar límite diario desde .env (99999 en dev, 50 en prod)
        $limiteChat = env('AI_CHAT_LIMIT', 50);
        $hoy = now()->startOfDay();
        $usosHoy = DB::table('ai_usage')
            ->where('user_id', $user->id)
            ->where('feature_type', 'chat')
            ->where('created_at', '>=', $hoy)
            ->count();

        if ($usosHoy >= $limiteChat) {
            return response()->json([
                'success' => false,
                'error' => 'Has alcanzado el límite de consultas diarias. Intenta de nuevo mañana.',
                'limit_reached' => true
            ], 429);
        }

        try {
            $message = $request->input('message');
            $history = $request->input('conversation_history', []);

            // Obtener información contextual del usuario y su clínica
            $clinicaId = $user->clinica_id;
            $clinica = $user->clinica; // Obtener la clínica completa
            
            // Citas próximas de la clínica (próximos 7 días) - INCLUIR ID
            $citasProximas = DB::table('citas')
                ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
                ->where('citas.clinica_id', $clinicaId)
                ->where('citas.estado', '!=', 'cancelada')
                ->where('citas.fecha', '>=', now()->toDateString())
                ->where('citas.fecha', '<=', now()->addDays(7)->toDateString())
                ->select('citas.id', 'citas.fecha', 'citas.hora', 'pacientes.nombre', 'pacientes.apellidoPat', 'citas.estado')
                ->orderBy('citas.fecha')
                ->orderBy('citas.hora')
                ->limit(20)
                ->get();

            // Estadísticas de la clínica
            $totalPacientes = DB::table('pacientes')
                ->where('clinica_id', $clinicaId)
                ->count();
                
            $citasHoy = DB::table('citas')
                ->where('clinica_id', $clinicaId)
                ->where('fecha', now()->toDateString())
                ->where('estado', '!=', 'cancelada')
                ->count();

            $contextoClinica = [
                'clinica_id' => $clinicaId,
                'tipo_clinica' => $clinica->tipo_clinica ?? 'rehabilitacion_cardiopulmonar',
                'total_pacientes' => $totalPacientes,
                'citas_hoy' => $citasHoy,
                'citas_proximas' => $citasProximas->map(function($cita) {
                    return [
                        'id' => $cita->id,  // Incluir ID para acciones
                        'fecha' => $cita->fecha,
                        'hora' => $cita->hora,
                        'paciente' => $cita->nombre . ' ' . $cita->apellidoPat,
                        'estado' => $cita->estado
                    ];
                })->toArray()
            ];

            Log::info('💬 Chat médico', [
                'user_id' => $user->id,
                'clinica_id' => $clinicaId,
                'usos_hoy' => $usosHoy
            ]);

            $result = $this->aiService->medicalChat($message, $history, $contextoClinica);

            if ($result['success']) {
                // Registrar uso
                DB::table('ai_usage')->insert([
                    'user_id' => $user->id,
                    'feature_type' => 'chat',
                    'tokens_used' => $result['tokens_used'] ?? 0,
                    'prompt' => $message,
                    'response' => $result['response'],
                    'model_used' => $result['model'] ?? 'gemini-pro',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'response' => $result['response'],
                    'model_used' => $result['model'] ?? 'unknown'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en chat médico: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la consulta'
            ], 500);
        }
    }

    /**
     * Obtener contexto proactivo para el asistente (citas del día, alertas, etc.)
     */
    public function getContext(Request $request)
    {
        try {
            $user = Auth::user();
            $clinicaId = $user->clinica_id;
            $tipoClinica = $user->clinica->tipo_clinica ?? 'rehabilitacion_cardiopulmonar';
            
            // Citas de hoy - obtener IDs primero
            $hoy = now()->toDateString();
            $citasHoyIds = DB::table('citas')
                ->where('citas.clinica_id', $clinicaId)
                ->where('citas.fecha', $hoy)
                ->where('citas.estado', '!=', 'cancelada')
                ->pluck('id');
            
            $countCitasHoy = $citasHoyIds->count();
            
            // Próxima cita (más cercana) - obtener ID
            $ahora = now();
            $proximaCitaId = DB::table('citas')
                ->where('citas.clinica_id', $clinicaId)
                ->where('citas.estado', '!=', 'cancelada')
                ->where(function($query) use ($hoy, $ahora) {
                    $query->where('citas.fecha', '>', $hoy)
                        ->orWhere(function($q) use ($hoy, $ahora) {
                            $q->where('citas.fecha', $hoy)
                              ->where('citas.hora', '>=', $ahora->format('H:i:s'));
                        });
                })
                ->orderBy('citas.fecha')
                ->orderBy('citas.hora')
                ->value('id');
            
            // Obtener próxima cita con relación para desencriptar
            $proximaCita = null;
            if ($proximaCitaId) {
                $citaModel = \App\Models\Cita::with('paciente')->find($proximaCitaId);
                if ($citaModel && $citaModel->paciente) {
                    $proximaCita = (object)[
                        'hora' => substr($citaModel->hora, 0, 5),
                        'nombre' => $citaModel->paciente->nombre,
                        'apellidoPat' => $citaModel->paciente->apellidoPat
                    ];
                }
            }
            
            // Generar alertas proactivas específicas según tipo de clínica
            $alertas = [];
            
            // Alertas comunes a todas las clínicas
            if ($countCitasHoy === 0) {
                $alertas[] = "No tienes citas programadas hoy. ¿Quieres revisar tu agenda de la semana?";
            }
            
            // Pacientes sin visita reciente (alertas específicas por tipo de clínica)
            $diasSinVisita = match($tipoClinica) {
                'dental' => 180, // 6 meses
                'rehabilitacion_cardiopulmonar' => 21, // 3 semanas
                'fisioterapia' => 14, // 2 semanas
                'psicologia' => 14, // 2 semanas
                'nutricion' => 21, // 3 semanas
                default => 30
            };
            
            // Obtener IDs de pacientes sin visita reciente
            $pacientesIds = DB::table('pacientes')
                ->leftJoin('citas', function($join) use ($clinicaId) {
                    $join->on('pacientes.id', '=', 'citas.paciente_id')
                         ->where('citas.clinica_id', $clinicaId)
                         ->where('citas.estado', 'completada');
                })
                ->where('pacientes.clinica_id', $clinicaId)
                ->select('pacientes.id', DB::raw('MAX(citas.fecha) as ultima_cita'))
                ->groupBy('pacientes.id')
                ->havingRaw("MAX(citas.fecha) IS NULL OR MAX(citas.fecha) < DATE_SUB(NOW(), INTERVAL ? DAY)", [$diasSinVisita])
                ->limit(3)
                ->pluck('id');
            
            if ($pacientesIds->count() > 0) {
                // Usar el modelo Paciente para desencriptar automáticamente
                $pacientesSinVisita = \App\Models\Paciente::whereIn('id', $pacientesIds)->get();
                
                $nombreClinica = match($tipoClinica) {
                    'dental' => 'limpieza dental',
                    'rehabilitacion_cardiopulmonar' => 'sesión de rehabilitación',
                    'fisioterapia' => 'sesión de fisioterapia',
                    'psicologia' => 'sesión de terapia',
                    'nutricion' => 'consulta nutricional',
                    default => 'consulta'
                };
                
                $paciente = $pacientesSinVisita->first();
                $nombreCompleto = $paciente->nombre . ' ' . $paciente->apellidoPat;
                $diasTexto = $diasSinVisita >= 30 ? round($diasSinVisita/30) . ' meses' : $diasSinVisita . ' días';
                
                $alertas[] = "{$nombreCompleto} lleva más de {$diasTexto} sin {$nombreClinica}. ¿Quieres contactarle?";
            }
            
            // Citas pendientes de confirmar (próximos 3 días)
            $citasPendientes = DB::table('citas')
                ->where('clinica_id', $clinicaId)
                ->where('estado', 'pendiente')
                ->where('fecha', '>=', $hoy)
                ->where('fecha', '<=', now()->addDays(3)->toDateString())
                ->count();
            
            if ($citasPendientes > 0) {
                $alertas[] = "Tienes {$citasPendientes} cita" . ($citasPendientes > 1 ? 's' : '') . " pendiente" . ($citasPendientes > 1 ? 's' : '') . " de confirmar en los próximos 3 días.";
            }
            
            // Formatear próxima cita
            $proximaCitaFormatted = null;
            if ($proximaCita) {
                $hora = substr($proximaCita->hora, 0, 5); // HH:MM
                $pacienteNombre = $proximaCita->nombre . ' ' . $proximaCita->apellidoPat;
                $proximaCitaFormatted = [
                    'hora' => $hora,
                    'paciente' => $pacienteNombre
                ];
            }
            
            // Obtener próximas 5 citas para notificaciones
            $citasProximas = \App\Models\Cita::with('paciente')
                ->where('clinica_id', $clinicaId)
                ->where('estado', '!=', 'cancelada')
                ->where(function($query) use ($hoy, $ahora) {
                    $query->where('fecha', '>', $hoy)
                        ->orWhere(function($q) use ($hoy, $ahora) {
                            $q->where('fecha', $hoy)
                              ->where('hora', '>=', $ahora->format('H:i:s'));
                        });
                })
                ->orderBy('fecha')
                ->orderBy('hora')
                ->limit(5)
                ->get()
                ->map(function($cita) {
                    return [
                        'id' => $cita->id,
                        'fecha' => $cita->fecha,
                        'hora' => $cita->hora,
                        'paciente' => $cita->paciente->nombre . ' ' . $cita->paciente->apellidoPat,
                        'estado' => $cita->estado
                    ];
                });
            
            return response()->json([
                'success' => true,
                'context' => [
                    'citas_hoy' => $countCitasHoy,
                    'proxima_cita' => $proximaCitaFormatted,
                    'citas_proximas' => $citasProximas,
                    'alertas' => $alertas,
                    'tipo_clinica' => $tipoClinica
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo contexto proactivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener contexto'
            ], 500);
        }
    }

    /**
     * Ejecutar acción solicitada por el asistente
     */
    public function executeAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'params' => 'nullable|array'
        ]);

        $user = Auth::user();
        $action = $request->input('action');
        $params = $request->input('params', []);

        try {
            switch ($action) {
                case 'cambiar_estado_cita':
                    return $this->cambiarEstadoCita($user, $params);
                
                case 'cancelar_cita':
                    return $this->cancelarCita($user, $params);
                
                case 'eliminar_cita':
                    return $this->eliminarCita($user, $params);
                
                case 'eliminar_citas_masivo':
                    return $this->eliminarCitasMasivo($user, $params);
                
                case 'agendar_cita':
                    return $this->agendarCita($user, $params);
                
                case 'buscar_paciente':
                    return $this->buscarPaciente($user, $params);
                
                case 'crear_evento':
                    return $this->crearEvento($user, $params);
                
                case 'obtener_metricas':
                    return $this->obtenerMetricas($user);
                
                case 'obtener_analiticas_pacientes':
                    return $this->obtenerAnaliticasPacientes($user);
                
                case 'analizar_paciente':
                    return $this->analizarPaciente($user, $params);
                
                case 'contar_citas_paciente':
                    return $this->contarCitasPaciente($user, $params);
                
                // ===== ANÁLISIS Y REPORTES AVANZADOS =====
                case 'generar_reporte_metricas':
                    return $this->generarReporteMetricas($user, $params);
                
                case 'analisis_predictivo_citas':
                    return $this->analisisPredictivoCitas($user);
                
                case 'identificar_pacientes_riesgo':
                    return $this->identificarPacientesRiesgo($user);
                
                case 'sugerencias_mejora_operativa':
                    return $this->sugerenciasMejoraOperativa($user);
                
                // ===== NOTIFICACIONES INTELIGENTES =====
                case 'generar_resumen_diario':
                    return $this->generarResumenDiario($user);
                
                case 'obtener_alertas_seguimiento':
                    return $this->obtenerAlertasSeguimiento($user);
                
                case 'sugerencias_proactivas':
                    return $this->sugerenciasProactivas($user);
                
                // ===== EXPEDIENTES CLÍNICOS =====
                case 'crear_expediente':
                    return $this->crearExpediente($user, $params);
                
                case 'editar_expediente':
                    return $this->editarExpediente($user, $params);
                
                case 'generar_reporte_clinico':
                    return $this->generarReporteClinico($user, $params);
                
                case 'buscar_en_expedientes':
                    return $this->buscarEnExpedientes($user, $params);
                
                case 'comparar_expedientes':
                    return $this->compararExpedientes($user, $params);
                
                case 'obtener_expediente':
                    return $this->obtenerExpediente($user, $params);
                
                // ===== GESTIÓN FINANCIERA =====
                case 'obtener_corte_caja':
                    return $this->obtenerCorteCaja($user, $params);
                
                case 'consultar_adeudos':
                    return $this->consultarAdeudos($user, $params);
                
                case 'resumen_ingresos_mensual':
                    return $this->resumenIngresosMensual($user, $params);
                
                case 'verificar_pago_firmado':
                    return $this->verificarPagoFirmado($user, $params);
                
                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Acción no reconocida'
                    ], 400);
            }
        } catch (\Exception $e) {
            Log::error("❌ Error ejecutando acción {$action}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al ejecutar la acción'
            ], 500);
        }
    }

    private function cambiarEstadoCita($user, $params)
    {
        if (!isset($params['cita_id']) || !isset($params['estado'])) {
            return response()->json(['success' => false, 'error' => 'Parámetros faltantes'], 400);
        }

        $cita = DB::table('citas')
            ->where('id', $params['cita_id'])
            ->where('clinica_id', $user->clinica_id)
            ->first();

        if (!$cita) {
            return response()->json(['success' => false, 'error' => 'Cita no encontrada'], 404);
        }

        DB::table('citas')
            ->where('id', $params['cita_id'])
            ->update(['estado' => $params['estado'], 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de cita actualizado exitosamente'
        ]);
    }

    private function cancelarCita($user, $params)
    {
        if (!isset($params['cita_id'])) {
            return response()->json(['success' => false, 'error' => 'ID de cita requerido'], 400);
        }

        $cita = DB::table('citas')
            ->where('id', $params['cita_id'])
            ->where('clinica_id', $user->clinica_id)
            ->first();

        if (!$cita) {
            return response()->json(['success' => false, 'error' => 'Cita no encontrada'], 404);
        }

        $motivo = $params['motivo'] ?? 'Cancelado por asistente virtual';

        DB::table('citas')
            ->where('id', $params['cita_id'])
            ->update([
                'estado' => 'cancelada',
                'motivo_cancelacion' => $motivo,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Cita cancelada exitosamente'
        ]);
    }

    private function eliminarCita($user, $params)
    {
        if (!isset($params['cita_id'])) {
            return response()->json(['success' => false, 'error' => 'ID de cita requerido'], 400);
        }

        $cita = DB::table('citas')
            ->where('id', $params['cita_id'])
            ->where('clinica_id', $user->clinica_id)
            ->first();

        if (!$cita) {
            return response()->json(['success' => false, 'error' => 'Cita no encontrada'], 404);
        }

        DB::table('citas')->where('id', $params['cita_id'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cita eliminada exitosamente'
        ]);
    }

    private function eliminarCitasMasivo($user, $params)
    {
        // Construir query base
        $query = DB::table('citas')
            ->where('clinica_id', $user->clinica_id);

        $filtrosAplicados = [];

        // Filtro por estado
        if (isset($params['estado']) && !empty($params['estado'])) {
            $query->where('estado', $params['estado']);
            $filtrosAplicados[] = "estado '{$params['estado']}'";
        }

        // Filtro por paciente (búsqueda flexible)
        if (isset($params['paciente_nombre']) && !empty($params['paciente_nombre'])) {
            // Función para normalizar texto
            $normalizar = function($texto) {
                $texto = mb_strtolower($texto, 'UTF-8');
                $texto = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                    ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
                    $texto
                );
                return trim($texto);
            };
            
            $nombreBuscar = $normalizar($params['paciente_nombre']);
            
            // Obtener todos los pacientes usando Eloquent para desencriptar nombres
            $pacientes = \App\Models\Paciente::where('clinica_id', $user->clinica_id)->get();
            
            Log::info('🔍 Buscando paciente para eliminar citas', [
                'nombre_buscado' => $params['paciente_nombre'],
                'total_pacientes' => $pacientes->count()
            ]);
            
            // Buscar paciente con similitud
            $pacienteEncontrado = null;
            $mejorSimilitud = 0;
            
            foreach ($pacientes as $p) {
                $nombreCompletoPaciente = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
                $nombrePaciente = $normalizar($p->nombre);
                
                $similitud = 0;
                similar_text($nombreCompletoPaciente, $nombreBuscar, $similitud);
                
                Log::info("  👤 Comparando: {$p->nombre} {$p->apellidoPat}", [
                    'similitud' => round($similitud, 2)
                ]);
                
                // Aceptar similitud >= 70%
                if ($similitud >= 70 && $similitud > $mejorSimilitud) {
                    $mejorSimilitud = $similitud;
                    $pacienteEncontrado = $p;
                }
            }

            if (!$pacienteEncontrado) {
                Log::warning("❌ Paciente no encontrado: {$params['paciente_nombre']}");
                return response()->json([
                    'success' => false,
                    'error' => "No se encontró ningún paciente similar a '{$params['paciente_nombre']}' en tu clínica. Verifica el nombre."
                ], 404);
            }
            
            Log::info("✅ Paciente encontrado para eliminar citas", [
                'nombre' => $pacienteEncontrado->nombre . ' ' . $pacienteEncontrado->apellidoPat,
                'similitud' => round($mejorSimilitud, 2) . '%'
            ]);

            $query->where('paciente_id', $pacienteEncontrado->id);
            $filtrosAplicados[] = "paciente '{$pacienteEncontrado->nombre} {$pacienteEncontrado->apellidoPat}'";
        }

        // Filtro por fecha específica
        if (isset($params['fecha']) && !empty($params['fecha'])) {
            $query->where('fecha', $params['fecha']);
            $filtrosAplicados[] = "fecha {$params['fecha']}";
        }

        // Filtro por rango de fechas
        if (isset($params['fecha_inicio']) && !empty($params['fecha_inicio'])) {
            $query->where('fecha', '>=', $params['fecha_inicio']);
            $filtrosAplicados[] = "desde {$params['fecha_inicio']}";
        }
        if (isset($params['fecha_fin']) && !empty($params['fecha_fin'])) {
            $query->where('fecha', '<=', $params['fecha_fin']);
            $filtrosAplicados[] = "hasta {$params['fecha_fin']}";
        }

        // Filtro por mes y año
        if (isset($params['mes']) && isset($params['año'])) {
            $mes = str_pad($params['mes'], 2, '0', STR_PAD_LEFT);
            $query->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = '{$params['año']}-{$mes}'");
            $filtrosAplicados[] = "mes {$params['mes']}/{$params['año']}";
        }

        // Validar que se aplicó al menos un filtro
        if (empty($filtrosAplicados)) {
            return response()->json([
                'success' => false,
                'error' => 'Debes especificar al menos un filtro (estado, paciente, fecha, etc.)'
            ], 400);
        }

        // Contar cuántas se van a eliminar (para confirmación)
        $count = $query->count();

        if ($count === 0) {
            return response()->json([
                'success' => true,
                'message' => 'No se encontraron citas con los filtros especificados.',
                'count' => 0,
                'filtros' => implode(', ', $filtrosAplicados)
            ]);
        }

        // Obtener vista previa de las citas a eliminar
        $citasPreview = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->where('citas.clinica_id', $user->clinica_id);

        // Aplicar los mismos filtros para preview
        if (isset($params['estado']) && !empty($params['estado'])) {
            $citasPreview->where('citas.estado', $params['estado']);
        }
        if (isset($pacienteEncontrado)) {
            $citasPreview->where('citas.paciente_id', $pacienteEncontrado->id);
        }
        if (isset($params['fecha']) && !empty($params['fecha'])) {
            $citasPreview->where('citas.fecha', $params['fecha']);
        }
        if (isset($params['fecha_inicio']) && !empty($params['fecha_inicio'])) {
            $citasPreview->where('citas.fecha', '>=', $params['fecha_inicio']);
        }
        if (isset($params['fecha_fin']) && !empty($params['fecha_fin'])) {
            $citasPreview->where('citas.fecha', '<=', $params['fecha_fin']);
        }
        if (isset($params['mes']) && isset($params['año'])) {
            $mes = str_pad($params['mes'], 2, '0', STR_PAD_LEFT);
            $citasPreview->whereRaw("DATE_FORMAT(citas.fecha, '%Y-%m') = '{$params['año']}-{$mes}'");
        }

        $preview = $citasPreview
            ->select(
                'citas.id',
                'citas.fecha',
                'citas.hora',
                'citas.estado',
                DB::raw('CONCAT(pacientes.nombre, " ", pacientes.apellidoPat) as paciente_nombre')
            )
            ->orderBy('citas.fecha')
            ->orderBy('citas.hora')
            ->limit(10)
            ->get();

        // Eliminar las citas
        $query->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$count} citas exitosamente.",
            'count' => $count,
            'filtros' => implode(', ', $filtrosAplicados),
            'preview' => $preview->take(5)->map(function($cita) {
                return "{$cita->fecha} {$cita->hora} - {$cita->paciente_nombre} ({$cita->estado})";
            })->toArray()
        ]);
    }

    private function agendarCita($user, $params)
    {
        // Validar parámetros requeridos
        if (!isset($params['paciente_nombre']) || !isset($params['fecha']) || !isset($params['hora'])) {
            return response()->json([
                'success' => false, 
                'error' => 'Nombre del paciente, fecha y hora son requeridos'
            ], 400);
        }

        // Formatear hora al formato HH:MM si solo viene el número
        $hora = $params['hora'];
        if (is_numeric($hora) || !str_contains($hora, ':')) {
            $hora = str_pad($hora, 2, '0', STR_PAD_LEFT) . ':00';
        }
        // Asegurar formato HH:MM
        if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
            return response()->json([
                'success' => false,
                'error' => 'Formato de hora inválido. Use HH:MM (ej: 14:00)'
            ], 400);
        }

        // Buscar paciente por nombre completo (búsqueda flexible sin acentos ni mayúsculas)
        $nombreCompleto = $params['paciente_nombre'];
        
        // Función para normalizar texto (quitar acentos y convertir a minúsculas)
        $normalizar = function($texto) {
            $texto = mb_strtolower($texto, 'UTF-8');
            $texto = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
                $texto
            );
            return trim($texto);
        };
        
        $nombreBuscar = $normalizar($nombreCompleto);
        
        // Obtener todos los pacientes de la clínica usando Eloquent para desencriptar nombres
        $pacientes = \App\Models\Paciente::where('clinica_id', $user->clinica_id)->get();
        
        Log::info('🔍 Buscando paciente', [
            'nombre_buscado' => $nombreCompleto,
            'nombre_normalizado' => $nombreBuscar,
            'total_pacientes' => $pacientes->count()
        ]);
        
        // Buscar paciente normalizando nombres con lógica mejorada
        $paciente = null;
        $coincidencias = [];
        
        foreach ($pacientes as $p) {
            $nombreCompletoPaciente = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
            $nombreApellidoPat = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat));
            $soloNombre = $normalizar(trim($p->nombre));
            
            // Calcular porcentajes de similitud
            $similaridadCompleta = 0;
            $similaridadApellido = 0;
            $similaridadNombre = 0;
            
            similar_text($nombreCompletoPaciente, $nombreBuscar, $similaridadCompleta);
            similar_text($nombreApellidoPat, $nombreBuscar, $similaridadApellido);
            similar_text($soloNombre, $nombreBuscar, $similaridadNombre);
            
            $mejorSimilaridad = max($similaridadCompleta, $similaridadApellido, $similaridadNombre);
            
            Log::info("  👤 Comparando: {$p->nombre} {$p->apellidoPat}", [
                'nombre_completo' => $nombreCompletoPaciente,
                'similitud_completa' => round($similaridadCompleta, 2),
                'similitud_apellido' => round($similaridadApellido, 2),
                'similitud_nombre' => round($similaridadNombre, 2),
                'mejor_similitud' => round($mejorSimilaridad, 2)
            ]);
            
            // Guardar coincidencias con >= 70% de similitud
            if ($mejorSimilaridad >= 70) {
                $coincidencias[] = [
                    'paciente' => $p,
                    'similitud' => $mejorSimilaridad
                ];
            }
        }
        
        // Ordenar por similitud descendente
        usort($coincidencias, function($a, $b) {
            return $b['similitud'] <=> $a['similitud'];
        });
        
        Log::info("📋 Coincidencias encontradas: " . count($coincidencias));
        
        // Si no hay coincidencias
        if (empty($coincidencias)) {
            Log::warning("❌ No se encontró paciente con nombre: {$nombreCompleto}");
            return response()->json([
                'success' => false,
                'error' => "No se encontró ningún paciente con el nombre '{$nombreCompleto}' en tu clínica. Por favor verifica el nombre completo."
            ], 404);
        }
        
        // Si hay exactamente 1 coincidencia con alta similitud (>= 90%), usar automáticamente
        if (count($coincidencias) === 1 && $coincidencias[0]['similitud'] >= 90) {
            $paciente = $coincidencias[0]['paciente'];
            Log::info("✅ Paciente único encontrado automáticamente", [
                'nombre' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                'similitud' => round($coincidencias[0]['similitud'], 2) . '%'
            ]);
        }
        // Si hay múltiples coincidencias o una con similitud media, mostrar opciones
        else {
            $listaOpciones = "Encontré varios pacientes con nombres similares. Por favor, especifica cuál:\n\n";
            foreach ($coincidencias as $index => $match) {
                $p = $match['paciente'];
                $num = $index + 1;
                $listaOpciones .= "{$num}. {$p->nombre} {$p->apellidoPat} {$p->apellidoMat}\n";
            }
            $listaOpciones .= "\nPor favor responde con el número o el nombre completo del paciente.";
            
            Log::info("📋 Mostrando múltiples opciones al usuario", [
                'total_opciones' => count($coincidencias)
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $listaOpciones,
                'requiere_seleccion' => true,
                'opciones' => array_map(function($match) {
                    $p = $match['paciente'];
                    return [
                        'id' => $p->id,
                        'nombre_completo' => "{$p->nombre} {$p->apellidoPat} {$p->apellidoMat}",
                        'similitud' => round($match['similitud'], 2)
                    ];
                }, $coincidencias)
            ]);
        }

        if (!$paciente) {
            Log::warning("❌ No se pudo determinar el paciente");
            return response()->json([
                'success' => false,
                'error' => "No se pudo determinar el paciente. Por favor especifica el nombre completo."
            ], 404);
        }

        // Validar que no exista cita duplicada
        $citaExistente = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('fecha', $params['fecha'])
            ->where('hora', $hora)
            ->where('estado', '!=', 'cancelada')
            ->first();

        if ($citaExistente) {
            Log::warning("⚠️ Cita duplicada detectada para paciente ID {$paciente->id}");
            return response()->json([
                'success' => false,
                'error' => 'Ya existe una cita para este paciente en la fecha y hora indicadas'
            ], 400);
        }

        try {
            // Obtener sucursal del usuario actual
            $sucursalId = $user->sucursal_id;
            
            // Crear la cita con todos los campos necesarios
            $citaId = DB::table('citas')->insertGetId([
                'paciente_id' => $paciente->id,
                'clinica_id' => $user->clinica_id,
                'sucursal_id' => $sucursalId,
                'admin_id' => $user->id,
                'user_id' => $user->id,
                'fecha' => $params['fecha'],
                'hora' => $hora,
                'estado' => 'confirmada',
                'primera_vez' => false,
                'notas' => $params['motivo'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("✅ Cita creada exitosamente", [
                'cita_id' => $citaId,
                'paciente' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                'clinica_id' => $user->clinica_id,
                'sucursal_id' => $sucursalId,
                'fecha' => $params['fecha'],
                'hora' => $hora
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cita agendada exitosamente para {$paciente->nombre} {$paciente->apellidoPat} el {$params['fecha']} a las {$hora}",
                'cita_id' => $citaId
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error al crear cita: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al crear la cita: ' . $e->getMessage()
            ], 500);
        }
    }
    private function buscarPaciente($user, $params)
    {
        if (!isset($params['nombre'])) {
            return response()->json([
                'success' => false,
                'error' => 'Nombre del paciente requerido'
            ], 400);
        }

        // Función para normalizar texto (quitar acentos y convertir a minúsculas)
        $normalizar = function($texto) {
            $texto = mb_strtolower($texto, 'UTF-8');
            $texto = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
                $texto
            );
            return trim($texto);
        };
        
        $nombreBuscar = $normalizar($params['nombre']);
        
        // Obtener todos los pacientes de la clínica usando Eloquent para desencriptar nombres
        $pacientes = \App\Models\Paciente::where('clinica_id', $user->clinica_id)->get();
        
        // Buscar pacientes normalizando nombres (recolectar todas las coincidencias)
        $pacientesEncontrados = [];
        
        foreach ($pacientes as $p) {
            $nombreCompletoPaciente = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
            $nombrePaciente = $normalizar($p->nombre);
            $apellidoPat = $normalizar($p->apellidoPat ?? '');
            $apellidoMat = $normalizar($p->apellidoMat ?? '');
            
            $prioridad = 0;
            
            // 1. Coincidencia exacta (100%)
            if ($nombreCompletoPaciente === $nombreBuscar || 
                $nombrePaciente === $nombreBuscar) {
                $prioridad = 100;
            }
            // 2. El nombre del paciente empieza con el texto buscado
            else if (str_starts_with($nombrePaciente, $nombreBuscar) || 
                str_starts_with($nombreCompletoPaciente, $nombreBuscar)) {
                $prioridad = 90;
            }
            // 3. Contiene el nombre de búsqueda como palabra completa
            else if (preg_match('/\b' . preg_quote($nombreBuscar, '/') . '\b/', $nombreCompletoPaciente)) {
                $prioridad = 80;
            }
            // 4. Contiene el nombre de búsqueda (substring)
            else if (strpos($nombreCompletoPaciente, $nombreBuscar) !== false) {
                $prioridad = 60;
            }
            
            // Si hay coincidencia, agregar a la lista
            if ($prioridad > 0) {
                $pacientesEncontrados[] = [
                    'paciente' => $p,
                    'prioridad' => $prioridad
                ];
            }
        }

        if (empty($pacientesEncontrados)) {
            return response()->json([
                'success' => false,
                'encontrado' => false,
                'message' => "No se encontró ningún paciente con el nombre '{$params['nombre']}' en tu clínica."
            ]);
        }

        // Ordenar por prioridad (mayor a menor)
        usort($pacientesEncontrados, function($a, $b) {
            return $b['prioridad'] - $a['prioridad'];
        });

        // Construir lista de pacientes con información
        $pacientesInfo = [];
        foreach ($pacientesEncontrados as $item) {
            $p = $item['paciente'];
            
            // Contar citas del paciente
            $totalCitas = DB::table('citas')
                ->where('paciente_id', $p->id)
                ->count();
            
            $citasPendientes = DB::table('citas')
                ->where('paciente_id', $p->id)
                ->where('estado', 'confirmada')
                ->where('fecha', '>=', now()->format('Y-m-d'))
                ->count();

            // Obtener próxima cita
            $proximaCita = DB::table('citas')
                ->where('paciente_id', $p->id)
                ->where('estado', 'confirmada')
                ->where('fecha', '>=', now()->format('Y-m-d'))
                ->orderBy('fecha')
                ->orderBy('hora')
                ->first();

            $pacientesInfo[] = [
                'id' => $p->id,
                'nombre_completo' => trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat),
                'expediente' => $p->numeroExpediente ?? 'N/A',
                'telefono' => $p->telefono ?? 'N/A',
                'edad' => $p->edad ?? 'N/A',
                'total_citas' => $totalCitas,
                'citas_pendientes' => $citasPendientes,
                'proxima_cita' => $proximaCita ? [
                    'fecha' => $proximaCita->fecha,
                    'hora' => $proximaCita->hora,
                    'id' => $proximaCita->id
                ] : null
            ];
        }

        $count = count($pacientesInfo);
        $mensaje = $count === 1 
            ? "Encontré 1 paciente:"
            : "Encontré {$count} pacientes:";

        return response()->json([
            'success' => true,
            'encontrado' => true,
            'count' => $count,
            'pacientes' => $pacientesInfo,
            'message' => $mensaje
        ]);
    }
    private function crearEvento($user, $params)
    {
        if (!isset($params['titulo']) || !isset($params['fecha'])) {
            return response()->json(['success' => false, 'error' => 'Título y fecha requeridos'], 400);
        }

        $eventoId = DB::table('eventos')->insertGetId([
            'user_id' => $user->id,
            'clinica_id' => $user->clinica_id,
            'tipo' => $params['tipo'] ?? 'recordatorio',
            'titulo' => $params['titulo'],
            'descripcion' => $params['descripcion'] ?? null,
            'fecha' => $params['fecha'],
            'hora' => $params['hora'] ?? null,
            'color' => $params['color'] ?? '#3B82F6',
            'completado' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evento creado exitosamente',
            'evento_id' => $eventoId
        ]);
    }

    private function obtenerMetricas($user)
    {
        $clinicaId = $user->clinica_id;

        // Métricas del dashboard
        $totalPacientes = DB::table('pacientes')->where('clinica_id', $clinicaId)->count();
        
        $citasHoy = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('fecha', now()->toDateString())
            ->where('estado', '!=', 'cancelada')
            ->count();
        
        $citasSemana = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('estado', '!=', 'cancelada')
            ->count();
        
        $citasMes = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('estado', '!=', 'cancelada')
            ->count();
        
        $pacientesNuevosMes = DB::table('pacientes')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        
        $citasCanceladas = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('estado', 'cancelada')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return response()->json([
            'success' => true,
            'metricas' => [
                'total_pacientes' => $totalPacientes,
                'citas_hoy' => $citasHoy,
                'citas_esta_semana' => $citasSemana,
                'citas_este_mes' => $citasMes,
                'pacientes_nuevos_mes' => $pacientesNuevosMes,
                'citas_canceladas_mes' => $citasCanceladas
            ]
        ]);
    }

    private function obtenerAnaliticasPacientes($user)
    {
        $clinicaId = $user->clinica_id;

        // Pacientes por género (genero es boolean: 0=femenino, 1=masculino)
        $pacientesPorGenero = DB::table('pacientes')
            ->select('genero', DB::raw('count(*) as total'))
            ->where('clinica_id', $clinicaId)
            ->groupBy('genero')
            ->get();

        $hombres = $pacientesPorGenero->firstWhere('genero', 1)->total ?? 0;
        $mujeres = $pacientesPorGenero->firstWhere('genero', 0)->total ?? 0;

        // Pacientes por tipo (cardiaca, pulmonar, ambos)
        $pacientesPorTipo = DB::table('pacientes')
            ->select('tipo_paciente', DB::raw('count(*) as total'))
            ->where('clinica_id', $clinicaId)
            ->groupBy('tipo_paciente')
            ->get();

        $tipoCardiaca = $pacientesPorTipo->firstWhere('tipo_paciente', 'cardiaca')->total ?? 0;
        $tipoPulmonar = $pacientesPorTipo->firstWhere('tipo_paciente', 'pulmonar')->total ?? 0;
        $tipoAmbos = $pacientesPorTipo->firstWhere('tipo_paciente', 'ambos')->total ?? 0;
        $tipoFisioterapia = $pacientesPorTipo->firstWhere('tipo_paciente', 'fisioterapia')->total ?? 0;

        // Promedio de edad
        $promedioEdad = DB::table('pacientes')
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('edad')
            ->where('edad', '!=', '')
            ->avg(DB::raw('CAST(edad AS DECIMAL(5,2))'));

        // Distribución por rangos de edad
        $pacientes = DB::table('pacientes')
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('edad')
            ->where('edad', '!=', '')
            ->get();

        $rangoEdades = [
            'menores_40' => 0,
            'entre_40_60' => 0,
            'mayores_60' => 0
        ];

        foreach ($pacientes as $p) {
            $edad = floatval($p->edad);
            if ($edad < 40) {
                $rangoEdades['menores_40']++;
            } elseif ($edad >= 40 && $edad < 60) {
                $rangoEdades['entre_40_60']++;
            } else {
                $rangoEdades['mayores_60']++;
            }
        }

        // Top 5 diagnósticos más comunes
        $diagnosticos = DB::table('pacientes')
            ->select('diagnostico', DB::raw('count(*) as total'))
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('diagnostico')
            ->where('diagnostico', '!=', '')
            ->groupBy('diagnostico')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Pacientes más activos (con más citas)
        $pacientesActivos = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->select(
                'pacientes.id',
                DB::raw('CONCAT(pacientes.nombre, " ", pacientes.apellidoPat) as nombre_completo'),
                DB::raw('count(*) as total_citas')
            )
            ->where('citas.clinica_id', $clinicaId)
            ->groupBy('pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat')
            ->orderBy('total_citas', 'desc')
            ->limit(5)
            ->get();

        // Pacientes nuevos últimos 30 días
        $pacientesNuevos30Dias = DB::table('pacientes')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Promedio de IMC
        $promedioIMC = DB::table('pacientes')
            ->where('clinica_id', $clinicaId)
            ->whereNotNull('imc')
            ->where('imc', '>', 0)
            ->avg('imc');

        $analiticas = [
            'total_pacientes' => $hombres + $mujeres,
            'hombres' => $hombres,
            'mujeres' => $mujeres,
            'porcentaje_hombres' => $hombres + $mujeres > 0 ? round(($hombres / ($hombres + $mujeres)) * 100, 1) : 0,
            'porcentaje_mujeres' => $hombres + $mujeres > 0 ? round(($mujeres / ($hombres + $mujeres)) * 100, 1) : 0,
            'tipos_paciente' => [
                'cardiaca' => $tipoCardiaca,
                'pulmonar' => $tipoPulmonar,
                'ambos' => $tipoAmbos,
                'fisioterapia' => $tipoFisioterapia
            ],
            'promedio_edad' => round($promedioEdad ?? 0, 1),
            'promedio_imc' => round($promedioIMC ?? 0, 1),
            'rangos_edad' => $rangoEdades,
            'diagnosticos_comunes' => $diagnosticos->map(function($item) {
                return [
                    'diagnostico' => $item->diagnostico,
                    'total' => $item->total
                ];
            })->toArray(),
            'pacientes_activos' => $pacientesActivos->map(function($item) {
                return [
                    'nombre' => $item->nombre_completo,
                    'total_citas' => $item->total_citas
                ];
            })->toArray(),
            'pacientes_nuevos_30dias' => $pacientesNuevos30Dias
        ];

        return response()->json([
            'success' => true,
            'analiticas' => $analiticas
        ]);
    }

    private function analizarPaciente($user, $params)
    {
        if (!isset($params['nombre'])) {
            return response()->json([
                'success' => false,
                'error' => 'Nombre del paciente requerido'
            ], 400);
        }

        // Buscar paciente (reutilizando lógica de búsqueda)
        $normalizar = function($texto) {
            $texto = mb_strtolower($texto, 'UTF-8');
            $texto = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
                $texto
            );
            return trim($texto);
        };
        
        $nombreBuscar = $normalizar($params['nombre']);
        
        $pacientes = DB::table('pacientes')
            ->where('clinica_id', $user->clinica_id)
            ->get();
        
        $paciente = null;
        $mejorCoincidencia = 0;
        
        foreach ($pacientes as $p) {
            $nombreCompletoPaciente = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
            $nombrePaciente = $normalizar($p->nombre);
            
            if ($nombreCompletoPaciente === $nombreBuscar || $nombrePaciente === $nombreBuscar) {
                $paciente = $p;
                $mejorCoincidencia = 1;
                break;
            }
            
            if (str_starts_with($nombrePaciente, $nombreBuscar) || 
                str_starts_with($nombreCompletoPaciente, $nombreBuscar)) {
                if (0.9 > $mejorCoincidencia) {
                    $mejorCoincidencia = 0.9;
                    $paciente = $p;
                }
                continue;
            }
            
            if (preg_match('/\b' . preg_quote($nombreBuscar, '/') . '\b/', $nombreCompletoPaciente)) {
                if (0.8 > $mejorCoincidencia) {
                    $mejorCoincidencia = 0.8;
                    $paciente = $p;
                }
            }
        }

        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['nombre']}'"
            ], 404);
        }

        // Recopilar datos clínicos (pseudonimizados)
        $totalCitas = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->count();
        
        $citasUltimos3Meses = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('fecha', '>=', now()->subMonths(3))
            ->count();
        
        $ultimaCita = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->orderBy('fecha', 'desc')
            ->first();

        // Datos pseudonimizados para análisis
        $datosClinicosAnonimos = [
            'id_anonimo' => 'Paciente-' . $paciente->id,
            'edad' => floatval($paciente->edad ?? 0),
            'genero' => $paciente->genero == 1 ? 'masculino' : 'femenino',
            'tipo_paciente' => $paciente->tipo_paciente ?? 'cardiaca',
            'mediciones' => [
                'peso' => $paciente->peso ?? 'N/A',
                'talla' => $paciente->talla ?? 'N/A',
                'imc' => $paciente->imc ?? 'N/A',
                'cintura' => $paciente->cintura ?? 'N/A'
            ],
            'diagnostico' => $paciente->diagnostico ?? 'No especificado',
            'medicamentos' => $paciente->medicamentos ?? 'No especificado',
            'historial_citas' => [
                'total' => $totalCitas,
                'ultimos_3_meses' => $citasUltimos3Meses,
                'ultima_fecha' => $ultimaCita ? $ultimaCita->fecha : 'N/A'
            ],
            'fecha_registro' => $paciente->created_at ?? 'Desconocida'
        ];

        // Solicitar análisis a Gemini
        $analisis = $this->aiService->analizarEstadoPaciente($datosClinicosAnonimos);

        if (!$analisis['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Error al generar análisis'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'paciente_nombre' => trim($paciente->nombre . ' ' . $paciente->apellidoPat),
            'analisis' => $analisis['analisis']
        ]);
    }

    private function contarCitasPaciente($user, $params)
    {
        if (!isset($params['nombre'])) {
            return response()->json([
                'success' => false,
                'error' => 'Nombre del paciente requerido'
            ], 400);
        }

        // Buscar paciente (reutilizando lógica de búsqueda normalizada)
        $normalizar = function($texto) {
            $texto = mb_strtolower($texto, 'UTF-8');
            $texto = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
                $texto
            );
            return trim($texto);
        };
        
        $nombreBuscar = $normalizar($params['nombre']);
        
        $pacientes = DB::table('pacientes')
            ->where('clinica_id', $user->clinica_id)
            ->get();
        
        $paciente = null;
        $mejorCoincidencia = 0;
        
        foreach ($pacientes as $p) {
            $nombreCompletoPaciente = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
            $nombrePaciente = $normalizar($p->nombre);
            
            if ($nombreCompletoPaciente === $nombreBuscar || $nombrePaciente === $nombreBuscar) {
                $paciente = $p;
                $mejorCoincidencia = 1;
                break;
            }
            
            if (str_starts_with($nombrePaciente, $nombreBuscar) || 
                str_starts_with($nombreCompletoPaciente, $nombreBuscar)) {
                if (0.9 > $mejorCoincidencia) {
                    $mejorCoincidencia = 0.9;
                    $paciente = $p;
                }
                continue;
            }
            
            if (preg_match('/\b' . preg_quote($nombreBuscar, '/') . '\b/', $nombreCompletoPaciente)) {
                if (0.8 > $mejorCoincidencia) {
                    $mejorCoincidencia = 0.8;
                    $paciente = $p;
                }
            }
        }

        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['nombre']}'"
            ], 404);
        }

        // Contar todas las citas del paciente
        $totalCitas = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->count();
        
        // Citas completadas
        $citasCompletadas = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('estado', 'completada')
            ->count();
        
        // Citas confirmadas (futuras)
        $citasConfirmadas = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('estado', 'confirmada')
            ->where('fecha', '>=', now()->format('Y-m-d'))
            ->count();
        
        // Citas canceladas
        $citasCanceladas = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('estado', 'cancelada')
            ->count();
        
        // Citas en los últimos 6 meses
        $citasUltimos6Meses = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('fecha', '>=', now()->subMonths(6)->format('Y-m-d'))
            ->count();
        
        // Última cita
        $ultimaCita = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->first();
        
        // Próxima cita
        $proximaCita = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('estado', 'confirmada')
            ->where('fecha', '>=', now()->format('Y-m-d'))
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->first();

        return response()->json([
            'success' => true,
            'paciente' => [
                'id' => $paciente->id,
                'nombre_completo' => trim($paciente->nombre . ' ' . $paciente->apellidoPat . ' ' . $paciente->apellidoMat),
                'expediente' => $paciente->numeroExpediente ?? 'N/A'
            ],
            'citas' => [
                'total' => $totalCitas,
                'completadas' => $citasCompletadas,
                'confirmadas' => $citasConfirmadas,
                'canceladas' => $citasCanceladas,
                'ultimos_6_meses' => $citasUltimos6Meses
            ],
            'ultima_cita' => $ultimaCita ? [
                'fecha' => $ultimaCita->fecha,
                'hora' => $ultimaCita->hora,
                'estado' => $ultimaCita->estado
            ] : null,
            'proxima_cita' => $proximaCita ? [
                'fecha' => $proximaCita->fecha,
                'hora' => $proximaCita->hora,
                'estado' => $proximaCita->estado
            ] : null
        ]);
    }

    // ==========================================
    // ANÁLISIS Y REPORTES AVANZADOS
    // ==========================================

    private function generarReporteMetricas($user, $params)
    {
        $periodo = $params['periodo'] ?? 'mes'; // dia, semana, mes, trimestre, año
        $formato = $params['formato'] ?? 'resumido'; // resumido, detallado

        $fechaInicio = match($periodo) {
            'dia' => now()->startOfDay(),
            'semana' => now()->startOfWeek(),
            'mes' => now()->startOfMonth(),
            'trimestre' => now()->startOfQuarter(),
            'año' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $clinicaId = $user->clinica_id;

        // Métricas de citas
        $totalCitas = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', $fechaInicio)
            ->count();

        $citasCompletadas = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('estado', 'completada')
            ->where('created_at', '>=', $fechaInicio)
            ->count();

        $citasCanceladas = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('estado', 'cancelada')
            ->where('created_at', '>=', $fechaInicio)
            ->count();

        $tasaCancelacion = $totalCitas > 0 ? round(($citasCanceladas / $totalCitas) * 100, 1) : 0;
        $tasaCompletadas = $totalCitas > 0 ? round(($citasCompletadas / $totalCitas) * 100, 1) : 0;

        // Métricas de pacientes
        $pacientesNuevos = DB::table('pacientes')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', $fechaInicio)
            ->count();

        $pacientesActivos = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->where('citas.clinica_id', $clinicaId)
            ->where('citas.fecha', '>=', $fechaInicio)
            ->distinct('pacientes.id')
            ->count('pacientes.id');

        // Ingresos (si existe tabla de pagos)
        $ingresos = DB::table('pagos')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('estado', 'pagado')
            ->sum('monto') ?? 0;

        $reporte = [
            'periodo' => $periodo,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
            'citas' => [
                'total' => $totalCitas,
                'completadas' => $citasCompletadas,
                'canceladas' => $citasCanceladas,
                'tasa_cancelacion' => $tasaCancelacion . '%',
                'tasa_completadas' => $tasaCompletadas . '%'
            ],
            'pacientes' => [
                'nuevos' => $pacientesNuevos,
                'activos' => $pacientesActivos
            ],
            'ingresos' => [
                'total' => '$' . number_format($ingresos, 2)
            ]
        ];

        if ($formato === 'detallado') {
            // Agregar detalles adicionales
            $diaMasActivo = DB::table('citas')
                ->where('clinica_id', $clinicaId)
                ->where('fecha', '>=', $fechaInicio)
                ->selectRaw('DATE_FORMAT(fecha, "%W") as dia, COUNT(*) as total')
                ->groupBy('dia')
                ->orderBy('total', 'desc')
                ->first();

            $horaPico = DB::table('citas')
                ->where('clinica_id', $clinicaId)
                ->where('fecha', '>=', $fechaInicio)
                ->selectRaw('HOUR(hora) as hora, COUNT(*) as total')
                ->groupBy('hora')
                ->orderBy('total', 'desc')
                ->first();

            $reporte['detalles'] = [
                'dia_mas_activo' => $diaMasActivo ? $diaMasActivo->dia : 'N/A',
                'hora_pico' => $horaPico ? $horaPico->hora . ':00' : 'N/A'
            ];
        }

        return response()->json([
            'success' => true,
            'reporte' => $reporte,
            'generado_en' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private function analisisPredictivoCitas($user)
    {
        $clinicaId = $user->clinica_id;

        // Análisis de cancelaciones
        $citasUltimos30Dias = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $totalCitas = $citasUltimos30Dias->count();
        $citasCanceladas = $citasUltimos30Dias->where('estado', 'cancelada')->count();
        $tasaCancelacion = $totalCitas > 0 ? round(($citasCanceladas / $totalCitas) * 100, 1) : 0;

        // Patrones de cancelación por día de semana
        $cancelacionesPorDia = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('estado', 'cancelada')
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('DAYNAME(fecha) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->orderBy('total', 'desc')
            ->get();

        // Patrones de cancelación por hora
        $cancelacionesPorHora = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('estado', 'cancelada')
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('HOUR(hora) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get();

        // Predicción simple
        $citasProximaSemana = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->whereBetween('fecha', [now()->addDay(), now()->addDays(7)])
            ->count();

        $cancelacionesEsperadas = round($citasProximaSemana * ($tasaCancelacion / 100));

        return response()->json([
            'success' => true,
            'analisis' => [
                'tasa_cancelacion_actual' => $tasaCancelacion . '%',
                'citas_proxima_semana' => $citasProximaSemana,
                'cancelaciones_esperadas' => $cancelacionesEsperadas,
                'dias_mayor_cancelacion' => $cancelacionesPorDia->take(3)->map(function($item) {
                    return $item->dia . ' (' . $item->total . ' cancelaciones)';
                })->toArray(),
                'horas_mayor_cancelacion' => $cancelacionesPorHora->map(function($item) {
                    return $item->hora . ':00' . ' (' . $item->total . ' cancelaciones)';
                })->toArray(),
                'recomendacion' => $tasaCancelacion > 20 
                    ? 'Alta tasa de cancelación. Considera implementar recordatorios automáticos 24h antes.'
                    : 'Tasa de cancelación normal. Mantén los recordatorios actuales.'
            ]
        ]);
    }

    private function identificarPacientesRiesgo($user)
    {
        $clinicaId = $user->clinica_id;

        // Pacientes sin citas recientes (más de 60 días)
        $pacientesSinCitasRecientes = DB::table('pacientes')
            ->leftJoin('citas', 'pacientes.id', '=', 'citas.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->whereNotNull('citas.id')
            ->selectRaw('pacientes.id, pacientes.nombre, pacientes.apellidoPat, MAX(citas.fecha) as ultima_cita')
            ->groupBy('pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat')
            ->havingRaw('MAX(citas.fecha) < ?', [now()->subDays(60)->format('Y-m-d')])
            ->limit(10)
            ->get();

        // Pacientes con alta tasa de cancelación
        $pacientesAltaCancelacion = DB::table('pacientes')
            ->join('citas', 'pacientes.id', '=', 'citas.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->selectRaw('pacientes.id, pacientes.nombre, pacientes.apellidoPat, 
                COUNT(*) as total_citas,
                SUM(CASE WHEN citas.estado = "cancelada" THEN 1 ELSE 0 END) as canceladas,
                ROUND((SUM(CASE WHEN citas.estado = "cancelada" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as tasa_cancelacion')
            ->groupBy('pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat')
            ->having('total_citas', '>=', 3)
            ->having('tasa_cancelacion', '>', 40)
            ->limit(10)
            ->get();

        // Pacientes que nunca han completado una cita
        $pacientesSinCompletadas = DB::table('pacientes')
            ->join('citas', 'pacientes.id', '=', 'citas.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->selectRaw('pacientes.id, pacientes.nombre, pacientes.apellidoPat, COUNT(*) as total_citas')
            ->groupBy('pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat')
            ->havingRaw('SUM(CASE WHEN citas.estado = "completada" THEN 1 ELSE 0 END) = 0')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'pacientes_riesgo' => [
                'sin_citas_recientes' => $pacientesSinCitasRecientes->map(function($p) {
                    return [
                        'nombre' => $p->nombre . ' ' . $p->apellidoPat,
                        'ultima_cita' => $p->ultima_cita,
                        'dias_sin_cita' => now()->diffInDays($p->ultima_cita),
                        'razon' => 'Sin citas hace más de 60 días'
                    ];
                })->toArray(),
                'alta_cancelacion' => $pacientesAltaCancelacion->map(function($p) {
                    return [
                        'nombre' => $p->nombre . ' ' . $p->apellidoPat,
                        'total_citas' => $p->total_citas,
                        'canceladas' => $p->canceladas,
                        'tasa_cancelacion' => $p->tasa_cancelacion . '%',
                        'razon' => 'Alta tasa de cancelación'
                    ];
                })->toArray(),
                'sin_completadas' => $pacientesSinCompletadas->map(function($p) {
                    return [
                        'nombre' => $p->nombre . ' ' . $p->apellidoPat,
                        'total_citas' => $p->total_citas,
                        'razon' => 'Nunca ha completado una cita'
                    ];
                })->toArray()
            ],
            'total_en_riesgo' => $pacientesSinCitasRecientes->count() + 
                                 $pacientesAltaCancelacion->count() + 
                                 $pacientesSinCompletadas->count()
        ]);
    }

    private function sugerenciasMejoraOperativa($user)
    {
        $clinicaId = $user->clinica_id;
        $sugerencias = [];

        // Análisis de horarios
        $citasPorHora = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('fecha', '>=', now()->subDays(30))
            ->selectRaw('HOUR(hora) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderBy('total', 'asc')
            ->get();

        $horaMenosOcupada = $citasPorHora->first();
        if ($horaMenosOcupada && $horaMenosOcupada->total < 5) {
            $sugerencias[] = [
                'tipo' => 'optimizacion_horarios',
                'prioridad' => 'media',
                'mensaje' => "La hora de {$horaMenosOcupada->hora}:00 tiene poca demanda ({$horaMenosOcupada->total} citas/mes). Considera ofrecer promociones o ajustar horarios.",
                'accion_sugerida' => 'Revisar disponibilidad de horarios'
            ];
        }

        // Análisis de cancelaciones
        $tasaCancelacion = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN estado = "cancelada" THEN 1 ELSE 0 END) as canceladas
            ')
            ->first();

        $tasa = $tasaCancelacion->total > 0 
            ? round(($tasaCancelacion->canceladas / $tasaCancelacion->total) * 100, 1) 
            : 0;

        if ($tasa > 20) {
            $sugerencias[] = [
                'tipo' => 'reducir_cancelaciones',
                'prioridad' => 'alta',
                'mensaje' => "Tasa de cancelación de {$tasa}% es alta. Implementa recordatorios automáticos por WhatsApp/SMS 24h antes de la cita.",
                'accion_sugerida' => 'Configurar recordatorios automáticos'
            ];
        }

        // Análisis de capacidad
        $citasHoy = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('fecha', now()->format('Y-m-d'))
            ->count();

        $promedioSemanal = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('fecha', '>=', now()->subDays(30))
            ->count() / 4;

        if ($citasHoy < ($promedioSemanal * 0.5)) {
            $sugerencias[] = [
                'tipo' => 'baja_ocupacion',
                'prioridad' => 'baja',
                'mensaje' => "Hoy tienes {$citasHoy} citas, por debajo del promedio. Contacta pacientes pendientes de agendar.",
                'accion_sugerida' => 'Llamar a pacientes sin citas próximas'
            ];
        }

        // Pacientes sin expediente clínico
        $pacientesSinExpediente = DB::table('pacientes')
            ->leftJoin('expedientes_clinicos', 'pacientes.id', '=', 'expedientes_clinicos.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->whereNull('expedientes_clinicos.id')
            ->count();

        if ($pacientesSinExpediente > 5) {
            $sugerencias[] = [
                'tipo' => 'expedientes_incompletos',
                'prioridad' => 'media',
                'mensaje' => "Tienes {$pacientesSinExpediente} pacientes sin expediente clínico. Completa la información para mejor seguimiento.",
                'accion_sugerida' => 'Crear expedientes faltantes'
            ];
        }

        return response()->json([
            'success' => true,
            'sugerencias' => $sugerencias,
            'total_sugerencias' => count($sugerencias),
            'generado_en' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // ==========================================
    // NOTIFICACIONES INTELIGENTES
    // ==========================================

    private function generarResumenDiario($user)
    {
        $clinicaId = $user->clinica_id;
        $hoy = now()->format('Y-m-d');

        // Citas de hoy
        $citasHoy = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->where('citas.clinica_id', $clinicaId)
            ->where('citas.fecha', $hoy)
            ->select('citas.*', DB::raw('CONCAT(pacientes.nombre, " ", pacientes.apellidoPat) as paciente_nombre'))
            ->orderBy('citas.hora')
            ->get();

        $citasCompletadas = $citasHoy->where('estado', 'completada')->count();
        $citasPendientes = $citasHoy->where('estado', 'confirmada')->count();
        $citasCanceladas = $citasHoy->where('estado', 'cancelada')->count();

        // Próximas citas (próximos 3 días)
        $citasProximas = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->where('citas.clinica_id', $clinicaId)
            ->whereBetween('citas.fecha', [now()->addDay()->format('Y-m-d'), now()->addDays(3)->format('Y-m-d')])
            ->where('citas.estado', '!=', 'cancelada')
            ->select('citas.fecha', 'citas.hora', DB::raw('CONCAT(pacientes.nombre, " ", pacientes.apellidoPat) as paciente_nombre'))
            ->orderBy('citas.fecha')
            ->orderBy('citas.hora')
            ->limit(5)
            ->get();

        // Tareas pendientes
        $eventosPendientes = DB::table('eventos')
            ->where('user_id', $user->id)
            ->where('fecha', '<=', now()->addDays(3)->format('Y-m-d'))
            ->where('completado', false)
            ->orderBy('fecha')
            ->limit(5)
            ->get();

        // Pacientes que requieren seguimiento
        $pacientesSeguimiento = DB::table('pacientes')
            ->leftJoin('citas', 'pacientes.id', '=', 'citas.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->selectRaw('pacientes.id, pacientes.nombre, pacientes.apellidoPat, MAX(citas.fecha) as ultima_cita')
            ->groupBy('pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat')
            ->havingRaw('MAX(citas.fecha) BETWEEN ? AND ?', [
                now()->subDays(45)->format('Y-m-d'),
                now()->subDays(30)->format('Y-m-d')
            ])
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'resumen_diario' => [
                'fecha' => $hoy,
                'citas_hoy' => [
                    'total' => $citasHoy->count(),
                    'completadas' => $citasCompletadas,
                    'pendientes' => $citasPendientes,
                    'canceladas' => $citasCanceladas,
                    'detalle' => $citasHoy->take(10)->map(function($cita) {
                        return [
                            'hora' => substr($cita->hora, 0, 5),
                            'paciente' => $cita->paciente_nombre,
                            'estado' => $cita->estado
                        ];
                    })->toArray()
                ],
                'proximas_citas' => $citasProximas->map(function($cita) {
                    return [
                        'fecha' => $cita->fecha,
                        'hora' => substr($cita->hora, 0, 5),
                        'paciente' => $cita->paciente_nombre
                    ];
                })->toArray(),
                'tareas_pendientes' => $eventosPendientes->map(function($evento) {
                    return [
                        'fecha' => $evento->fecha,
                        'titulo' => $evento->titulo,
                        'tipo' => $evento->tipo
                    ];
                })->toArray(),
                'pacientes_seguimiento' => $pacientesSeguimiento->map(function($p) {
                    return [
                        'nombre' => $p->nombre . ' ' . $p->apellidoPat,
                        'ultima_cita' => $p->ultima_cita,
                        'dias_sin_cita' => now()->diffInDays($p->ultima_cita)
                    ];
                })->toArray()
            ],
            'mensaje' => "Buenos días! Tienes {$citasHoy->count()} citas programadas para hoy.",
            'generado_en' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private function obtenerAlertasSeguimiento($user)
    {
        $clinicaId = $user->clinica_id;
        $alertas = [];

        // Citas próximas sin confirmar (en las próximas 24 horas)
        $citasSinConfirmar = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->where('citas.clinica_id', $clinicaId)
            ->where('citas.estado', 'pendiente')
            ->whereBetween('citas.fecha', [now()->format('Y-m-d'), now()->addDay()->format('Y-m-d')])
            ->select('citas.*', DB::raw('CONCAT(pacientes.nombre, " ", pacientes.apellidoPat) as paciente_nombre'))
            ->get();

        if ($citasSinConfirmar->count() > 0) {
            $alertas[] = [
                'tipo' => 'citas_sin_confirmar',
                'prioridad' => 'alta',
                'cantidad' => $citasSinConfirmar->count(),
                'mensaje' => "Tienes {$citasSinConfirmar->count()} citas pendientes de confirmar en las próximas 24 horas.",
                'pacientes' => $citasSinConfirmar->map(function($c) {
                    return $c->paciente_nombre . ' - ' . $c->fecha . ' ' . substr($c->hora, 0, 5);
                })->toArray()
            ];
        }

        // Pacientes sin citas hace más de 3 meses
        $pacientesInactivos = DB::table('pacientes')
            ->leftJoin('citas', 'pacientes.id', '=', 'citas.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->selectRaw('pacientes.id, pacientes.nombre, pacientes.apellidoPat, MAX(citas.fecha) as ultima_cita')
            ->groupBy('pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat')
            ->havingRaw('MAX(citas.fecha) < ?', [now()->subDays(90)->format('Y-m-d')])
            ->limit(10)
            ->get();

        if ($pacientesInactivos->count() > 0) {
            $alertas[] = [
                'tipo' => 'pacientes_inactivos',
                'prioridad' => 'media',
                'cantidad' => $pacientesInactivos->count(),
                'mensaje' => "{$pacientesInactivos->count()} pacientes sin citas hace más de 3 meses. Considera contactarlos para seguimiento.",
                'pacientes' => $pacientesInactivos->map(function($p) {
                    return [
                        'nombre' => $p->nombre . ' ' . $p->apellidoPat,
                        'ultima_cita' => $p->ultima_cita
                    ];
                })->toArray()
            ];
        }

        // Recordatorios/eventos para hoy
        $recordatoriosHoy = DB::table('eventos')
            ->where('user_id', $user->id)
            ->where('fecha', now()->format('Y-m-d'))
            ->where('completado', false)
            ->get();

        if ($recordatoriosHoy->count() > 0) {
            $alertas[] = [
                'tipo' => 'recordatorios',
                'prioridad' => 'normal',
                'cantidad' => $recordatoriosHoy->count(),
                'mensaje' => "Tienes {$recordatoriosHoy->count()} recordatorios para hoy.",
                'recordatorios' => $recordatoriosHoy->map(function($r) {
                    return $r->titulo . ($r->hora ? ' - ' . substr($r->hora, 0, 5) : '');
                })->toArray()
            ];
        }

        return response()->json([
            'success' => true,
            'alertas' => $alertas,
            'total_alertas' => count($alertas),
            'generado_en' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private function sugerenciasProactivas($user)
    {
        $clinicaId = $user->clinica_id;
        $sugerencias = [];

        // Sugerencia: Agendar citas para pacientes sin citas próximas
        $pacientesSinCitasProximas = DB::table('pacientes')
            ->leftJoin('citas', function($join) {
                $join->on('pacientes.id', '=', 'citas.paciente_id')
                     ->where('citas.fecha', '>=', now()->format('Y-m-d'));
            })
            ->where('pacientes.clinica_id', $clinicaId)
            ->whereNull('citas.id')
            ->limit(5)
            ->get(['pacientes.id', 'pacientes.nombre', 'pacientes.apellidoPat']);

        if ($pacientesSinCitasProximas->count() > 0) {
            $sugerencias[] = [
                'tipo' => 'agendar_citas',
                'icono' => '📅',
                'mensaje' => "¿Quieres agendar citas para los {$pacientesSinCitasProximas->count()} pacientes que no tienen citas próximas?",
                'accion' => 'Puedo ayudarte a agendarlas',
                'pacientes' => $pacientesSinCitasProximas->map(function($p) {
                    return $p->nombre . ' ' . $p->apellidoPat;
                })->toArray()
            ];
        }

        // Sugerencia: Optimizar horarios vacíos
        $horasVacias = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('fecha', '>=', now()->format('Y-m-d'))
            ->where('fecha', '<=', now()->addDays(7)->format('Y-m-d'))
            ->selectRaw('fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->having('total', '<', 5)
            ->get();

        if ($horasVacias->count() > 0) {
            $sugerencias[] = [
                'tipo' => 'optimizar_agenda',
                'icono' => '⏰',
                'mensaje' => "Tienes {$horasVacias->count()} días con poca ocupación la próxima semana. ¿Quieres que te sugiera pacientes para contactar?",
                'accion' => 'Mostrar lista de pacientes sugeridos',
                'dias' => $horasVacias->pluck('fecha')->toArray()
            ];
        }

        // Sugerencia: Crear expedientes faltantes
        $pacientesSinExpediente = DB::table('pacientes')
            ->leftJoin('expedientes_clinicos', 'pacientes.id', '=', 'expedientes_clinicos.paciente_id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->whereNull('expedientes_clinicos.id')
            ->count();

        if ($pacientesSinExpediente > 0) {
            $sugerencias[] = [
                'tipo' => 'completar_expedientes',
                'icono' => '📋',
                'mensaje' => "Hay {$pacientesSinExpediente} pacientes sin expediente clínico. ¿Quieres que te ayude a crearlos?",
                'accion' => 'Iniciar creación de expedientes'
            ];
        }

        // Sugerencia: Enviar recordatorios
        $citasMañana = DB::table('citas')
            ->where('clinica_id', $clinicaId)
            ->where('fecha', now()->addDay()->format('Y-m-d'))
            ->where('estado', 'confirmada')
            ->count();

        if ($citasMañana > 0) {
            $sugerencias[] = [
                'tipo' => 'enviar_recordatorios',
                'icono' => '🔔',
                'mensaje' => "Tienes {$citasMañana} citas mañana. ¿Quieres enviar recordatorios automáticos a los pacientes?",
                'accion' => 'Enviar recordatorios por WhatsApp/SMS'
            ];
        }

        return response()->json([
            'success' => true,
            'sugerencias' => $sugerencias,
            'total_sugerencias' => count($sugerencias),
            'mensaje' => count($sugerencias) > 0 
                ? "Tengo " . count($sugerencias) . " sugerencias para mejorar tu gestión hoy."
                : "Todo está al día! No tengo sugerencias en este momento.",
            'generado_en' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // ==========================================
    // EXPEDIENTES CLÍNICOS
    // ==========================================

    private function obtenerExpediente($user, $params)
    {
        if (!isset($params['paciente_nombre'])) {
            return response()->json(['success' => false, 'error' => 'Nombre del paciente requerido'], 400);
        }

        // Buscar paciente
        $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
        
        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['paciente_nombre']}'"
            ], 404);
        }

        // Obtener expediente
        $expediente = DB::table('expedientes_clinicos')
            ->where('paciente_id', $paciente->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$expediente) {
            return response()->json([
                'success' => true,
                'tiene_expediente' => false,
                'paciente' => [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre . ' ' . $paciente->apellidoPat
                ],
                'mensaje' => "El paciente {$paciente->nombre} {$paciente->apellidoPat} no tiene expediente clínico aún."
            ]);
        }

        return response()->json([
            'success' => true,
            'tiene_expediente' => true,
            'paciente' => [
                'id' => $paciente->id,
                'nombre' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                'edad' => $paciente->edad ?? 'N/A',
                'genero' => $paciente->genero == 1 ? 'Masculino' : 'Femenino'
            ],
            'expediente' => [
                'id' => $expediente->id,
                'antecedentes_personales' => $expediente->antecedentes_personales,
                'antecedentes_familiares' => $expediente->antecedentes_familiares,
                'diagnostico_actual' => $expediente->diagnostico_actual,
                'tratamiento_actual' => $expediente->tratamiento_actual,
                'notas' => $expediente->notas,
                'ultima_actualizacion' => $expediente->updated_at
            ]
        ]);
    }

    private function crearExpediente($user, $params)
    {
        if (!isset($params['paciente_nombre'])) {
            return response()->json(['success' => false, 'error' => 'Nombre del paciente requerido'], 400);
        }

        // Buscar paciente
        $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
        
        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['paciente_nombre']}'"
            ], 404);
        }

        // Verificar si ya tiene expediente
        $expedienteExistente = DB::table('expedientes_clinicos')
            ->where('paciente_id', $paciente->id)
            ->exists();

        if ($expedienteExistente) {
            return response()->json([
                'success' => false,
                'error' => "El paciente {$paciente->nombre} {$paciente->apellidoPat} ya tiene un expediente clínico. Usa 'editar expediente' para modificarlo."
            ], 400);
        }

        // Crear expediente
        $expedienteId = DB::table('expedientes_clinicos')->insertGetId([
            'paciente_id' => $paciente->id,
            'user_id' => $user->id,
            'antecedentes_personales' => $params['antecedentes_personales'] ?? null,
            'antecedentes_familiares' => $params['antecedentes_familiares'] ?? null,
            'diagnostico_actual' => $params['diagnostico'] ?? null,
            'tratamiento_actual' => $params['tratamiento'] ?? null,
            'notas' => $params['notas'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Expediente clínico creado exitosamente para {$paciente->nombre} {$paciente->apellidoPat}",
            'expediente_id' => $expedienteId
        ]);
    }

    private function editarExpediente($user, $params)
    {
        if (!isset($params['paciente_nombre'])) {
            return response()->json(['success' => false, 'error' => 'Nombre del paciente requerido'], 400);
        }

        // Buscar paciente
        $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
        
        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['paciente_nombre']}'"
            ], 404);
        }

        // Buscar expediente
        $expediente = DB::table('expedientes_clinicos')
            ->where('paciente_id', $paciente->id)
            ->first();

        if (!$expediente) {
            return response()->json([
                'success' => false,
                'error' => "El paciente no tiene expediente clínico. Usa 'crear expediente' primero."
            ], 404);
        }

        // Preparar campos a actualizar
        $updates = ['updated_at' => now()];
        
        if (isset($params['antecedentes_personales'])) {
            $updates['antecedentes_personales'] = $params['antecedentes_personales'];
        }
        if (isset($params['antecedentes_familiares'])) {
            $updates['antecedentes_familiares'] = $params['antecedentes_familiares'];
        }
        if (isset($params['diagnostico'])) {
            $updates['diagnostico_actual'] = $params['diagnostico'];
        }
        if (isset($params['tratamiento'])) {
            $updates['tratamiento_actual'] = $params['tratamiento'];
        }
        if (isset($params['notas'])) {
            $updates['notas'] = $params['notas'];
        }

        // Actualizar expediente
        DB::table('expedientes_clinicos')
            ->where('id', $expediente->id)
            ->update($updates);

        return response()->json([
            'success' => true,
            'message' => "Expediente de {$paciente->nombre} {$paciente->apellidoPat} actualizado exitosamente"
        ]);
    }

    private function generarReporteClinico($user, $params)
    {
        if (!isset($params['paciente_nombre']) || !isset($params['tipo'])) {
            return response()->json(['success' => false, 'error' => 'Nombre del paciente y tipo de reporte requeridos'], 400);
        }

        $tipo = $params['tipo']; // nutricional, psicologico, fisioterapia, general

        // Buscar paciente
        $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
        
        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['paciente_nombre']}'"
            ], 404);
        }

        // Obtener expediente
        $expediente = DB::table('expedientes_clinicos')
            ->where('paciente_id', $paciente->id)
            ->first();

        // Construir contexto para IA
        $contexto = "Genera un reporte clínico profesional de tipo '{$tipo}' para:\n\n";
        $contexto .= "PACIENTE: {$paciente->nombre} {$paciente->apellidoPat}\n";
        $contexto .= "EDAD: " . ($paciente->edad ?? 'N/A') . " años\n";
        $contexto .= "GÉNERO: " . ($paciente->genero == 1 ? 'Masculino' : 'Femenino') . "\n\n";

        if ($expediente) {
            $contexto .= "ANTECEDENTES PERSONALES: " . ($expediente->antecedentes_personales ?? 'Sin información') . "\n";
            $contexto .= "ANTECEDENTES FAMILIARES: " . ($expediente->antecedentes_familiares ?? 'Sin información') . "\n";
            $contexto .= "DIAGNÓSTICO: " . ($expediente->diagnostico_actual ?? 'Sin información') . "\n";
            $contexto .= "TRATAMIENTO ACTUAL: " . ($expediente->tratamiento_actual ?? 'Sin información') . "\n\n";
        }

        // Historial de citas recientes
        $citasRecientes = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('estado', 'completada')
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();

        if ($citasRecientes->count() > 0) {
            $contexto .= "HISTORIAL DE CITAS (últimas 5):\n";
            foreach ($citasRecientes as $cita) {
                $contexto .= "- {$cita->fecha}: " . ($cita->notas ?? 'Sin notas') . "\n";
            }
        }

        // Solicitar reporte a IA
        $result = $this->aiService->generarReporteClinico($contexto, $tipo);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Error al generar el reporte'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'reporte' => $result['reporte'],
            'tipo' => $tipo,
            'paciente' => $paciente->nombre . ' ' . $paciente->apellidoPat,
            'generado_en' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private function buscarEnExpedientes($user, $params)
    {
        if (!isset($params['termino'])) {
            return response()->json(['success' => false, 'error' => 'Término de búsqueda requerido'], 400);
        }

        $termino = $params['termino'];
        $clinicaId = $user->clinica_id;

        $resultados = DB::table('expedientes_clinicos')
            ->join('pacientes', 'expedientes_clinicos.paciente_id', '=', 'pacientes.id')
            ->where('pacientes.clinica_id', $clinicaId)
            ->where(function($query) use ($termino) {
                $query->where('expedientes_clinicos.antecedentes_personales', 'LIKE', "%{$termino}%")
                      ->orWhere('expedientes_clinicos.antecedentes_familiares', 'LIKE', "%{$termino}%")
                      ->orWhere('expedientes_clinicos.diagnostico_actual', 'LIKE', "%{$termino}%")
                      ->orWhere('expedientes_clinicos.tratamiento_actual', 'LIKE', "%{$termino}%")
                      ->orWhere('expedientes_clinicos.notas', 'LIKE', "%{$termino}%");
            })
            ->select(
                'pacientes.id',
                'pacientes.nombre',
                'pacientes.apellidoPat',
                'expedientes_clinicos.diagnostico_actual',
                'expedientes_clinicos.updated_at'
            )
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'termino_buscado' => $termino,
            'resultados' => $resultados->map(function($r) {
                return [
                    'paciente' => $r->nombre . ' ' . $r->apellidoPat,
                    'diagnostico' => $r->diagnostico_actual ?? 'Sin diagnóstico',
                    'ultima_actualizacion' => $r->updated_at
                ];
            })->toArray(),
            'total_encontrados' => $resultados->count()
        ]);
    }

    private function compararExpedientes($user, $params)
    {
        if (!isset($params['paciente_nombre']) || !isset($params['fecha_inicio']) || !isset($params['fecha_fin'])) {
            return response()->json(['success' => false, 'error' => 'Nombre del paciente y fechas requeridas'], 400);
        }

        // Buscar paciente
        $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
        
        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$params['paciente_nombre']}'"
            ], 404);
        }

        // Obtener expedientes en el rango de fechas
        $expedientes = DB::table('expedientes_clinicos')
            ->where('paciente_id', $paciente->id)
            ->whereBetween('updated_at', [$params['fecha_inicio'], $params['fecha_fin']])
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($expedientes->count() < 2) {
            return response()->json([
                'success' => false,
                'error' => 'No hay suficientes versiones del expediente para comparar en ese rango de fechas'
            ], 404);
        }

        $primero = $expedientes->first();
        $ultimo = $expedientes->last();

        $comparacion = [
            'paciente' => $paciente->nombre . ' ' . $paciente->apellidoPat,
            'periodo' => [
                'inicio' => $params['fecha_inicio'],
                'fin' => $params['fecha_fin']
            ],
            'cambios' => [
                'diagnostico' => [
                    'antes' => $primero->diagnostico_actual ?? 'Sin información',
                    'despues' => $ultimo->diagnostico_actual ?? 'Sin información'
                ],
                'tratamiento' => [
                    'antes' => $primero->tratamiento_actual ?? 'Sin información',
                    'despues' => $ultimo->tratamiento_actual ?? 'Sin información'
                ]
            ],
            'total_actualizaciones' => $expedientes->count()
        ];

        return response()->json([
            'success' => true,
            'comparacion' => $comparacion
        ]);
    }

    // Función helper para buscar paciente
    private function buscarPacientePorNombre($user, $nombreBuscado)
    {
        $normalizar = function($texto) {
            $texto = mb_strtolower($texto, 'UTF-8');
            $texto = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
                ['a', 'e', 'i', 'o', 'u', 'n'],
                $texto
            );
            return trim($texto);
        };
        
        $nombreBuscar = $normalizar($nombreBuscado);
        
        $pacientes = DB::table('pacientes')
            ->where('clinica_id', $user->clinica_id)
            ->get();
        
        foreach ($pacientes as $p) {
            $nombreCompleto = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
            $nombrePaciente = $normalizar($p->nombre);
            
            if ($nombreCompleto === $nombreBuscar || 
                $nombrePaciente === $nombreBuscar ||
                str_starts_with($nombreCompleto, $nombreBuscar) ||
                strpos($nombreCompleto, $nombreBuscar) !== false) {
                return $p;
            }
        }
        
        return null;
    }

    // ===== MÉTODOS DE GESTIÓN FINANCIERA =====

    /**
     * Obtener corte de caja del día o fecha específica
     */
    private function obtenerCorteCaja($user, $params)
    {
        try {
            $clinicaId = $user->clinica_id;
            $sucursalNombre = $params['sucursal'] ?? null;
            $fecha = $params['fecha'] ?? 'hoy';
            
            // Parsear fecha
            if ($fecha === 'hoy') {
                $fecha = now()->toDateString();
            } elseif ($fecha === 'ayer') {
                $fecha = now()->subDay()->toDateString();
            }
            
            Log::info('🔍 Consultando corte de caja', [
                'clinica_id' => $clinicaId,
                'fecha' => $fecha,
                'sucursal' => $sucursalNombre ?? 'Todas'
            ]);
            
            // Query base - USAR created_at en lugar de fecha_pago
            $query = DB::table('pagos')
                ->where('clinica_id', $clinicaId)
                ->whereDate('created_at', $fecha);
            
            // Filtrar por sucursal si se especifica
            if ($sucursalNombre) {
                $sucursal = DB::table('sucursales')
                    ->where('clinica_id', $clinicaId)
                    ->where('nombre', 'like', "%{$sucursalNombre}%")
                    ->first();
                
                if ($sucursal) {
                    $query->where('sucursal_id', $sucursal->id);
                }
            }
            
            $pagos = $query->get();
            
            Log::info('📊 Pagos encontrados: ' . $pagos->count());
            
            if ($pagos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No hay pagos registrados para esta fecha.',
                    'corte' => [
                        'fecha' => $fecha,
                        'total' => 0,
                        'cantidad_pagos' => 0,
                        'por_metodo' => []
                    ]
                ]);
            }
            
            // Calcular totales
            $total = 0;
            $porMetodo = [
                'efectivo' => 0,
                'tarjeta' => 0,
                'transferencia' => 0
            ];
            $pagosExitosos = 0;
            
            foreach ($pagos as $pago) {
                try {
                    // Desencriptar monto con manejo de errores
                    $montoEncriptado = $pago->monto;
                    Log::info('💰 Procesando pago ID ' . $pago->id, [
                        'metodo' => $pago->metodo_pago,
                        'monto_encriptado_length' => strlen($montoEncriptado)
                    ]);
                    
                    // Usar Crypt::decryptString() para strings simples sin serialización
                    $monto = \Illuminate\Support\Facades\Crypt::decryptString($montoEncriptado);
                    $montoFloat = floatval($monto);
                    
                    Log::info('✅ Pago ID ' . $pago->id . ' desencriptado: $' . $montoFloat);
                    
                    $total += $montoFloat;
                    
                    $metodo = $pago->metodo_pago;
                    if (isset($porMetodo[$metodo])) {
                        $porMetodo[$metodo] += $montoFloat;
                    }
                    
                    $pagosExitosos++;
                } catch (\Exception $e) {
                    Log::error('❌ Error desencriptando pago ID ' . $pago->id . ': ' . $e->getMessage());
                    Log::error('Monto encriptado: ' . substr($pago->monto, 0, 50) . '...');
                    continue; // Saltar este pago si hay error de desencriptación
                }
            }
            
            Log::info('💵 Resumen corte de caja', [
                'total_pagos' => $pagos->count(),
                'pagos_procesados' => $pagosExitosos,
                'total' => $total,
                'efectivo' => $porMetodo['efectivo'],
                'tarjeta' => $porMetodo['tarjeta'],
                'transferencia' => $porMetodo['transferencia']
            ]);
            
            return response()->json([
                'success' => true,
                'corte' => [
                    'fecha' => $fecha,
                    'total' => number_format($total, 2),
                    'cantidad_pagos' => $pagos->count(),
                    'pagos_procesados' => $pagosExitosos,
                    'por_metodo' => [
                        'efectivo' => number_format($porMetodo['efectivo'], 2),
                        'tarjeta' => number_format($porMetodo['tarjeta'], 2),
                        'transferencia' => number_format($porMetodo['transferencia'], 2)
                    ],
                    'sucursal' => $sucursalNombre ?? 'Todas'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo corte de caja: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener corte de caja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consultar adeudos de un paciente
     */
    private function consultarAdeudos($user, $params)
    {
        try {
            if (!isset($params['paciente_nombre'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nombre del paciente requerido'
                ], 400);
            }
            
            $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
            
            if (!$paciente) {
                return response()->json([
                    'success' => false,
                    'encontrado' => false,
                    'message' => 'Paciente no encontrado'
                ]);
            }
            
            // Obtener pagos del paciente
            $pagos = DB::table('pagos')
                ->where('clinica_id', $user->clinica_id)
                ->where('paciente_id', $paciente->id)
                ->orderBy('fecha_pago', 'desc')
                ->get();
            
            $totalPagado = 0;
            foreach ($pagos as $pago) {
                $totalPagado += decrypt($pago->monto);
            }
            
            // Por ahora, si no hay información de deuda total, solo mostrar pagos
            return response()->json([
                'success' => true,
                'paciente' => [
                    'nombre' => $paciente->nombre . ' ' . $paciente->apellidoPat,
                    'expediente' => $paciente->expediente
                ],
                'total_pagado' => number_format($totalPagado, 2),
                'cantidad_pagos' => $pagos->count(),
                'ultimo_pago' => $pagos->isNotEmpty() ? [
                    'fecha' => $pagos->first()->fecha_pago,
                    'monto' => number_format(decrypt($pagos->first()->monto), 2),
                    'metodo' => $pagos->first()->metodo_pago
                ] : null
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error consultando adeudos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al consultar adeudos'
            ], 500);
        }
    }

    /**
     * Resumen de ingresos mensual
     */
    private function resumenIngresosMensual($user, $params)
    {
        try {
            $mes = $params['mes'] ?? now()->format('m');
            $anio = $params['anio'] ?? now()->format('Y');
            
            // Convertir nombre de mes a número si es necesario
            $meses = [
                'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
            ];
            
            if (isset($meses[strtolower($mes)])) {
                $mes = $meses[strtolower($mes)];
            }
            
            $pagos = DB::table('pagos')
                ->where('clinica_id', $user->clinica_id)
                ->whereYear('fecha_pago', $anio)
                ->whereMonth('fecha_pago', $mes)
                ->get();
            
            if ($pagos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No hay ingresos registrados para este mes.',
                    'resumen' => [
                        'mes' => $mes,
                        'anio' => $anio,
                        'total' => 0,
                        'cantidad_pagos' => 0
                    ]
                ]);
            }
            
            $total = 0;
            $porMetodo = ['efectivo' => 0, 'tarjeta' => 0, 'transferencia' => 0];
            
            foreach ($pagos as $pago) {
                $monto = decrypt($pago->monto);
                $total += $monto;
                
                if (isset($porMetodo[$pago->metodo_pago])) {
                    $porMetodo[$pago->metodo_pago] += $monto;
                }
            }
            
            $nombreMes = array_search((int)$mes, $meses) ?: $mes;
            
            return response()->json([
                'success' => true,
                'resumen' => [
                    'mes' => ucfirst($nombreMes),
                    'anio' => $anio,
                    'total' => number_format($total, 2),
                    'cantidad_pagos' => $pagos->count(),
                    'promedio_diario' => number_format($total / now()->daysInMonth, 2),
                    'por_metodo' => [
                        'efectivo' => number_format($porMetodo['efectivo'], 2),
                        'tarjeta' => number_format($porMetodo['tarjeta'], 2),
                        'transferencia' => number_format($porMetodo['transferencia'], 2)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo resumen mensual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener resumen de ingresos'
            ], 500);
        }
    }

    /**
     * Verificar si existe un pago firmado de un paciente
     */
    private function verificarPagoFirmado($user, $params)
    {
        try {
            if (!isset($params['paciente_nombre']) || !isset($params['monto'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nombre del paciente y monto requeridos'
                ], 400);
            }
            
            $paciente = $this->buscarPacientePorNombre($user, $params['paciente_nombre']);
            
            if (!$paciente) {
                return response()->json([
                    'success' => false,
                    'encontrado' => false,
                    'message' => 'Paciente no encontrado'
                ]);
            }
            
            $montoBuscado = (float)$params['monto'];
            
            // Buscar pagos del paciente con monto similar
            $pagos = DB::table('pagos')
                ->where('clinica_id', $user->clinica_id)
                ->where('paciente_id', $paciente->id)
                ->whereNotNull('firma_paciente')
                ->orderBy('fecha_pago', 'desc')
                ->get();
            
            $pagoEncontrado = null;
            foreach ($pagos as $pago) {
                $montoPago = (float)decrypt($pago->monto);
                // Comparar con tolerancia de $0.01
                if (abs($montoPago - $montoBuscado) < 0.01) {
                    $pagoEncontrado = $pago;
                    break;
                }
            }
            
            if ($pagoEncontrado) {
                return response()->json([
                    'success' => true,
                    'pagado' => true,
                    'pago' => [
                        'fecha' => $pagoEncontrado->fecha_pago,
                        'monto' => number_format(decrypt($pagoEncontrado->monto), 2),
                        'metodo' => $pagoEncontrado->metodo_pago,
                        'concepto' => decrypt($pagoEncontrado->concepto),
                        'firmado' => !empty($pagoEncontrado->firma_paciente)
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'pagado' => false,
                'message' => "No se encontró un pago firmado de $" . number_format($montoBuscado, 2)
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error verificando pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al verificar pago'
            ], 500);
        }
    }
}
