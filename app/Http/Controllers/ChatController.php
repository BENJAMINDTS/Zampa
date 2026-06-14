<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Table;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            'data'    => [
                'reply'  => $result['message'],
                'cards'  => $result['cards'] ?? [],
                'closed' => $result['closed'],
            ],
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

    /**
     * Devuelve las categorías con productos activos del restaurante para el widget de chat.
     * Permite al frontend mostrar tarjetas de productos sin pasar por la IA.
     *
     * @param  string  $tableHash  Hash único de la mesa
     * @return JsonResponse
     */
    public function menu(string $tableHash): JsonResponse
    {
        $table = Table::where('unique_hash', $tableHash)
            ->with('user')
            ->firstOrFail();

        $userId = $table->user_id;

        $payload = Cache::remember("chat-menu:{$userId}", 300, function () use ($table) {
            $categories = $table->user->categories()
                ->with(['products' => function ($q) {
                    $q->where('is_active', true)
                      ->where('is_available', true)
                      ->orderBy('name')
                      ->with(['ingredients', 'variants']);
                }])
                ->get()
                ->filter(fn ($c) => $c->products->isNotEmpty())
                ->map(fn ($c) => [
                    'id'          => $c->id,
                    'name'        => $c->name,
                    'destination' => $c->destination,
                    'products'    => $c->products->map(fn ($p) => [
                        'id'          => $p->id,
                        'name'        => $p->name,
                        'description' => $p->description ?? '',
                        'price'       => $p->variants->isNotEmpty()
                                            ? (float) $p->variants->min('price')
                                            : (float) $p->price,
                        'variants'    => $p->variants->map(fn ($v) => [
                            'id'    => $v->id,
                            'name'  => $v->name,
                            'price' => (float) $v->price,
                        ])->values(),
                        'ingredients' => $p->ingredients->map(fn ($i) => [
                            'id'          => $i->id,
                            'name'        => $i->name,
                            'is_allergen' => (bool) $i->is_allergen,
                        ])->values(),
                    ])->values(),
                ])
                ->values();

            return [
                'restaurant' => $table->user->business_name ?: $table->user->name,
                'table'      => $table->name,
                'categories' => $categories,
            ];
        });

        return response()->json(['success' => true, 'data' => $payload]);
    }
}
