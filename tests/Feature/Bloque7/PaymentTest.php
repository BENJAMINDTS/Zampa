<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Conversation;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTenants;

uses(RefreshDatabase::class);
uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── Solicitud de cuenta (BillRequestController) ─────────────────────────────

it('bill request marks order as bill_requested and stores preferred payment method', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [
        'payment_method' => 'cash',
    ])->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'id'                       => $order->id,
        'bill_requested'           => true,
        'requested_payment_method' => 'cash',
    ]);
});

it('bill request accepts card as preferred payment method', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [
        'payment_method' => 'card',
    ])->assertOk();

    $this->assertDatabaseHas('orders', [
        'id'                       => $order->id,
        'bill_requested'           => true,
        'requested_payment_method' => 'card',
    ]);
});

it('bill request returns 404 when no active order exists for the table', function () {
    $table = Table::factory()->for($this->user)->create();

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [
        'payment_method' => 'cash',
    ])->assertNotFound();
});

it('bill request returns 404 with invalid table hash', function () {
    $this->postJson('/api/v1/bill-request/invalid-hash-xyz', [
        'payment_method' => 'cash',
    ])->assertNotFound();
});

it('bill request returns 422 when payment_method is missing', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [])
        ->assertUnprocessable();
});

it('bill request returns 422 when payment_method is invalid', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [
        'payment_method' => 'bitcoin',
    ])->assertUnprocessable();
});

it('bill request does not find a closed order', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->create(['table_id' => $table->id, 'status' => 'closed']);

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [
        'payment_method' => 'cash',
    ])->assertNotFound();
});

// ─── Pago en efectivo (PaymentController) ────────────────────────────────────

it('cash payment closes the order with payment_method cash', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $table = Table::factory()->create(['user_id' => $admin->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->actingAs($admin)
        ->postJson(route('payments.cash', $order))
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'id'             => $order->id,
        'status'         => 'closed',
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'bill_requested' => false,
    ]);
});

it('cash payment returns 403 when order belongs to another user', function () {
    $admin      = User::factory()->create(['role' => 'admin']);
    $otherAdmin = User::factory()->create(['role' => 'admin']);
    $table      = Table::factory()->create(['user_id' => $otherAdmin->id]);
    $order      = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->actingAs($admin)
        ->postJson(route('payments.cash', $order))
        ->assertForbidden();
});

it('cash payment returns 422 when order is already closed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $table = Table::factory()->create(['user_id' => $admin->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'closed']);

    $this->actingAs($admin)
        ->postJson(route('payments.cash', $order))
        ->assertUnprocessable();
});

it('cash payment redirects unauthenticated user to login', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->postJson(route('payments.cash', $order))
        ->assertUnauthorized();
});

// ─── Total del pedido (CardPaymentController::total) ─────────────────────────
// CardPaymentController injects StripeService in its constructor, so every test
// that hits these routes must mock it to avoid the null-config Stripe exception.

it('total endpoint returns the order total for an active order', function () {
    $this->mock(StripeService::class)->shouldNotReceive('createPaymentIntent', 'confirmPayment');

    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create([
        'table_id' => $table->id,
        'status'   => 'pending',
        'total'    => 42.50,
    ]);

    $this->getJson("/api/v1/payment/{$table->unique_hash}/total")
        ->assertOk()
        ->assertJson(['success' => true, 'total' => 42.50]);
});

it('total endpoint returns 404 when no active order exists', function () {
    $this->mock(StripeService::class)->shouldNotReceive('createPaymentIntent', 'confirmPayment');

    $table = Table::factory()->for($this->user)->create();

    $this->getJson("/api/v1/payment/{$table->unique_hash}/total")
        ->assertNotFound();
});

it('total endpoint returns 404 with invalid hash', function () {
    $this->mock(StripeService::class)->shouldNotReceive('createPaymentIntent', 'confirmPayment');

    $this->getJson('/api/v1/payment/nonexistent-hash/total')
        ->assertNotFound();
});

// ─── Pago con tarjeta — intent (CardPaymentController::intent) ───────────────

it('intent endpoint creates a PaymentIntent via Stripe and returns client_secret', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create([
        'table_id' => $table->id,
        'status'   => 'pending',
        'total'    => 20.00,
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('createPaymentIntent')
        ->once()
        ->withArgs(fn (int $amount) => $amount === 2000)
        ->andReturn([
            'id'            => 'pi_test_123',
            'client_secret' => 'secret_test',
            'status'        => 'requires_payment_method',
        ]);

    $this->postJson("/api/v1/payment/{$table->unique_hash}/intent", ['tip' => 0])
        ->assertOk()
        ->assertJson([
            'success'       => true,
            'client_secret' => 'secret_test',
            'total'         => 20.00,
        ]);
});

it('intent endpoint includes tip in the grand total sent to Stripe', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create([
        'table_id' => $table->id,
        'status'   => 'pending',
        'total'    => 20.00,
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('createPaymentIntent')
        ->once()
        ->withArgs(fn (int $amount) => $amount === 2500) // 20 + 5 = 25 €
        ->andReturn([
            'id'            => 'pi_test_456',
            'client_secret' => 'secret_tip',
            'status'        => 'requires_payment_method',
        ]);

    $this->postJson("/api/v1/payment/{$table->unique_hash}/intent", ['tip' => 5.00])
        ->assertOk()
        ->assertJson([
            'tip'         => 5.00,
            'grand_total' => 25.00,
        ]);
});

it('intent endpoint returns 404 when no active order exists', function () {
    $table = Table::factory()->for($this->user)->create();

    $this->mock(StripeService::class)
        ->shouldNotReceive('createPaymentIntent');

    $this->postJson("/api/v1/payment/{$table->unique_hash}/intent", [])
        ->assertNotFound();
});

it('intent endpoint returns 422 when tip is negative', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->mock(StripeService::class)
        ->shouldNotReceive('createPaymentIntent');

    $this->postJson("/api/v1/payment/{$table->unique_hash}/intent", ['tip' => -1])
        ->assertUnprocessable();
});

it('intent endpoint returns 404 with invalid hash', function () {
    $this->mock(StripeService::class)
        ->shouldNotReceive('createPaymentIntent');

    $this->postJson('/api/v1/payment/bad-hash/intent', [])
        ->assertNotFound();
});

// ─── Pago con tarjeta — confirm (CardPaymentController::confirm) ──────────────

it('confirm endpoint closes the order with payment_method card when Stripe succeeds', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create([
        'table_id' => $table->id,
        'status'   => 'pending',
        'total'    => 30.00,
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->once()
        ->with('pi_test_789')
        ->andReturn(['id' => 'pi_test_789', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$table->unique_hash}/confirm", [
        'payment_intent_id' => 'pi_test_789',
        'tip'               => 0,
    ])->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'id'             => $order->id,
        'status'         => 'closed',
        'payment_method' => 'card',
        'payment_status' => 'paid',
        'tip'            => 0,
    ]);
});

it('tip is stored separately in orders.tip after card payment', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create([
        'table_id' => $table->id,
        'status'   => 'pending',
        'total'    => 18.00,
    ]);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->andReturn(['id' => 'pi_test_tip', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$table->unique_hash}/confirm", [
        'payment_intent_id' => 'pi_test_tip',
        'tip'               => 3.50,
    ])->assertOk();

    $this->assertDatabaseHas('orders', [
        'id'    => $order->id,
        'tip'   => 3.50,
        'total' => 18.00,
    ]);
});

it('confirm endpoint returns 422 and does not close the order when Stripe status is not succeeded', function () {
    $table = Table::factory()->for($this->user)->create();
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->andReturn(['id' => 'pi_failed', 'status' => 'requires_payment_method']);

    $this->postJson("/api/v1/payment/{$table->unique_hash}/confirm", [
        'payment_intent_id' => 'pi_failed',
    ])->assertUnprocessable()->assertJson(['success' => false]);

    expect($order->fresh()->status)->not->toBe('closed');
});

it('confirm endpoint returns 422 when payment_intent_id is missing', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    $this->mock(StripeService::class)
        ->shouldNotReceive('confirmPayment');

    $this->postJson("/api/v1/payment/{$table->unique_hash}/confirm", [])
        ->assertUnprocessable();
});

it('confirm endpoint returns 404 when no active order exists', function () {
    $table = Table::factory()->for($this->user)->create();

    $this->mock(StripeService::class)
        ->shouldNotReceive('confirmPayment');

    $this->postJson("/api/v1/payment/{$table->unique_hash}/confirm", [
        'payment_intent_id' => 'pi_test_no_order',
    ])->assertNotFound();
});

it('confirm endpoint returns 404 with invalid hash', function () {
    $this->mock(StripeService::class)
        ->shouldNotReceive('confirmPayment');

    $this->postJson('/api/v1/payment/bad-hash/confirm', [
        'payment_intent_id' => 'pi_test_xyz',
    ])->assertNotFound();
});

// ─── Cierre de conversaciones al cerrar pedido ────────────────────────────────

it('closing a card payment closes active conversations on the table', function () {
    $table        = Table::factory()->for($this->user)->create();
    $order        = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $conversation = Conversation::factory()->create(['table_id' => $table->id, 'status' => 'active']);

    $this->mock(StripeService::class)
        ->shouldReceive('confirmPayment')
        ->andReturn(['id' => 'pi_conv_close', 'status' => 'succeeded']);

    $this->postJson("/api/v1/payment/{$table->unique_hash}/confirm", [
        'payment_intent_id' => 'pi_conv_close',
        'tip'               => 0,
    ])->assertOk();

    $this->assertDatabaseHas('conversations', [
        'id'     => $conversation->id,
        'status' => 'closed',
    ]);
});

it('closing a cash payment closes active conversations on the table', function () {
    $admin        = User::factory()->create(['role' => 'admin']);
    $table        = Table::factory()->create(['user_id' => $admin->id]);
    $order        = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $conversation = Conversation::factory()->create(['table_id' => $table->id, 'status' => 'active']);

    $this->actingAs($admin)
        ->postJson(route('payments.cash', $order))
        ->assertOk();

    $this->assertDatabaseHas('conversations', [
        'id'     => $conversation->id,
        'status' => 'closed',
    ]);
});

// ─── Stripe nunca se llama sin mock ──────────────────────────────────────────

it('StripeService is never instantiated during bill request (no real calls)', function () {
    $table = Table::factory()->for($this->user)->create();
    Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    // BillRequestController no inyecta StripeService — verificamos que no explota
    $this->mock(StripeService::class)
        ->shouldNotReceive('createPaymentIntent')
        ->shouldNotReceive('confirmPayment');

    $this->postJson("/api/v1/bill-request/{$table->unique_hash}", [
        'payment_method' => 'card',
    ])->assertOk();
});
