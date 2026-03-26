<?php

return [

    /*
    | Versiones publicadas de documentos legales (registrar en aviso al aceptar).
    */
    'version_aviso_privacidad' => env('LEGAL_VERSION_AVISO', '2025-03-01'),
    'version_terminos' => env('LEGAL_VERSION_TERMINOS', '2025-03-01'),

    /*
    | URLs a los textos completos (sitio institucional o PDF).
    */
    'url_aviso_privacidad' => env('LEGAL_URL_AVISO_PRIVACIDAD', ''),
    'url_terminos' => env('LEGAL_URL_TERMINOS', ''),

    /*
    | Días de validez del enlace enviado por correo.
    */
    'consentimiento_enlace_dias' => (int) env('LEGAL_CONSENT_LINK_DAYS', 14),

];
