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
        'cardiologia' => [
            'nombre' => 'Cardiología',
            'descripcion' => 'Especialidad en enfermedades del corazón y sistema cardiovascular',
            'modulos' => ['expediente_cardiologia', 'ecocardiograma', 'electrocardiograma'],
            'color' => '#EF4444'
        ],
        'dermatologia' => [
            'nombre' => 'Dermatología',
            'descripcion' => 'Especialidad en enfermedades de la piel',
            'modulos' => ['expediente_dermatologia', 'tratamientos_derma'],
            'color' => '#F59E0B'
        ],
        'endocrinologia' => [
            'nombre' => 'Endocrinología',
            'descripcion' => 'Especialidad en trastornos hormonales y metabólicos',
            'modulos' => ['expediente_endocrinologia', 'seguimiento_hormonal'],
            'color' => '#8B5CF6'
        ],
        'gastroenterologia' => [
            'nombre' => 'Gastroenterología',
            'descripcion' => 'Especialidad en enfermedades del sistema digestivo',
            'modulos' => ['expediente_gastro', 'endoscopias'],
            'color' => '#10B981'
        ],
        'ginecologia' => [
            'nombre' => 'Ginecología y Obstetricia',
            'descripcion' => 'Especialidad en salud reproductiva femenina',
            'modulos' => ['expediente_gineco', 'control_prenatal', 'ultrasonido'],
            'color' => '#EC4899'
        ],
        'neurologia' => [
            'nombre' => 'Neurología',
            'descripcion' => 'Especialidad en enfermedades del sistema nervioso',
            'modulos' => ['expediente_neuro', 'electroencefalograma'],
            'color' => '#6366F1'
        ],
        'oftalmologia' => [
            'nombre' => 'Oftalmología',
            'descripcion' => 'Especialidad en enfermedades de los ojos',
            'modulos' => ['expediente_oftalmo', 'examen_visual'],
            'color' => '#06B6D4'
        ],
        'ortopedia' => [
            'nombre' => 'Ortopedia y Traumatología',
            'descripcion' => 'Especialidad en huesos, articulaciones y músculos',
            'modulos' => ['expediente_ortopedia', 'radiografias'],
            'color' => '#84CC16'
        ],
        'otorrinolaringologia' => [
            'nombre' => 'Otorrinolaringología',
            'descripcion' => 'Especialidad en oído, nariz y garganta',
            'modulos' => ['expediente_orl', 'audiometria'],
            'color' => '#14B8A6'
        ],
        'pediatria' => [
            'nombre' => 'Pediatría',
            'descripcion' => 'Especialidad en salud infantil',
            'modulos' => ['expediente_pediatria', 'vacunacion', 'crecimiento'],
            'color' => '#F472B6'
        ],
        'psiquiatria' => [
            'nombre' => 'Psiquiatría',
            'descripcion' => 'Especialidad en salud mental',
            'modulos' => ['expediente_psiquiatria', 'tratamientos_psiq'],
            'color' => '#A855F7'
        ],
        'urologia' => [
            'nombre' => 'Urología',
            'descripcion' => 'Especialidad en sistema urinario y reproductor masculino',
            'modulos' => ['expediente_urologia'],
            'color' => '#3B82F6'
        ],
        'neumologia' => [
            'nombre' => 'Neumología',
            'descripcion' => 'Especialidad en enfermedades respiratorias',
            'modulos' => ['expediente_neumologia', 'espirometria'],
            'color' => '#0EA5E9'
        ],
        'nefrologia' => [
            'nombre' => 'Nefrología',
            'descripcion' => 'Especialidad en enfermedades renales',
            'modulos' => ['expediente_nefrologia', 'dialisis'],
            'color' => '#06B6D4'
        ],
        'reumatologia' => [
            'nombre' => 'Reumatología',
            'descripcion' => 'Especialidad en enfermedades reumáticas y autoinmunes',
            'modulos' => ['expediente_reumatologia'],
            'color' => '#F97316'
        ],
        'oncologia' => [
            'nombre' => 'Oncología',
            'descripcion' => 'Especialidad en tratamiento del cáncer',
            'modulos' => ['expediente_oncologia', 'quimioterapia'],
            'color' => '#DC2626'
        ],
        'anestesiologia' => [
            'nombre' => 'Anestesiología',
            'descripcion' => 'Especialidad en anestesia y manejo del dolor',
            'modulos' => ['expediente_anestesia'],
            'color' => '#64748B'
        ],
        'cirugia_general' => [
            'nombre' => 'Cirugía General',
            'descripcion' => 'Especialidad en procedimientos quirúrgicos',
            'modulos' => ['expediente_cirugia', 'procedimientos'],
            'color' => '#7C3AED'
        ],
        'medicina_interna' => [
            'nombre' => 'Medicina Interna',
            'descripcion' => 'Especialidad en diagnóstico y tratamiento de enfermedades del adulto',
            'modulos' => ['expediente_interna'],
            'color' => '#0891B2'
        ],
        'geriatria' => [
            'nombre' => 'Geriatría',
            'descripcion' => 'Especialidad en salud del adulto mayor',
            'modulos' => ['expediente_geriatria', 'valoracion_geriatrica'],
            'color' => '#78716C'
        ],
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
            'nombre' => 'Dental / Odontología',
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
            'nombre' => 'Medicina General',
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
