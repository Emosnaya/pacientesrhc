<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*', 'finanzas/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://www.pacientesrhc.com',
        'https://api.pacientesrhc.com',
        'https://app.pacientesrhc.com',
        'https://lynkamed.mx',
        'https://www.lynkamed.mx',
        'https://app.lynkamed.mx',
        'https://api.lynkamed.mx',
    ],

    'allowed_origins_patterns' => ['/localhost.*/'],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-Clinic-Registration-Token',
        'X-Internal-Consultorio-Token',
        'X-XSRF-TOKEN',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
