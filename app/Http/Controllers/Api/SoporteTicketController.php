<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoporteTicket;
use App\Models\SoporteMensaje;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SoporteTicketController extends Controller
{
    /**
     * Crear un nuevo ticket de soporte (usuarios)
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria' => ['required', Rule::in(['duda', 'error', 'sugerencia', 'otro'])],
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string|max:5000',
            'clinica_id' => 'nullable|exists:clinicas,id',
            'origen' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        
        // Obtener nombre de clínica si se proporcionó
        $clinicaNombre = null;
        if ($request->clinica_id) {
            $clinica = Clinica::find($request->clinica_id);
            $clinicaNombre = $clinica?->nombre;
        }

        // Determinar origen: portal_paciente, dashboard, etc.
        $origen = $request->origen ?? 'dashboard';
        if ($user->paciente_id || $user->es_paciente_portal) {
            $origen = 'portal_paciente';
        }

        $ticket = SoporteTicket::create([
            'categoria' => $request->categoria,
            'asunto' => $request->asunto,
            'mensaje' => $request->mensaje,
            'user_id' => $user->id,
            'clinica_id' => $request->clinica_id,
            'usuario_nombre' => trim(($user->nombre ?? '') . ' ' . ($user->apellidoPat ?? '') . ' ' . ($user->apellidoMat ?? '')),
            'usuario_email' => $user->email,
            'clinica_nombre' => $clinicaNombre,
            'status' => 'nuevo',
            'prioridad' => 'media',
            'origen' => $origen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado exitosamente',
            'ticket' => [
                'id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'status' => $ticket->status,
            ],
        ], 201);
    }

    /**
     * Obtener tickets del usuario autenticado
     */
    public function misTickets(Request $request)
    {
        $user = Auth::user();
        
        $tickets = SoporteTicket::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Ver detalle de un ticket propio
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $ticket = SoporteTicket::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }

    // ===== MÉTODOS ADMIN =====

    /**
     * Listar todos los tickets (admin)
     */
    public function adminIndex(Request $request)
    {
        $query = SoporteTicket::query();

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('clinica_id')) {
            $query->where('clinica_id', $request->clinica_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_ticket', 'like', "%{$search}%")
                  ->orWhere('asunto', 'like', "%{$search}%")
                  ->orWhere('usuario_nombre', 'like', "%{$search}%")
                  ->orWhere('usuario_email', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Paginación
        $perPage = $request->get('per_page', 20);
        $tickets = $query->paginate($perPage);

        // Estadísticas rápidas
        $stats = [
            'total' => SoporteTicket::count(),
            'nuevos' => SoporteTicket::where('status', 'nuevo')->count(),
            'en_proceso' => SoporteTicket::where('status', 'en_proceso')->count(),
            'resueltos' => SoporteTicket::where('status', 'resuelto')->count(),
        ];

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'stats' => $stats,
        ]);
    }

    /**
     * Ver detalle de ticket (admin)
     */
    public function adminShow($id)
    {
        $ticket = SoporteTicket::with(['user', 'clinica', 'resolvedBy'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }

    /**
     * Actualizar ticket (admin)
     */
    public function adminUpdate(Request $request, $id)
    {
        $ticket = SoporteTicket::findOrFail($id);

        $request->validate([
            'status' => ['nullable', Rule::in(['nuevo', 'en_proceso', 'resuelto', 'cerrado'])],
            'prioridad' => ['nullable', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'respuesta' => 'nullable|string|max:10000',
        ]);

        $updateData = [];

        if ($request->filled('status')) {
            $updateData['status'] = $request->status;
            
            // Si se marca como resuelto, guardar quién y cuándo
            if ($request->status === 'resuelto') {
                $updateData['resuelto_por'] = Auth::id();
                $updateData['resuelto_at'] = now();
            }
        }

        if ($request->filled('prioridad')) {
            $updateData['prioridad'] = $request->prioridad;
        }

        if ($request->filled('respuesta')) {
            $updateData['respuesta'] = $request->respuesta;
        }

        $ticket->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Ticket actualizado',
            'ticket' => $ticket->fresh(),
        ]);
    }

    /**
     * Responder y resolver ticket (admin)
     */
    public function adminResponder(Request $request, $id)
    {
        $ticket = SoporteTicket::findOrFail($id);

        $request->validate([
            'respuesta' => 'required|string|max:10000',
            'cerrar' => 'nullable|boolean',
        ]);

        $status = $request->boolean('cerrar') ? 'cerrado' : 'resuelto';

        $ticket->update([
            'respuesta' => $request->respuesta,
            'status' => $status,
            'resuelto_por' => Auth::id(),
            'resuelto_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket respondido y marcado como ' . $status,
            'ticket' => $ticket->fresh(),
        ]);
    }

    /* ──────────────────────────────────────────────────────────
     * CHAT EN VIVO (mensajes por ticket)
     * ────────────────────────────────────────────────────────── */

    /**
     * Obtener mensajes de chat de un ticket (usuario)
     */
    public function getMensajes(Request $request, $id)
    {
        $user   = Auth::user();
        $ticket = SoporteTicket::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $since = $request->query('since'); // timestamp ISO para polling incremental

        $query = $ticket->mensajes()->with('sender:id,nombre,apellidoPat,apellidoMat');
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        $mensajes = $query->get()->map(fn($m) => $this->formatMensaje($m));

        // Marcar como leídos los mensajes de admin no leídos
        $ticket->mensajes()
            ->where('sender_type', 'admin')
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);

        return response()->json(['mensajes' => $mensajes]);
    }

    /**
     * Enviar mensaje en un ticket (usuario)
     */
    public function enviarMensaje(Request $request, $id)
    {
        $user   = Auth::user();
        $ticket = SoporteTicket::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate(['mensaje' => 'required|string|max:5000']);

        // Reabrir si estaba cerrado/resuelto
        if (in_array($ticket->status, ['resuelto', 'cerrado'])) {
            $ticket->update(['status' => 'en_proceso']);
        }

        $mensaje = SoporteMensaje::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $user->id,
            'sender_type' => 'user',
            'mensaje'     => $request->mensaje,
        ]);

        return response()->json(['mensaje' => $this->formatMensaje($mensaje->load('sender:id,nombre,apellidoPat,apellidoMat'))]);
    }

    /**
     * Obtener mensajes de chat de un ticket (admin)
     */
    public function adminGetMensajes(Request $request, $id)
    {
        $ticket = SoporteTicket::findOrFail($id);
        $since  = $request->query('since');

        $query = $ticket->mensajes()->with('sender:id,nombre,apellidoPat,apellidoMat');
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        $mensajes = $query->get()->map(fn($m) => $this->formatMensaje($m));

        // Marcar como leídos los mensajes de user no leídos
        $ticket->mensajes()
            ->where('sender_type', 'user')
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);

        return response()->json(['mensajes' => $mensajes]);
    }

    /**
     * Enviar mensaje en un ticket (admin)
     */
    public function adminEnviarMensaje(Request $request, $id)
    {
        $ticket = SoporteTicket::findOrFail($id);

        $request->validate(['mensaje' => 'required|string|max:5000']);

        // Marcar ticket en proceso si estaba nuevo
        if ($ticket->status === 'nuevo') {
            $ticket->update(['status' => 'en_proceso']);
        }

        $mensaje = SoporteMensaje::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => Auth::id(),
            'sender_type' => 'admin',
            'mensaje'     => $request->mensaje,
        ]);

        return response()->json(['mensaje' => $this->formatMensaje($mensaje->load('sender:id,nombre,apellidoPat,apellidoMat'))]);
    }

    /**
     * Número de mensajes no leídos de admin para el usuario
     */
    public function mensajesNoLeidos()
    {
        $userId = Auth::id();
        $count = SoporteMensaje::whereHas('ticket', fn($q) => $q->where('user_id', $userId))
            ->where('sender_type', 'admin')
            ->whereNull('leido_at')
            ->count();

        return response()->json(['no_leidos' => $count]);
    }

    /**
     * Número de mensajes no leídos de users para el admin
     */
    public function adminMensajesNoLeidos()
    {
        $count = SoporteMensaje::where('sender_type', 'user')
            ->whereNull('leido_at')
            ->count();

        return response()->json(['no_leidos' => $count]);
    }

    /**
     * Buscar usuarios para iniciar chat (admin)
     */
    public function buscarUsuarios(Request $request)
    {
        $q = $request->query('q', '');

        $users = \App\Models\User::with('clinica:id,nombre')
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                      ->orWhere('apellidoPat', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->whereNull('paciente_id') // excluir pacientes del portal
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id', 'nombre', 'apellidoPat', 'apellidoMat', 'email', 'clinica_id'])
            ->map(fn($u) => [
                'id'           => $u->id,
                'name'         => trim(($u->nombre ?? '') . ' ' . ($u->apellidoPat ?? '') . ' ' . ($u->apellidoMat ?? '')),
                'email'        => $u->email,
                'clinica'      => $u->clinica?->nombre,
            ]);

        return response()->json(['usuarios' => $users]);
    }

    /**
     * Admin inicia un chat/ticket con un usuario específico
     */
    public function adminIniciarChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'asunto'  => 'required|string|max:255',
            'mensaje' => 'required|string|max:5000',
        ]);

        $targetUser = \App\Models\User::findOrFail($request->user_id);

        // Crear ticket en nombre del usuario destino
        $ticket = SoporteTicket::create([
            'categoria'      => 'otro',
            'asunto'         => $request->asunto,
            'mensaje'        => $request->mensaje,
            'user_id'        => $targetUser->id,
            'clinica_id'     => $targetUser->clinica_id,
            'usuario_nombre' => trim(($targetUser->nombre ?? '') . ' ' . ($targetUser->apellidoPat ?? '') . ' ' . ($targetUser->apellidoMat ?? '')),
            'usuario_email'  => $targetUser->email,
            'clinica_nombre' => $targetUser->clinica?->nombre,
            'status'         => 'en_proceso',
            'prioridad'      => 'media',
            'origen'         => 'admin_iniciado',
        ]);

        // Enviar el primer mensaje como admin
        $mensaje = SoporteMensaje::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => Auth::id(),
            'sender_type' => 'admin',
            'mensaje'     => $request->mensaje,
        ]);

        return response()->json([
            'success' => true,
            'ticket'  => $ticket->fresh(),
            'mensaje' => $this->formatMensaje($mensaje->load('sender:id,nombre,apellidoPat,apellidoMat')),
        ]);
    }

    private function formatMensaje($m): array
    {
        return [
            'id'          => $m->id,
            'ticket_id'   => $m->ticket_id,
            'sender_type' => $m->sender_type,
            'sender_name' => $m->sender ? trim(($m->sender->nombre ?? '') . ' ' . ($m->sender->apellidoPat ?? '')) : 'Soporte',
            'mensaje'     => $m->mensaje,
            'leido_at'    => $m->leido_at,
            'created_at'  => $m->created_at->toIso8601String(),
        ];
    }
}
