<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class UserManagementController extends Controller
{
    /**
     * Crear un nuevo usuario (solo administradores)
     */
    public function createUser(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para crear usuarios'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidoPat' => 'required|string|max:255',
            'apellidoMat' => 'required|string|max:255',
            'cedula' => '',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'rol' => 'nullable|string|in:' . config('roles.validacion_in'),
            'isAdmin' => 'boolean',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validar que la sucursal pertenezca a la clínica del admin
        if ($request->sucursal_id) {
            $sucursal = \App\Models\Sucursal::find($request->sucursal_id);
            if (!$sucursal || $sucursal->clinica_id !== $user->clinica_id) {
                return response()->json(['error' => 'La sucursal no pertenece a tu clínica'], 403);
            }
        }

        $newUser = User::create([
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat,
            'apellidoMat' => $request->apellidoMat,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol ?: null,
            'isAdmin' => $request->isAdmin ?? false,
            'email_verified' => true, // Admin created users are pre-verified
            'email_verified_at' => now(),
            'clinica_id' => $user->clinica_id, // Asignar a la misma clínica que el admin
            'sucursal_id' => $request->sucursal_id // Asignar sucursal si se proporciona
        ]);

        // Enviar correo con credenciales
        $clinica = $newUser->clinica;
        try {
            Mail::send('emails.user-credentials', [
                'user' => $newUser,
                'password' => $request->password, // Enviar la contraseña en texto plano
                'clinica' => $clinica
            ], function ($message) use ($newUser, $clinica) {
                $message->to($newUser->email)
                        ->subject('Credenciales de Acceso - ' . ($clinica->nombre ?? 'Sistema Médico'));
            });
        } catch (\Exception $e) {
            // Log error but don't fail user creation
            \Log::error('Error sending user credentials email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Usuario creado exitosamente. Se han enviado las credenciales por correo.',
            'user' => $newUser
        ], 201);
    }

    /**
     * Listar todos los usuarios (solo administradores)
     */
    public function listDoctors(Request $request): JsonResponse
    {
        $user = $request->user();
    
        // Filtrar usuarios por la misma clínica que el usuario autenticado
        $users = User::select('id', 'nombre', 'apellidoPat', 'apellidoMat', 'cedula', 'email', 'isAdmin', 'created_at')
            ->where('clinica_id', $user->clinica_id)
            ->where('isAdmin', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['users' => $users]);
    }

    public function listAllUsers(Request $request): JsonResponse
    {
        $user = $request->user();
        $users = User::select('id', 'nombre', 'apellidoPat', 'apellidoMat', 'cedula', 'email', 'rol', 'isAdmin', 'sucursal_id', 'created_at')
            ->where('clinica_id', $user->clinica_id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['users' => $users]);
    }

    /**
     * Actualizar un usuario (solo administradores)
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para actualizar usuarios'], 403);
        }

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Verificar que el usuario pertenece a la misma clínica
        if ($targetUser->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos para actualizar usuarios de otras clínicas'], 403);
        }

        // No permitir que un admin quite el admin a otro admin
        if ($targetUser->isAdmin() && $request->has('isAdmin') && !$request->isAdmin) {
            return response()->json(['error' => 'No puedes quitarle el privilegio de administrador a otro administrador'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidoPat' => 'required|string|max:255',
            'apellidoMat' => 'required|string|max:255',
            'cedula' => 'nullable|string|unique:users,cedula,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'rol' => 'nullable|string|in:' . config('roles.validacion_in'),
            'isAdmin' => 'boolean',
            'sucursal_id' => 'nullable|exists:sucursales,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validar que la sucursal pertenezca a la clínica del admin
        if ($request->has('sucursal_id') && $request->sucursal_id) {
            $sucursal = \App\Models\Sucursal::find($request->sucursal_id);
            if (!$sucursal || $sucursal->clinica_id !== $user->clinica_id) {
                return response()->json(['error' => 'La sucursal no pertenece a tu clínica'], 403);
            }
        }

        $updateData = [
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat,
            'apellidoMat' => $request->apellidoMat,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'rol' => $request->rol ?: null,
            'isAdmin' => $request->isAdmin ?? false
        ];

        // Actualizar sucursal si se proporciona
        if ($request->has('sucursal_id')) {
            $updateData['sucursal_id'] = $request->sucursal_id;
        }

        // Solo actualizar la contraseña si se proporciona
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $targetUser->update($updateData);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $targetUser->fresh()
        ]);
    }

    /**
     * Eliminar un usuario (solo administradores)
     */
    public function deleteUser(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para eliminar usuarios'], 403);
        }

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Verificar que el usuario pertenece a la misma clínica
        if ($targetUser->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes permisos para eliminar usuarios de otras clínicas'], 403);
        }

        // No permitir que un admin se elimine a sí mismo
        if ($targetUser->id === $user->id) {
            return response()->json(['error' => 'No puedes eliminar tu propia cuenta'], 403);
        }

        // Eliminar permisos asociados
        $targetUser->permissions()->delete();
        $targetUser->grantedPermissions()->delete();

        // Eliminar el usuario
        $targetUser->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    /**
     * Asignar permisos a un usuario sobre un recurso específico
     */
    public function assignPermission(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para asignar permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'permissionable_type' => 'required|string',
            'permissionable_id' => 'required|integer',
            'can_read' => 'boolean',
            'can_write' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar que el recurso existe y pertenece al admin
        $resource = $this->getResource($request->permissionable_type, $request->permissionable_id);
        
        if (!$resource) {
            return response()->json([
                'error' => 'Recurso no encontrado',
                'debug' => [
                    'permissionable_type' => $request->permissionable_type,
                    'permissionable_id' => $request->permissionable_id,
                    'resource_class' => $this->getResourceClass($request->permissionable_type)
                ]
            ], 404);
        }

        // Verificar que el recurso pertenece al admin actual
        if ($resource->user_id !== $user->id) {
            return response()->json([
                'error' => 'Solo puedes asignar permisos sobre tus propios recursos',
                'debug' => [
                    'resource_user_id' => $resource->user_id,
                    'current_user_id' => $user->id,
                    'resource_type' => $request->permissionable_type,
                    'resource_id' => $request->permissionable_id,
                    'resource_object' => $resource
                ]
            ], 403);
        }

        // Verificar que el usuario no es admin
        $targetUser = User::find($request->user_id);
        if ($targetUser->isAdmin()) {
            return response()->json(['error' => 'No puedes asignar permisos a otros administradores'], 403);
        }

        // Crear o actualizar el permiso
        $permission = UserPermission::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'permissionable_type' => $this->getResourceClass($request->permissionable_type),
                'permissionable_id' => $request->permissionable_id
            ],
            [
                'granted_by' => $user->id,
                'can_read' => $request->can_read ?? false,
                'can_write' => $request->can_write ?? false,
                'can_edit' => $request->can_edit ?? false,
                'can_delete' => $request->can_delete ?? false
            ]
        );

        return response()->json([
            'message' => 'Permisos asignados exitosamente',
            'permission' => $permission->load(['user', 'permissionable'])
        ]);
    }

    /**
     * Asignar permisos a todos los recursos de un tipo
     */
    public function assignBulkPermissions(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para asignar permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'permissionable_type' => 'required|string',
            'can_read' => 'boolean',
            'can_write' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar que el usuario no es admin
        $targetUser = User::find($request->user_id);
        if ($targetUser->isAdmin()) {
            return response()->json(['error' => 'No puedes asignar permisos a otros administradores'], 403);
        }

        $resourceType = $request->permissionable_type;
        $resourceClass = $this->getResourceClass($resourceType);
        
        // Obtener todos los recursos del admin actual del tipo especificado
        $resources = $this->getAllResourcesOfType($resourceType, $user->id);
        
        if ($resources->isEmpty()) {
            return response()->json([
                'message' => 'No tienes recursos de este tipo para asignar permisos',
                'resource_type' => $resourceType
            ]);
        }

        $assignedPermissions = [];
        
        // Asignar permisos a cada recurso
        foreach ($resources as $resource) {
            $permission = UserPermission::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'permissionable_type' => $resourceClass,
                    'permissionable_id' => $resource->id
                ],
                [
                    'granted_by' => $user->id,
                    'can_read' => $request->can_read ?? false,
                    'can_write' => $request->can_write ?? false,
                    'can_edit' => $request->can_edit ?? false,
                    'can_delete' => $request->can_delete ?? false
                ]
            );
            
            $assignedPermissions[] = $permission;
        }

        return response()->json([
            'message' => "Permisos asignados exitosamente a {$resources->count()} recursos",
            'resource_type' => $resourceType,
            'assigned_count' => $resources->count(),
            'permissions' => $assignedPermissions
        ]);
    }

    /**
     * Revocar permisos masivos de un usuario
     */
    public function revokeBulkPermissions(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para revocar permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'permissionable_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $resourceType = $request->permissionable_type;
        $resourceClass = $this->getResourceClass($resourceType);
        
        // Revocar todos los permisos del usuario sobre recursos de este tipo
        $deletedCount = UserPermission::where('user_id', $request->user_id)
            ->where('permissionable_type', $resourceClass)
            ->delete();

        return response()->json([
            'message' => "Se revocaron {$deletedCount} permisos del tipo {$resourceType}",
            'resource_type' => $resourceType,
            'revoked_count' => $deletedCount
        ]);
    }

    /**
     * Revocar permisos de un usuario
     */
    public function revokePermission(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para revocar permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'permissionable_type' => 'required|string',
            'permissionable_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $resourceClass = $this->getResourceClass($request->permissionable_type);
        
        $permission = UserPermission::where('user_id', $request->user_id)
            ->where('permissionable_type', $resourceClass)
            ->where('permissionable_id', $request->permissionable_id)
            ->first();

        if (!$permission) {
            return response()->json([
                'error' => 'Permiso no encontrado',
                'debug' => [
                    'user_id' => $request->user_id,
                    'permissionable_type' => $request->permissionable_type,
                    'permissionable_id' => $request->permissionable_id,
                    'resource_class' => $resourceClass,
                    'granted_by' => $user->id
                ]
            ], 404);
        }

        $permission->delete();

        return response()->json(['message' => 'Permisos revocados exitosamente']);
    }

    /**
     * Listar permisos de un usuario
     */
    public function getUserPermissions(Request $request, $userId): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para ver permisos de usuarios'], 403);
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $permissions = $targetUser->permissions()
            ->with(['permissionable', 'grantedBy'])
            ->get();

        return response()->json(['permissions' => $permissions]);
    }

    /**
     * Listar recursos del admin con permisos asignados
     */
    public function getMyResourcesWithPermissions(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden ver sus recursos'], 403);
        }

        $pacientes = $user->pacientes()->with(['permissions.user'])->get();
        $expedientes = $user->expedientes()->with(['permissions.user'])->get();

        return response()->json([
            'pacientes' => $pacientes,
            'expedientes' => $expedientes
        ]);
    }

    /**
     * Obtener el recurso por tipo e ID
     */
    private function getResource(string $type, int $id)
    {
        // Si es 0, significa que es para todos los recursos de ese tipo
        if ($id === 0) {
            return (object) ['user_id' => auth()->id(), 'id' => 0];
        }
        
        switch ($type) {
            case 'pacientes':
                return Paciente::find($id);
            case 'expedientes':
                return ReporteFinal::find($id);
            case 'clinicos':
                return \App\Models\Clinico::find($id);
            case 'esfuerzos':
                return \App\Models\Esfuerzo::find($id);
            case 'estratificaciones':
                return \App\Models\Estratificacion::find($id);
            case 'reporte_nutris':
                return \App\Models\ReporteNutri::find($id);
            case 'reporte_psicos':
                return \App\Models\ReportePsico::find($id);
            case 'reporte_fisios':
                return \App\Models\ReporteFisio::find($id);
            default:
                return null;
        }
    }

    /**
     * Obtener la clase del recurso
     */
    private function getResourceClass(string $type): string
    {
        // Si ya es una clase completa, devolverla tal como está
        if (strpos($type, 'App\\Models\\') === 0) {
            return $type;
        }
        
        // Si es un tipo corto, convertirlo a clase completa
        switch ($type) {
            case 'pacientes':
                return Paciente::class;
            case 'expedientes':
                return ReporteFinal::class;
            case 'clinicos':
                return \App\Models\Clinico::class;
            case 'esfuerzos':
                return \App\Models\Esfuerzo::class;
            case 'estratificaciones':
                return \App\Models\Estratificacion::class;
            case 'reporte_nutris':
                return \App\Models\ReporteNutri::class;
            case 'reporte_psicos':
                return \App\Models\ReportePsico::class;
            case 'reporte_fisios':
                return \App\Models\ReporteFisio::class;
            default:
                return '';
        }
    }

    /**
     * Obtener todos los recursos de un tipo específico que pertenecen al usuario
     */
    private function getAllResourcesOfType(string $type, int $userId)
    {
        switch ($type) {
            case 'pacientes':
                return Paciente::where('user_id', $userId)->get();
            case 'expedientes':
                return ReporteFinal::where('user_id', $userId)->get();
            case 'clinicos':
                return \App\Models\Clinico::where('user_id', $userId)->get();
            case 'esfuerzos':
                return \App\Models\Esfuerzo::where('user_id', $userId)->get();
            case 'estratificaciones':
                return \App\Models\Estratificacion::where('user_id', $userId)->get();
            case 'reporte_nutris':
                return \App\Models\ReporteNutri::where('user_id', $userId)->get();
            case 'reporte_psicos':
                return \App\Models\ReportePsico::where('user_id', $userId)->get();
            case 'reporte_fisios':
                return \App\Models\ReporteFisio::where('user_id', $userId)->get();
            default:
                return collect();
        }
    }
}
