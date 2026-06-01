<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);
uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── Enrutado al crear el pedido ─────────────────────────────────────────────

it('assigns kitchen destination to items of kitchen category', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $product  = Product::factory()->for($this->user)->create(['category_id' => $category->id]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertCreated();

    $this->assertDatabaseHas('order_items', [
        'product_id'  => $product->id,
        'destination' => 'kitchen',
    ]);
});

it('assigns bar destination to items of bar category', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'bar']);
    $product  = Product::factory()->for($this->user)->create(['category_id' => $category->id]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertCreated();

    $this->assertDatabaseHas('order_items', [
        'product_id'  => $product->id,
        'destination' => 'bar',
    ]);
});

it('assigns correct destination when order has mixed kitchen and bar items', function () {
    $table           = Table::factory()->for($this->user)->create();
    $kitchenCategory = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $barCategory     = Category::factory()->for($this->user)->create(['destination' => 'bar']);
    $kitchenProduct  = Product::factory()->for($this->user)->create(['category_id' => $kitchenCategory->id]);
    $barProduct      = Product::factory()->for($this->user)->create(['category_id' => $barCategory->id]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $kitchenProduct->id, 'quantity' => 1],
            ['product_id' => $barProduct->id,     'quantity' => 2],
        ],
    ])->assertCreated();

    $this->assertDatabaseHas('order_items', [
        'product_id'  => $kitchenProduct->id,
        'destination' => 'kitchen',
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id'  => $barProduct->id,
        'destination' => 'bar',
    ]);
});

// ─── Panel de cocina — filtrado por destination ───────────────────────────────

it('kitchen panel only shows orders with kitchen destination items', function () {
    $kitchenUser     = User::factory()->create(['role' => 'kitchen']);
    $table           = Table::factory()->create(['user_id' => $kitchenUser->id]);
    $kitchenCategory = Category::factory()->create(['user_id' => $kitchenUser->id, 'destination' => 'kitchen']);
    $kitchenProduct  = Product::factory()->create(['user_id' => $kitchenUser->id, 'category_id' => $kitchenCategory->id]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $kitchenProduct->id,
        'status'      => 'queued',
        'destination' => 'kitchen',
    ]);

    $response = $this->actingAs($kitchenUser)
        ->get(route('kitchen.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->pluck('id')->toArray())->toContain($order->id);
});

it('kitchen panel does not show orders with only bar destination items', function () {
    $kitchenUser = User::factory()->create(['role' => 'kitchen']);
    $table       = Table::factory()->create(['user_id' => $kitchenUser->id]);
    $barCategory = Category::factory()->create(['user_id' => $kitchenUser->id, 'destination' => 'bar']);
    $barProduct  = Product::factory()->create(['user_id' => $kitchenUser->id, 'category_id' => $barCategory->id]);

    $barOrder = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create([
        'order_id'    => $barOrder->id,
        'product_id'  => $barProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $response = $this->actingAs($kitchenUser)
        ->get(route('kitchen.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->pluck('id')->toArray())->not->toContain($barOrder->id);
});

it('bar items do not appear among kitchen panel items', function () {
    $kitchenUser     = User::factory()->create(['role' => 'kitchen']);
    $table           = Table::factory()->create(['user_id' => $kitchenUser->id]);
    $kitchenCategory = Category::factory()->create(['user_id' => $kitchenUser->id, 'destination' => 'kitchen']);
    $barCategory     = Category::factory()->create(['user_id' => $kitchenUser->id, 'destination' => 'bar']);
    $kitchenProduct  = Product::factory()->create(['user_id' => $kitchenUser->id, 'category_id' => $kitchenCategory->id]);
    $barProduct      = Product::factory()->create(['user_id' => $kitchenUser->id, 'category_id' => $barCategory->id]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $kitchenProduct->id,
        'status'      => 'queued',
        'destination' => 'kitchen',
    ]);
    $barItem = OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $barProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $response = $this->actingAs($kitchenUser)
        ->get(route('kitchen.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    $allItemIds = $orders->flatMap(fn($o) => $o->items->pluck('id'))->toArray();

    expect($allItemIds)->not->toContain($barItem->id);
});

// ─── Guard: cocina no puede marcar ítems de barra ─────────────────────────────

it('kitchen panel returns 403 when trying to mark a bar item as ready', function () {
    $kitchenUser = User::factory()->create(['role' => 'kitchen']);
    $table       = Table::factory()->create(['user_id' => $kitchenUser->id]);
    $barProduct  = Product::factory()->create(['user_id' => $kitchenUser->id]);

    $order   = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $barItem = OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $barProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $this->actingAs($kitchenUser)
        ->postJson(route('kitchen.item.ready', $barItem))
        ->assertForbidden();

    expect($barItem->fresh()->status)->toBe('queued');
});

it('mixed order does not become ready while bar items are still queued', function () {
    $kitchenUser     = User::factory()->create(['role' => 'kitchen']);
    $table           = Table::factory()->create(['user_id' => $kitchenUser->id]);
    $kitchenProduct  = Product::factory()->create(['user_id' => $kitchenUser->id]);

    $order       = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $kitchenItem = OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $kitchenProduct->id,
        'status'      => 'queued',
        'destination' => 'kitchen',
    ]);
    OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $kitchenProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $this->actingAs($kitchenUser)
        ->postJson(route('kitchen.item.ready', $kitchenItem))
        ->assertOk()
        ->assertJsonPath('all_ready', false);

    expect($order->fresh()->status)->not->toBe('ready');
});
