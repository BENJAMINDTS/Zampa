<?php

use App\Models\Conversation;
use App\Models\Table;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    Cache::flush();
    $this->setupTenants();
    $this->table = Table::factory()->for($this->user)->create();

    Http::fake([
        '*groq*' => Http::response([
            'choices' => [['message' => ['content' => 'Respuesta mock del chatbot']]],
            'usage'   => ['total_tokens' => 50],
        ], 200),
    ]);
});

// ─── Start: crear conversación ────────────────────────────────────────────────

it('creates a new conversation on start', function () {
    $this->postJson(route('api.chat.start', $this->table->unique_hash))
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('conversations', [
        'table_id' => $this->table->id,
        'status'   => 'active',
    ]);
});

it('returns 404 when starting chat with invalid table hash', function () {
    $this->postJson(route('api.chat.start', 'hash-invalido-xyz'))
        ->assertNotFound();
});

it('creates a new conversation when the previous one is closed', function () {
    Conversation::factory()->for($this->table)->create(['status' => 'closed']);

    $this->postJson(route('api.chat.start', $this->table->unique_hash))
        ->assertCreated();

    expect(Conversation::where('table_id', $this->table->id)->count())->toBe(2);
    expect(Conversation::where('table_id', $this->table->id)->where('status', 'active')->count())->toBe(1);
});

// ─── Send: enviar mensaje ─────────────────────────────────────────────────────

it('returns mocked assistant reply on send', function () {
    $conversation = Conversation::factory()->for($this->table)->create();

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => 'Hola, ¿qué tenéis de menú?',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.reply', 'Respuesta mock del chatbot');
});

it('persists user and assistant messages after send', function () {
    $conversation = Conversation::factory()->for($this->table)->create();

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => '¿Tenéis opciones sin gluten?',
    ])->assertOk();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role'            => 'user',
        'content'         => '¿Tenéis opciones sin gluten?',
    ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role'            => 'assistant',
        'content'         => 'Respuesta mock del chatbot',
    ]);
});

it('reuses active conversation across multiple messages', function () {
    $conversation = Conversation::factory()->for($this->table)->create();

    $this->postJson(route('api.chat.send', $conversation), ['message' => 'Primer mensaje'])->assertOk();
    $this->postJson(route('api.chat.send', $conversation), ['message' => 'Segundo mensaje'])->assertOk();

    // 2 mensajes user + 2 respuestas assistant
    expect($conversation->messages()->count())->toBe(4);
});

it('includes previous message history in context sent to Groq', function () {
    $conversation = Conversation::factory()->for($this->table)->create();

    $conversation->messages()->create([
        'role'    => 'user',
        'content' => 'Mensaje anterior en el historial',
    ]);

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => 'Nuevo mensaje del cliente',
    ])->assertOk();

    Http::assertSent(function ($request) {
        $messages = collect($request->data()['messages'])->pluck('content')->toArray();

        return in_array('Mensaje anterior en el historial', $messages)
            && in_array('Nuevo mensaje del cliente', $messages);
    });
});

it('returns 422 when sending to a closed conversation', function () {
    $conversation = Conversation::factory()->for($this->table)->create(['status' => 'closed']);

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => 'Intento de mensaje',
    ])->assertUnprocessable();
});

it('closes conversation and returns closed flag when tokens_used reaches MAX_TOKENS', function () {
    $conversation = Conversation::factory()->for($this->table)->create([
        'tokens_used' => 6000,
    ]);

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => 'Este mensaje supera el límite de tokens',
    ])
        ->assertOk()
        ->assertJsonPath('data.closed', true);

    expect($conversation->fresh()->status)->toBe('closed');
});

// ─── Llamadas reales a Groq — nunca sin mock ──────────────────────────────────

it('groq api is only called once per send request', function () {
    $conversation = Conversation::factory()->for($this->table)->create();

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => 'Test de conteo de llamadas',
    ])->assertOk();

    Http::assertSentCount(1);
});

it('handles groq api 500 error gracefully without crashing', function () {
    $this->mock(OpenAIService::class)
         ->shouldReceive('sendMessage')
         ->andThrow(new \Exception('Error en API de IA: 500'));

    $conversation = Conversation::factory()->for($this->table)->create();

    $this->postJson(route('api.chat.send', $conversation), [
        'message' => 'Este mensaje fallará en la API',
    ])
        ->assertOk()
        ->assertJsonPath('success', false);
});

// ─── Close: cerrar conversación ───────────────────────────────────────────────

it('closes an active conversation via close endpoint', function () {
    $conversation = Conversation::factory()->for($this->table)->create(['status' => 'active']);

    $this->deleteJson(route('api.chat.close', $conversation))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($conversation->fresh()->status)->toBe('closed');
});

// ─── Rate limiting ────────────────────────────────────────────────────────────

it('returns 429 after exceeding rate limit on chat send', function () {
    $conversation = Conversation::factory()->for($this->table)->create();

    for ($i = 0; $i < 60; $i++) {
        $this->postJson(route('api.chat.send', $conversation), ['message' => 'mensaje'])->assertOk();
    }

    $this->postJson(route('api.chat.send', $conversation), ['message' => 'mensaje extra'])
        ->assertTooManyRequests();
});
