<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
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
        'cedula_especialista',
        'universidad',
        'logo_universidad',
        'email',
        'password',
        'isAdmin',
        'isSuperAdmin',
        'imagen',
        'firma_digital',
        'email_verification_token',
        'email_verified',
        'clinica_id',
        'sucursal_id',
        'clinica_activa_id',
        'rol',
        // Suscripción para consultorios privados
        'tiene_suscripcion_consultorio',
        'plan_consultorio',
        'ciclo_facturacion',
        'stripe_customer_id',
        'stripe_subscription_id',
        'suscripcion_inicio',
        'suscripcion_fin',
        'trial_ends_at',
        'consultorios_adicionales_comprados',
        'paciente_id',
        'password_set_at',
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
        'tiene_suscripcion_consultorio' => 'boolean',
        'suscripcion_inicio' => 'datetime',
        'suscripcion_fin' => 'datetime',
        'trial_ends_at' => 'datetime',
        'password_set_at' => 'datetime',
    ];

    /**
     * Atributos que se agregan al serializar (JSON) para API / sidebar / expedientes
     */
    protected $appends = ['titulo_profesional', 'nombre_con_titulo', 'clinica_efectiva_id', 'es_paciente_portal'];

    /**
     * Título profesional según rol: Dr., Dra., Lic. (vacío si no aplica)
     */
    public function getTituloProfesionalAttribute(): string
    {
        if ($this->rol === 'paciente' || $this->paciente_id) {
            return '';
        }
        $titulos = config('roles.titulos', []);
        return $this->rol && isset($titulos[$this->rol]) ? $titulos[$this->rol] : '';
    }

    /**
     * Nombre completo con título para mostrar en sidebar y expedientes: "Dr. Juan Pérez"
     */
    public function getNombreConTituloAttribute(): string
    {
        $nombre = trim(($this->nombre ?? '') . ' ' . ($this->apellidoPat ?? '') . ' ' . ($this->apellidoMat ?? ''));
        $titulo = $this->titulo_profesional;
        return $titulo ? $titulo . ' ' . $nombre : $nombre;
    }

    /**
     * Relación con la clínica
     */
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    /**
     * Clínica activa seleccionada (consultorio privado u otra clínica en la que trabaja).
     * Si es null, se usa clinica_id como fallback.
     */
    public function clinicaActiva()
    {
        return $this->belongsTo(Clinica::class, 'clinica_activa_id');
    }

    /**
     * Todas las clínicas/consultorios a los que pertenece este usuario (pivot user_clinicas)
     */
    public function clinicas()
    {
        return $this->belongsToMany(Clinica::class, 'user_clinicas')
                    ->using(UserClinica::class)
                    ->withPivot(['rol_en_clinica', 'activa', 'invitado_por'])
                    ->withTimestamps();
    }

    /**
     * ID de la clínica efectiva: clinica_activa_id si está seteada, si no la clinica_id original.
     * Usar en todos los controllers en vez de $user->clinica_id cuando el usuario puede
     * pertenecer a múltiples espacios de trabajo.
     */
    public function getClinicaEfectivaIdAttribute(): ?int
    {
        return $this->clinica_activa_id ?? $this->clinica_id;
    }

    public function getEsPacientePortalAttribute(): bool
    {
        return $this->paciente_id !== null;
    }

    /**
     * Expediente clínico vinculado (cuenta portal del paciente).
     */
    public function pacienteRecord()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function isPatientPortalAccount(): bool
    {
        return $this->paciente_id !== null;
    }

    /**
     * Sucursales adicionales asignadas (misma clínica; ver multisucursal para participantes).
     */
    public function sucursalesAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'user_sucursal')->withTimestamps();
    }

    /**
     * Sobrescribe en el modelo (para JSON de /api/user) isAdmin, isSuperAdmin y rol_en_clinica
     * desde user_clinicas para la clínica efectiva. Si no hay fila activa, fuerza false/null
     * (no usar columnas legacy de users).
     */
    public function applyWorkspacePermissionsFromPivot(): void
    {
        $clinicaEfectivaId = $this->clinica_efectiva_id;
        if (! $clinicaEfectivaId) {
            $this->isAdmin = false;
            $this->isSuperAdmin = false;
            $this->rol_en_clinica = null;

            return;
        }

        $relacion = DB::table('user_clinicas')
            ->where('user_id', $this->id)
            ->where('clinica_id', $clinicaEfectivaId)
            ->where('activa', true)
            ->first();

        if ($relacion) {
            $this->isAdmin = (bool) ($relacion->isAdmin ?? false);
            $this->isSuperAdmin = (bool) ($relacion->isSuperAdmin ?? false);
            $this->rol_en_clinica = $relacion->rol_en_clinica;
        } else {
            $this->isAdmin = false;
            $this->isSuperAdmin = false;
            $this->rol_en_clinica = null;
        }
    }

    /**
     * IDs de sucursales que el usuario puede elegir en la clínica dada (no superadmin).
     * Incluye users.sucursal_id si pertenece a esa clínica, más filas en user_sucursal.
     *
     * @return int[] lista vacía si no aplica
     */
    public function getSucursalesPermitidasIdsForClinica(?int $clinicaId = null): array
    {
        $clinicaId = $clinicaId ?? $this->clinica_efectiva_id;
        if (! $clinicaId) {
            return [];
        }

        if ($this->isSuperAdmin($clinicaId)) {
            return [];
        }

        $ids = [];

        if ($this->sucursal_id) {
            $ok = DB::table('sucursales')
                ->where('id', $this->sucursal_id)
                ->where('clinica_id', $clinicaId)
                ->exists();
            if ($ok) {
                $ids[] = (int) $this->sucursal_id;
            }
        }

        $extra = DB::table('user_sucursal')
            ->join('sucursales', 'sucursales.id', '=', 'user_sucursal.sucursal_id')
            ->where('user_sucursal.user_id', $this->id)
            ->where('sucursales.clinica_id', $clinicaId)
            ->pluck('user_sucursal.sucursal_id')
            ->all();

        foreach ($extra as $sid) {
            $ids[] = (int) $sid;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Atributos pivot para propietario de consultorio/clínica (administración completa en ese workspace).
     */
    public static function pivotPropietarioConsultorio(): array
    {
        return [
            'rol_en_clinica' => 'propietario',
            'activa' => true,
            'isAdmin' => true,
            'isSuperAdmin' => true,
        ];
    }

    /**
     * Relación con la sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
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
     * Verificar si el usuario es administrador EN LA CLÍNICA ACTIVA.
     * Nueva arquitectura: Los permisos de admin son POR CLÍNICA (en user_clinicas).
     * 
     * @param int|null $clinicaId Si se especifica, verifica en esa clínica. Si no, usa clinica_efectiva_id.
     */
    public function isAdmin(?int $clinicaId = null): bool
    {
        $targetClinicaId = $clinicaId ?? $this->clinica_efectiva_id;
        
        if (!$targetClinicaId) {
            return false;
        }

        // Buscar en pivot user_clinicas
        $relacion = DB::table('user_clinicas')
            ->where('user_id', $this->id)
            ->where('clinica_id', $targetClinicaId)
            ->where('activa', true)
            ->first();

        return $relacion ? (bool) ($relacion->isAdmin ?? false) : false;
    }

    /**
     * Verificar si el usuario es super administrador EN LA CLÍNICA ACTIVA.
     * Nueva arquitectura: Los permisos de superadmin son POR CLÍNICA (en user_clinicas).
     * 
     * @param int|null $clinicaId Si se especifica, verifica en esa clínica. Si no, usa clinica_efectiva_id.
     */
    public function isSuperAdmin(?int $clinicaId = null): bool
    {
        $targetClinicaId = $clinicaId ?? $this->clinica_efectiva_id;
        
        if (!$targetClinicaId) {
            return false;
        }

        // Buscar en pivot user_clinicas
        $relacion = DB::table('user_clinicas')
            ->where('user_id', $this->id)
            ->where('clinica_id', $targetClinicaId)
            ->where('activa', true)
            ->first();

        return $relacion ? (bool) ($relacion->isSuperAdmin ?? false) : false;
    }

    /**
     * Verificar si el usuario puede eliminar/desvincular recursos.
     * Según la jerarquía: SuperAdmin puede todo, Admin puede eliminar/desvincular.
     * Usuarios normales solo pueden editar (los cambios se trackean en logs).
     * 
     * @param int|null $clinicaId Si se especifica, verifica en esa clínica. Si no, usa clinica_efectiva_id.
     */
    public function canDelete(?int $clinicaId = null): bool
    {
        return $this->isAdmin($clinicaId) || $this->isSuperAdmin($clinicaId);
    }

    /**
     * Verificar si el usuario tiene acceso administrativo (admin o superadmin).
     * Útil para validar acceso a gestión de usuarios, finanzas, etc.
     * 
     * @param int|null $clinicaId Si se especifica, verifica en esa clínica. Si no, usa clinica_efectiva_id.
     */
    public function hasAdminAccess(?int $clinicaId = null): bool
    {
        return $this->isAdmin($clinicaId) || $this->isSuperAdmin($clinicaId);
    }

    /**
     * Rol es "firmante" (doctor, doctora, licenciado): solo puede usar su propia firma
     */
    public function isFirmante(): bool
    {
        $roles = config('roles.roles_firmantes', ['doctor', 'doctora', 'licenciado']);
        return $this->rol && in_array($this->rol, $roles);
    }

    /**
     * Rol es administrativo (recepcionista, administrativo, laboratorista): solo descarga sin firma
     */
    public function isAdministrativo(): bool
    {
        $roles = config('roles.roles_administrativos', ['recepcionista', 'administrativo', 'laboratorista']);
        return $this->rol && in_array($this->rol, $roles);
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

    // ========== MÉTODOS DE SUSCRIPCIÓN PARA CONSULTORIOS PRIVADOS ==========

    /**
     * Verificar si el usuario tiene suscripción activa para consultorios privados
     */
    public function tieneSuscripcionConsultorioActiva(): bool
    {
        if (!$this->tiene_suscripcion_consultorio) {
            return false;
        }

        // Si está en trial
        if ($this->trial_ends_at && now()->lessThan($this->trial_ends_at)) {
            return true;
        }

        // Si tiene suscripción pagada activa
        if ($this->suscripcion_fin && now()->lessThan($this->suscripcion_fin)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si puede crear consultorios privados
     */
    public function puedeCrearConsultorios(): bool
    {
        return $this->tieneSuscripcionConsultorioActiva();
    }

    /**
     * Obtener cantidad de consultorios privados que puede tener
     */
    public function getLimiteConsultoriosAttribute(): int
    {
        if (!$this->tieneSuscripcionConsultorioActiva()) {
            return 0;
        }

        // 1 consultorio incluido en el plan + adicionales comprados
        return 1 + ($this->consultorios_adicionales_comprados ?? 0);
    }

    /**
     * Obtener cantidad de consultorios privados que tiene actualmente
     */
    public function getCantidadConsultoriosPrivadosAttribute(): int
    {
        return $this->clinicas()
            ->where('clinicas.es_consultorio_privado', true)
            ->wherePivot('activa', true)
            ->count();
    }

    /**
     * Verificar si puede crear un consultorio adicional
     */
    public function puedeCrearConsultorioAdicional(): bool
    {
        if (!$this->tieneSuscripcionConsultorioActiva()) {
            return false;
        }

        return $this->cantidad_consultorios_privados < $this->limite_consultorios;
    }

    /**
     * El otro usuario tiene membresía activa en el mismo workspace que el actual.
     */
    public function sharesActiveWorkspaceWithUser(int $otherUserId): bool
    {
        $efectiva = $this->clinica_efectiva_id;
        if (! $efectiva) {
            return false;
        }

        return DB::table('user_clinicas')
            ->where('user_id', $otherUserId)
            ->where('clinica_id', $efectiva)
            ->where('activa', true)
            ->exists();
    }

    /**
     * Ver/editar perfil propio o, si es admin del workspace, de miembros del mismo espacio.
     */
    public function canAccessProfileOf(self $target): bool
    {
        if ((int) $this->id === (int) $target->id) {
            return true;
        }

        if (! $this->hasAdminAccess()) {
            return false;
        }

        return $this->sharesActiveWorkspaceWithUser((int) $target->id);
    }
}
