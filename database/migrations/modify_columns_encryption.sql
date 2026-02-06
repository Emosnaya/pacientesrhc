-- Script SQL para modificar columnas y soportar cifrado
-- Ejecutar: mysql -u root -p cercap < database/migrations/modify_columns_encryption.sql

USE cercap;

-- ============================================
-- PACIENTES
-- ============================================
ALTER TABLE pacientes MODIFY COLUMN nombre TEXT;
ALTER TABLE pacientes MODIFY COLUMN apellidoPat TEXT;
ALTER TABLE pacientes MODIFY COLUMN apellidoMat TEXT;
ALTER TABLE pacientes MODIFY COLUMN telefono TEXT;
ALTER TABLE pacientes MODIFY COLUMN email TEXT;
ALTER TABLE pacientes MODIFY COLUMN domicilio TEXT;
ALTER TABLE pacientes MODIFY COLUMN diagnostico TEXT;
ALTER TABLE pacientes MODIFY COLUMN medicamentos TEXT;

-- ============================================
-- RECETAS
-- ============================================
ALTER TABLE recetas MODIFY COLUMN diagnostico_principal TEXT;
ALTER TABLE recetas MODIFY COLUMN indicaciones_generales TEXT;

-- ============================================
-- ODONTOGRAMAS
-- ============================================
ALTER TABLE odontogramas MODIFY COLUMN diagnostico TEXT;
ALTER TABLE odontogramas MODIFY COLUMN pronostico TEXT;
ALTER TABLE odontogramas MODIFY COLUMN observaciones TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ap_calculo_supragingival_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ap_calculo_infragingival_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ap_movilidad_dental_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ap_bolsas_periodontales_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ap_pseudobolsas_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ap_indice_placa_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ae_endo_defectuosa_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ae_necrosis_pulpar_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ae_pulpitis_irreversible_dientes TEXT;
ALTER TABLE odontogramas MODIFY COLUMN ae_lesiones_periapicales_dientes TEXT;

-- ============================================
-- HISTORIA CLINICA DENTAL
-- ============================================
ALTER TABLE historia_clinica_dental MODIFY COLUMN alergias TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN medicamento_detalle TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN anestesicos_detalle TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN medicamentos_alergicos_detalle TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN historia_enfermedad TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN motivo_consulta TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN at_fuma_detalle TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN at_drogas_detalle TEXT;
ALTER TABLE historia_clinica_dental MODIFY COLUMN at_toma_detalle TEXT;

-- ============================================
-- HISTORIA CLINICA FISIOTERAPIA
-- ============================================
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN motivo_consulta TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN padecimiento_actual TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_heredofamiliares TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_personales_patologicos TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_personales_no_patologicos TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_quirurgicos_traumaticos TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN diagnostico_medico TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN diagnostico_fisioterapeutico TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN objetivos_tratamiento TEXT;
ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN pronostico TEXT;

-- ============================================
-- NOTA EVOLUCION FISIOTERAPIA
-- ============================================
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN diagnostico_fisioterapeutico TEXT;
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN observaciones_subjetivas TEXT;
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN observaciones_objetivas TEXT;
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN tecnicas_modalidades_aplicadas TEXT;
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN ejercicio_terapeutico TEXT;
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN respuesta_tratamiento TEXT;
ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN plan TEXT;

-- ============================================
-- NOTA ALTA FISIOTERAPIA
-- ============================================
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN diagnostico_medico TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN diagnostico_fisioterapeutico_inicial TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN tratamiento_otorgado TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN evolucion_resultados TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN mejoria_funcional TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN objetivos_alcanzados TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN estado_funcional_alta TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN recomendaciones_seguimiento TEXT;
ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN pronostico_funcional TEXT;

-- ============================================
-- PRUEBA ESFUERZO PULMONAR
-- ============================================
ALTER TABLE prueba_esfuerzo_pulmonars MODIFY COLUMN interpretacion TEXT;
ALTER TABLE prueba_esfuerzo_pulmonars MODIFY COLUMN plan_manejo_complementario TEXT;

-- ============================================
-- REPORTES - Campos de texto con información sensible
-- ============================================

-- ReporteNutri - Campos de texto que contienen evaluaciones
ALTER TABLE reporte_nutris MODIFY COLUMN recomendaciones TEXT;
ALTER TABLE reporte_nutris MODIFY COLUMN diagnostico TEXT;
ALTER TABLE reporte_nutris MODIFY COLUMN observaciones TEXT;
ALTER TABLE reporte_nutris MODIFY COLUMN recomendacion TEXT;

-- Esfuerzos - Campo de conclusiones médicas
ALTER TABLE esfuerzos MODIFY COLUMN conclusiones TEXT;

-- Clinicos - Campo de exploración física y diagnóstico
ALTER TABLE clinicos MODIFY COLUMN exploracion_fisica TEXT;
ALTER TABLE clinicos MODIFY COLUMN diagnostico_general TEXT;
ALTER TABLE clinicos MODIFY COLUMN plan TEXT;

-- ReporteFisio - Solo tiene archivo (no necesita modificación, es ruta)
-- ReporteFinal - Solo tiene datos numéricos estructurados (no necesita modificación)

-- ============================================
-- PAGOS - Motor Financiero
-- ============================================
ALTER TABLE pagos MODIFY COLUMN monto TEXT;
ALTER TABLE pagos MODIFY COLUMN referencia TEXT;
ALTER TABLE pagos MODIFY COLUMN concepto TEXT;
ALTER TABLE pagos MODIFY COLUMN notas TEXT;

-- ============================================
-- VERIFICACION
-- ============================================
SELECT 'Modificación completada. Ahora ejecuta: php artisan data:encrypt-existing' AS mensaje;
