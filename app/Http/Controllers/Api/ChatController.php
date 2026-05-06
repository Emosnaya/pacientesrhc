<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversacion;
use App\Models\ChatParticipante;
use App\Models\ChatMensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    private function clinicaId(): int
    {
        $user = Auth::user();
        return (int) ($user->clinica_activa_id ?? $user->clinica_id);
    }

    private function esOperativo(): bool
    {
        $user = Auth::user();
        return !($user->paciente_id || $user->es_paciente_portal);
    }

    // ── GET /api/chat/conversaciones ─────────────────────────
    // Lista de conversaciones del usuario con último mensaje y no-leídos
    public function conversaciones()
    {
        if (!$this->esOperativo()) return response()->json(['error' => 'No autorizado'], 403);

        $userId    = Auth::id();
        $clinicaId = $this->clinicaId();

        $conversaciones = ChatConversacion::whereHas('participantes', fn($q) => $q->where('user_id', $userId))
            ->where('clinica_id', $clinicaId)
            ->with([
                'ultimoMensaje.user:id,nombre,apellidoPat',
                'participantes.user:id,nombre,apellidoPat',
            ])
            ->orderByDesc(function ($query) {
                // Ordenar por último mensaje
                $query->select('created_at')
                    ->from('chat_mensajes')
                    ->whereColumn('conversacion_id', 'chat_conversaciones.id')
                    ->latest()
                    ->limit(1);
            })
            ->get()
            ->map(function ($conv) use ($userId) {
                $pivotRow = $conv->participantes->firstWhere('user_id', $userId);
                $lastRead = $pivotRow?->last_read_at;

                // No-leídos: mensajes después de last_read_at de otros usuarios
                $noLeidos = ChatMensaje::where('conversacion_id', $conv->id)
                    ->where('user_id', '!=', $userId)
                    ->when($lastRead, fn($q) => $q->where('created_at', '>', $lastRead))
                    ->count();

                return $this->formatConversacion($conv, $userId, $noLeidos);
            });

        return response()->json(['conversaciones' => $conversaciones]);
    }

    // ── POST /api/chat/conversaciones ────────────────────────
    // Crear DM o grupo. Para DM, si ya existe devuelve el existente.
    public function crear(Request $request)
    {
        if (!$this->esOperativo()) return response()->json(['error' => 'No autorizado'], 403);

        $request->validate([
            'tipo'           => 'required|in:directo,grupo',
            'participantes'  => 'required|array|min:1|max:50',
            'participantes.*'=> 'exists:users,id',
            'nombre'         => 'nullable|string|max:100',
        ]);

        $userId    = Auth::id();
        $clinicaId = $this->clinicaId();

        // Asegurar que el creador esté incluido
        $participantesIds = array_unique(array_merge([$userId], $request->participantes));

        // Para DM (directo entre 2 personas): buscar si ya existe
        if ($request->tipo === 'directo' && count($participantesIds) === 2) {
            $otroId = collect($participantesIds)->first(fn($id) => $id !== $userId);
            $existing = ChatConversacion::where('clinica_id', $clinicaId)
                ->where('tipo', 'directo')
                ->whereHas('participantes', fn($q) => $q->where('user_id', $userId))
                ->whereHas('participantes', fn($q) => $q->where('user_id', $otroId))
                ->first();

            if ($existing) {
                $existing->load(['ultimoMensaje.user:id,nombre,apellidoPat', 'participantes.user:id,nombre,apellidoPat']);
                return response()->json(['conversacion' => $this->formatConversacion($existing, $userId, 0)]);
            }
        }

        // Verificar que los participantes pertenezcan a la misma clínica
        $validIds = DB::table('user_clinicas')
            ->where('clinica_id', $clinicaId)
            ->whereIn('user_id', $participantesIds)
            ->pluck('user_id')
            ->toArray();

        // El creador siempre es válido
        if (!in_array($userId, $validIds)) $validIds[] = $userId;

        $conv = ChatConversacion::create([
            'clinica_id' => $clinicaId,
            'tipo'       => $request->tipo,
            'nombre'     => $request->tipo === 'grupo' ? ($request->nombre ?: 'Grupo') : null,
            'created_by' => $userId,
        ]);

        foreach ($validIds as $pid) {
            ChatParticipante::create([
                'conversacion_id' => $conv->id,
                'user_id'         => $pid,
            ]);
        }

        $conv->load(['ultimoMensaje.user:id,nombre,apellidoPat', 'participantes.user:id,nombre,apellidoPat']);
        return response()->json(['conversacion' => $this->formatConversacion($conv, $userId, 0)], 201);
    }

    // ── GET /api/chat/conversaciones/{id}/mensajes?since= ────
    public function getMensajes(Request $request, $id)
    {
        $userId = Auth::id();
        $conv   = ChatConversacion::whereHas('participantes', fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);

        $since = $request->query('since');
        $query = ChatMensaje::with('user:id,nombre,apellidoPat')
            ->where('conversacion_id', $conv->id);

        if ($since) {
            $query->where('created_at', '>', $since);
        } else {
            $query->latest()->limit(60);
            $mensajes = $query->get()->reverse()->values();
            return response()->json(['mensajes' => $mensajes->map(fn($m) => $this->formatMensaje($m))]);
        }

        return response()->json(['mensajes' => $query->orderBy('created_at')->get()->map(fn($m) => $this->formatMensaje($m))]);
    }

    // ── POST /api/chat/conversaciones/{id}/mensajes ──────────
    public function enviarMensaje(Request $request, $id)
    {
        if (!$this->esOperativo()) return response()->json(['error' => 'No autorizado'], 403);

        $request->validate(['mensaje' => 'required|string|max:2000']);
        $userId = Auth::id();

        $conv = ChatConversacion::whereHas('participantes', fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);

        $m = ChatMensaje::create([
            'conversacion_id' => $conv->id,
            'user_id'         => $userId,
            'mensaje'         => $request->mensaje,
        ]);

        // Marcar como leído para el emisor
        ChatParticipante::where('conversacion_id', $conv->id)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        $m->load('user:id,nombre,apellidoPat');
        return response()->json(['mensaje' => $this->formatMensaje($m)], 201);
    }

    // ── POST /api/chat/conversaciones/{id}/leido ─────────────
    public function marcarLeido($id)
    {
        $userId = Auth::id();
        ChatParticipante::where('conversacion_id', $id)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ── GET /api/chat/miembros ───────────────────────────────
    // Usuarios operativos de la misma clínica para iniciar chats
    public function miembros()
    {
        $clinicaId = $this->clinicaId();
        $userId    = Auth::id();

        $miembros = DB::table('user_clinicas')
            ->join('users', 'users.id', '=', 'user_clinicas.user_id')
            ->where('user_clinicas.clinica_id', $clinicaId)
            ->where('users.id', '!=', $userId)
            ->whereNull('users.paciente_id')
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

    // ── GET /api/chat/no-leidos ──────────────────────────────
    public function noLeidos()
    {
        $userId    = Auth::id();
        $clinicaId = $this->clinicaId();

        $total = 0;
        $conversaciones = ChatConversacion::whereHas('participantes', fn($q) => $q->where('user_id', $userId))
            ->where('clinica_id', $clinicaId)
            ->with(['participantes' => fn($q) => $q->where('user_id', $userId)])
            ->get();

        foreach ($conversaciones as $conv) {
            $lastRead = $conv->participantes->first()?->last_read_at;
            $count = ChatMensaje::where('conversacion_id', $conv->id)
                ->where('user_id', '!=', $userId)
                ->when($lastRead, fn($q) => $q->where('created_at', '>', $lastRead))
                ->count();
            $total += $count;
        }

        return response()->json(['no_leidos' => $total]);
    }

    // ── Formatters ───────────────────────────────────────────
    private function formatConversacion(ChatConversacion $conv, int $myId, int $noLeidos): array
    {
        $otros = $conv->participantes->where('user_id', '!=', $myId)->values();

        // Para DM el nombre es el otro participante; para grupo el nombre del grupo
        if ($conv->tipo === 'directo') {
            $otro = $otros->first()?->user;
            $nombre = $otro ? trim(($otro->nombre ?? '') . ' ' . ($otro->apellidoPat ?? '')) : 'Chat';
        } else {
            $nombre = $conv->nombre ?: 'Grupo';
        }

        $ultimo = $conv->ultimoMensaje;
        $autorUltimo = $ultimo?->user;

        return [
            'id'           => $conv->id,
            'tipo'         => $conv->tipo,
            'nombre'       => $nombre,
            'no_leidos'    => $noLeidos,
            'participantes'=> $conv->participantes->map(fn($p) => [
                'user_id' => $p->user_id,
                'nombre'  => $p->user ? trim(($p->user->nombre ?? '') . ' ' . ($p->user->apellidoPat ?? '')) : '',
            ])->values(),
            'ultimo_mensaje' => $ultimo ? [
                'mensaje'   => $ultimo->mensaje,
                'autor'     => $autorUltimo ? trim(($autorUltimo->nombre ?? '')) : '',
                'es_propio' => $ultimo->user_id === $myId,
                'created_at'=> $ultimo->created_at->toIso8601String(),
            ] : null,
        ];
    }

    private function formatMensaje(ChatMensaje $m): array
    {
        return [
            'id'              => $m->id,
            'conversacion_id' => $m->conversacion_id,
            'user_id'         => $m->user_id,
            'nombre'          => $m->user ? trim(($m->user->nombre ?? '') . ' ' . ($m->user->apellidoPat ?? '')) : 'Usuario',
            'mensaje'         => $m->mensaje,
            'created_at'      => $m->created_at->toIso8601String(),
        ];
    }
}
