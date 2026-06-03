<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->json('antecedentes_gineco_obstetricos')->nullable()->after('antecedentes_familiares');
            // Estructura: { menarquia: str, fum: str, ciclos: {regulares: bool, duracion: str},
            //              menopausia: {tiene: bool, edad: str, tipo: str},
            //              terapia_hormonal: {tiene: bool, tipo: str},
            //              formula_obstetrica: {gestas: str, partos: str, cesareas: str, abortos: str, hijos_vivos: str},
            //              complicaciones: {preeclampsia: bool, eclampsia: bool, diabetes_gestacional: bool,
            //                               parto_pretermino: bool, perdida_gestacional_recurrente: bool},
            //              otros: str }
        });
    }

    public function down(): void
    {
        Schema::table('historia_clinica_cardiologias', function (Blueprint $table) {
            $table->dropColumn('antecedentes_gineco_obstetricos');
        });
    }
};
