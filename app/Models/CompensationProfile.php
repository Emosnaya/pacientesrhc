<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompensationProfile extends Model
{
    protected $fillable = [
        'clinica_id',
        'user_id',
        'sueldo_fijo',
        'comision_pct',
        'activo',
        'notas',
    ];

    protected $casts = [
        'sueldo_fijo' => 'float',
        'comision_pct' => 'float',
        'activo' => 'boolean',
    ];

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
