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
use App\Services\TicketPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new TicketPdfService();

    $this->user = User::factory()->create([
        'business_name' => 'Restaurante Test',
        'role'          => 'admin',
    ]);

    $this->config = TicketConfig::factory()->create([
        'user_id'     => $this->user->id,
        'template'    => 'classic',
        'tax_id'      => 'B12345678',
        'footer_text' => 'Gracias por su visita.',
        'logo'        => null,
    ]);

    $this->table = Table::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Mesa 5',
    ]);

    $this->order = Order::factory()->create([
        'table_id'       => $this->table->id,
        'total'          => 15.50,
        'tip'            => 1.50,
        'payment_method' => 'card',
        'payment_status' => 'paid',
    ]);

    $this->product = Product::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Pizza Margarita',
        'price'   => 12.00,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $this->order->id,
        'product_id' => $this->product->id,
        'quantity'   => 1,
        'price'      => 12.00,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $this->order->id,
        'product_id' => $this->product->id,
        'quantity'   => 2,
        'price'      => 1.50,
    ]);
});

// ── generate() ────────────────────────────────────────────────────────────────

it('generates a pdf for a given order and config', function () {
    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf-content'));

    $response = $this->service->generate($this->order, $this->config);

    expect($response)->not->toBeNull();
});

it('uses the correct template based on config', function () {
    $this->config->update(['template' => 'modern']);

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(fn ($view) => $view === 'tickets.modern')
        ->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf'));

    $this->service->generate($this->order, $this->config);
});

it('uses minimal template when config is set to minimal', function () {
    $this->config->update(['template' => 'minimal']);

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(fn ($view) => $view === 'tickets.minimal')
        ->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf'));

    $this->service->generate($this->order, $this->config);
});

// ── getFilename() ──────────────────────────────────────────────────────────────

it('generates correct filename with order id and date', function () {
    $filename = $this->service->getFilename($this->order);

    expect($filename)->toBe('ticket-' . $this->order->id . '-' . now()->format('Y-m-d') . '.pdf');
});

// ── buildTicketData() ──────────────────────────────────────────────────────────

it('includes order items in ticket data', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data['lines'])->toHaveCount(2);
});

it('calculates subtotal correctly', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    // item 1: 1 × 12.00 = 12.00, item 2: 2 × 1.50 = 3.00 → subtotal = 15.00
    expect($data['subtotal'])->toBe(15.00);
});

it('includes tip in total when present', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data['tip'])->toBe(1.50);
});

it('total equals subtotal plus tip', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data['total'])->toBe(round($data['subtotal'] + $data['tip'], 2));
});

it('buildTicketData includes restaurant name from user', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data['business_name'])->toBe('Restaurante Test');
});

it('buildTicketData uses business_name from user', function () {
    $this->user->update(['business_name' => 'Mi Restaurante S.L.']);

    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->fresh()->load('user')
    );

    expect($data['business_name'])->toBe('Mi Restaurante S.L.');
});

it('buildTicketData includes table name', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data['table_name'])->toBe('Mesa 5');
});

it('buildTicketData includes logo url when logo exists', function () {
    Storage::fake('public');
    $this->config->update(['logo' => 'tickets/logo.png']);

    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->fresh()->load('user')
    );

    expect($data['logo_url'])->not->toBeNull();
});

it('buildTicketData logo url is null when no logo', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data['logo_url'])->toBeNull();
});

// ── Selección de template (Bloque 17.4) ───────────────────────────────────────

it('uses classic template when config is classic', function () {
    $this->config->update(['template' => 'classic']);

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(fn ($view) => $view === 'tickets.classic')
        ->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(new Response('pdf'));

    $this->service->generate($this->order, $this->config->fresh());
});

it('ticket data includes all required fields for templates', function () {
    $data = $this->service->buildTicketData(
        $this->order->load(['items.product', 'items.variant', 'table']),
        $this->config->load('user')
    );

    expect($data)->toHaveKeys([
        'business_name',
        'tax_id',
        'logo_url',
        'footer_text',
        'template',
        'table_name',
        'date',
        'lines',
        'subtotal',
        'tip',
        'total',
        'payment_method',
        'order_id',
    ]);
});
