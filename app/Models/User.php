<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidoPat',
        'apellidoMat',
        'cedula',
        'email',
        'password',
        'isAdmin',
        'isSuperAdmin',
        'imagen',
        'firma_digital',
        'email_verification_token',
        'email_verified',
        'clinica_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'isAdmin' => 'boolean',
        'isSuperAdmin' => 'boolean',
        'email_verified' => 'boolean',
    ];

    /**
     * Relación con la clínica
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Relación con los permisos que el usuario ha recibido
     */
    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Relación con los permisos que el usuario ha otorgado (solo admins)
     */
    public function grantedPermissions()
    {
        return $this->hasMany(UserPermission::class, 'granted_by');
    }

    /**
     * Relación con los pacientes del usuario
     */
    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }

    /**
     * Relación con los expedientes del usuario
     */
    public function expedientes()
    {
        return $this->hasMany(ReporteFinal::class);
    }

    /**
     * Relación con los expedientes pulmonares del usuario
     */
    public function expedientesPulmonares()
    {
        return $this->hasMany(ExpedientePulmonar::class);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin ?? false;
    }

    /**
     * Verificar si el usuario tiene permiso sobre un recurso específico
     */
    public function hasPermissionOn($permissionable, string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // 1. Verificar permisos directos sobre el recurso
        $userPermission = $this->permissions()
            ->where('permissionable_type', get_class($permissionable))
            ->where('permissionable_id', $permissionable->id)
            ->first();

        if ($userPermission && $userPermission->hasPermission($permission)) {
            return true;
        }

        // 2. Si es un paciente, verificar permisos indirectos a través de reportes
        if ($permissionable instanceof \App\Models\Paciente) {
            $reportPermissions = $this->permissions()
                ->whereIn('permissionable_type', [
                    \App\Models\Clinico::class,
                    \App\Models\Esfuerzo::class,
                    \App\Models\Estratificacion::class,
                    \App\Models\ReporteFinal::class,
                    \App\Models\ReporteNutri::class,
                    \App\Models\ReportePsico::class,
                    \App\Models\ReporteFisio::class
                ])
                ->get();

            foreach ($reportPermissions as $reportPermission) {
                $resource = $reportPermission->permissionable;
                if ($resource && isset($resource->paciente_id) && $resource->paciente_id == $permissionable->id) {
                    if ($reportPermission->hasPermission($permission)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Obtener todos los recursos a los que el usuario tiene acceso
     */
    public function getAccessibleResources(string $resourceType = null)
    {
        if ($this->isAdmin()) {
            // Los admins solo tienen acceso a sus propios recursos
            if ($resourceType === 'pacientes') {
                return $this->pacientes;
            } elseif ($resourceType === 'expedientes') {
                return $this->expedientes;
            }
            return collect();
        }

        $permissions = $this->permissions();
        
        if ($resourceType) {
            $permissions->where('permissionable_type', $resourceType);
        }

        return $permissions->with('permissionable')->get();
    }

    /**
     * Obtener pacientes accesibles (incluyendo los asociados a reportes con permisos)
     */
    public function getAccessiblePacientes($tipoPaciente = null)
    {
        if ($this->isAdmin()) {
            // Los administradores solo pueden ver los pacientes que ellos han creado
            $query = $this->pacientes();
            if ($tipoPaciente) {
                $query->where('tipo_paciente', $tipoPaciente);
            }
            return $query->get();
        }

        $pacienteIds = collect();

        // 1. Pacientes con permisos directos
        $directPermissions = $this->permissions()
            ->where('permissionable_type', \App\Models\Paciente::class)
            ->pluck('permissionable_id');
        $pacienteIds = $pacienteIds->merge($directPermissions);

        // 2. Pacientes asociados a reportes con permisos
        $reportPermissions = $this->permissions()
            ->whereIn('permissionable_type', [
                \App\Models\Clinico::class,
                \App\Models\Esfuerzo::class,
                \App\Models\Estratificacion::class,
                \App\Models\ReporteFinal::class,
                \App\Models\ReporteNutri::class,
                \App\Models\ReportePsico::class,
                \App\Models\ReporteFisio::class,
                \App\Models\ExpedientePulmonar::class
            ])
            ->get();

        foreach ($reportPermissions as $permission) {
            $resource = $permission->permissionable;
            if ($resource && isset($resource->paciente_id)) {
                $pacienteIds->push($resource->paciente_id);
            }
        }

        // 3. Obtener pacientes únicos
        $uniquePacienteIds = $pacienteIds->unique()->values()->toArray();
        
        $query = \App\Models\Paciente::whereIn('id', $uniquePacienteIds);
        if ($tipoPaciente) {
            $query->where('tipo_paciente', $tipoPaciente);
        }
        
        return $query->get();
    }

    /**
     * Relación con las citas creadas por el usuario
     */
    public function citasCreadas()
    {
        return $this->hasMany(Cita::class, 'admin_id');
    }

    /**
     * Verificar si el usuario puede acceder a una cita específica
     * Ahora cualquier usuario puede ver citas de su misma clínica
     */
    public function canAccessCita($cita, string $permission = 'can_read'): bool
    {
        // Verificar que la cita pertenece a la misma clínica del usuario
        return $cita->clinica_id === $this->clinica_id;
    }

    /**
     * Verificar si el usuario puede acceder a un paciente específico
     */
    public function canAccessPaciente($paciente, string $permission = 'can_read'): bool
    {
        if ($this->isAdmin()) {
            // Los admins solo pueden acceder a sus propios pacientes
            return $paciente->user_id === $this->id;
        } else {
            // Usuarios no-admin verifican permisos específicos
            return $this->hasPermissionOn($paciente, $permission);
        }
    }
}
