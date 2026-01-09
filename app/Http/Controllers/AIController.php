<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        
        // Obtener todos los pacientes de la clínica
        $pacientes = DB::table('pacientes')
            ->where('clinica_id', $user->clinica_id)
            ->get();
        
        // Buscar paciente normalizando nombres
        $paciente = null;
        foreach ($pacientes as $p) {
            $nombreCompletoPaciente = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat . ' ' . $p->apellidoMat));
            $nombreApellidoPat = $normalizar(trim($p->nombre . ' ' . $p->apellidoPat));
            
            // Comparación flexible (contiene o coincide parcialmente)
            if (
                strpos($nombreCompletoPaciente, $nombreBuscar) !== false ||
                strpos($nombreBuscar, $nombreCompletoPaciente) !== false ||
                strpos($nombreCompletoPaciente, $nombreBuscar) !== false ||
                similar_text($nombreCompletoPaciente, $nombreBuscar) > (strlen($nombreBuscar) * 0.7)
            ) {
                $paciente = $p;
                break;
            }
        }

        if (!$paciente) {
            return response()->json([
                'success' => false,
                'error' => "No se encontró al paciente '{$nombreCompleto}' en tu clínica. Por favor verifica el nombre completo."
            ], 404);
        }

        // Validar que no exista cita duplicada
        $citaExistente = DB::table('citas')
            ->where('paciente_id', $paciente->id)
            ->where('fecha', $params['fecha'])
            ->where('hora', $params['hora'])
            ->where('estado', '!=', 'cancelada')
            ->first();

        if ($citaExistente) {
            return response()->json([
                'success' => false,
                'error' => 'Ya existe una cita para este paciente en la fecha y hora indicadas'
            ], 400);
        }

        // Crear la cita
        $citaId = DB::table('citas')->insertGetId([
            'paciente_id' => $paciente->id,
            'clinica_id' => $user->clinica_id,
            'user_id' => $user->id,
            'admin_id' => $user->id, // Agregar admin_id requerido
            'fecha' => $params['fecha'],
            'hora' => $hora,
            'estado' => 'confirmada',
            'primera_vez' => false,
            'notas' => $params['motivo'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Cita agendada exitosamente para {$paciente->nombre} {$paciente->apellidoPat} el {$params['fecha']} a las {$params['hora']}",
            'cita_id' => $citaId
        ]);
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
        
        // Obtener todos los pacientes de la clínica
        $pacientes = DB::table('pacientes')
            ->where('clinica_id', $user->clinica_id)
            ->get();
        
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
}
