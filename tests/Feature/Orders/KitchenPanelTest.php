<?php

use App\Models\Conversation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

it('redirects unauthenticated user away from kitchen panel', function () {
    $this->get(route('kitchen.index'))
        ->assertRedirect(route('login'));
});

it('shows kitchen panel to admin with kitchen role', function () {
    $admin = User::factory()->admin()->alsoKitchen()->create();

    $this->actingAs($admin)
        ->get(route('kitchen.index'))
        ->assertOk()
        ->assertViewIs('kitchen.index');
});

it('returns 403 for waiter role user', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($waiter)
        ->get(route('kitchen.index'))
        ->assertForbidden();
});

it('shows only pending and cooking orders of the authenticated restaurant', function () {
    $admin       = User::factory()->admin()->create();
    $kitchenUser = User::factory()->kitchen()->staffOf($admin)->create();
    $table       = Table::factory()->create(['user_id' => $admin->id]);
    $product     = Product::factory()->create(['user_id' => $admin->id]);

    $pending = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $cooking = Order::factory()->create(['table_id' => $table->id, 'status' => 'cooking']);
    $served  = Order::factory()->create(['table_id' => $table->id, 'status' => 'served']);

    OrderItem::factory()->create(['order_id' => $pending->id, 'product_id' => $product->id, 'status' => 'queued']);
    OrderItem::factory()->create(['order_id' => $cooking->id, 'product_id' => $product->id, 'status' => 'queued']);

    $response = $this->actingAs($kitchenUser)
        ->get(route('kitchen.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->pluck('id')->toArray())
        ->toContain($pending->id)
        ->toContain($cooking->id)
        ->not->toContain($served->id);
});

it('does not show orders from other restaurants', function () {
    $admin       = User::factory()->admin()->create();
    $kitchenUser = User::factory()->kitchen()->staffOf($admin)->create();
    $otherAdmin  = User::factory()->admin()->create();

    $ownTable   = Table::factory()->create(['user_id' => $admin->id]);
    $otherTable = Table::factory()->create(['user_id' => $otherAdmin->id]);

    $ownProduct   = Product::factory()->create(['user_id' => $admin->id]);
    $otherProduct = Product::factory()->create(['user_id' => $otherAdmin->id]);

    $ownOrder   = Order::factory()->create(['table_id' => $ownTable->id,   'status' => 'pending']);
    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id, 'status' => 'pending']);

    OrderItem::factory()->create(['order_id' => $ownOrder->id,   'product_id' => $ownProduct->id,   'status' => 'queued']);
    OrderItem::factory()->create(['order_id' => $otherOrder->id, 'product_id' => $otherProduct->id, 'status' => 'queued']);

    $response = $this->actingAs($kitchenUser)
        ->get(route('kitchen.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->pluck('id')->toArray())
        ->toContain($ownOrder->id)
        ->not->toContain($otherOrder->id);
});

it('does not show closed orders', function () {
    $admin       = User::factory()->admin()->create();
    $kitchenUser = User::factory()->kitchen()->staffOf($admin)->create();
    $table       = Table::factory()->create(['user_id' => $admin->id]);
    $product     = Product::factory()->create(['user_id' => $admin->id]);

    $closedOrder = Order::factory()->create(['table_id' => $table->id, 'status' => 'closed']);

    OrderItem::factory()->create(['order_id' => $closedOrder->id, 'product_id' => $product->id, 'status' => 'queued']);

    $response = $this->actingAs($kitchenUser)
        ->get(route('kitchen.index'))
        ->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->pluck('id')->toArray())
        ->not->toContain($closedOrder->id);
});

it('kitchen staff can change order item status to ready', function () {
    $admin       = User::factory()->admin()->create();
    $kitchenUser = User::factory()->kitchen()->staffOf($admin)->create();
    $table       = Table::factory()->create(['user_id' => $admin->id]);
    $product     = Product::factory()->create(['user_id' => $admin->id]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $item  = OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'status'     => 'queued',
    ]);

    $this->actingAs($kitchenUser)
        ->postJson(route('kitchen.item.ready', $item))
        ->assertOk()
        ->assertJsonPath('item_id', $item->id);

    expect($item->fresh()->status)->toBe('ready');
});

it('all items ready triggers order status change to ready', function () {
    $admin       = User::factory()->admin()->create();
    $kitchenUser = User::factory()->kitchen()->staffOf($admin)->create();
    $table       = Table::factory()->create(['user_id' => $admin->id]);
    $product     = Product::factory()->create(['user_id' => $admin->id]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $item1 = OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'status'     => 'queued',
    ]);
    $item2 = OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'status'     => 'ready',
    ]);

    $this->actingAs($kitchenUser)
        ->postJson(route('kitchen.item.ready', $item1))
        ->assertOk()
        ->assertJsonPath('all_ready', true)
        ->assertJsonPath('order_status', 'ready');

    expect($order->fresh()->status)->toBe('ready');
});

it('conversation closes when order status changes to closed', function () {
    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $order        = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $conversation = Conversation::factory()->create(['table_id' => $table->id, 'status' => 'active']);

    $order->update(['status' => 'closed']);

    expect($conversation->fresh()->status)->toBe('closed');
});
