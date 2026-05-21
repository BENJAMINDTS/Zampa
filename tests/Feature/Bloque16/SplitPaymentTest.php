<?php

/**
 * @author BenjaminDTS
 * @author AyrtonAlania
 */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemPayment;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTenants;

uses(RefreshDatabase::class);
uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();

    // Stripe SDK no está instalado en el entorno de tests — siempre mocked.
    $this->mock(StripeService::class);

    $this->user->update([
        'split_payment_enabled'   => true,
        'split_payment_max_parts' => 10,
    ]);

    $this->table = Table::factory()->for($this->user)->create();

    $this->order = Order::factory()->create([
        'table_id'       => $this->table->id,
        'status'         => 'pending',
        'payment_status' => 'pending',
        'total'          => 30.00,
    ]);

    $this->item1 = OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'price'    => 10.00,
        'quantity' => 1,
    ]);

    $this->item2 = OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'price'    => 20.00,
        'quantity' => 1,
    ]);
});

// ─── claimedItems ─────────────────────────────────────────────────────────────

it('claimedItems returns all items with claimed=false when no payments exist', function () {
    $this->getJson("/api/v1/payment/{$this->table->unique_hash}/split/items")
        ->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonCount(2, 'items')
        ->assertJsonPath('items.0.claimed', false)
        ->assertJsonPath('items.1.claimed', false);
});

it('claimedItems marks item as claimed when a pending payment exists', function () {
    OrderItemPayment::factory()->create([
        'order_id'      => $this->order->id,
        'order_item_id' => $this->item1->id,
        'status'        => 'pending',
        'mode'          => 'items',
    ]);

    $response = $this->getJson("/api/v1/payment/{$this->table->unique_hash}/split/items")
        ->assertOk();

    $items = collect($response->json('items'));
    expect($items->firstWhere('id', $this->item1->id)['claimed'])->toBeTrue();
    expect($items->firstWhere('id', $this->item2->id)['claimed'])->toBeFalse();
});

it('claimedItems returns 404 with invalid table hash', function () {
    $this->getJson('/api/v1/payment/invalid-hash/split/items')
        ->assertNotFound();
});

it('claimedItems returns 404 when no active order exists', function () {
    $this->order->update(['status' => 'closed']);

    $this->getJson("/api/v1/payment/{$this->table->unique_hash}/split/items")
        ->assertNotFound();
});

// ─── payItems ─────────────────────────────────────────────────────────────────

it('payItems creates a pending OrderItemPayment per selected item', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn([
            'id'            => 'pi_test_items_001',
            'client_secret' => 'secret_items_001',
            'status'        => 'requires_payment_method',
        ]);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonStructure(['client_secret', 'amount']);

    $this->assertDatabaseHas('order_item_payments', [
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item1->id,
        'stripe_payment_intent_id' => 'pi_test_items_001',
        'status'                   => 'pending',
        'mode'                     => 'items',
    ]);
});

it('payItems returns 409 when an item is already claimed', function () {
    OrderItemPayment::factory()->create([
        'order_id'      => $this->order->id,
        'order_item_id' => $this->item1->id,
        'status'        => 'pending',
        'mode'          => 'items',
    ]);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertStatus(409)
      ->assertJsonPath('success', false);
});

it('payItems returns 422 when item_ids is empty', function () {
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [],
    ])->assertUnprocessable();
});

it('payItems returns 422 when item belongs to a different order', function () {
    $otherTable = Table::factory()->for($this->other)->create();
    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id]);
    $otherItem  = OrderItem::factory()->create(['order_id' => $otherOrder->id]);

    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->never();

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$otherItem->id],
    ])->assertUnprocessable();
});

it('payItems returns 404 with invalid table hash', function () {
    $this->postJson('/api/v1/payment/invalid-hash/split/pay-items', [
        'item_ids' => [$this->item1->id],
    ])->assertNotFound();
});

it('payItems returns 404 when no active order exists', function () {
    $this->order->update(['status' => 'closed']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertNotFound();
});

// ─── payEquitative ────────────────────────────────────────────────────────────

it('payEquitative creates an OrderItemPayment for the diner\'s part', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn([
            'id'            => 'pi_test_eq_001',
            'client_secret' => 'secret_eq_001',
            'status'        => 'requires_payment_method',
        ]);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 3,
        'part_number' => 1,
    ])->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('parts_total', 3)
      ->assertJsonPath('part_number', 1);

    $this->assertDatabaseHas('order_item_payments', [
        'order_id'     => $this->order->id,
        'order_item_id'=> null,
        'status'       => 'pending',
        'mode'         => 'equitative',
        'parts_total'  => 3,
        'part_number'  => 1,
    ]);
});

it('payEquitative calculates the correct per-person amount', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_test_eq_002', 'client_secret' => 'secret_eq_002', 'status' => 'requires_payment_method']);

    $response = $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 3,
        'part_number' => 1,
    ])->assertOk();

    expect(round($response->json('amount'), 2))->toBe(round(30 / 3, 2));
});

it('payEquitative returns 422 when part_number exceeds people', function () {
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 3,
        'part_number' => 5,
    ])->assertUnprocessable();
});

it('payEquitative returns 422 when people is less than 2', function () {
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 1,
        'part_number' => 1,
    ])->assertUnprocessable();
});

it('payEquitative returns 404 with invalid table hash', function () {
    $this->postJson('/api/v1/payment/invalid-hash/split/pay-eq', [
        'people'      => 2,
        'part_number' => 1,
    ])->assertNotFound();
});

it('payEquitative returns 404 when no active order exists', function () {
    $this->order->update(['status' => 'closed']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 2,
        'part_number' => 1,
    ])->assertNotFound();
});

// ─── confirm ──────────────────────────────────────────────────────────────────

it('confirm marks split payment as paid when Stripe succeeds', function () {
    $splitPayment = OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item1->id,
        'amount'                   => 10.00,
        'stripe_payment_intent_id' => 'pi_test_confirm_001',
        'status'                   => 'pending',
        'mode'                     => 'items',
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_test_confirm_001', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_test_confirm_001',
    ])->assertOk()->assertJsonPath('success', true);

    $this->assertDatabaseHas('order_item_payments', [
        'id'     => $splitPayment->id,
        'status' => 'paid',
    ]);
});

it('confirm marks split payment as failed when Stripe does not succeed', function () {
    $splitPayment = OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'stripe_payment_intent_id' => 'pi_test_fail_001',
        'status'                   => 'pending',
        'mode'                     => 'equitative',
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_test_fail_001', 'status' => 'requires_payment_method']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_test_fail_001',
    ])->assertUnprocessable();

    $this->assertDatabaseHas('order_item_payments', [
        'id'     => $splitPayment->id,
        'status' => 'failed',
    ]);
});

it('confirm closes order automatically when fully paid via split', function () {
    // Item1 ya pagado; Item2 es el que confirmamos ahora
    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item1->id,
        'amount'                   => 10.00,
        'stripe_payment_intent_id' => 'pi_test_partial_001',
        'status'                   => 'paid',
        'mode'                     => 'items',
    ]);

    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item2->id,
        'amount'                   => 20.00,
        'stripe_payment_intent_id' => 'pi_test_partial_002',
        'status'                   => 'pending',
        'mode'                     => 'items',
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_test_partial_002', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_test_partial_002',
    ])->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('fully_paid', true);

    $this->assertDatabaseHas('orders', [
        'id'             => $this->order->id,
        'payment_status' => 'paid',
        'status'         => 'closed',
    ]);
});

it('confirm does not close order when partially paid', function () {
    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item1->id,
        'amount'                   => 10.00,
        'stripe_payment_intent_id' => 'pi_test_partial_003',
        'status'                   => 'pending',
        'mode'                     => 'items',
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_test_partial_003', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_test_partial_003',
    ])->assertOk()
      ->assertJsonPath('fully_paid', false);

    $this->assertDatabaseHas('orders', [
        'id'             => $this->order->id,
        'payment_status' => 'pending',
        'status'         => 'pending',
    ]);
});

it('confirm returns 404 when payment_intent_id does not belong to the order', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->never();

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_completely_unknown',
    ])->assertNotFound();
});

it('confirm returns 422 when payment_intent_id is missing', function () {
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [])
        ->assertUnprocessable();
});

it('confirm returns 404 with invalid table hash', function () {
    $this->postJson('/api/v1/payment/invalid-hash/split/confirm', [
        'payment_intent_id' => 'pi_test_xyz',
    ])->assertNotFound();
});

// ─── Model helpers ────────────────────────────────────────────────────────────

it('OrderItem isClaimed returns true when a pending payment exists', function () {
    OrderItemPayment::factory()->create([
        'order_id'      => $this->order->id,
        'order_item_id' => $this->item1->id,
        'status'        => 'pending',
        'mode'          => 'items',
    ]);

    expect($this->item1->fresh()->isClaimed())->toBeTrue();
});

it('OrderItem isClaimed returns false when no payment exists', function () {
    expect($this->item1->fresh()->isClaimed())->toBeFalse();
});

it('OrderItem isClaimed returns false when only failed payment exists', function () {
    OrderItemPayment::factory()->create([
        'order_id'      => $this->order->id,
        'order_item_id' => $this->item1->id,
        'status'        => 'failed',
        'mode'          => 'items',
    ]);

    expect($this->item1->fresh()->isClaimed())->toBeFalse();
});

it('Order isFullyPaidViaSplit returns true when paid amount covers total', function () {
    OrderItemPayment::factory()->create([
        'order_id' => $this->order->id,
        'amount'   => 30.00,
        'status'   => 'paid',
        'mode'     => 'equitative',
    ]);

    expect($this->order->fresh()->isFullyPaidViaSplit())->toBeTrue();
});

it('Order isFullyPaidViaSplit returns false when paid amount is below total', function () {
    OrderItemPayment::factory()->create([
        'order_id' => $this->order->id,
        'amount'   => 10.00,
        'status'   => 'paid',
        'mode'     => 'equitative',
    ]);

    expect($this->order->fresh()->isFullyPaidViaSplit())->toBeFalse();
});

// ─── MODO A — tests con nombres del spec ─────────────────────────────────────

it('customer can claim order items for payment', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_claim_001', 'client_secret' => 'secret_claim_001', 'status' => 'requires_payment_method']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id, $this->item2->id],
    ])->assertOk()->assertJsonPath('success', true);

    $this->assertDatabaseHas('order_item_payments', ['order_item_id' => $this->item1->id, 'status' => 'pending']);
    $this->assertDatabaseHas('order_item_payments', ['order_item_id' => $this->item2->id, 'status' => 'pending']);
});

it('claimed items are blocked for other customers', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_first_001', 'client_secret' => 'secret_first_001', 'status' => 'requires_payment_method']);

    // Customer A claims item1
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertOk();

    // Customer B tries to claim the same item
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertStatus(409)->assertJsonPath('success', false);
});

it('two customers cannot claim the same item simultaneously', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_race_001', 'client_secret' => 'secret_race_001', 'status' => 'requires_payment_method']);

    // Simulate race: first claim succeeds and creates the payment row
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertOk();

    // Second concurrent claim is rejected because item is now claimed
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertStatus(409);

    $this->assertDatabaseCount('order_item_payments', 1);
});

it('creates payment intent for claimed items amount', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_amount_001', 'client_secret' => 'secret_amount_001', 'status' => 'requires_payment_method']);

    $response = $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id, $this->item2->id],
    ])->assertOk();

    // item1 = 10.00, item2 = 20.00 → total = 30.00
    expect((float) $response->json('amount'))->toBe(30.0);
});

it('confirms payment and marks items as paid', function () {
    $payment = OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item1->id,
        'amount'                   => 10.00,
        'stripe_payment_intent_id' => 'pi_mark_paid_001',
        'status'                   => 'pending',
        'mode'                     => 'items',
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_mark_paid_001', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_mark_paid_001',
    ])->assertOk()->assertJsonPath('success', true);

    expect($payment->fresh()->status)->toBe('paid');
});

it('order payment_status becomes paid when all items paid', function () {
    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item1->id,
        'amount'                   => 10.00,
        'stripe_payment_intent_id' => 'pi_all_paid_001',
        'status'                   => 'paid',
        'mode'                     => 'items',
    ]);
    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'order_item_id'            => $this->item2->id,
        'amount'                   => 20.00,
        'stripe_payment_intent_id' => 'pi_all_paid_002',
        'status'                   => 'pending',
        'mode'                     => 'items',
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_all_paid_002', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_all_paid_002',
    ])->assertOk()->assertJsonPath('fully_paid', true);

    expect($this->order->fresh()->payment_status)->toBe('paid');
});

// ─── MODO B — tests con nombres del spec ─────────────────────────────────────

it('creates payment intent for equitative fraction', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_eq_spec_001', 'client_secret' => 'secret_eq_spec_001', 'status' => 'requires_payment_method']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 2,
        'part_number' => 1,
    ])->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['client_secret']);
});

it('equitative amount is total divided by parts', function () {
    $this->mock(StripeService::class)
        ->shouldReceive('createSplitPaymentIntent')
        ->once()
        ->andReturn(['id' => 'pi_eq_div_001', 'client_secret' => 'secret_eq_div_001', 'status' => 'requires_payment_method']);

    $response = $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 4,
        'part_number' => 1,
    ])->assertOk();

    expect(round($response->json('amount'), 2))->toBe(round(30.0 / 4, 2));
});

it('fails with less than 2 parts', function () {
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 1,
        'part_number' => 1,
    ])->assertUnprocessable();
});

it('fails when parts exceed gerente max_parts', function () {
    $this->user->update(['split_payment_max_parts' => 4]);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 6,
        'part_number' => 1,
    ])->assertUnprocessable();
});

it('order payment_status becomes paid when all parts confirmed', function () {
    // 2 partes equitativas de 15€ cada una; primera ya pagada
    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'amount'                   => 15.00,
        'stripe_payment_intent_id' => 'pi_eq_parts_001',
        'status'                   => 'paid',
        'mode'                     => 'equitative',
        'parts_total'              => 2,
        'part_number'              => 1,
    ]);
    OrderItemPayment::factory()->create([
        'order_id'                 => $this->order->id,
        'amount'                   => 15.00,
        'stripe_payment_intent_id' => 'pi_eq_parts_002',
        'status'                   => 'pending',
        'mode'                     => 'equitative',
        'parts_total'              => 2,
        'part_number'              => 2,
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->andReturn(['id' => 'pi_eq_parts_002', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_eq_parts_002',
    ])->assertOk()->assertJsonPath('fully_paid', true);

    expect($this->order->fresh()->payment_status)->toBe('paid');
});

// ─── SEGURIDAD ────────────────────────────────────────────────────────────────

it('cannot claim items from another restaurants order', function () {
    $otherTable = Table::factory()->for($this->other)->create();
    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id, 'status' => 'pending', 'payment_status' => 'pending']);
    $otherItem  = OrderItem::factory()->create(['order_id' => $otherOrder->id, 'price' => 5.00, 'quantity' => 1]);

    // Try to pay an item from another restaurant via our table hash
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$otherItem->id],
    ])->assertUnprocessable();

    $this->assertDatabaseEmpty('order_item_payments');
});

it('cannot pay items from another restaurants order', function () {
    $otherTable = Table::factory()->for($this->other)->create();
    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id, 'status' => 'pending', 'payment_status' => 'pending']);

    // The payment intent belongs to the other restaurant's order
    OrderItemPayment::factory()->create([
        'order_id'                 => $otherOrder->id,
        'amount'                   => 10.00,
        'stripe_payment_intent_id' => 'pi_other_order_001',
        'status'                   => 'pending',
        'mode'                     => 'equitative',
    ]);

    // Confirm via our table hash — should 404 because payment belongs to another order
    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_other_order_001',
    ])->assertNotFound();
});

it('split payment disabled returns 403 on all endpoints', function () {
    $this->user->update(['split_payment_enabled' => false]);

    $this->getJson("/api/v1/payment/{$this->table->unique_hash}/split/items")
        ->assertForbidden();

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-items", [
        'item_ids' => [$this->item1->id],
    ])->assertForbidden();

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/pay-eq", [
        'people'      => 2,
        'part_number' => 1,
    ])->assertForbidden();

    $this->postJson("/api/v1/payment/{$this->table->unique_hash}/split/confirm", [
        'payment_intent_id' => 'pi_any',
    ])->assertForbidden();
});
