<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Models\TicketConfig;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'business_name' => 'Restaurante Test',
        'role'          => 'admin',
    ]);
    $this->other = User::factory()->create([
        'business_name' => 'Otro Restaurante',
        'role'          => 'admin',
    ]);

    $this->table      = Table::factory()->create(['user_id' => $this->admin->id]);
    $this->otherTable = Table::factory()->create(['user_id' => $this->other->id]);

    $product = Product::factory()->create(['user_id' => $this->admin->id]);

    $this->order = Order::factory()->create([
        'table_id'       => $this->table->id,
        'payment_status' => 'paid',
        'payment_method' => 'card',
        'total'          => 15.00,
        'tip'            => 0,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $this->order->id,
        'product_id' => $product->id,
        'quantity'   => 1,
        'price'      => 15.00,
    ]);

    $this->unpaidOrder = Order::factory()->create([
        'table_id'       => $this->table->id,
        'payment_status' => 'pending',
    ]);

    $otherProduct = Product::factory()->create(['user_id' => $this->other->id]);

    $this->otherOrder = Order::factory()->create([
        'table_id'       => $this->otherTable->id,
        'payment_status' => 'paid',
        'payment_method' => 'card',
        'total'          => 10.00,
        'tip'            => 0,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $this->otherOrder->id,
        'product_id' => $otherProduct->id,
        'quantity'   => 1,
        'price'      => 10.00,
    ]);

    $this->config = TicketConfig::factory()->create([
        'user_id'  => $this->admin->id,
        'template' => 'classic',
    ]);
});

// ── download (pública) ────────────────────────────────────────────────────────

it('customer can download ticket after paid order', function () {
    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf'));

    $response = $this->get(
        route('ticket.download', $this->order) . '?hash=' . $this->table->unique_hash
    );

    expect($response->status())->toBe(200);
});

it('customer cannot download ticket if order is not paid', function () {
    $response = $this->get(
        route('ticket.download', $this->unpaidOrder) . '?hash=' . $this->table->unique_hash
    );

    expect($response->status())->toBe(403);
});

it('customer cannot download ticket of another restaurant', function () {
    // El hash de $this->table no coincide con el hash de $this->otherTable
    $response = $this->get(
        route('ticket.download', $this->otherOrder) . '?hash=' . $this->table->unique_hash
    );

    expect($response->status())->toBe(403);
});

it('download returns a pdf response', function () {
    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf-content', 200, [
        'Content-Type' => 'application/pdf',
    ]));

    $response = $this->get(
        route('ticket.download', $this->order) . '?hash=' . $this->table->unique_hash
    );

    expect($response->status())->toBe(200);
});

it('download uses default config when no ticket config exists', function () {
    $this->config->delete();

    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf'));

    $response = $this->get(
        route('ticket.download', $this->order) . '?hash=' . $this->table->unique_hash
    );

    expect($response->status())->toBe(200);
    // firstOrNew no persiste — no se crea registro en BD
    expect(TicketConfig::where('user_id', $this->admin->id)->exists())->toBeFalse();
});

it('download without hash parameter returns 403', function () {
    $response = $this->get(route('ticket.download', $this->order));

    expect($response->status())->toBe(403);
});

// ── reprint (protegida) ───────────────────────────────────────────────────────

it('admin can reprint ticket of own restaurant order', function () {
    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf'));

    $response = $this->actingAs($this->admin)
        ->get(route('ticket.reprint', $this->order));

    expect($response->status())->toBe(200);
});

it('admin cannot reprint ticket of another restaurant', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('ticket.reprint', $this->otherOrder));

    expect($response->status())->toBe(403);
});

it('returns 403 for waiter trying to reprint', function () {
    $waiter = User::factory()->create([
        'role'     => 'waiter',
        'admin_id' => $this->admin->id,
    ]);

    $response = $this->actingAs($waiter)
        ->get(route('ticket.reprint', $this->order));

    expect($response->status())->toBe(403);
});

it('unauthenticated user is redirected from reprint', function () {
    $response = $this->get(route('ticket.reprint', $this->order));

    $response->assertRedirect('/login');
});

it('reprint returns a pdf response', function () {
    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf-content', 200, [
        'Content-Type' => 'application/pdf',
    ]));

    $response = $this->actingAs($this->admin)
        ->get(route('ticket.reprint', $this->order));

    expect($response->status())->toBe(200);
});
