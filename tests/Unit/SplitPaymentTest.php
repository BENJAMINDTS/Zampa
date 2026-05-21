<?php

/**
 * Unit tests para los helpers de cobro partido:
 * Order::isFullyPaidViaSplit(), getPaidAmountViaSplit()
 * OrderItem::isClaimed(), isPaidViaSplit()
 *
 * @author AyrtonAlania
 */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Order helpers ────────────────────────────────────────────────────────────

it('isFullyPaidViaSplit returns true when all items paid', function () {
    $order = Order::factory()->create(['total' => 20.00]);

    OrderItemPayment::factory()->create([
        'order_id' => $order->id,
        'amount'   => 20.00,
        'status'   => 'paid',
        'mode'     => 'equitative',
    ]);

    expect($order->fresh()->isFullyPaidViaSplit())->toBeTrue();
});

it('isFullyPaidViaSplit returns false when some items pending', function () {
    $order = Order::factory()->create(['total' => 20.00]);

    OrderItemPayment::factory()->create([
        'order_id' => $order->id,
        'amount'   => 10.00,
        'status'   => 'paid',
        'mode'     => 'equitative',
    ]);
    OrderItemPayment::factory()->create([
        'order_id' => $order->id,
        'amount'   => 10.00,
        'status'   => 'pending',
        'mode'     => 'equitative',
    ]);

    expect($order->fresh()->isFullyPaidViaSplit())->toBeFalse();
});

it('getPaidAmountViaSplit sums confirmed payments correctly', function () {
    $order = Order::factory()->create(['total' => 30.00]);

    OrderItemPayment::factory()->create(['order_id' => $order->id, 'amount' => 10.00, 'status' => 'paid',    'mode' => 'equitative']);
    OrderItemPayment::factory()->create(['order_id' => $order->id, 'amount' => 15.00, 'status' => 'paid',    'mode' => 'equitative']);
    OrderItemPayment::factory()->create(['order_id' => $order->id, 'amount' =>  5.00, 'status' => 'pending', 'mode' => 'equitative']);
    OrderItemPayment::factory()->create(['order_id' => $order->id, 'amount' =>  8.00, 'status' => 'failed',  'mode' => 'equitative']);

    expect($order->fresh()->getPaidAmountViaSplit())->toBe(25.0);
});

// ─── OrderItem helpers ────────────────────────────────────────────────────────

it('isClaimed returns true when item has pending payment', function () {
    $order = Order::factory()->create();
    $item  = OrderItem::factory()->create(['order_id' => $order->id]);

    OrderItemPayment::factory()->create([
        'order_id'      => $order->id,
        'order_item_id' => $item->id,
        'status'        => 'pending',
        'mode'          => 'items',
    ]);

    expect($item->fresh()->isClaimed())->toBeTrue();
});

it('isClaimed returns false when no payment exists', function () {
    $order = Order::factory()->create();
    $item  = OrderItem::factory()->create(['order_id' => $order->id]);

    expect($item->fresh()->isClaimed())->toBeFalse();
});

it('isPaidViaSplit returns true when item has paid payment', function () {
    $order = Order::factory()->create();
    $item  = OrderItem::factory()->create(['order_id' => $order->id]);

    OrderItemPayment::factory()->create([
        'order_id'      => $order->id,
        'order_item_id' => $item->id,
        'status'        => 'paid',
        'mode'          => 'items',
    ]);

    expect($item->fresh()->isPaidViaSplit())->toBeTrue();
});
