<?php

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        '*groq*' => Http::response([
            'choices' => [['message' => ['content' => 'Respuesta del asistente']]],
            'usage'   => ['total_tokens' => 50],
        ], 200),
    ]);

    $this->user         = User::factory()->create(['name' => 'Restaurante Test']);
    $this->table        = Table::factory()->for($this->user)->create();
    $this->conversation = Conversation::factory()->for($this->table)->create();
    $this->service      = app(ChatService::class);
});

// ─── buildContext: nombre del restaurante ─────────────────────────────────────

it('buildContext includes restaurant name in system prompt', function () {
    $this->service->handleMessage($this->conversation, 'Hola');

    Http::assertSent(function ($request) {
        $system = collect($request->data()['messages'])->firstWhere('role', 'system');

        return str_contains($system['content'], 'Restaurante Test');
    });
});

// ─── buildContext: productos activos ──────────────────────────────────────────

it('buildContext includes active products in system prompt', function () {
    $category = Category::factory()->for($this->user)->create();
    Product::factory()->for($this->user)->for($category)->create([
        'name'      => 'Paella Valenciana',
        'is_active' => true,
    ]);

    $this->service->handleMessage($this->conversation, 'Hola');

    Http::assertSent(function ($request) {
        $system = collect($request->data()['messages'])->firstWhere('role', 'system');

        return str_contains($system['content'], 'Paella Valenciana');
    });
});

it('buildContext does not include inactive products', function () {
    $category = Category::factory()->for($this->user)->create();
    Product::factory()->for($this->user)->for($category)->create([
        'name'      => 'Plato Oculto',
        'is_active' => false,
    ]);

    $this->service->handleMessage($this->conversation, 'Hola');

    Http::assertSent(function ($request) {
        $system = collect($request->data()['messages'])->firstWhere('role', 'system');

        return ! str_contains($system['content'], 'Plato Oculto');
    });
});

// ─── buildContext: alérgenos ──────────────────────────────────────────────────

it('buildContext includes allergens in system prompt', function () {
    $category  = Category::factory()->for($this->user)->create();
    $product   = Product::factory()->for($this->user)->for($category)->create(['is_active' => true]);
    $allergen  = Ingredient::factory()->for($this->user)->create([
        'name'        => 'Gluten',
        'is_allergen' => true,
    ]);
    $product->ingredients()->attach($allergen);

    $this->service->handleMessage($this->conversation, 'Hola');

    Http::assertSent(function ($request) {
        $system = collect($request->data()['messages'])->firstWhere('role', 'system');

        return str_contains($system['content'], 'Gluten');
    });
});

// ─── buildContext: historial de mensajes ──────────────────────────────────────

it('buildContext includes previous message history', function () {
    $this->conversation->messages()->create([
        'role'    => 'user',
        'content' => 'Mensaje anterior del historial',
    ]);

    $this->service->handleMessage($this->conversation, 'Nuevo mensaje');

    Http::assertSent(function ($request) {
        return collect($request->data()['messages'])
            ->pluck('content')
            ->contains('Mensaje anterior del historial');
    });
});

// ─── Límite de tokens (equivalente a shouldCloseConversation) ─────────────────

it('closes conversation and returns closed flag when tokens_used reaches MAX_TOKENS', function () {
    $this->conversation->update(['tokens_used' => 6000]);

    $result = $this->service->handleMessage($this->conversation, 'Mensaje que supera el límite');

    expect($result['closed'])->toBeTrue();
    expect($this->conversation->fresh()->status)->toBe('closed');
});

it('does not close conversation when tokens_used is below MAX_TOKENS', function () {
    $this->conversation->update(['tokens_used' => 5999]);

    $result = $this->service->handleMessage($this->conversation, 'Mensaje dentro del límite');

    expect($result['closed'])->toBeFalse();
    expect($this->conversation->fresh()->status)->toBe('active');
});
