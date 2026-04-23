<?php

use App\Services\OpenAIService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = app(OpenAIService::class);
});

// ─── Happy path ───────────────────────────────────────────────────────────────

it('sendMessage returns full API response array', function () {
    Http::fake([
        '*groq*' => Http::response([
            'choices' => [['message' => ['content' => 'Hola, ¿en qué te puedo ayudar?']]],
            'usage'   => ['total_tokens' => 42],
        ], 200),
    ]);

    $response = $this->service->sendMessage([
        ['role' => 'user', 'content' => 'Hola'],
    ]);

    expect($response['choices'][0]['message']['content'])->toBe('Hola, ¿en qué te puedo ayudar?');
    expect($response['usage']['total_tokens'])->toBe(42);
});

// ─── Errores de la API ────────────────────────────────────────────────────────

it('sendMessage throws exception when API responds with 500', function () {
    Http::fake(['*groq*' => Http::response([], 500)]);

    expect(fn () => $this->service->sendMessage([
        ['role' => 'user', 'content' => 'Hola'],
    ]))->toThrow(\Exception::class, 'Error en API de IA: 500');
});

it('sendMessage throws exception when API responds with 401', function () {
    Http::fake(['*groq*' => Http::response(['error' => 'Unauthorized'], 401)]);

    expect(fn () => $this->service->sendMessage([
        ['role' => 'user', 'content' => 'Hola'],
    ]))->toThrow(\Exception::class);
});

it('sendMessage throws exception when API responds with 429', function () {
    Http::fake(['*groq*' => Http::response(['error' => 'Rate limit exceeded'], 429)]);

    expect(fn () => $this->service->sendMessage([
        ['role' => 'user', 'content' => 'Hola'],
    ]))->toThrow(\Exception::class);
});

// ─── Control de llamadas reales ───────────────────────────────────────────────

it('only sends one HTTP request per sendMessage call', function () {
    Http::fake([
        '*groq*' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
            'usage'   => ['total_tokens' => 10],
        ], 200),
    ]);

    $this->service->sendMessage([['role' => 'user', 'content' => 'test']]);

    Http::assertSentCount(1);
});

// ─── Payload no contiene datos sensibles ─────────────────────────────────────

it('does not include sensitive data in request payload', function () {
    Http::fake([
        '*groq*' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
            'usage'   => ['total_tokens' => 5],
        ], 200),
    ]);

    $this->service->sendMessage([
        ['role' => 'user', 'content' => 'Quiero ver el menú'],
    ]);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return ! array_key_exists('password', $body)
            && ! array_key_exists('token', $body)
            && ! array_key_exists('secret', $body);
    });
});
