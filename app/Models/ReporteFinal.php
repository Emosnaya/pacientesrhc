<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteFinal extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'sucursal_id',
        'fecha',
        'fecha_inicio',
        'fecha_final',
        'fc_basal',
        'doble_pr_bas',
        'fc_maxima',
        'doble_pr_max',
        'fc_borg12',
        'doble_pr_b12',
        'carga_max',
        'mets_por',
        'tiempo_ejer',
        'recup_fc',
        'umbral_isq',
        'umbral_isq_fc',
        'max_des_st',
        'indice_ta_es',
        'recup_tas',
        'resp_crono',
        'iem',
        'pod_car_eje',
        'duke',
        'veteranos',
        'score_ang',
        'pe_1',
        'pe_2',
        'tipo_exp',
    ];

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con los permisos otorgados sobre este expediente
     */
    public function permissions()
    {
        return $this->morphMany(UserPermission::class, 'permissionable');
    }
}
