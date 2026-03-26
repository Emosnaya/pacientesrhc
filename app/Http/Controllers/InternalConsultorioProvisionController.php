<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Alta de consultorio por operador (sin exigir suscripción Stripe del médico).
 */
class InternalConsultorioProvisionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'owner_email' => 'required|email|exists:users,email',
            'nombre' => 'required|string|max:255',
            'tipo_clinica' => 'nullable|string|in:'.implode(',', array_keys(config('clinica_tipos.tipos'))),
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'co_owner_email' => 'nullable|email|exists:users,email',
        ]);

        /** @var User $owner */
        $owner = User::where('email', $request->owner_email)->firstOrFail();

        DB::beginTransaction();
        try {
            $consultorio = Clinica::create([
                'nombre' => $request->nombre,
                'tipo_clinica' => $request->tipo_clinica ?? 'general',
                'modulos_habilitados' => [],
                'email' => $request->email ?? $owner->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'plan' => 'profesional',
                'duration' => 'mensual',
                'pagado' => true,
                'activa' => true,
                'es_consultorio_privado' => true,
                'propietario_user_id' => $owner->id,
                'permite_multiples_sucursales' => false,
                'max_sucursales' => 1,
                'max_usuarios' => 10,
                'max_pacientes' => 500,
                'fecha_vencimiento' => now()->addYear(),
            ]);

            $sucursal = Sucursal::create([
                'clinica_id' => $consultorio->id,
                'nombre' => $request->nombre,
                'es_principal' => true,
                'activa' => true,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
            ]);

            if (! $owner->clinicas()->where('clinica_id', $consultorio->id)->exists()) {
                $owner->clinicas()->attach($consultorio->id, User::pivotPropietarioConsultorio());
            }

            if ($request->filled('co_owner_email') && strcasecmp($request->co_owner_email, $owner->email) !== 0) {
                /** @var User $co */
                $co = User::where('email', $request->co_owner_email)->firstOrFail();
                if (! $co->clinicas()->where('clinica_id', $consultorio->id)->exists()) {
                    $co->clinicas()->attach($consultorio->id, User::pivotPropietarioConsultorio());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'consultorio' => $consultorio->fresh(),
                'sucursal_principal_id' => $sucursal->id,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
