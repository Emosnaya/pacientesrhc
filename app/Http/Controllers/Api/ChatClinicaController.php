<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatClinicaMensaje;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatClinicaController extends Controller
{
    /**
     * Devuelve la clinica_id efectiva para el usuario autenticado.
     */
    private function clinicaId(): int
    {
        $user = Auth::user();
        return (int) ($user->clinica_activa_id ?? $user->clinica_id);
    }

    /**
     * GET /api/chat-clinica/mensajes?since=ISO8601&limit=50
     * Devuelve mensajes del canal de la clínica del usuario.
     * Si ?since= se pasa, sólo devuelve mensajes posteriores a ese timestamp.
     */
    public function getMensajes(Request $request)
    {
        $clinicaId = $this->clinicaId();
        if (!$clinicaId) {
            return response()->json(['mensajes' => []]);
        }

        $since  = $request->query('since');
        $limit  = min((int) ($request->query('limit', 50)), 100);

        $query = ChatClinicaMensaje::with('user:id,nombre,apellidoPat')
            ->where('clinica_id', $clinicaId)
            ->orderBy('created_at', 'asc');

        if ($since) {
            $query->where('created_at', '>', $since);
        } else {
            // Carga inicial: últimos $limit mensajes
            $query->latest()->limit($limit);
            $mensajes = $query->get()->reverse()->values();
            return response()->json(['mensajes' => $mensajes->map(fn($m) => $this->format($m))]);
        }

        $mensajes = $query->get();
        return response()->json(['mensajes' => $mensajes->map(fn($m) => $this->format($m))]);
    }

    /**
     * POST /api/chat-clinica/mensajes
     * Envía un mensaje al canal de la clínica.
     */
    public function enviarMensaje(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:2000']);

        $user      = Auth::user();
        $clinicaId = $this->clinicaId();

        if (!$clinicaId) {
            return response()->json(['error' => 'Sin clínica asignada'], 422);
        }

        // Bloquear pacientes del portal
        if ($user->paciente_id || $user->es_paciente_portal) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $m = ChatClinicaMensaje::create([
            'clinica_id' => $clinicaId,
            'user_id'    => $user->id,
            'mensaje'    => $request->mensaje,
        ]);

        $m->load('user:id,nombre,apellidoPat');

        return response()->json(['mensaje' => $this->format($m)], 201);
    }

    /**
     * GET /api/chat-clinica/miembros
     * Lista de usuarios operativos de la misma clínica (para mostrar quiénes están en el chat).
     */
    public function miembros()
    {
        $clinicaId = $this->clinicaId();
        if (!$clinicaId) {
            return response()->json(['miembros' => []]);
        }

        // Usuarios de user_clinicas que NO sean pacientes del portal
        $miembros = DB::table('user_clinicas')
            ->join('users', 'users.id', '=', 'user_clinicas.user_id')
            ->where('user_clinicas.clinica_id', $clinicaId)
            ->whereNull('users.paciente_id')
            ->whereNull('users.deleted_at')
            ->select('users.id', 'users.nombre', 'users.apellidoPat', 'users.email')
            ->orderBy('users.nombre')
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'nombre' => trim(($u->nombre ?? '') . ' ' . ($u->apellidoPat ?? '')),
                'email'  => $u->email,
            ]);

        return response()->json(['miembros' => $miembros]);
    }

    private function format(ChatClinicaMensaje $m): array
    {
        return [
            'id'         => $m->id,
            'user_id'    => $m->user_id,
            'nombre'     => $m->user ? trim(($m->user->nombre ?? '') . ' ' . ($m->user->apellidoPat ?? '')) : 'Usuario',
            'mensaje'    => $m->mensaje,
            'created_at' => $m->created_at->toIso8601String(),
        ];
    }
}
