<?php

use App\Models\Conversation;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Devuelve la estructura de respuesta mockeada de Groq/OpenAI.
 */
function groqOkResponse(string $content = 'Respuesta mock del asistente', int $tokens = 100): array
{
    return [
        'choices' => [['message' => ['content' => $content]]],
        'usage'   => ['total_tokens' => $tokens],
    ];
}

beforeEach(function () {
    $this->user  = User::factory()->create();
    $this->table = Table::factory()->create(['user_id' => $this->user->id]);
});

it('crea una nueva conversación en el primer mensaje', function () {
    $response = $this->postJson("/api/v1/chat/{$this->table->unique_hash}/start");

    $response->assertStatus(201)
             ->assertJsonStructure(['success', 'data' => ['conversation_id'], 'message']);

    $this->assertDatabaseHas('conversations', [
        'table_id' => $this->table->id,
        'status'   => 'active',
    ]);
});

it('reutiliza la conversación activa en mensajes siguientes', function () {
    Http::fake(['api.groq.com/*' => Http::response(groqOkResponse(), 200)]);

    $conversation = Conversation::factory()->create(['table_id' => $this->table->id]);

    $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => 'Hola']);
    $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => '¿Qué hay de comer?']);

    expect(Conversation::where('table_id', $this->table->id)->count())->toBe(1);
    expect($conversation->fresh()->status)->toBe('active');
});

it('devuelve la respuesta del asistente mockeada', function () {
    Http::fake(['api.groq.com/*' => Http::response(groqOkResponse('Hola, soy tu asistente.'), 200)]);

    $conversation = Conversation::factory()->create(['table_id' => $this->table->id]);

    $response = $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => 'Hola']);

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'data'    => ['reply' => 'Hola, soy tu asistente.', 'closed' => false],
             ]);
});

it('devuelve 404 con hash de mesa inválido', function () {
    $response = $this->postJson('/api/v1/chat/hash-que-no-existe/start');

    $response->assertStatus(404);
});

it('incluye el historial de mensajes previos en el contexto enviado a Groq', function () {
    Http::fake(['api.groq.com/*' => Http::response(groqOkResponse(), 200)]);

    $conversation = Conversation::factory()->create(['table_id' => $this->table->id]);

    $conversation->messages()->create(['role' => 'user',      'content' => 'Pregunta anterior del cliente']);
    $conversation->messages()->create(['role' => 'assistant', 'content' => 'Respuesta anterior del asistente']);

    $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => 'Nuevo mensaje']);

    Http::assertSent(function ($request) {
        $messages = $request->data()['messages'] ?? [];
        $contents = collect($messages)->pluck('content')->all();

        return in_array('Pregunta anterior del cliente', $contents)
            && in_array('Respuesta anterior del asistente', $contents);
    });
});

it('cierra la conversación al superar MAX_TOKENS 6000', function () {
    Http::fake(['api.groq.com/*' => Http::response(groqOkResponse(), 200)]);

    $conversation = Conversation::factory()->create([
        'table_id'    => $this->table->id,
        'tokens_used' => 6000,
    ]);

    $response = $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => 'Mensaje']);

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'data'    => ['closed' => true],
             ]);

    expect($conversation->fresh()->status)->toBe('closed');

    // Groq no debe haberse llamado cuando se supera el límite
    Http::assertSentCount(0);
});

it('crea nueva conversación cuando la anterior está cerrada', function () {
    Http::fake(['api.groq.com/*' => Http::response(groqOkResponse(), 200)]);

    $closed = Conversation::factory()->create([
        'table_id' => $this->table->id,
        'status'   => 'closed',
    ]);

    // Enviar mensaje a conversación cerrada devuelve 422
    $this->postJson("/api/v1/chat/{$closed->id}/message", ['message' => 'Hola'])
         ->assertStatus(422)
         ->assertJsonFragment(['message' => 'Esta conversación ya está cerrada. Inicia una nueva.']);

    // Crear nueva conversación desde el endpoint start funciona
    $this->postJson("/api/v1/chat/{$this->table->unique_hash}/start")
         ->assertStatus(201);

    expect(Conversation::where('table_id', $this->table->id)->count())->toBe(2);
});

it('Groq nunca es llamado sin mock — Http::assertSentCount verifica el interceptor', function () {
    Http::fake(['api.groq.com/*' => Http::response(groqOkResponse(), 200)]);

    $conversation = Conversation::factory()->create(['table_id' => $this->table->id]);

    $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => 'Hola']);

    // Exactamente 1 llamada interceptada por el mock — nunca llegó a la red real
    Http::assertSentCount(1);
});

it('el error 500 de Groq devuelve un error manejado sin explotar la app', function () {
    Http::fake(['api.groq.com/*' => Http::response([], 500)]);

    $conversation = Conversation::factory()->create(['table_id' => $this->table->id]);

    $response = $this->postJson("/api/v1/chat/{$conversation->id}/message", ['message' => 'Hola']);

    $response->assertStatus(200)
             ->assertJson([
                 'success' => false,
                 'message' => 'Error en el asistente.',
             ]);
});

it('rate limiting activo en la ruta de chat', function () {
    Cache::flush();

    for ($i = 0; $i < 60; $i++) {
        $this->postJson("/api/v1/chat/{$this->table->unique_hash}/start");
    }

    $response = $this->postJson("/api/v1/chat/{$this->table->unique_hash}/start");
    $response->assertStatus(429);
});
