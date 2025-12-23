<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
}
