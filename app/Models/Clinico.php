<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinico extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'fecha',
        'contenido'
    ];

    protected $casts = [
        'contenido' => 'encrypted',
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
     * Relación con los permisos otorgados sobre este reporte
     */
    public function permissions()
    {
        return $this->morphMany(UserPermission::class, 'permissionable');
    }
}
