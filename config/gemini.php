<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Gemini API Key
    |--------------------------------------------------------------------------
    |
    | Aquí se especifica tu API key de Google Gemini.
    | Puedes obtener una en: https://makersuite.google.com/app/apikey
    |
    */

    'api_key' => env('GEMINI_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Gemini Model
    |--------------------------------------------------------------------------
    |
    | El modelo de Gemini a utilizar. Opciones:
    | - gemini-pro: Modelo optimizado para texto
    | - gemini-pro-vision: Modelo que soporta imágenes
    |
    */

    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | URL base de la API de Google Gemini
    |
    */

    'base_url' => 'https://generativelanguage.googleapis.com/v1/',

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Tiempo de espera máximo para las peticiones HTTP en segundos
    |
    */

    'timeout' => env('GEMINI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Temperature
    |--------------------------------------------------------------------------
    |
    | Control la creatividad de las respuestas (0.0 - 1.0)
    | Valores más altos = más creativo pero menos consistente
    |
    */

    'temperature' => env('GEMINI_TEMPERATURE', 0.7),

    /*
    |--------------------------------------------------------------------------
    | Max Output Tokens
    |--------------------------------------------------------------------------
    |
    | Número máximo de tokens en la respuesta
    |
    */

    'max_tokens' => env('GEMINI_MAX_TOKENS', 1000),
];
