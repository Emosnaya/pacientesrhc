<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar columnas a TEXT usando SQL raw para evitar dependencia de doctrine/dbal
        
        // Pacientes
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN nombre TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN apellidoPat TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN apellidoMat TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN telefono TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN email TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN domicilio TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN diagnostico TEXT');
        DB::statement('ALTER TABLE pacientes MODIFY COLUMN medicamentos TEXT');

        // Recetas
        DB::statement('ALTER TABLE recetas MODIFY COLUMN diagnostico_principal TEXT');
        DB::statement('ALTER TABLE recetas MODIFY COLUMN indicaciones_generales TEXT');

        // Expediente Pulmonar (tabla: expedientes_pulmonar)
        if (DB::getSchemaBuilder()->hasTable('expedientes_pulmonar')) {
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN antecedentes_heredo_familiares TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN antecedentes_alergicos TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN antecedentes_quirurgicos TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN antecedentes_traumaticos TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN antecedentes_exposicionales TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN tabaquismo_detalle TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN alcoholismo_detalle TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN toxicomanias_detalle TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN diagnosticos_finales TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN plan_tratamiento TEXT');
            DB::statement('ALTER TABLE expedientes_pulmonar MODIFY COLUMN motivo_envio TEXT');
        }

        // Odontogramas
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN diagnostico TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN pronostico TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN observaciones TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ap_calculo_supragingival_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ap_calculo_infragingival_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ap_movilidad_dental_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ap_bolsas_periodontales_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ap_pseudobolsas_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ap_indice_placa_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ae_endo_defectuosa_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ae_necrosis_pulpar_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ae_pulpitis_irreversible_dientes TEXT');
        DB::statement('ALTER TABLE odontogramas MODIFY COLUMN ae_lesiones_periapicales_dientes TEXT');

        // Historia Clínica Dental
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN alergias TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN medicamento_detalle TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN anestesicos_detalle TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN medicamentos_alergicos_detalle TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN historia_enfermedad TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN motivo_consulta TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN at_fuma_detalle TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN at_drogas_detalle TEXT');
        DB::statement('ALTER TABLE historia_clinica_dental MODIFY COLUMN at_toma_detalle TEXT');

        // Historia Clínica Fisioterapia
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN motivo_consulta TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN padecimiento_actual TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_heredofamiliares TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_personales_patologicos TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_personales_no_patologicos TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN antecedentes_quirurgicos_traumaticos TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN diagnostico_medico TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN diagnostico_fisioterapeutico TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN objetivos_tratamiento TEXT');
        DB::statement('ALTER TABLE historia_clinica_fisioterapia MODIFY COLUMN pronostico TEXT');

        // Nota Evolución Fisioterapia
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN diagnostico_fisioterapeutico TEXT');
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN observaciones_subjetivas TEXT');
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN observaciones_objetivas TEXT');
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN tecnicas_modalidades_aplicadas TEXT');
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN ejercicio_terapeutico TEXT');
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN respuesta_tratamiento TEXT');
        DB::statement('ALTER TABLE nota_evolucion_fisioterapia MODIFY COLUMN plan TEXT');

        // Nota Alta Fisioterapia
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN diagnostico_medico TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN diagnostico_fisioterapeutico_inicial TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN tratamiento_otorgado TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN evolucion_resultados TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN mejoria_funcional TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN objetivos_alcanzados TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN estado_funcional_alta TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN recomendaciones_seguimiento TEXT');
        DB::statement('ALTER TABLE nota_alta_fisioterapia MODIFY COLUMN pronostico_funcional TEXT');

        // Prueba Esfuerzo Pulmonar (tabla con 's' al final)
        if (DB::getSchemaBuilder()->hasTable('prueba_esfuerzo_pulmonars')) {
            DB::statement('ALTER TABLE prueba_esfuerzo_pulmonars MODIFY COLUMN interpretacion TEXT');
            DB::statement('ALTER TABLE prueba_esfuerzo_pulmonars MODIFY COLUMN plan_manejo_complementario TEXT');
        }

        // Pagos (Motor Financiero)
        if (DB::getSchemaBuilder()->hasTable('pagos')) {
            DB::statement('ALTER TABLE pagos MODIFY COLUMN monto TEXT');
            DB::statement('ALTER TABLE pagos MODIFY COLUMN referencia TEXT');
            DB::statement('ALTER TABLE pagos MODIFY COLUMN concepto TEXT');
            DB::statement('ALTER TABLE pagos MODIFY COLUMN notas TEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir - los campos TEXT pueden contener datos más pequeños también
    }
};
