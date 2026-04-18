<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Encapsula la comunicación HTTP con la API de OpenAI.
 *
 * Solo responsable de hacer la llamada y devolver el JSON.
 * El manejo de contexto, tokens y persistencia es responsabilidad de ChatService.
 *
 * @author AyrtonAlania
 */
class OpenAIService
{
    /**
     * Envía un array de mensajes a la API de OpenAI y devuelve la respuesta completa.
     *
     * @param  array  $messages  Array de mensajes con claves 'role' y 'content'
     * @return array             Respuesta JSON decodificada de la API
     *
     * @throws \Exception Si la API responde con error HTTP
     */
    public function sendMessage(array $messages): array
    {
        $response = Http::withToken(config('services.openai.key'))
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4o-mini',
                'messages'    => $messages,
                'temperature' => 0.7,
                'max_tokens'  => 500,
            ]);

        if ($response->failed()) {
            throw new \Exception('Error en API de IA: ' . $response->status());
        }

        return $response->json();
    }
}
