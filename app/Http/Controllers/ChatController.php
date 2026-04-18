<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Table;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestiona los endpoints públicos del chatbot IA.
 *
 * Los endpoints son accesibles sin autenticación porque la carta digital
 * es pública. El multitenancy se garantiza implícitamente a través de
 * table_hash → table → user_id.
 *
 * @author AyrtonAlania
 */
class ChatController extends Controller
{
    /**
     * @param  ChatService  $chatService
     */
    public function __construct(protected ChatService $chatService) {}

    /**
     * Inicia una nueva conversación para la mesa identificada por su unique_hash.
     *
     * @param  string  $tableHash  Hash único de la mesa (desde la URL del QR)
     * @return JsonResponse
     */
    public function start(string $tableHash): JsonResponse
    {
        $table = Table::where('unique_hash', $tableHash)->firstOrFail();

        $conversation = Conversation::create([
            'table_id' => $table->id,
            'status'   => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data'    => ['conversation_id' => $conversation->id],
            'message' => 'Conversación iniciada.',
        ], 201);
    }

    /**
     * Envía un mensaje del cliente y devuelve la respuesta del asistente IA.
     *
     * @param  Request       $request
     * @param  Conversation  $conversation
     * @return JsonResponse
     */
    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        if ($conversation->status === 'closed') {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Esta conversación ya está cerrada. Inicia una nueva.',
            ], 422);
        }

        $result = $this->chatService->handleMessage($conversation, $validated['message']);

        return response()->json([
            'success' => ! $result['error'],
            'data'    => ['reply' => $result['message'], 'closed' => $result['closed']],
            'message' => $result['error'] ? 'Error en el asistente.' : 'OK',
        ]);
    }

    /**
     * Cierra manualmente una conversación activa.
     *
     * @param  Conversation  $conversation
     * @return JsonResponse
     */
    public function close(Conversation $conversation): JsonResponse
    {
        $conversation->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Conversación cerrada.',
        ]);
    }
}
