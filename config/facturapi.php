<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Timbrado CFDI (cuenta maestra)
    |--------------------------------------------------------------------------
    |
    | user_key   → UserKey (sk_user_...). Obligatoria. Crea organizaciones por
    |              clínica/médico, sube CSD y obtiene las API keys de cada org.
    |
    | test_key   → Opcional (sk_test_... de la cuenta). Solo para pruebas
    |              directas contra la API; el timbrado normal usa las keys
    |              guardadas por organización en la base de datos.
    |
    | environment → "test" | "live"
    |              - test: siempre timbra con la key de prueba de cada org.
    |              - live: timbra con la key live de cada org (si ya existe).
    |              Después de pagar la activación (~$299 MXN), pon live aquí
    |              y sincroniza keys (Gestión clínica → CSD o re-subir CSD).
    |
    | Las keys LIVE no van en este archivo: se guardan por organización en
    | clinicas.facturapi_api_key_live y efirmas.facturapi_api_key_live,
    | obtenidas automáticamente con la UserKey vía API al sincronizar.
    |
    */

    'user_key' => env('FACTURAPI_USER_KEY'),

    'test_key' => env('FACTURAPI_TEST_KEY'),

    'environment' => env('FACTURAPI_ENVIRONMENT', 'test'),

    'base_url' => 'https://www.facturapi.io/v2',
];
