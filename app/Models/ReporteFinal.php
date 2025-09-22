<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteFinal extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'fecha',
        'contenido'
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
