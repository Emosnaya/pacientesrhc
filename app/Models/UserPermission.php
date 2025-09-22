<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'granted_by',
        'permissionable_type',
        'permissionable_id',
        'can_read',
        'can_write',
        'can_edit',
        'can_delete'
    ];

    protected $casts = [
        'can_read' => 'boolean',
        'can_write' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean'
    ];

    /**
     * Relación con el usuario que recibe el permiso
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el usuario admin que otorga el permiso
     */
    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Relación polimórfica con expedientes o pacientes
     */
    public function permissionable()
    {
        return $this->morphTo();
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        return $this->$permission ?? false;
    }

    /**
     * Obtener todos los permisos como array
     */
    public function getPermissions(): array
    {
        return [
            'can_read' => $this->can_read,
            'can_write' => $this->can_write,
            'can_edit' => $this->can_edit,
            'can_delete' => $this->can_delete
        ];
    }
}
