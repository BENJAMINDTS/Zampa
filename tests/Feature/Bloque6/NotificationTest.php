<?php

/**
 * @author AyrtonAlania
 * @author SebastianBCF
 * @author BenjaminDTS
 */

use App\Models\Order;
use App\Models\Table;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

// ─── Endpoint GET /notifications/ready ───────────────────────────────────────

it('notification_ready false means order is not returned by ready endpoint', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => false,
    ]);

    $this->actingAs($waiter)
        ->getJson(route('notifications.ready'))
        ->assertOk()
        ->assertJsonPath('orders', []);
});

it('notification persists in ready endpoint until explicit dismiss', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => true,
    ]);

    // Primera consulta — debe devolver la notificación
    $this->actingAs($waiter)
        ->getJson(route('notifications.ready'))
        ->assertOk()
        ->assertJsonCount(1, 'orders');

    // Segunda consulta sin dismiss — sigue ahí
    $this->actingAs($waiter)
        ->getJson(route('notifications.ready'))
        ->assertOk()
        ->assertJsonCount(1, 'orders')
        ->assertJsonPath('orders.0.id', $order->id);
});

it('does not show notification after dismiss on page reload', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => true,
    ]);

    // Camarero confirma la notificación
    $this->actingAs($waiter)
        ->patchJson(route('notifications.dismiss', $order))
        ->assertOk();

    // Simula recarga de página: el endpoint no debe devolver la orden
    $this->actingAs($waiter)
        ->getJson(route('notifications.ready'))
        ->assertOk()
        ->assertJsonPath('orders', []);

    expect($order->fresh()->notification_ready)->toBeFalse();
});

it('multiple polls return the same notification without duplication', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => true,
    ]);

    $first  = $this->actingAs($waiter)->getJson(route('notifications.ready'))->json('orders');
    $second = $this->actingAs($waiter)->getJson(route('notifications.ready'))->json('orders');
    $third  = $this->actingAs($waiter)->getJson(route('notifications.ready'))->json('orders');

    expect(count($first))->toBe(1);
    expect(count($second))->toBe(1);
    expect(count($third))->toBe(1);
    expect($first[0]['id'])->toBe($second[0]['id'])->toBe($third[0]['id']);
});

// ─── Regresión: readyOrders renderizado server-side (fix ghost flicker) ─────────

it('bar panel includes ready notifications in initial html without polling', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => true,
    ]);

    $response = $this->actingAs($waiter)->get(route('bar.index'));

    $response->assertOk();
    // El JSON de readyOrders debe estar embebido en el HTML inicial
    $response->assertSee('"id":' . $order->id, false);
    $response->assertSee('"table":"' . $table->name . '"', false);
});

it('bar panel initial html has no ready orders when notification_ready is false', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => false,
    ]);

    $response = $this->actingAs($waiter)->get(route('bar.index'));

    $response->assertOk();
    $response->assertSee('<script id="bar-ready-orders" type="application/json">[]</script>', false);
});

// ─── Regresión: carga de vista no borra notification_ready (ghost fix) ───────

it('loading bar index does not clear notification_ready', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => true,
    ]);

    // Simula varias recargas de página: el flag debe mantenerse true en BD.
    $this->actingAs($waiter)->get(route('bar.index'))->assertOk();
    $this->actingAs($waiter)->get(route('bar.index'))->assertOk();
    $this->actingAs($waiter)->get(route('bar.index'))->assertOk();

    expect($order->fresh()->notification_ready)->toBeTrue();
});

it('notification_ready is cleared when all bar items are marked served', function () {
    $waiter  = User::factory()->create(['role' => 'waiter']);
    $table   = Table::factory()->create(['user_id' => $waiter->id]);
    $product = \App\Models\Product::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'           => $table->id,
        'status'             => 'ready',
        'notification_ready' => true,
    ]);
    $item = \App\Models\OrderItem::factory()->create([
        'order_id'    => $order->id,
        'product_id'  => $product->id,
        'status'      => 'ready',
        'destination' => 'bar',
    ]);

    $this->actingAs($waiter)
        ->patchJson(route('bar.items.served', $item))
        ->assertOk();

    expect($order->fresh()->notification_ready)->toBeFalse();
});

// ─── Endpoint GET /notifications/bill-requests ────────────────────────────────

it('bill_requested false means order is not returned by bill endpoint', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    Order::factory()->create([
        'table_id'      => $table->id,
        'bill_requested' => false,
    ]);

    $this->actingAs($waiter)
        ->getJson(route('notifications.bill.requests'))
        ->assertOk()
        ->assertJsonPath('orders', []);
});

it('bill notification persists until explicit dismiss', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'      => $table->id,
        'bill_requested' => true,
    ]);

    $this->actingAs($waiter)
        ->getJson(route('notifications.bill.requests'))
        ->assertOk()
        ->assertJsonCount(1, 'orders')
        ->assertJsonPath('orders.0.id', $order->id);

    // Segunda consulta sin dismiss — sigue ahí
    $this->actingAs($waiter)
        ->getJson(route('notifications.bill.requests'))
        ->assertOk()
        ->assertJsonCount(1, 'orders');
});

it('does not show bill notification after dismiss on page reload', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $table  = Table::factory()->create(['user_id' => $waiter->id]);

    $order = Order::factory()->create([
        'table_id'      => $table->id,
        'bill_requested' => true,
    ]);

    $this->actingAs($waiter)
        ->patchJson(route('notifications.bill.dismiss', $order))
        ->assertOk();

    // Simula recarga: el endpoint no debe devolver la orden
    $this->actingAs($waiter)
        ->getJson(route('notifications.bill.requests'))
        ->assertOk()
        ->assertJsonPath('orders', []);

    expect($order->fresh()->bill_requested)->toBeFalse();
});

it('returns 403 when dismissing bill request from another restaurant', function () {
    $waiter      = User::factory()->create(['role' => 'waiter']);
    $otherWaiter = User::factory()->create(['role' => 'waiter']);
    $otherTable  = Table::factory()->create(['user_id' => $otherWaiter->id]);

    $otherOrder = Order::factory()->create([
        'table_id'      => $otherTable->id,
        'bill_requested' => true,
    ]);

    $this->actingAs($waiter)
        ->patchJson(route('notifications.bill.dismiss', $otherOrder))
        ->assertForbidden();

    expect($otherOrder->fresh()->bill_requested)->toBeTrue();
});
