<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Obtener perfil del usuario
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }

    /**
     * Actualizar perfil del usuario
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255',
            'apellidoPat' => 'nullable|string|max:255',
            'apellidoMat' => 'nullable|string|max:255',
            'cedula' => 'nullable|string|unique:users,cedula,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [];

        // Solo actualizar campos que se proporcionan
        if ($request->filled('nombre')) {
            $updateData['nombre'] = $request->nombre;
        }
        if ($request->filled('apellidoPat')) {
            $updateData['apellidoPat'] = $request->apellidoPat;
        }
        if ($request->filled('apellidoMat')) {
            $updateData['apellidoMat'] = $request->apellidoMat;
        }
        if ($request->filled('cedula')) {
            $updateData['cedula'] = $request->cedula;
        }
        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Manejar la subida de imagen
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            // Eliminar imagen anterior si existe y no es la por defecto
            if ($user->imagen && $user->imagen !== 'perfiles/avatar-default.png') {
                Storage::disk('public')->delete($user->imagen);
            }

            $imagen = $request->file('imagen');
            $extension = $imagen->getClientOriginalExtension();
            $nombreImagen = $user->cedula . '.' . $extension;
            $rutaImagen = $imagen->storeAs('perfiles', $nombreImagen, 'public');
            $updateData['imagen'] = $rutaImagen;
        }

        // Siempre actualizar, incluso si no hay datos específicos
        // (esto permite actualizar solo la imagen si es necesario)
        $user->update($updateData);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Subir imagen de perfil
     */
    public function uploadImage(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Eliminar imagen anterior si existe y no es la por defecto
            if ($user->imagen && $user->imagen !== 'perfiles/avatar-default.png') {
                Storage::disk('public')->delete($user->imagen);
            }

            $imagen = $request->file('imagen');
            $extension = $imagen->getClientOriginalExtension();
            $nombreImagen = $user->cedula . '.' . $extension;
            $rutaImagen = $imagen->storeAs('perfiles', $nombreImagen, 'public');
            
            $user->update(['imagen' => $rutaImagen]);

            return response()->json([
                'message' => 'Imagen actualizada exitosamente',
                'user' => $user->fresh(),
                'imagen_url' => asset('storage/' . $rutaImagen)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}