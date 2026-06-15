<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Lógica principal del chatbot IA.
 *
 * Responsable de:
 * - Cargar exactamente los mismos productos que muestra la carta digital (caché compartida)
 * - Buscar productos relevantes en la BD según el mensaje del usuario
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

    private const STOP_WORDS = [
        'que', 'con', 'del', 'los', 'las', 'una', 'uno', 'unos', 'unas',
        'algo', 'algún', 'alguna', 'para', 'por', 'favor', 'quiero', 'quisiera',
        'dame', 'ponme', 'tenéis', 'tienen', 'hay', 'tienes', 'tiene', 'tener',
        'pedir', 'pido', 'pedimos', 'traer', 'traeme', 'trae', 'tráeme',
        'puede', 'puedo', 'podría', 'gustaría', 'sería', 'comer', 'tomar',
        'ver', 'mostrar', 'recomendar', 'recomienda', 'ayuda', 'hola', 'buenas',
    ];

    /**
     * @param  OpenAIService  $openAI
     */
    public function __construct(protected OpenAIService $openAI) {}

    /**
     * Procesa un mensaje del usuario y devuelve la respuesta del asistente.
     *
     * Carga las categorías de la carta digital (caché compartida con MenuController)
     * antes de llamar a la IA para garantizar que el contexto y las tarjetas son
     * exactamente los mismos productos que ve el cliente en la carta.
     *
     * @param  Conversation  $conversation
     * @param  string        $userMessage
     * @return array{error: bool, message: string, cards: array, closed: bool}
     */
    public function handleMessage(Conversation $conversation, string $userMessage): array
    {
        if ($conversation->tokens_used >= self::MAX_TOKENS) {
            $conversation->update(['status' => 'closed']);

            return [
                'error'   => false,
                'message' => 'Hemos llegado al límite de esta conversación. '
                           . 'Confirma tu pedido o inicia una nueva conversación.',
                'cards'   => [],
                'closed'  => true,
            ];
        }

        // Carga el menú exactamente igual que la carta digital (misma caché)
        $categories = $this->getMenuCategories($conversation->table->user_id);

        // Busca productos relevantes del mensaje — solo del pool de la carta
        $cards = $this->searchProducts($userMessage, $categories);

        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $userMessage,
        ]);

        $messages = $this->buildContext($conversation, $categories, $cards);

        try {
            $response = $this->openAI->sendMessage($messages);
        } catch (\Exception) {
            return [
                'error'   => true,
                'message' => 'El asistente no está disponible en este momento. '
                           . 'Puedes usar el menú tradicional.',
                'cards'   => [],
                'closed'  => false,
            ];
        }

        $reply      = $response['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $response['usage']['total_tokens'] ?? 0;

        $conversation->messages()->create([
            'role'        => 'assistant',
            'content'     => $reply,
            'tokens_used' => $tokensUsed,
        ]);

        $conversation->increment('tokens_used', $tokensUsed);

        return [
            'error'   => false,
            'message' => $reply,
            'cards'   => $cards,
            'closed'  => false,
        ];
    }

    /**
     * Devuelve las categorías con productos activos y disponibles del restaurante,
     * usando la misma clave de caché que MenuController para garantizar coherencia.
     * Solo incluye productos que pertenecen a una categoría visible en la carta.
     *
     * @param  int  $userId
     * @return Collection
     */
    private function getMenuCategories(int $userId): Collection
    {
        return Cache::remember("menu:{$userId}", 300, function () use ($userId) {
            return Category::where('user_id', $userId)
                ->with(['products' => function ($q) {
                    $q->where('is_active', true)
                      ->where('is_available', true)
                      ->with('ingredients')
                      ->orderBy('sort_order')
                      ->orderBy('name');
                }])
                ->orderBy('name')
                ->get()
                ->filter(fn($c) => $c->products->isNotEmpty());
        });
    }

    /**
     * Busca en el pool de productos de la carta los que coincidan con el mensaje.
     * Opera exclusivamente sobre la misma colección que muestra la carta digital
     * para que nunca aparezcan tarjetas de platos no visibles al cliente.
     *
     * @param  string      $query
     * @param  Collection  $categories  Categorías ya cargadas de la carta
     * @return array
     */
    private function searchProducts(string $query, Collection $categories): array
    {
        $lower = mb_strtolower($query);

        $isAllergyDeclaration = (bool) preg_match(
            '/\b(alergi[ao]|intoleranci[ao]|no\s+puedo\s+(comer|tomar)|no\s+como|no\s+tomo|soy\s+cel[ií]ac|me\s+sient[ae]\s+mal)\b/u',
            $lower
        );

        $terms = array_values(array_filter(
            preg_split('/[\s,.\-]+/u', $lower),
            fn($t) => mb_strlen($t) >= 3 && !in_array($t, self::STOP_WORDS, true)
        ));

        if (empty($terms)) {
            return [];
        }

        // Pool de productos exactamente igual al de la carta digital
        $allProducts = $categories->flatMap(fn($c) => $c->products->map(function (Product $p) use ($c): Product {
            $p->_categoryName = $c->name;
            return $p;
        }));

        if ($isAllergyDeclaration) {
            // Productos seguros: ningún ingrediente coincide con el alérgeno declarado
            $safe = $allProducts->filter(function (Product $p) use ($terms): bool {
                foreach ($p->ingredients as $ingredient) {
                    foreach ($terms as $term) {
                        if (str_contains(mb_strtolower($ingredient->name), $term)) {
                            return false;
                        }
                        foreach ($ingredient->allergen_types ?? [] as $slug) {
                            if (str_contains(mb_strtolower((string) $slug), $term)) {
                                return false;
                            }
                        }
                    }
                }
                return true;
            })->take(5);

            return $this->formatProducts($safe);
        }

        // Búsqueda positiva con lógica AND: el plato debe contener TODOS los términos.
        // Fallback OR si ningún plato cumple la combinación completa.
        $filterFn = function (Product $p, array $requiredTerms): bool {
            foreach ($requiredTerms as $term) {
                $found = false;
                if (str_contains(mb_strtolower($p->name), $term)) {
                    $found = true;
                } elseif ($p->description && str_contains(mb_strtolower($p->description), $term)) {
                    $found = true;
                } else {
                    foreach ($p->ingredients as $ingredient) {
                        if (str_contains(mb_strtolower($ingredient->name), $term)) {
                            $found = true;
                            break;
                        }
                        foreach ($ingredient->allergen_types ?? [] as $slug) {
                            if (str_contains(mb_strtolower((string) $slug), $term)) {
                                $found = true;
                                break 2;
                            }
                        }
                    }
                }
                if (!$found) {
                    return false;
                }
            }
            return true;
        };

        $matched = $allProducts->filter(fn($p) => $filterFn($p, $terms))->take(5);

        if ($matched->isEmpty() && count($terms) > 1) {
            $matched = $allProducts->filter(fn($p) => $filterFn($p, [reset($terms)]))->take(5);
        }

        return $this->formatProducts($matched);
    }

    /**
     * @param  Collection  $products
     * @return array
     */
    private function formatProducts(Collection $products): array
    {
        return $products
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
     * El menú que recibe la IA es exactamente el mismo que muestra la carta digital
     * (misma caché). Si el backend encontró productos relevantes, los destaca como
     * contexto para que la IA genere una respuesta coherente con las tarjetas.
     *
     * @param  Conversation  $conversation
     * @param  Collection    $categories       Categorías ya cargadas de la carta
     * @param  array         $relevantProducts Productos encontrados por el backend
     * @return array
     */
    private function buildContext(Conversation $conversation, Collection $categories, array $relevantProducts = []): array
    {
        $user           = $conversation->table->user;
        $restaurantName = $user->business_name ?: $user->name;
        $categoryNames  = $categories->pluck('name')->join(', ');

        $menuLines = $categories->flatMap(fn($c) => $c->products->map(function (Product $p) use ($c): string {
            $ingredients = $p->ingredients->map(fn($i) => $i->name)->join(', ');
            $allergens   = $p->ingredients
                ->where('is_allergen', true)
                ->map(fn($i) => $i->name)
                ->join(', ');

            $line = "[{$c->name}] {$p->name} (" . number_format($p->price, 2) . '€)';

            if ($ingredients !== '') {
                $line .= ": {$ingredients}";
            }
            if ($allergens !== '') {
                $line .= " [ALÉRGENOS: {$allergens}]";
            }

            return $line;
        }))->join("\n");

        $relevantHint = '';
        if (!empty($relevantProducts)) {
            $count        = count($relevantProducts);
            $relevantHint = "\n\nATENCIÓN — EL SISTEMA YA MUESTRA {$count} TARJETA(S) VISUAL(ES) AL CLIENTE.\n"
                          . "NORMA ABSOLUTA: NO menciones nombres de platos, precios ni ingredientes en tu respuesta.\n"
                          . "El cliente ya los ve en las tarjetas. Tu respuesta debe ser:\n"
                          . "1. Una frase de introducción cálida (ej: '¡Aquí tienes las opciones disponibles! 😊')\n"
                          . "2. Opcionalmente una frase de cierre breve (ej: '¿Te animas con alguno?')\n"
                          . "Total: máximo 2 frases. Sin nombres de platos. Sin precios. Sin listas.";
        }

        $systemPrompt = [
            'role'    => 'system',
            'content' => "Eres Zampi, el asistente virtual del restaurante \"{$restaurantName}\". "
                       . "Tu tono es siempre cálido, amable y cercano, como si fueras un camarero de confianza. "
                       . "Respondes en español de España, de forma concisa y natural.\n\n"
                       . "CATEGORÍAS DISPONIBLES EN LA CARTA: {$categoryNames}\n\n"
                       . "PLATOS DE LA CARTA (formato: [Categoría] Nombre (precio): ingredientes [ALÉRGENOS: ...]):\n"
                       . $menuLines
                       . $relevantHint . "\n\n"
                       . "NORMAS GENERALES (CRÍTICO):\n"
                       . "- El menú de arriba es la ÚNICA fuente de verdad. Solo existen los platos listados.\n"
                       . "- NUNCA menciones, sugieras ni inventes platos, precios ni ingredientes que no aparezcan en la lista.\n"
                       . "- NUNCA sugieras modificar recetas ni ingredientes de los platos.\n"
                       . "- Si el cliente pide algo que no existe, díselo y recomiéndale alternativas reales del menú.\n\n"
                       . "NORMA CRÍTICA — LISTADO DE CARTA:\n"
                       . "- NUNCA copies ni listes el menú completo en tu respuesta de texto, aunque el cliente te lo pida explícitamente.\n"
                       . "- Si el cliente pide ver la carta o el menú, dile con un mensaje corto que puede explorar\n"
                       . "  las categorías ({$categoryNames}) usando los botones del chat. No listes los platos.\n\n"
                       . "NORMAS DE FORMATO:\n"
                       . "- Usa emojis para transmitir cercanía: 😊 🍽️ 🎉 👌 😋 🔥 ✨ 🙌 🤔 ⚠️ 💬.\n"
                       . "- CUANDO EL SISTEMA MUESTRA TARJETAS: escribe SOLO 1-2 frases (introducción + cierre). "
                       . "PROHIBIDO mencionar nombres de platos, precios o ingredientes en el texto. Las tarjetas ya lo muestran.\n"
                       . "- CUANDO NO HAY TARJETAS: puedes nombrar 1-2 platos del menú en el texto, nunca la lista completa.\n"
                       . "- NO uses asteriscos, guiones de lista, markdown ni formato HTML.\n"
                       . "- NO incluyas marcadores, códigos ni IDs de ningún tipo en tu respuesta.\n\n"
                       . "VARIEDAD EN EL LENGUAJE:\n"
                       . "- NUNCA repitas la misma frase de apertura o cierre en mensajes seguidos.\n"
                       . "- Evita frases genéricas como «¡Genial elección!», «¡Claro que sí!», «¡Por supuesto!».\n"
                       . "- Varía el vocabulario y el tono para sonar como una persona real.\n\n"
                       . "NORMAS SOBRE ALÉRGENOS:\n"
                       . "- Distingue si el cliente PIDE un ingrediente (quiere comerlo) o DECLARA una alergia (quiere evitarlo).\n"
                       . "- Si pide algo con un alérgeno: recomiéndale los platos que lo llevan con entusiasmo.\n"
                       . "- Si declara una alergia: responde con empatía e indica qué platos NO contienen ese alérgeno.\n"
                       . "- Nunca minimices la importancia de una alergia declarada.\n\n"
                       . "BÚSQUEDA DE ALTERNATIVAS:\n"
                       . "- Si lo pedido no existe, ofrece siempre al menos 2 alternativas del menú con justificación breve.\n"
                       . "- Usa el menú como única fuente. No inventes.",
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
