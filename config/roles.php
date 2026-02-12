<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Roles que pueden firmar documentos (solo con su propia firma digital)
    | Nunca se permite seleccionar la firma de otro usuario.
    |--------------------------------------------------------------------------
    */
    'roles_firmantes' => [
        'doctor',
        'doctora',
        'licenciado',
        'director_medico',
        'nutriologo',
        'psicologo',
        'fisioterapeuta',
        'rehabilitador',
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles administrativos / de apoyo (solo descargan, PDF sin firma digital)
    |--------------------------------------------------------------------------
    */
    'roles_administrativos' => [
        'recepcionista',
        'administrativo',
        'asistente',
        'laboratorista',
        'enfermera',
        'enfermero',
        'coordinador',
        'asistente_medico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Listado de roles para selects (perfil, registro, usuarios)
    |--------------------------------------------------------------------------
    */
    'lista' => [
        'doctor'          => 'Doctor',
        'doctora'         => 'Doctora',
        'licenciado'      => 'Licenciado(a)',
        'director_medico' => 'Director(a) médico',
        'nutriologo'      => 'Nutriólogo(a)',
        'psicologo'       => 'Psicólogo(a)',
        'fisioterapeuta'  => 'Fisioterapeuta',
        'rehabilitador'   => 'Rehabilitador',
        'recepcionista'   => 'Recepcionista',
        'administrativo'  => 'Administrativo(a)',
        'asistente'       => 'Asistente',
        'laboratorista'   => 'Laboratorista',
        'enfermera'       => 'Enfermera',
        'enfermero'       => 'Enfermero',
        'coordinador'     => 'Coordinador(a)',
        'asistente_medico'=> 'Asistente médico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Título profesional para mostrar (sidebar, expedientes, PDFs): Dr., Dra., Lic., Enf.
    |--------------------------------------------------------------------------
    */
    'titulos' => [
        'doctor'          => 'Dr.',
        'doctora'         => 'Dra.',
        'licenciado'      => 'Lic.',
        'director_medico' => 'Dr.',
        'nutriologo'      => 'Lic.',
        'psicologo'       => 'Lic.',
        'fisioterapeuta'  => 'Lic.',
        'rehabilitador'   => 'Lic.',
        'recepcionista'   => '',
        'administrativo'  => '',
        'asistente'       => '',
        'laboratorista'   => '',
        'enfermera'       => 'Enf.',
        'enfermero'       => 'Enf.',
        'coordinador'     => '',
        'asistente_medico'=> '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validación: string permitido para rol (para reglas Rule::in)
    |--------------------------------------------------------------------------
    */
    'validacion_in' => 'doctor,doctora,licenciado,director_medico,nutriologo,psicologo,fisioterapeuta,rehabilitador,recepcionista,administrativo,asistente,laboratorista,enfermera,enfermero,coordinador,asistente_medico',
];
