<?php

/**
 * @author SebastianBCF
 */

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user  = User::factory()->create(['role' => 'admin', 'is_waiter' => true]);
    $this->other = User::factory()->create(['role' => 'admin', 'is_waiter' => true]);
});

// ─── POST /api/v1/waiter-call/{hash} ─────────────────────────────────────────

it('sets waiter_called true on active order', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->for($table)->create(['status' => 'pending', 'waiter_called' => false]);

    $this->postJson("/api/v1/waiter-call/{$table->unique_hash}")
         ->assertOk()
         ->assertJson(['success' => true]);

    expect($order->fresh()->waiter_called)->toBeTrue();
});

it('works when order status is cooking', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->for($table)->create(['status' => 'cooking', 'waiter_called' => false]);

    $this->postJson("/api/v1/waiter-call/{$table->unique_hash}")
         ->assertOk();

    expect($order->fresh()->waiter_called)->toBeTrue();
});

it('returns 404 when table hash is invalid', function () {
    $this->postJson('/api/v1/waiter-call/nonexistent-hash')
         ->assertNotFound();
});

it('returns 404 when there is no active order', function () {
    $table = Table::factory()->for($this->user)->create();

    $this->postJson("/api/v1/waiter-call/{$table->unique_hash}")
         ->assertStatus(404)
         ->assertJson(['success' => false]);
});

it('returns 429 after exceeding throttle limit', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->for($table)->create(['status' => 'pending']);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson("/api/v1/waiter-call/{$table->unique_hash}");
    }

    $this->postJson("/api/v1/waiter-call/{$table->unique_hash}")
         ->assertStatus(429);
});

// ─── GET /notifications/waiter-calls ─────────────────────────────────────────

it('returns waiter_called orders for authenticated user', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->for($table)->create(['status' => 'pending', 'waiter_called' => true]);

    $this->actingAs($this->user)
         ->getJson(route('notifications.waiter.calls'))
         ->assertOk()
         ->assertJsonCount(1, 'orders');
});

it('does not return orders from other users', function () {
    $table = Table::factory()->for($this->other)->create();
    Order::factory()->for($table)->create(['status' => 'pending', 'waiter_called' => true]);

    $this->actingAs($this->user)
         ->getJson(route('notifications.waiter.calls'))
         ->assertOk()
         ->assertJsonCount(0, 'orders');
});

it('does not return orders where waiter_called is false', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->for($table)->create(['status' => 'pending', 'waiter_called' => false]);

    $this->actingAs($this->user)
         ->getJson(route('notifications.waiter.calls'))
         ->assertOk()
         ->assertJsonCount(0, 'orders');
});

it('redirects unauthenticated user from waiter-calls endpoint', function () {
    $this->getJson(route('notifications.waiter.calls'))
         ->assertUnauthorized();
});

// ─── PATCH /notifications/{order}/dismiss-waiter-call ────────────────────────

it('dismisses waiter_called on own order', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->for($table)->create(['waiter_called' => true]);

    $this->actingAs($this->user)
         ->patchJson(route('notifications.waiter.dismiss', $order))
         ->assertOk()
         ->assertJson(['success' => true]);

    expect($order->fresh()->waiter_called)->toBeFalse();
});

it('returns 403 when dismissing another users order', function () {
    $table = Table::factory()->for($this->other)->create();
    $order = Order::factory()->for($table)->create(['waiter_called' => true]);

    $this->actingAs($this->user)
         ->patchJson(route('notifications.waiter.dismiss', $order))
         ->assertForbidden();
});
