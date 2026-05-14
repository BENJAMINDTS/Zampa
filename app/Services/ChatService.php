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
            'cards'   => $this->extractMentionedProducts($conversation, $assistantReply),
            'closed'  => false,
        ];
    }

    /**
     * Detecta qué productos del menú menciona el texto de la IA y los devuelve
     * como tarjetas estructuradas para el frontend.
     *
     * @param  Conversation  $conversation
     * @param  string        $text
     * @return array
     */
    private function extractMentionedProducts(Conversation $conversation, string $text): array
    {
        $lower = mb_strtolower($text);

        return Product::where('user_id', $conversation->table->user->id)
            ->where('is_active', true)
            ->with('ingredients')
            ->get()
            ->filter(fn (Product $p) => str_contains($lower, mb_strtolower($p->name)))
            ->map(fn (Product $p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => (float) $p->price,
                'description' => $p->description ?? '',
                'image'       => $p->image ? asset('storage/' . $p->image) : null,
                'allergens'   => $p->ingredients
                    ->where('is_allergen', true)
                    ->pluck('name')
                    ->values(),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Construye el array de mensajes que se envía a la API.
     *
     * Incluye un system prompt dinámico con el menú real del restaurante,
     * la receta (ingredientes + alérgenos) de cada plato, y el historial
     * de la conversación. La IA puede LEER recetas pero nunca modificarlas.
     *
     * @param  Conversation  $conversation
     * @return array
     */
    private function buildContext(Conversation $conversation): array
    {
        $user           = $conversation->table->user;
        $restaurantName = $user->name;

        $menuLines = Product::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('ingredients')
            ->get()
            ->map(function (Product $p): string {
                $ingredients = $p->ingredients->map(fn($i) => $i->name)->join(', ');
                $allergens   = $p->ingredients
                    ->where('is_allergen', true)
                    ->map(fn($i) => $i->name)
                    ->join(', ');

                $line = "- {$p->name} (" . number_format($p->price, 2) . '€)';

                if ($ingredients !== '') {
                    $line .= ": {$ingredients}";
                }
                if ($allergens !== '') {
                    $line .= " [ALÉRGENOS: {$allergens}]";
                }

                return $line;
            })
            ->join("\n");

        $systemPrompt = [
            'role'    => 'system',
            'content' => "Eres Zampi, el asistente virtual del restaurante \"{$restaurantName}\". "
                       . "Tu tono es siempre cálido, amable y cercano, como si fueras un camarero de confianza. "
                       . "Respondes en español de España, de forma concisa y natural.\n\n"
                       . "MENÚ DISPONIBLE (nombre, precio, ingredientes y alérgenos):\n"
                       . $menuLines . "\n\n"
                       . "NORMAS GENERALES:\n"
                       . "- NUNCA sugieras modificar recetas, precios ni ingredientes.\n"
                       . "- No inventes platos, precios ni ingredientes que no aparezcan en la lista.\n"
                       . "- Si tienes dudas sobre un caso muy específico, invita amablemente al cliente a consultarlo con el personal.\n\n"
                       . "NORMAS DE FORMATO:\n"
                       . "- Cuando recomiendes o menciones varios platos, usa este formato exacto:\n"
                       . "  1. Una frase breve de introducción cálida.\n"
                       . "  2. Cada plato en su propia línea, precedido de un guion: «— Nombre del plato: breve descripción o motivo por el que lo recomiendas (máx. 10 palabras)».\n"
                       . "  3. Una frase corta de cierre invitando a preguntar o pedir.\n"
                       . "- Si solo mencionas un plato, escríbelo en texto natural sin guion.\n"
                       . "- NO incluyas precios en el texto: el sistema los mostrará en tarjetas visuales automáticamente.\n"
                       . "- NO uses asteriscos, markdown ni formato HTML.\n\n"
                       . "NORMAS SOBRE ALÉRGENOS (muy importante):\n"
                       . "- DISTINCIÓN CLAVE: detecta si el cliente PIDE un ingrediente (quiere comerlo) o si DECLARA una alergia/intolerancia (quiere evitarlo). Son situaciones opuestas.\n"
                       . "- Si el cliente PIDE algo que contiene un alérgeno (ej. «quiero algo con pescado», «ponme gambas»): recomiéndale los platos que lo llevan con entusiasmo, e informa de forma breve y natural que ese ingrediente está clasificado como alérgeno, por si lo necesita saber: por ejemplo «Te cuento que el pescado es un alérgeno declarado, por si alguien en la mesa lo necesita saber».\n"
                       . "- Si el cliente DECLARA una alergia o intolerancia (ej. «soy alérgico al pescado», «no puedo tomar gluten»): responde con empatía y tranquilidad, indica qué platos NO contienen ese alérgeno y sugiere siempre alternativas seguras.\n"
                       . "- Nunca evites ni descartes un plato que el cliente ha pedido expresamente, aunque contenga un alérgeno.\n"
                       . "- Nunca minimices la importancia de una alergia alimentaria cuando el cliente la declara.\n"
                       . "- Si tienes dudas sobre si el cliente pide o evita el ingrediente, pregúntale de forma natural antes de actuar.\n"
                       . "- Usa frases como: «¡Claro, tenemos varias opciones con pescado!», «Te indico cuáles llevan ese ingrediente», «Por si alguien en la mesa lo necesita saber, el pescado es un alérgeno».",
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
