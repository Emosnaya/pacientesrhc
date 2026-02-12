-- =============================================================================
-- REFERENCIA SOLO: Estructura y ejemplo de INSERT para datos dummy dental.
-- =============================================================================
-- NO EJECUTAR EN PRODUCCIÓN: En prod, nombre, apellidos, telefono, email,
-- domicilio, motivo_consulta, alergias (pacientes) y los campos encrypted de
-- historia_clinica_dental y odontogramas se guardan ENCRIPTADOS por Laravel.
-- Si insertas aquí texto plano, la app fallará al desencriptar.
-- Usar en su lugar: php artisan demo:seed-dummy-dental-bulk {clinica_id}
-- Ver: database/DUMMY_DENTAL_BULK_README.md
-- =============================================================================

-- Ejemplo de columnas para 1 paciente (valores plain; en prod Laravel los cifra).
-- Reemplaza @clinica_id, @sucursal_id, @user_id por IDs reales de tu BD.
-- registro debe ser único por clínica.

/*
INSERT INTO pacientes (
  registro, nombre, apellidoPat, apellidoMat, telefono, email,
  fechaNacimiento, edad, genero, domicilio, motivo_consulta, alergias,
  tipo_paciente, user_id, clinica_id, sucursal_id, color, created_at, updated_at
) VALUES (
  '2001',
  'Guadalupe',           -- en prod: cifrado
  'Reyes',               -- en prod: cifrado
  'Soto',                -- en prod: cifrado
  '55 1000 2000',        -- en prod: cifrado
  'guadalupe.reyes@ejemplo.com',  -- en prod: cifrado
  '1990-05-15', 30, 0,
  'Calle Dummy 1, Col. Centro',   -- en prod: cifrado
  'Revisión y limpieza',         -- en prod: cifrado
  NULL,                          -- en prod: cifrado
  'general',
  @user_id,
  @clinica_id,
  @sucursal_id,
  '#4ECDC4',
  NOW(), NOW()
);
*/

-- Historia clínica dental: paciente_id, sucursal_id, user_id obligatorios.
-- Campos encrypted: alergias, anestesicos_detalle, medicamentos_alergicos_detalle,
-- historia_enfermedad, motivo_consulta, at_fuma_detalle, at_drogas_detalle, at_toma_detalle.

/*
INSERT INTO historia_clinica_dental (
  paciente_id, sucursal_id, user_id, fecha, nombre_doctor,
  alergias, motivo_consulta, historia_enfermedad,
  veces_cepilla_dia, higienizacion_metodo,
  sv_ta, sv_pulso, sv_fc, sv_peso, sv_altura,
  etb_carrillos, etb_encias, etb_lengua, etb_paladar, etb_atm, etb_labios,
  created_at, updated_at
) VALUES (
  @paciente_id, @sucursal_id, @user_id, CURDATE(), 'Dr. Ejemplo',
  NULL, 'Revisión dental. Dummy.', 'Paciente sano. Sin antecedentes.',
  2, 'Cepillo manual',
  '120/80', '72', '72', '70', '165',
  'Sin lesiones', 'Normales', 'Normal', 'Normal', 'Sin datos', 'Normales',
  NOW(), NOW()
);
*/

-- Odontograma: dientes es JSON (52 piezas FDI). diagnostico, pronostico, observaciones
-- y los campos *_dientes están encrypted en prod.

/*
INSERT INTO odontogramas (
  paciente_id, sucursal_id, historia_clinica_dental_id, fecha,
  dientes,
  diagnostico, pronostico, observaciones,
  created_at, updated_at
) VALUES (
  @paciente_id, @sucursal_id, @historia_id, CURDATE(),
  '{"18":{"numero":18,"estado":"sano","caras_afectadas":[],"notas":""}, ...}',  -- usar Odontograma::inicializarDientes() vía Laravel
  'Buen estado general.', 'Favorable.', 'Paciente dummy.',
  NOW(), NOW()
);
*/

-- Resumen columnas no cifradas útiles para consultas/reportes:
-- pacientes: id, registro, fechaNacimiento, edad, genero, tipo_paciente, user_id, clinica_id, sucursal_id, color, created_at
-- historia_clinica_dental: id, paciente_id, sucursal_id, user_id, fecha, nombre_doctor, veces_cepilla_dia, sv_*, etb_* (texto no cifrado), booleans, created_at
-- odontogramas: id, paciente_id, sucursal_id, historia_clinica_dental_id, fecha, dientes (JSON), booleans ap_*, ae_*, created_at
