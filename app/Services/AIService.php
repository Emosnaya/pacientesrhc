<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $client;
    protected $apiKey;
    protected $model;
    protected $availableModels;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->availableModels = config('gemini.models');
        $this->model = $this->selectBestModel();
        $this->client = new Client([
            'base_uri' => config('gemini.base_url'),
            'timeout' => config('gemini.timeout'),
        ]);
    }

    /**
     * Obtener configuración del asistente según el tipo de clínica
     */
    protected function getAssistantConfig($tipoClinica)
    {
        $configs = [
            'rehabilitacion_cardiopulmonar' => [
                'name' => 'Dr. CardioBot',
                'specialty' => 'cardiología y rehabilitación cardiopulmonar',
                'description' => 'un asistente médico virtual especializado en cardiología y rehabilitación cardiopulmonar',
                'focus' => 'salud cardiovascular y pulmonar',
                'treatments' => 'tratamientos y procedimientos cardíacos y pulmonares'
            ],
            'fisioterapia' => [
                'name' => 'FisioBot',
                'specialty' => 'fisioterapia y rehabilitación física',
                'description' => 'un asistente especializado en fisioterapia y rehabilitación física',
                'focus' => 'rehabilitación motora, lesiones deportivas y terapia física',
                'treatments' => 'tratamientos fisioterapéuticos, ejercicios de rehabilitación y terapia manual'
            ],
            'dental' => [
                'name' => 'DentalBot',
                'specialty' => 'odontología',
                'description' => 'un asistente dental especializado en salud bucal',
                'focus' => 'salud dental, prevención de caries y tratamientos odontológicos',
                'treatments' => 'procedimientos dentales, ortodoncia, endodoncia y periodoncia'
            ],
            'psicologia' => [
                'name' => 'PsicoBot',
                'specialty' => 'psicología clínica',
                'description' => 'un asistente psicológico especializado en salud mental',
                'focus' => 'salud mental, terapia psicológica y bienestar emocional',
                'treatments' => 'terapias psicológicas, técnicas de manejo emocional y apoyo psicológico'
            ],
            'nutricion' => [
                'name' => 'NutriBot',
                'specialty' => 'nutrición clínica',
                'description' => 'un asistente nutricional especializado en alimentación saludable',
                'focus' => 'nutrición, planes alimenticios y hábitos saludables',
                'treatments' => 'planes nutricionales, dietas terapéuticas y educación alimentaria'
            ]
        ];

        return $configs[$tipoClinica] ?? $configs['rehabilitacion_cardiopulmonar'];
    }

    /**
     * Seleccionar el mejor modelo disponible basado en uso actual
     */
    protected function selectBestModel()
    {
        // Por ahora usar el primario, pero puede expandirse con lógica de tracking
        return $this->availableModels['primary'] ?? config('gemini.model');
    }

    /**
     * Intentar con fallback si el modelo actual falla por límite
     */
    protected function makeRequestWithFallback($endpoint, $data)
    {
        $models = array_values($this->availableModels);
        $lastError = null;

        foreach ($models as $model) {
            try {
                $this->model = $model;
                Log::info("⚡ Intentando con modelo: {$model}");
                
                $response = $this->client->post("/v1beta/models/{$model}:{$endpoint}", [
                    'json' => $data,
                    'query' => ['key' => $this->apiKey]
                ]);

                $result = json_decode($response->getBody()->getContents(), true);
                Log::info("✅ Modelo {$model} funcionó correctamente");
                return ['success' => true, 'data' => $result, 'model' => $model];
                
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("⚠️ Modelo {$model} falló: {$lastError}");
                
                // Si es error de rate limit (429), intentar con siguiente modelo
                if (str_contains($lastError, '429') || str_contains($lastError, 'quota')) {
                    continue;
                }
                // Si es otro tipo de error, fallar inmediatamente
                break;
            }
        }

        return ['success' => false, 'error' => $lastError];
    }

    /**
     * Transcribir audio a texto usando Gemini
     * Nota: Gemini Pro no soporta transcripción de audio directamente.
     * Esta funcionalidad se mantiene usando la Web Speech API en el frontend (gratis)
     */
    public function transcribeAudio($audioPath)
    {
        // Gemini Pro no tiene capacidad de transcripción de audio
        // Usar Web Speech API en el frontend (VoiceTranscriptionFree.jsx)
        Log::info('🎤 Transcripción de audio solo disponible en frontend (Web Speech API)');
        
        return [
            'success' => false,
            'error' => 'La transcripción de audio se realiza en el frontend usando Web Speech API'
        ];
    }

    /**
     * Autocompletar texto médico usando Gemini
     */
    public function autocompleteText($text, $context = '', $tipoReporte = 'general')
    {
        try {
            $prompts = [
                'nutri' => 'Eres un asistente especializado en nutrición clínica para pacientes cardíacos. Completa el texto de manera profesional y médicamente apropiada.',
                'psico' => 'Eres un asistente especializado en psicología clínica para pacientes cardíacos. Completa el texto de manera profesional y empática.',
                'fisio' => 'Eres un asistente especializado en fisioterapia cardiovascular. Completa el texto de manera profesional y técnica.',
                'clinico' => 'Eres un asistente médico especializado en cardiología. Completa el texto de manera profesional y clínica.',
                'general' => 'Eres un asistente médico especializado en rehabilitación cardíaca. Completa el texto de manera profesional.'
            ];

            $systemPrompt = $prompts[$tipoReporte] ?? $prompts['general'];
            $systemPrompt .= ' Devuelve SOLO la continuación del texto, sin repetir lo que ya está escrito. Máximo 2-3 oraciones.';

            $prompt = $systemPrompt . "\n\n";
            
            if (!empty($context)) {
                $prompt .= "Contexto del paciente: $context\n\n";
            }

            $prompt .= "Completa este texto: $text";

            $completion = $this->callGemini($prompt, 150);

            if ($completion['success']) {
                return [
                    'success' => true,
                    'completion' => trim($completion['text'])
                ];
            }

            return $completion;

        } catch (\Exception $e) {
            Log::error('❌ Error en autocompletado: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al generar autocompletado'
            ];
        }
    }

    /**
     * Resumir texto largo usando Gemini
     */
    public function summarizeText($text, $tipoReporte = 'general')
    {
        try {
            $prompts = [
                'nutri' => 'Resume este reporte nutricional de manera concisa, destacando los puntos más importantes.',
                'psico' => 'Resume esta evaluación psicológica de manera concisa, destacando los hallazgos principales.',
                'fisio' => 'Resume este reporte de fisioterapia de manera concisa, destacando el progreso y recomendaciones clave.',
                'general' => 'Resume este reporte médico de manera concisa y profesional.'
            ];

            $userPrompt = $prompts[$tipoReporte] ?? $prompts['general'];

            $prompt = "Eres un asistente médico que resume reportes de manera profesional y concisa.\n\n";
            $prompt .= "$userPrompt\n\nTexto:\n$text";

            $summary = $this->callGemini($prompt, 300, 0.5);

            if ($summary['success']) {
                return [
                    'success' => true,
                    'summary' => trim($summary['text'])
                ];
            }

            return $summary;

        } catch (\Exception $e) {
            Log::error('❌ Error en resumen: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al generar resumen'
            ];
        }
    }

    /**
     * Generar insights automáticos del dashboard
     * Analiza datos de pacientes y genera reporte ejecutivo
     */
    public function generateDashboardInsights($pacientes, $reportes = [])
    {
        try {
            // Preparar datos estadísticos
            $totalPacientes = count($pacientes);
            $pacientesActivos = collect($pacientes)->filter(function($p) {
                return !empty($p['updated_at']) &&
                       now()->diffInDays($p['updated_at']) <= 30;
            })->count();

            // Analizar reportes recientes
            $reportesRecientes = collect($reportes)->filter(function($r) {
                return now()->diffInDays($r['created_at']) <= 7;
            });

            $mejoras = 0;
            $requierenSeguimiento = 0;

            // Análisis básico (esto se puede mejorar con más lógica de negocio)
            foreach ($pacientes as $paciente) {
                // Pacientes que requieren seguimiento (sin actividad reciente)
                if (!empty($paciente['updated_at']) && now()->diffInDays($paciente['updated_at']) > 14) {
                    $requierenSeguimiento++;
                }
            }

            // Simular mejoras basadas en reportes recientes (se puede refinar)
            $mejoras = floor($reportesRecientes->count() * 0.3);

            // Crear contexto para Gemini
            $contexto = "
Total de pacientes: $totalPacientes
Pacientes activos (último mes): $pacientesActivos
Reportes recientes (última semana): {$reportesRecientes->count()}
Pacientes que requieren seguimiento: $requierenSeguimiento
Pacientes con mejoras significativas: $mejoras
";

            $prompt = "Eres un analista médico especializado en rehabilitación cardíaca. Genera insights ejecutivos concisos y accionables para directores de clínica. Usa un tono profesional y resalta información clave.\n\n";
            $prompt .= "Genera un reporte ejecutivo semanal basado en estos datos:\n\n$contexto\n\n";
            $prompt .= "Incluye:\n";
            $prompt .= "1. Resumen general del estado de la clínica\n";
            $prompt .= "2. Pacientes que requieren atención inmediata\n";
            $prompt .= "3. Logros y mejoras significativas\n";
            $prompt .= "4. Recomendaciones accionables\n\n";
            $prompt .= "Mantén el reporte conciso (máximo 200 palabras).";

            $response = $this->callGemini($prompt, 400, 0.7);

            if (!$response['success']) {
                throw new \Exception($response['error'] ?? 'Error al generar insights');
            }

            $insights = trim($response['text']);

            return [
                'success' => true,
                'insights' => $insights,
                'stats' => [
                    'total_pacientes' => $totalPacientes,
                    'pacientes_activos' => $pacientesActivos,
                    'requieren_seguimiento' => $requierenSeguimiento,
                    'mejoras_significativas' => $mejoras,
                    'reportes_recientes' => $reportesRecientes->count(),
                ],
                'generated_at' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error al generar insights: ' . $e->getMessage());
            
            // Fallback: retornar insights básicos sin IA
            return [
                'success' => true,
                'insights' => "Resumen ejecutivo: Se tienen $totalPacientes pacientes registrados, con $pacientesActivos activos en el último mes. Se identificaron $requierenSeguimiento pacientes que requieren seguimiento y $mejoras con mejoras significativas.",
                'stats' => [
                    'total_pacientes' => $totalPacientes ?? 0,
                    'pacientes_activos' => $pacientesActivos ?? 0,
                    'requieren_seguimiento' => $requierenSeguimiento ?? 0,
                    'mejoras_significativas' => $mejoras ?? 0,
                    'reportes_recientes' => count($reportes ?? []),
                ],
                'generated_at' => now()->toDateTimeString(),
                'fallback' => true
            ];
        }
    }

    /**
     * Hacer llamada a Google Gemini API
     * 
     * @param string $prompt El texto a enviar a Gemini
     * @param int $maxTokens Número máximo de tokens en la respuesta
     * @param float $temperature Creatividad de la respuesta (0.0 - 1.0)
     * @return array
     */
    private function callGemini($prompt, $maxTokens = null, $temperature = null)
    {
        try {
            if (empty($this->apiKey)) {
                Log::warning('⚠️ API Key de Gemini no configurada');
                return [
                    'success' => false,
                    'error' => 'API Key de Gemini no configurada'
                ];
            }

            $maxTokens = $maxTokens ?? config('gemini.max_tokens');
            $temperature = $temperature ?? config('gemini.temperature');

            $url = "models/{$this->model}:generateContent?key={$this->apiKey}";

            $body = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ]
            ];

            Log::info('🤖 Llamando a Gemini API...', ['model' => $this->model]);

            $response = $this->client->post($url, [
                'json' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Verificar estructura de respuesta de Gemini
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('❌ Respuesta de Gemini con formato inválido', ['data' => $data]);
                return [
                    'success' => false,
                    'error' => 'Respuesta inválida de Gemini API'
                ];
            }

            $text = $data['candidates'][0]['content']['parts'][0]['text'];

            Log::info('✅ Respuesta de Gemini recibida exitosamente');

            return [
                'success' => true,
                'text' => $text
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorBody = $e->getResponse()->getBody()->getContents();
            
            Log::error('❌ Error en llamada a Gemini API', [
                'status' => $statusCode,
                'error' => $errorBody
            ]);

            if ($statusCode === 400) {
                return [
                    'success' => false,
                    'error' => 'Solicitud inválida a Gemini API'
                ];
            } else if ($statusCode === 403) {
                return [
                    'success' => false,
                    'error' => 'API Key inválida o sin permisos'
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al comunicarse con Gemini API'
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error general en Gemini: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al comunicarse con Gemini'
            ];
        }
    }

    /**
     * Analizar estado general de un paciente (con datos pseudonimizados)
     */
    public function analizarEstadoPaciente($datosClinicosAnonimos)
    {
        try {
            $prompt = "Eres un médico especialista en cardiología y rehabilitación cardíaca. Analiza el siguiente caso clínico y proporciona un resumen del estado general del paciente. IMPORTANTE: Los datos están pseudonimizados por privacidad.\n\n";
            
            $prompt .= "DATOS DEL PACIENTE:\n";
            $prompt .= "- ID: {$datosClinicosAnonimos['id_anonimo']}\n";
            $prompt .= "- Edad: {$datosClinicosAnonimos['edad']} años\n";
            $prompt .= "- Género: {$datosClinicosAnonimos['genero']}\n";
            $prompt .= "- Tipo: {$datosClinicosAnonimos['tipo_paciente']}\n\n";
            
            $prompt .= "MEDICIONES:\n";
            $prompt .= "- Peso: {$datosClinicosAnonimos['mediciones']['peso']} kg\n";
            $prompt .= "- Talla: {$datosClinicosAnonimos['mediciones']['talla']} cm\n";
            $prompt .= "- IMC: {$datosClinicosAnonimos['mediciones']['imc']}\n";
            $prompt .= "- Cintura: {$datosClinicosAnonimos['mediciones']['cintura']} cm\n\n";
            
            $prompt .= "DIAGNÓSTICO: {$datosClinicosAnonimos['diagnostico']}\n\n";
            $prompt .= "MEDICAMENTOS: {$datosClinicosAnonimos['medicamentos']}\n\n";
            
            $prompt .= "HISTORIAL DE CITAS:\n";
            $prompt .= "- Total de citas: {$datosClinicosAnonimos['historial_citas']['total']}\n";
            $prompt .= "- Últimos 3 meses: {$datosClinicosAnonimos['historial_citas']['ultimos_3_meses']}\n";
            $prompt .= "- Última cita: {$datosClinicosAnonimos['historial_citas']['ultima_fecha']}\n\n";
            
            $prompt .= "INSTRUCCIONES:\n";
            $prompt .= "1. Evalúa el estado general considerando IMC, diagnóstico y medicación\n";
            $prompt .= "2. Identifica factores de riesgo cardiovascular presentes\n";
            $prompt .= "3. Evalúa adherencia al tratamiento (basado en frecuencia de citas)\n";
            $prompt .= "4. Proporciona recomendaciones generales de seguimiento\n";
            $prompt .= "5. Sé conciso (máximo 250 palabras)\n";
            $prompt .= "6. NO uses formato markdown ni asteriscos\n";
            $prompt .= "7. NO menciones que los datos están pseudonimizados al usuario final\n\n";
            $prompt .= "Responde en español, de forma profesional pero comprensible.";

            $requestData = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $prompt]]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500
                ]
            ];

            $response = $this->client->post("/v1beta/models/{$this->model}:generateContent", [
                'json' => $requestData,
                'query' => ['key' => $this->apiKey]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $analisis = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            Log::info('✅ Análisis de paciente generado exitosamente');
            
            return [
                'success' => true,
                'analisis' => $analisis,
                'model' => $this->model
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error en análisis de paciente: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al comunicarse con Gemini'
            ];
        }
    }

    /**
     * Chat médico - Asistente virtual para consultas médicas y agendamiento
     */
    public function medicalChat($message, $conversationHistory = [], $contextoClinica = [])
    {
        try {
            // Obtener configuración del asistente según el tipo de clínica
            $tipoClinica = $contextoClinica['tipo_clinica'] ?? 'rehabilitacion_cardiopulmonar';
            $assistantConfig = $this->getAssistantConfig($tipoClinica);
            
            // Construir información contextual de la clínica
            $infoClinica = '';
            if (!empty($contextoClinica)) {
                $infoClinica = "\n\nINFORMACIÓN DE TU CLÍNICA:";
                $infoClinica .= "\n- Total de pacientes registrados: {$contextoClinica['total_pacientes']}";
                $infoClinica .= "\n- Citas programadas para hoy: {$contextoClinica['citas_hoy']}";
                
                if (!empty($contextoClinica['citas_proximas'])) {
                    $infoClinica .= "\n\nCITAS PRÓXIMAS (Próximos 7 días):";
                    foreach ($contextoClinica['citas_proximas'] as $cita) {
                        $infoClinica .= "\n- ID: {$cita['id']} | {$cita['fecha']} a las {$cita['hora']}: {$cita['paciente']} ({$cita['estado']})";
                    }
                } else {
                    $infoClinica .= "\n\nNo hay citas programadas para los próximos 7 días.";
                }
            }

            $systemPrompt = "Eres {$assistantConfig['name']}, {$assistantConfig['description']}. 
            
TUS CAPACIDADES PRINCIPALES:
1. 📅 Gestión de Citas: Consultar, agendar, modificar, eliminar (individual o masivo)
2. 👥 Gestión de Pacientes: Buscar, analizar estado, historial de citas
3. 📊 Análisis y Reportes: Métricas, predicciones, identificar pacientes en riesgo
4. 🔔 Notificaciones: Resúmenes diarios, alertas, sugerencias proactivas
5. 📋 Expedientes Clínicos: Crear, editar, consultar, generar reportes clínicos
6. 💡 Consultas Médicas: Información sobre {$assistantConfig['focus']}

ACCIONES DISPONIBLES (responde con [ACCION:nombre|param:valor]):

📅 GESTIÓN DE CITAS:
- [ACCION:cambiar_estado_cita|cita_id:123|estado:confirmada]
- [ACCION:cancelar_cita|cita_id:123|motivo:razón]
- [ACCION:eliminar_cita|cita_id:123]
- [ACCION:eliminar_citas_masivo|estado:cancelada|paciente_nombre:Juan|fecha:2026-02-04|mes:1|año:2026]
- [ACCION:agendar_cita|paciente_nombre:Juan Pérez|fecha:2026-02-10|hora:14:00|motivo:Consulta]

👥 GESTIÓN DE PACIENTES:
- [ACCION:buscar_paciente|nombre:Juan Pérez]
- [ACCION:analizar_paciente|nombre:Juan Pérez]
- [ACCION:contar_citas_paciente|nombre:Juan Pérez]

📊 ANÁLISIS Y REPORTES:
- [ACCION:obtener_metricas]
- [ACCION:obtener_analiticas_pacientes]
- [ACCION:generar_reporte_metricas|periodo:mes|formato:detallado]
  * periodo: dia, semana, mes, trimestre, año
  * formato: resumido, detallado
- [ACCION:analisis_predictivo_citas]
- [ACCION:identificar_pacientes_riesgo]
- [ACCION:sugerencias_mejora_operativa]

🔔 NOTIFICACIONES Y ALERTAS:
- [ACCION:generar_resumen_diario]
- [ACCION:obtener_alertas_seguimiento]
- [ACCION:sugerencias_proactivas]
- [ACCION:crear_evento|tipo:recordatorio|titulo:Llamar laboratorio|fecha:2026-02-05|hora:14:00]

📋 EXPEDIENTES CLÍNICOS:
- [ACCION:obtener_expediente|paciente_nombre:Juan Pérez]
- [ACCION:crear_expediente|paciente_nombre:Juan|antecedentes_personales:...|diagnostico:...|tratamiento:...|notas:...]
- [ACCION:editar_expediente|paciente_nombre:Juan|diagnostico:...|notas:...]
- [ACCION:generar_reporte_clinico|paciente_nombre:Juan|tipo:nutricional]
  * tipo: nutricional, psicologico, fisioterapia, general
- [ACCION:buscar_en_expedientes|termino:diabetes]
- [ACCION:comparar_expedientes|paciente_nombre:Juan|fecha_inicio:2026-01-01|fecha_fin:2026-02-04]

{$infoClinica}

REGLAS IMPORTANTES:
✅ Sé proactivo: Ofrece sugerencias útiles basadas en el contexto
✅ Usa las herramientas: Cuando el usuario necesite datos, ejecuta la acción correspondiente
✅ Respuestas breves: Máximo 200 palabras, directo al punto
✅ Formato limpio: NO uses asteriscos ni markdown en texto normal
✅ IDs internos: Los IDs de citas están en contexto, úsalos pero no los menciones al usuario
✅ Referencias: Cuando el usuario dice \"la primera\", \"la segunda\", busca el ID en el contexto
✅ Confirmaciones: Antes de eliminar masivamente, confirma cuántos registros afectará
✅ Privacidad: NUNCA menciones datos sensibles innecesariamente
✅ Precisión: Si no sabes algo, admítelo y recomienda consultar con el médico
✅ Hora formato: Siempre HH:MM (24h): 09:00, 14:00, 16:30 - NUNCA solo el número
- NUNCA des dosis específicas de medicamentos. Siempre indica que deben ser determinadas por el médico tratante

EJEMPLOS DE RESPUESTAS CON ACCIONES:

Usuario: ¿Cuántas citas tengo hoy?
Asistente: Tienes 2 citas hoy: 1) 14:00 con Virginia Flores y 2) 17:00 con Miguel Delgado. Ambas confirmadas.

Usuario: Cancela la segunda
Asistente: Voy a cancelar la cita de las 17:00 con Miguel Delgado. [ACCION:cancelar_cita|cita_id:52|motivo:Cancelado por solicitud del usuario]

Usuario: Cancela la cita de Juan Pérez de mañana
Asistente: Voy a cancelar la cita de Juan Pérez programada para mañana. [ACCION:cancelar_cita|cita_id:45|motivo:Cancelado por solicitud del usuario]

Usuario: Agenda una cita para María López mañana a las 3pm para chequeo
Asistente: Perfecto, voy a agendar la cita para María López mañana a las 15:00. [ACCION:agendar_cita|paciente_nombre:María López|fecha:2026-01-10|hora:15:00|motivo:Chequeo de rutina]

Usuario: ¿Está registrado el paciente Carlos Hernández?
Asistente: Déjame verificar si Carlos Hernández está en tu sistema. [ACCION:buscar_paciente|nombre:Carlos Hernández]

Usuario: Dame el estado general de María López
Asistente: Voy a analizar el estado clínico de María López. [ACCION:analizar_paciente|nombre:María López]

Usuario: ¿Cómo está Juan Pérez?
Asistente: Te genero un análisis de su estado actual. [ACCION:analizar_paciente|nombre:Juan Pérez]

Usuario: Existe un paciente llamado Ana Martinez?
Asistente: Voy a buscar a Ana Martinez en tu base de datos. [ACCION:buscar_paciente|nombre:Ana Martinez]

Usuario: Recuérdame llamar al laboratorio mañana a las 2pm
Asistente: Perfecto, he creado un recordatorio para mañana a las 2pm para llamar al laboratorio. [ACCION:crear_evento|tipo:recordatorio|titulo:Llamar al laboratorio|fecha:2026-01-10|hora:14:00]

Usuario: Dame un resumen de las métricas de este mes
Asistente: Claro, voy a obtener las métricas actualizadas de tu clínica. [ACCION:obtener_metricas]

Usuario: ¿Cuántos pacientes hombres y mujeres tengo?
Asistente: Voy a consultar las analíticas de tus pacientes. [ACCION:obtener_analiticas_pacientes]

Usuario: Dame información sobre la edad de mis pacientes
Asistente: Te muestro las estadísticas de edad de tus pacientes. [ACCION:obtener_analiticas_pacientes]

Usuario: ¿Cuántas citas ha tenido Lidia Ilvea?
Asistente: Voy a consultar el historial completo de citas de Lidia Ilvea. [ACCION:contar_citas_paciente|nombre:Lidia Ilvea]

Usuario: Cuenta las citas de Juan Pérez
Asistente: Voy a revisar cuántas citas ha tenido Juan Pérez. [ACCION:contar_citas_paciente|nombre:Juan Pérez]

Usuario: Historial de citas de María López
Asistente: Consultando el historial de citas de María López. [ACCION:contar_citas_paciente|nombre:María López]

Usuario: Elimina todas las citas canceladas
Asistente: Voy a eliminar todas las citas con estado cancelado. [ACCION:eliminar_citas_masivo|estado:cancelada]

Usuario: Borra las citas pendientes de Juan Pérez
Asistente: Eliminando todas las citas pendientes de Juan Pérez. [ACCION:eliminar_citas_masivo|estado:pendiente|paciente_nombre:Juan Pérez]

Usuario: Elimina todas las citas de hoy
Asistente: Voy a eliminar todas las citas programadas para hoy. [ACCION:eliminar_citas_masivo|fecha:2026-02-04]

Usuario: Borra las citas canceladas de María López del mes pasado
Asistente: Eliminando las citas canceladas de María López de enero. [ACCION:eliminar_citas_masivo|estado:cancelada|paciente_nombre:María López|mes:1|año:2026]

Usuario: Elimina todas las citas completadas de enero
Asistente: Voy a eliminar todas las citas completadas del mes de enero. [ACCION:eliminar_citas_masivo|estado:completada|mes:1|año:2026]

Usuario: Borra las citas pendientes del 10 al 20 de enero
Asistente: Eliminando citas pendientes entre el 10 y 20 de enero. [ACCION:eliminar_citas_masivo|estado:pendiente|fecha_inicio:2026-01-10|fecha_fin:2026-01-20]

📊 ANÁLISIS Y REPORTES AVANZADOS:
Usuario: Dame un reporte detallado de este mes
Asistente: Generando reporte completo de métricas del mes. [ACCION:generar_reporte_metricas|periodo:mes|formato:detallado]

Usuario: ¿Cuántas cancelaciones voy a tener la próxima semana?
Asistente: Voy a analizar los patrones de cancelación para predecir. [ACCION:analisis_predictivo_citas]

Usuario: ¿Qué pacientes están en riesgo?
Asistente: Identificando pacientes que requieren atención inmediata. [ACCION:identificar_pacientes_riesgo]

Usuario: Dame sugerencias para mejorar mi clínica
Asistente: Analizando datos operativos para generar recomendaciones. [ACCION:sugerencias_mejora_operativa]

🔔 NOTIFICACIONES Y RESÚMENES:
Usuario: Dame un resumen de hoy
Asistente: Preparando tu resumen diario con todas las actividades. [ACCION:generar_resumen_diario]

Usuario: ¿Qué alertas tengo?
Asistente: Revisando alertas y seguimientos pendientes. [ACCION:obtener_alertas_seguimiento]

Usuario: ¿Qué me recomiendas hacer hoy?
Asistente: Generando sugerencias proactivas basadas en tu situación actual. [ACCION:sugerencias_proactivas]

📋 EXPEDIENTES CLÍNICOS:
Usuario: Muéstrame el expediente de Juan Pérez
Asistente: Consultando expediente clínico completo. [ACCION:obtener_expediente|paciente_nombre:Juan Pérez]

Usuario: Crea un expediente para María López con diagnóstico de diabetes
Asistente: Creando expediente clínico nuevo. [ACCION:crear_expediente|paciente_nombre:María López|diagnostico:Diabetes tipo 2|notas:Paciente requiere seguimiento nutricional]

Usuario: Actualiza el expediente de Juan, cambió su tratamiento
Asistente: ¿Cuál es el nuevo tratamiento? (Esperando respuesta para completar)

Usuario: Genera un reporte nutricional para María López
Asistente: Generando reporte nutricional profesional. [ACCION:generar_reporte_clinico|paciente_nombre:María López|tipo:nutricional]

Usuario: Busca pacientes con hipertensión en los expedientes
Asistente: Buscando en todos los expedientes clínicos. [ACCION:buscar_en_expedientes|termino:hipertensión]

Usuario: Compara el expediente de Juan de enero a hoy
Asistente: Comparando evolución del paciente. [ACCION:comparar_expedientes|paciente_nombre:Juan Pérez|fecha_inicio:2026-01-01|fecha_fin:2026-02-04]

Usuario: ¿Qué es un electrocardiograma?
Asistente: Un electrocardiograma (ECG) es una prueba que registra la actividad eléctrica del corazón. Es indolora, rápida (5-10 min) y ayuda a detectar problemas como arritmias o infartos. ¿Necesitas agendar uno?";

            // Construir historial de conversación
            $contents = [];
            
            // Agregar contexto del sistema
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $systemPrompt]]
            ];
            $contents[] = [
                'role' => 'model',
                'parts' => [['text' => 'Entendido. Soy Dr. CardioBot y estoy listo para ayudarte con consultas médicas, información sobre citas de tu clínica y agendamiento.']]
            ];
            
            // Agregar historial previo (máximo 10 mensajes)
            $historyCount = 0;
            foreach ($conversationHistory as $msg) {
                if ($historyCount >= 10) break;
                
                $contents[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'model',
                    'parts' => [['text' => $msg['content']]]
                ];
                $historyCount++;
            }
            
            // Agregar mensaje actual
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];

            // Usar sistema de fallback para manejar límites
            $result = $this->makeRequestWithFallback('generateContent', [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 400,
                    'topP' => 0.8,
                    'topK' => 40
                ]
            ]);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => 'Todos los modelos han alcanzado su límite. Intenta más tarde.'
                ];
            }

            $data = $result['data'];
            $modelUsed = $result['model'];

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = $data['candidates'][0]['content']['parts'][0]['text'];
                
                return [
                    'success' => true,
                    'response' => $responseText,
                    'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? 0,
                    'model' => $modelUsed
                ];
            }

            return [
                'success' => false,
                'error' => 'No se pudo generar respuesta'
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error en chat médico: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al procesar la consulta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte clínico con IA
     */
    public function generarReporteClinico($contexto, $tipoReporte)
    {
        try {
            $prompts = [
                'nutricional' => 'Genera un reporte nutricional profesional basado en la información del paciente. Incluye: evaluación del estado nutricional, recomendaciones dietéticas específicas, plan alimenticio sugerido y objetivos a corto/mediano plazo.',
                'psicologico' => 'Genera un reporte psicológico profesional. Incluye: evaluación del estado emocional, observaciones clínicas relevantes, recomendaciones terapéuticas y plan de seguimiento.',
                'fisioterapia' => 'Genera un reporte de fisioterapia profesional. Incluye: evaluación funcional, plan de tratamiento, ejercicios recomendados, progreso esperado y consideraciones especiales.',
                'general' => 'Genera un reporte clínico general profesional. Incluye: resumen del estado actual, evaluación integral, recomendaciones médicas y plan de seguimiento.'
            ];

            $systemPrompt = $prompts[$tipoReporte] ?? $prompts['general'];
            $systemPrompt .= "\n\nIMPORTANTE:\n- Usa lenguaje médico profesional pero comprensible\n- Sé específico y basado en la información proporcionada\n- Incluye recomendaciones prácticas y accionables\n- Formato: párrafos claros, sin markdown\n- Máximo 400 palabras\n\n";

            $prompt = $systemPrompt . $contexto;

            $result = $this->callGemini($prompt, 800, 0.7);

            if ($result['success']) {
                return [
                    'success' => true,
                    'reporte' => trim($result['text'])
                ];
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('❌ Error generando reporte clínico: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al generar el reporte'
            ];
        }
    }
}
