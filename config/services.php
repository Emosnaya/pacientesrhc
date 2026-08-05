<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    | Registro de clínicas (B2B) y provisionamiento interno de consultorios.
    | Si el secret está vacío, los middlewares permiten acceso (solo desarrollo).
    */
    'clinic_registration' => [
        'secret' => env('CLINIC_REGISTRATION_SECRET'),
    ],

    'internal_consultorio' => [
        'secret' => env('INTERNAL_CONSULTORIO_SETUP_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Gateway
    |--------------------------------------------------------------------------
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio WhatsApp Integration
    |--------------------------------------------------------------------------
    */
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        // Formato: whatsapp:+52XXXXXXXXXX (prod) o whatsapp:+14155238886 (sandbox)
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        'enabled' => env('WHATSAPP_ENABLED', false),
        // Alias legacy usado por comandos antiguos
        'whatsapp_enabled' => env('WHATSAPP_ENABLED', false),
        // Callback de entrega (Twilio POSTea a esta URL). Vacío → APP_URL/api/whatsapp/status
        'status_callback' => (($cb = env('TWILIO_STATUS_CALLBACK')) && $cb !== '')
            ? $cb
            : (rtrim((string) env('APP_URL', ''), '/') . '/api/whatsapp/status'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blind indexes / búsqueda sobre campos cifrados
    |--------------------------------------------------------------------------
    */
    'search' => [
        'index_key' => env('SEARCH_INDEX_KEY', env('APP_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps
    |--------------------------------------------------------------------------
    | geocoding_key: restringida por IP (backend).
    | maps_key: para clientes móviles (bundle/package), se expone vía config Expo.
    */
    'google_maps' => [
        'geocoding_key' => env('GOOGLE_MAPS_GEOCODING_KEY', env('GOOGLE_MAPS_API_KEY')),
        'maps_key' => env('GOOGLE_MAPS_MOBILE_KEY', env('GOOGLE_MAPS_API_KEY')),
    ],

];
