<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model para usuarios internos de Lynkamed (backoffice)
 * 
 * Estos usuarios son empleados de Lynkamed que gestionan clínicas,
 * consultorios, suscripciones, etc. desde el panel de administración.
 */
class AdminUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Verificar si es superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Verificar si puede gestionar clínicas
     */
    public function canManageClinicas(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    /**
     * Verificar si puede gestionar suscripciones
     */
    public function canManageSuscripciones(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'ventas']);
    }

    /**
     * Verificar si puede ver soporte
     */
    public function canViewSoporte(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'soporte']);
    }

    /**
     * Actualizar información de último login
     */
    public function updateLastLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
