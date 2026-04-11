<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoporteTicket;
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
        ]);

        $user = Auth::user();
        
        // Obtener nombre de clínica si se proporcionó
        $clinicaNombre = null;
        if ($request->clinica_id) {
            $clinica = Clinica::find($request->clinica_id);
            $clinicaNombre = $clinica?->nombre;
        }

        $ticket = SoporteTicket::create([
            'categoria' => $request->categoria,
            'asunto' => $request->asunto,
            'mensaje' => $request->mensaje,
            'user_id' => $user->id,
            'clinica_id' => $request->clinica_id,
            'usuario_nombre' => $user->name,
            'usuario_email' => $user->email,
            'clinica_nombre' => $clinicaNombre,
            'status' => 'nuevo',
            'prioridad' => 'media',
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
}
