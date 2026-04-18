<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Product;

/**
 * Lógica principal del chatbot IA.
 *
 * Responsable de:
 * - Construir el system prompt con el menú real del restaurante
 * - Recuperar el historial de mensajes como contexto para la IA
 * - Controlar el límite de tokens por conversación
 * - Persistir mensajes de usuario y asistente en la BD
 * - Manejar el fallback si la API de IA falla
 *
 * @author AyrtonAlania
 */
class ChatService
{
    private const MAX_TOKENS = 6000;

    /**
     * @param  OpenAIService  $openAI
     */
    public function __construct(protected OpenAIService $openAI) {}

    /**
     * Procesa un mensaje del usuario y devuelve la respuesta del asistente.
     *
     * Persiste ambos mensajes en la BD y actualiza el contador de tokens.
     *
     * @param  Conversation  $conversation
     * @param  string        $userMessage
     * @return array{error: bool, message: string}
     */
    public function handleMessage(Conversation $conversation, string $userMessage): array
    {
        if ($conversation->tokens_used >= self::MAX_TOKENS) {
            $conversation->update(['status' => 'closed']);

            return [
                'error'   => false,
                'message' => 'Hemos llegado al límite de esta conversación. '
                           . 'Confirma tu pedido o inicia una nueva conversación.',
                'closed'  => true,
            ];
        }

        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $userMessage,
        ]);

        $messages = $this->buildContext($conversation);

        try {
            $response = $this->openAI->sendMessage($messages);
        } catch (\Exception) {
            return [
                'error'   => true,
                'message' => 'El asistente no está disponible en este momento. '
                           . 'Puedes usar el menú tradicional.',
                'closed'  => false,
            ];
        }

        $assistantReply = $response['choices'][0]['message']['content'] ?? '';
        $tokensUsed     = $response['usage']['total_tokens'] ?? 0;

        $conversation->messages()->create([
            'role'        => 'assistant',
            'content'     => $assistantReply,
            'tokens_used' => $tokensUsed,
        ]);

        $conversation->increment('tokens_used', $tokensUsed);

        return [
            'error'   => false,
            'message' => $assistantReply,
            'closed'  => false,
        ];
    }

    /**
     * Construye el array de mensajes que se envía a la API.
     *
     * Incluye un system prompt dinámico con el menú real del restaurante
     * y los últimos 10 mensajes de la conversación como historial.
     *
     * @param  Conversation  $conversation
     * @return array
     */
    private function buildContext(Conversation $conversation): array
    {
        $restaurantName = $conversation->table->user->name;

        $products = Product::where('user_id', $conversation->table->user_id)
            ->where('is_active', true)
            ->with('ingredients')
            ->get()
            ->map(fn($p) => $p->name . ' (' . number_format($p->price, 2) . '€)')
            ->implode(', ');

        $allergens = Product::where('user_id', $conversation->table->user_id)
            ->where('is_active', true)
            ->with(['ingredients' => fn($q) => $q->where('is_allergen', true)])
            ->get()
            ->flatMap(fn($p) => $p->ingredients->pluck('name'))
            ->unique()
            ->implode(', ');

        $systemPrompt = [
            'role'    => 'system',
            'content' => "Eres el asistente virtual del restaurante \"{$restaurantName}\". "
                       . "Solo puedes recomendar platos del siguiente menú: {$products}. "
                       . "Alérgenos presentes en la carta: {$allergens}. "
                       . "No inventes platos, precios ni ingredientes que no estén en la lista. "
                       . "Si no sabes algo, indica amablemente que consulte al camarero. "
                       . "Responde siempre en español y de forma concisa.",
        ];

        $history = $conversation->messages()
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($msg) => [
                'role'    => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();

        return array_merge([$systemPrompt], $history);
    }
}
