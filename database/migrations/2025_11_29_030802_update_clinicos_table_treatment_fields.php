<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Primero convertir los datos existentes de texto a booleanos
        $fields = [
            'betabloqueador',
            'nitratos',
            'calcioantagonista',
            'aspirina',
            'anticoagulacion',
            'iecas',
            'atii',
            'diureticos',
            'estatinas',
            'fibratos',
            'digoxina',
            'antiarritmicos'
        ];

        foreach ($fields as $field) {
            // Convertir valores de texto a booleanos
            // Si tiene algún valor (no null y no vacío), convertir a 1, sino 0
            DB::statement("UPDATE clinicos SET {$field} = CASE 
                WHEN {$field} IS NULL OR {$field} = '' OR {$field} = 'No' OR {$field} = 'false' OR {$field} = '0' THEN 0
                ELSE 1
            END");
        }

        // Ahora cambiar campos de tratamiento de string a boolean
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN betabloqueador TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN nitratos TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN calcioantagonista TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN aspirina TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN anticoagulacion TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN iecas TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN atii TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN diureticos TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN estatinas TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN fibratos TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN digoxina TINYINT(1) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN antiarritmicos TINYINT(1) NULL');

        // Agregar nuevos campos booleanos
        DB::statement('ALTER TABLE clinicos ADD COLUMN arni TINYINT(1) NULL AFTER antiarritmicos');
        DB::statement('ALTER TABLE clinicos ADD COLUMN sglt2 TINYINT(1) NULL AFTER arni');
        DB::statement('ALTER TABLE clinicos ADD COLUMN mra TINYINT(1) NULL AFTER sglt2');
        DB::statement('ALTER TABLE clinicos ADD COLUMN ivabradina TINYINT(1) NULL AFTER mra');
        DB::statement('ALTER TABLE clinicos ADD COLUMN pcsk0 TINYINT(1) NULL AFTER ivabradina');
        DB::statement('ALTER TABLE clinicos ADD COLUMN ranalozina TINYINT(1) NULL AFTER pcsk0');
        DB::statement('ALTER TABLE clinicos ADD COLUMN timetrazidina TINYINT(1) NULL AFTER ranalozina');
        DB::statement('ALTER TABLE clinicos ADD COLUMN inhibidor_adp TINYINT(1) NULL AFTER timetrazidina');

        DB::statement('ALTER TABLE clinicos ADD COLUMN im_post_inferior DATE NULL AFTER imdelVD');

        // Agregar PRO-BNP en química
        DB::statement('ALTER TABLE clinicos ADD COLUMN pro_bnp VARCHAR(255) NULL AFTER pcras');

        // Agregar QANT y QPOSTERINFERIOR en ECG
        DB::statement('ALTER TABLE clinicos ADD COLUMN q_ant VARCHAR(255) NULL AFTER q_lat');
        DB::statement('ALTER TABLE clinicos ADD COLUMN q_poster_inferior VARCHAR(255) NULL AFTER q_ant');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir campos de tratamiento a string
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN betabloqueador VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN nitratos VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN calcioantagonista VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN aspirina VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN anticoagulacion VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN iecas VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN atii VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN diureticos VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN estatinas VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN fibratos VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN digoxina VARCHAR(255) NULL');
        DB::statement('ALTER TABLE clinicos MODIFY COLUMN antiarritmicos VARCHAR(255) NULL');

        // Eliminar nuevos campos
        DB::statement('ALTER TABLE clinicos DROP COLUMN arni');
        DB::statement('ALTER TABLE clinicos DROP COLUMN sglt2');
        DB::statement('ALTER TABLE clinicos DROP COLUMN mra');
        DB::statement('ALTER TABLE clinicos DROP COLUMN ivabradina');
        DB::statement('ALTER TABLE clinicos DROP COLUMN pcsk0');
        DB::statement('ALTER TABLE clinicos DROP COLUMN ranalozina');
        DB::statement('ALTER TABLE clinicos DROP COLUMN timetrazidina');
        DB::statement('ALTER TABLE clinicos DROP COLUMN inhibidor_adp');
        DB::statement('ALTER TABLE clinicos DROP COLUMN pro_bnp');
        DB::statement('ALTER TABLE clinicos DROP COLUMN q_ant');
        DB::statement('ALTER TABLE clinicos DROP COLUMN q_poster_inferior');
    }
};
