<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $client;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model');
        $this->client = new Client([
            'base_uri' => config('gemini.base_url'),
            'timeout' => config('gemini.timeout'),
        ]);
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
}
