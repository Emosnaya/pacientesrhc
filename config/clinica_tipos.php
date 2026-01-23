<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Módulos Disponibles para Selección
    |--------------------------------------------------------------------------
    |
    | Define los módulos que pueden ser habilitados/deshabilitados por clínica
    |
    */

    'modulos_seleccionables' => [
        'cardiaco' => [
            'nombre' => 'Rehabilitación Cardíaca',
            'descripcion' => 'Expedientes cardíacos, pruebas de esfuerzo, estratificación',
            'expedientes' => ['clinico', 'esfuerzo', 'estratificacion', 'reporteFinal'],
            'color' => '#EF4444',
            'icon' => 'heart'
        ],
        'pulmonar' => [
            'nombre' => 'Rehabilitación Pulmonar',
            'descripcion' => 'Expedientes y reportes pulmonares',
            'expedientes' => ['reportePulmonar', 'reporteFinalPulmonar'],
            'color' => '#3B82F6',
            'icon' => 'lungs'
        ],
        'fisioterapia' => [
            'nombre' => 'Fisioterapia',
            'descripcion' => 'Reportes fisioterapéuticos y rehabilitación física',
            'expedientes' => ['reporteFisio'],
            'color' => '#10B981',
            'icon' => 'dumbbell'
        ],
        'nutricion' => [
            'nombre' => 'Nutrición',
            'descripcion' => 'Evaluaciones y planes nutricionales',
            'expedientes' => ['reporteNutri'],
            'color' => '#F59E0B',
            'icon' => 'utensils'
        ],
        'psicologia' => [
            'nombre' => 'Psicología',
            'descripcion' => 'Evaluaciones y seguimiento psicológico',
            'expedientes' => ['reportePsico'],
            'color' => '#8B5CF6',
            'icon' => 'brain'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Clínicas Predefinidos
    |--------------------------------------------------------------------------
    */

    'tipos' => [
        'rehabilitacion_cardiopulmonar' => [
            'nombre' => 'Rehabilitación Cardíaca, Pulmonar y Fisioterapia',
            'descripcion' => 'Clínica especializada en rehabilitación cardiovascular, pulmonar y fisioterapia',
            'modulos' => [
                'expediente_cardiaco',
                'expediente_pulmonar',
                'expediente_fisioterapia',
                'prueba_esfuerzo',
                'estratificacion',
                'reporte_final',
                'reporte_nutri',
                'reporte_psico'
            ],
            'color' => '#3B82F6'
        ],
        'dental' => [
            'nombre' => 'Clínica Dental',
            'descripcion' => 'Clínica especializada en odontología y salud bucal',
            'modulos' => [
                'expediente_dental',
                'odontograma',
                'tratamientos',
                'presupuestos',
                'imagenologia'
            ],
            'color' => '#10B981'
        ],
        'fisioterapia' => [
            'nombre' => 'Fisioterapia',
            'descripcion' => 'Centro de fisioterapia y rehabilitación física',
            'modulos' => [
                'expediente_fisio',
                'sesiones',
                'ejercicios',
                'evaluaciones'
            ],
            'color' => '#F59E0B'
        ],
        'nutricion' => [
            'nombre' => 'Nutrición',
            'descripcion' => 'Consultorio de nutrición y dietética',
            'modulos' => [
                'expediente_nutri',
                'planes_alimenticios',
                'mediciones',
                'seguimiento'
            ],
            'color' => '#8B5CF6'
        ],
        'psicologia' => [
            'nombre' => 'Psicología',
            'descripcion' => 'Consultorio de psicología y salud mental',
            'modulos' => [
                'expediente_psico',
                'sesiones',
                'evaluaciones',
                'tratamientos'
            ],
            'color' => '#EC4899'
        ],
        'general' => [
            'nombre' => 'Clínica General',
            'descripcion' => 'Clínica de medicina general',
            'modulos' => [
                'expediente_general',
                'consultas',
                'recetas',
                'laboratorio'
            ],
            'color' => '#6B7280'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipo por Defecto
    |--------------------------------------------------------------------------
    */

    'default' => 'rehabilitacion_cardiopulmonar',

    /*
    |--------------------------------------------------------------------------
    | Tipos que requieren módulos especiales
    |--------------------------------------------------------------------------
    */

    'requiere_modulo_cardiaco' => [
        'rehabilitacion_cardiopulmonar'
    ],

    'requiere_modulo_dental' => [
        'dental'
    ],
];
