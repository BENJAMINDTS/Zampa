<?php

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

// ─── Acceso al panel ──────────────────────────────────────────────────────────

it('redirects unauthenticated user away from bar panel', function () {
    $this->get(route('bar.index'))
        ->assertRedirect(route('login'));
});

it('shows bar panel to waiter role', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($waiter)
        ->get(route('bar.index'))
        ->assertOk()
        ->assertViewIs('bar.index');
});

it('shows bar panel to admin role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('bar.index'))
        ->assertOk()
        ->assertViewIs('bar.index');
});

it('returns 403 for kitchen role on bar panel', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);

    $this->actingAs($kitchen)
        ->get(route('bar.index'))
        ->assertForbidden();
});

// ─── Filtrado de ítems por destination ───────────────────────────────────────

it('shows only bar destination items in bar panel', function () {
    $waiter     = User::factory()->create(['role' => 'waiter']);
    $table      = Table::factory()->create(['user_id' => $waiter->id]);
    $barProduct = Product::factory()->create(['user_id' => $waiter->id]);

    $order   = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $barItem = OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $barProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $response = $this->actingAs($waiter)
        ->get(route('bar.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    $allItemIds = $orders->flatMap(fn($o) => $o->items->pluck('id'))->toArray();

    expect($allItemIds)->toContain($barItem->id);
});

it('does not show kitchen items in bar panel', function () {
    $waiter         = User::factory()->create(['role' => 'waiter']);
    $table          = Table::factory()->create(['user_id' => $waiter->id]);
    $kitchenProduct = Product::factory()->create(['user_id' => $waiter->id]);
    $barProduct     = Product::factory()->create(['user_id' => $waiter->id]);

    $order          = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $kitchenItem    = OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $kitchenProduct->id,
        'status'      => 'queued',
        'destination' => 'kitchen',
    ]);
    OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $barProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $response = $this->actingAs($waiter)
        ->get(route('bar.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    $allItemIds = $orders->flatMap(fn($o) => $o->items->pluck('id'))->toArray();

    expect($allItemIds)->not->toContain($kitchenItem->id);
});

it('does not show items from other restaurants', function () {
    $waiter       = User::factory()->create(['role' => 'waiter']);
    $otherWaiter  = User::factory()->create(['role' => 'waiter']);

    $ownTable   = Table::factory()->create(['user_id' => $waiter->id]);
    $otherTable = Table::factory()->create(['user_id' => $otherWaiter->id]);

    $ownProduct   = Product::factory()->create(['user_id' => $waiter->id]);
    $otherProduct = Product::factory()->create(['user_id' => $otherWaiter->id]);

    $ownOrder   = Order::factory()->create(['table_id' => $ownTable->id,   'status' => 'pending']);
    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id, 'status' => 'pending']);

    OrderItem::factory()->create([
        'order_id'    => $ownOrder->id,
        'product_id'  => $ownProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);
    OrderItem::factory()->create([
        'order_id'    => $otherOrder->id,
        'product_id'  => $otherProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $response = $this->actingAs($waiter)
        ->get(route('bar.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->pluck('id')->toArray())
        ->toContain($ownOrder->id)
        ->not->toContain($otherOrder->id);
});

// ─── Acción markItemReady ─────────────────────────────────────────────────────

it('waiter can mark a bar item as ready', function () {
    $waiter  = User::factory()->create(['role' => 'waiter']);
    $table   = Table::factory()->create(['user_id' => $waiter->id]);
    $product = Product::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $item  = OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $product->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $this->actingAs($waiter)
        ->patchJson(route('bar.items.update', $item))
        ->assertOk()
        ->assertJsonPath('item_id', $item->id);

    expect($item->fresh()->status)->toBe('ready');
});

it('returns 403 when waiter tries to update item from another restaurant', function () {
    $waiter       = User::factory()->create(['role' => 'waiter']);
    $otherWaiter  = User::factory()->create(['role' => 'waiter']);

    $otherTable   = Table::factory()->create(['user_id' => $otherWaiter->id]);
    $otherProduct = Product::factory()->create(['user_id' => $otherWaiter->id]);

    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id, 'status' => 'pending']);
    $otherItem  = OrderItem::factory()->create([
        'order_id'    => $otherOrder->id,
        'product_id'  => $otherProduct->id,
        'status'      => 'queued',
        'destination' => 'bar',
    ]);

    $this->actingAs($waiter)
        ->patchJson(route('bar.items.update', $otherItem))
        ->assertForbidden();

    expect($otherItem->fresh()->status)->toBe('queued');
});
