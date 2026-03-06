<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cumplimiento NOM-024-SSA3-2012 y LFPDPPP (Aviso de Privacidad)
     * Cumplimiento NOM-004-SSA3-2012 (Archivo Muerto - Resguardo 5 años)
     */
    public function up()
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Aviso de Privacidad (LFPDPPP)
            $table->timestamp('aviso_privacidad_aceptado_at')->nullable()->after('sucursal_id');
            $table->string('version_aviso', 20)->nullable()->after('aviso_privacidad_aceptado_at');
            
            // Archivo Muerto (NOM-004-SSA3-2012 - Resguardo 5 años)
            $table->boolean('archivo_muerto')->default(false)->after('version_aviso');
            $table->timestamp('archivo_muerto_at')->nullable()->after('archivo_muerto');
            $table->text('archivo_muerto_motivo')->nullable()->after('archivo_muerto_at');
            
            // Índices para consultas rápidas de cumplimiento
            $table->index('archivo_muerto');
            $table->index('archivo_muerto_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex(['archivo_muerto']);
            $table->dropIndex(['archivo_muerto_at']);
            
            $table->dropColumn([
                'aviso_privacidad_aceptado_at',
                'version_aviso',
                'archivo_muerto',
                'archivo_muerto_at',
                'archivo_muerto_motivo'
            ]);
        });
    }
};
