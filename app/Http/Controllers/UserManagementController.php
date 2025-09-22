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
            'cedula' => 'required|string|unique:users,cedula',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'isAdmin' => 'boolean',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newUser = User::create([
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat,
            'apellidoMat' => $request->apellidoMat,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'isAdmin' => $request->isAdmin ?? false
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $newUser
        ], 201);
    }

    /**
     * Listar todos los usuarios (solo administradores)
     */
    public function listUsers(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'No tienes permisos para ver la lista de usuarios'], 403);
        }

        $users = User::select('id', 'nombre', 'apellidoPat', 'apellidoMat', 'cedula', 'email', 'isAdmin', 'created_at')
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

        // No permitir que un admin quite el admin a otro admin
        if ($targetUser->isAdmin() && $request->has('isAdmin') && !$request->isAdmin) {
            return response()->json(['error' => 'No puedes quitarle el privilegio de administrador a otro administrador'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidoPat' => 'required|string|max:255',
            'apellidoMat' => 'required|string|max:255',
            'cedula' => 'required|string|unique:users,cedula,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'isAdmin' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat,
            'apellidoMat' => $request->apellidoMat,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'isAdmin' => $request->isAdmin ?? false
        ];

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
}
