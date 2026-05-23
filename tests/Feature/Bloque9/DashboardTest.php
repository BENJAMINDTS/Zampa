<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->other = User::factory()->admin()->create();
});

// ─── Acceso y autenticación ────────────────────────────────────────────────────

it('redirects unauthenticated user away from dashboard', function () {
    $this->get(route('dashboard'))
         ->assertRedirect(route('login'));
});

it('shows dashboard to admin user', function () {
    $this->actingAs($this->admin)
         ->get(route('dashboard'))
         ->assertOk()
         ->assertViewIs('dashboard.index');
});

it('returns 403 for waiter role', function () {
    $waiter = User::factory()->waiter()->create();

    $this->actingAs($waiter)
         ->get(route('dashboard'))
         ->assertForbidden();
});

it('returns 403 for kitchen role', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)
         ->get(route('dashboard'))
         ->assertForbidden();
});

// ─── Multitenancy ──────────────────────────────────────────────────────────────

it('dashboard only shows income from the authenticated restaurant', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 50.00,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->assertOk()
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(50.0);
});

it('dashboard does not show income from other restaurants', function () {
    $otherTable = Table::factory()->create(['user_id' => $this->other->id]);

    Order::factory()->create([
        'table_id'       => $otherTable->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 100.00,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->assertOk()
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(0.0);
});

// ─── Cálculo de ingresos ───────────────────────────────────────────────────────

it('calculates cash income correctly for the period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 30.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 20.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(50.0);
    expect((int) $summary->cash_count)->toBe(2);
});

it('calculates card income correctly for the period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'card',
        'payment_status' => 'paid',
        'total'          => 45.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->viewData('summary');

    expect((float) $summary->card_revenue)->toBe(45.0);
    expect((int) $summary->card_count)->toBe(1);
});

it('calculates card tips correctly for the period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'card',
        'payment_status' => 'paid',
        'total'          => 40.00,
        'tip'            => 5.00,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->viewData('summary');

    expect((float) $summary->card_tip_revenue)->toBe(5.0);
});

it('calculates total income correctly for the period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 30.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'card',
        'payment_status' => 'paid',
        'total'          => 40.00,
        'tip'            => 5.00,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->viewData('summary');

    expect((int) $summary->total_count)->toBe(2);
    // cash(30) + card(40) + card_tip(5) = 75
    $grand = (float) $summary->cash_revenue
           + (float) $summary->card_revenue
           + (float) $summary->cash_tip_revenue
           + (float) $summary->card_tip_revenue;
    expect($grand)->toBe(75.0);
});

// ─── Solo pedidos pagados ──────────────────────────────────────────────────────

it('only counts paid orders not pending or cancelled', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 50.00,
        'updated_at'     => now(),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'pending',
        'total'          => 99.00,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(50.0);
    expect((int) $summary->total_count)->toBe(1);
});

// ─── Filtros de período ────────────────────────────────────────────────────────

it('filters income by today period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 40.00,
        'updated_at'     => now()->midDay(),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 99.00,
        'updated_at'     => now()->subDay()->midDay(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'today']))
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(40.0);
});

it('filters income by this week period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 60.00,
        'updated_at'     => now()->startOfWeek()->addHours(12),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 99.00,
        'updated_at'     => now()->subWeek(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'week']))
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(60.0);
});

it('filters income by this month period', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 70.00,
        'updated_at'     => now()->startOfMonth()->addDays(5),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 99.00,
        'updated_at'     => now()->subMonth(),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(70.0);
});

it('filters income by custom date range', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 80.00,
        'updated_at'     => Carbon::parse('2024-03-15 12:00:00'),
    ]);
    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 99.00,
        'updated_at'     => Carbon::parse('2024-04-01 12:00:00'),
    ]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', [
                        'period' => 'custom',
                        'from'   => '2024-03-01',
                        'to'     => '2024-03-31',
                    ]))
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(80.0);
});

// ─── Staff con admin_id (sub-gerente) ─────────────────────────────────────────

it('staff admin can access dashboard and sees their admin restaurant data', function () {
    $subAdmin = User::factory()->admin()->staffOf($this->admin)->create();

    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 55.00,
        'updated_at'     => now(),
    ]);

    $summary = $this->actingAs($subAdmin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->assertOk()
                    ->viewData('summary');

    expect((float) $summary->cash_revenue)->toBe(55.0);
});

// ─── Bloque 9.2: Mesa más rentable ────────────────────────────────────────────

it('shows the table with highest revenue for the period', function () {
    $tableA = Table::factory()->create(['user_id' => $this->admin->id, 'name' => 'Mesa 1']);
    $tableB = Table::factory()->create(['user_id' => $this->admin->id, 'name' => 'Mesa 2']);

    Order::factory()->create([
        'table_id' => $tableA->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 30.00, 'updated_at' => now(),
    ]);
    Order::factory()->create([
        'table_id' => $tableB->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 80.00, 'updated_at' => now(),
    ]);

    $topTable = $this->actingAs($this->admin)
                     ->get(route('dashboard', ['period' => 'month']))
                     ->assertOk()
                     ->viewData('topTable');

    expect($topTable->table_name)->toBe('Mesa 2');
    expect((float) $topTable->table_revenue)->toBe(80.0);
});

it('top table calculation only includes the restaurants tables', function () {
    $myTable    = Table::factory()->create(['user_id' => $this->admin->id, 'name' => 'Mi Mesa']);
    $otherTable = Table::factory()->create(['user_id' => $this->other->id, 'name' => 'Mesa Ajena']);

    Order::factory()->create([
        'table_id' => $myTable->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 40.00, 'updated_at' => now(),
    ]);
    Order::factory()->create([
        'table_id' => $otherTable->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 200.00, 'updated_at' => now(),
    ]);

    $topTable = $this->actingAs($this->admin)
                     ->get(route('dashboard', ['period' => 'month']))
                     ->assertOk()
                     ->viewData('topTable');

    expect($topTable->table_name)->toBe('Mi Mesa');
});

it('top table calculation only counts paid orders', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'name' => 'Mesa 1']);

    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 50.00, 'updated_at' => now(),
    ]);
    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'cash',
        'payment_status' => 'pending', 'total' => 999.00, 'updated_at' => now(),
    ]);

    $topTable = $this->actingAs($this->admin)
                     ->get(route('dashboard', ['period' => 'month']))
                     ->assertOk()
                     ->viewData('topTable');

    expect((float) $topTable->table_revenue)->toBe(50.0);
    expect((int) $topTable->table_order_count)->toBe(1);
});

it('returns null top table when no paid orders exist for the period', function () {
    $topTable = $this->actingAs($this->admin)
                     ->get(route('dashboard', ['period' => 'month']))
                     ->assertOk()
                     ->viewData('topTable');

    expect($topTable)->toBeNull();
});

// ─── Bloque 9.3: Platos más pedidos ───────────────────────────────────────────

it('shows top ordered products for the period', function () {
    $table    = Table::factory()->create(['user_id' => $this->admin->id]);
    $category = \App\Models\Category::factory()->create(['user_id' => $this->admin->id, 'name' => 'Entrantes']);
    $product  = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $category->id, 'name' => 'Croquetas']);
    $order    = Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'updated_at' => now()]);

    \App\Models\OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 3, 'price' => 6.00]);

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect($topProducts)->toHaveCount(1);
    expect($topProducts->first()->product_name)->toBe('Croquetas');
    expect((int) $topProducts->first()->times_ordered)->toBe(3);
    expect((float) $topProducts->first()->product_revenue)->toBe(18.0);
});

it('top products only includes the restaurants products', function () {
    $myTable    = Table::factory()->create(['user_id' => $this->admin->id]);
    $otherTable = Table::factory()->create(['user_id' => $this->other->id]);

    $myCat    = \App\Models\Category::factory()->create(['user_id' => $this->admin->id]);
    $otherCat = \App\Models\Category::factory()->create(['user_id' => $this->other->id]);

    $myProduct    = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $myCat->id, 'name' => 'Mi Plato']);
    $otherProduct = \App\Models\Product::factory()->create(['user_id' => $this->other->id, 'category_id' => $otherCat->id, 'name' => 'Plato Ajeno']);

    $myOrder    = Order::factory()->create(['table_id' => $myTable->id, 'payment_status' => 'paid', 'updated_at' => now()]);
    $otherOrder = Order::factory()->create(['table_id' => $otherTable->id, 'payment_status' => 'paid', 'updated_at' => now()]);

    \App\Models\OrderItem::factory()->create(['order_id' => $myOrder->id, 'product_id' => $myProduct->id, 'quantity' => 1, 'price' => 10.00]);
    \App\Models\OrderItem::factory()->create(['order_id' => $otherOrder->id, 'product_id' => $otherProduct->id, 'quantity' => 5, 'price' => 10.00]);

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect($topProducts)->toHaveCount(1);
    expect($topProducts->first()->product_name)->toBe('Mi Plato');
});

it('top products are ordered by quantity descending', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);
    $cat   = \App\Models\Category::factory()->create(['user_id' => $this->admin->id]);

    $productA = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $cat->id, 'name' => 'Plato A']);
    $productB = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $cat->id, 'name' => 'Plato B']);

    $order = Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'updated_at' => now()]);

    \App\Models\OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $productA->id, 'quantity' => 2, 'price' => 10.00]);
    \App\Models\OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $productB->id, 'quantity' => 7, 'price' => 10.00]);

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect($topProducts->first()->product_name)->toBe('Plato B');
    expect($topProducts->last()->product_name)->toBe('Plato A');
});

it('top products calculation only counts paid orders', function () {
    $table   = Table::factory()->create(['user_id' => $this->admin->id]);
    $cat     = \App\Models\Category::factory()->create(['user_id' => $this->admin->id]);
    $product = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $cat->id]);

    $paid    = Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid',    'updated_at' => now()]);
    $pending = Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'pending', 'updated_at' => now()]);

    \App\Models\OrderItem::factory()->create(['order_id' => $paid->id,    'product_id' => $product->id, 'quantity' => 2, 'price' => 5.00]);
    \App\Models\OrderItem::factory()->create(['order_id' => $pending->id, 'product_id' => $product->id, 'quantity' => 9, 'price' => 5.00]);

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect((int) $topProducts->first()->times_ordered)->toBe(2);
});

it('top products excludes tapas category products', function () {
    $table     = Table::factory()->create(['user_id' => $this->admin->id]);
    $normalCat = \App\Models\Category::factory()->create(['user_id' => $this->admin->id, 'name' => 'Entrantes']);
    $tapasCat  = \App\Models\Category::factory()->create(['user_id' => $this->admin->id, 'name' => 'Tapas']);

    $normalProduct = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $normalCat->id, 'name' => 'Croquetas']);
    $tapasProduct  = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $tapasCat->id,  'name' => 'Tapa de queso']);

    $order = Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'updated_at' => now()]);

    \App\Models\OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $normalProduct->id, 'quantity' => 1, 'price' => 8.00]);
    \App\Models\OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $tapasProduct->id,  'quantity' => 5, 'price' => 1.50]);

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    $names = $topProducts->pluck('product_name')->toArray();
    expect($names)->toContain('Croquetas');
    expect($names)->not->toContain('Tapa de queso');
});

it('top products does not include products from other restaurants', function () {
    $otherTable   = Table::factory()->create(['user_id' => $this->other->id]);
    $otherCat     = \App\Models\Category::factory()->create(['user_id' => $this->other->id]);
    $otherProduct = \App\Models\Product::factory()->create(['user_id' => $this->other->id, 'category_id' => $otherCat->id]);
    $otherOrder   = Order::factory()->create(['table_id' => $otherTable->id, 'payment_status' => 'paid', 'updated_at' => now()]);

    \App\Models\OrderItem::factory()->create(['order_id' => $otherOrder->id, 'product_id' => $otherProduct->id, 'quantity' => 10, 'price' => 10.00]);

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect($topProducts)->toBeEmpty();
});

it('returns empty list when no paid orders exist for the period', function () {
    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect($topProducts)->toBeEmpty();
});

it('limits results to top 10 products', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);
    $cat   = \App\Models\Category::factory()->create(['user_id' => $this->admin->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'updated_at' => now()]);

    foreach (range(1, 11) as $i) {
        $product = \App\Models\Product::factory()->create(['user_id' => $this->admin->id, 'category_id' => $cat->id]);
        \App\Models\OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $i, 'price' => 5.00]);
    }

    $topProducts = $this->actingAs($this->admin)
                        ->get(route('dashboard', ['period' => 'month']))
                        ->assertOk()
                        ->viewData('topProducts');

    expect($topProducts)->toHaveCount(10);
});

it('top table changes correctly when period filter changes', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'name' => 'Mesa 1']);

    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 60.00,
        'updated_at' => now()->subMonth()->startOfDay()->addHours(12),
    ]);

    $topTableMonth = $this->actingAs($this->admin)
                          ->get(route('dashboard', ['period' => 'month']))
                          ->assertOk()
                          ->viewData('topTable');

    expect($topTableMonth)->toBeNull();

    $topTableCustom = $this->actingAs($this->admin)
                           ->get(route('dashboard', [
                               'period' => 'custom',
                               'from'   => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                               'to'     => now()->subMonth()->endOfMonth()->format('Y-m-d'),
                           ]))
                           ->assertOk()
                           ->viewData('topTable');

    expect((float) $topTableCustom->table_revenue)->toBe(60.0);
});

it('top table does not include tables from other restaurants', function () {
    $otherTable = Table::factory()->create(['user_id' => $this->other->id]);

    Order::factory()->create([
        'table_id' => $otherTable->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 500.00, 'updated_at' => now(),
    ]);

    $topTable = $this->actingAs($this->admin)
                     ->get(route('dashboard', ['period' => 'month']))
                     ->assertOk()
                     ->viewData('topTable');

    expect($topTable)->toBeNull();
});

// ─── Bloque 9.4: Horas punta y ticket medio ───────────────────────────────────

it('returns peak hours data in the view', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_status' => 'paid',
        'total'          => 20.00,
        'updated_at'     => now()->startOfMonth()->addDays(2)->addHours(14),
    ]);

    $peakHours = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('peakHours');

    expect($peakHours)->not->toBeEmpty();
    expect((int) $peakHours->first()->hour)->toBe(14);
});

it('peak hours returns top 3 hours ordered by order count descending', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);
    $day   = now()->startOfMonth()->addDays(2);

    // Hour 14: 3 orders, Hour 20: 2 orders, Hour 9: 1 order (4th), Hour 11: 4 orders (1st)
    foreach (range(1, 3) as $_) {
        Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'total' => 10.00, 'updated_at' => $day->copy()->addHours(14)]);
    }
    foreach (range(1, 2) as $_) {
        Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'total' => 10.00, 'updated_at' => $day->copy()->addHours(20)]);
    }
    Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'total' => 10.00, 'updated_at' => $day->copy()->addHours(9)]);
    foreach (range(1, 4) as $_) {
        Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid', 'total' => 10.00, 'updated_at' => $day->copy()->addHours(11)]);
    }

    $peakHours = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('peakHours');

    expect($peakHours)->toHaveCount(3);
    expect((int) $peakHours->first()->order_count)->toBe(4);
    expect((int) $peakHours->first()->hour)->toBe(11);
    expect((int) $peakHours->get(1)->order_count)->toBe(3);
    expect((int) $peakHours->last()->order_count)->toBe(2);
});

it('peak hours returns empty collection when no paid orders exist', function () {
    $peakHours = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('peakHours');

    expect($peakHours)->toBeEmpty();
});

it('peak hours only counts paid orders', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);
    $at    = now()->startOfMonth()->addDays(2)->addHours(14);

    Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'paid',    'total' => 10.00, 'updated_at' => $at]);
    Order::factory()->create(['table_id' => $table->id, 'payment_status' => 'pending', 'total' => 10.00, 'updated_at' => $at]);

    $peakHours = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('peakHours');

    expect($peakHours)->toHaveCount(1);
    expect((int) $peakHours->first()->order_count)->toBe(1);
});

it('peak hours only includes tables from the same restaurant', function () {
    $myTable    = Table::factory()->create(['user_id' => $this->admin->id]);
    $otherTable = Table::factory()->create(['user_id' => $this->other->id]);
    $at         = now()->startOfMonth()->addDays(2)->addHours(14);

    Order::factory()->create(['table_id' => $myTable->id,    'payment_status' => 'paid', 'total' => 10.00, 'updated_at' => $at]);
    Order::factory()->create(['table_id' => $otherTable->id, 'payment_status' => 'paid', 'total' => 10.00, 'updated_at' => $at]);

    $peakHours = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('peakHours');

    expect($peakHours)->toHaveCount(1);
    expect((int) $peakHours->first()->order_count)->toBe(1);
});

it('calculates average ticket correctly', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 30.00, 'tip' => 0, 'updated_at' => now(),
    ]);
    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'card',
        'payment_status' => 'paid', 'total' => 50.00, 'tip' => 5.00, 'updated_at' => now(),
    ]);

    $avgTicket = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('avgTicket');

    // (30 + 50 + 5) / 2 = 42.5
    expect((float) $avgTicket)->toBe(42.5);
});

it('returns zero average ticket when no paid orders exist for the period', function () {
    $avgTicket = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('avgTicket');

    expect((float) $avgTicket)->toBe(0.0);
});

it('average ticket updates correctly when period filter changes', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'cash', 'payment_status' => 'paid',
        'total' => 60.00, 'tip' => 0,
        'updated_at' => now()->subMonth()->startOfDay()->addHours(12),
    ]);

    $avgTicketMonth = $this->actingAs($this->admin)
                           ->get(route('dashboard', ['period' => 'month']))
                           ->assertOk()
                           ->viewData('avgTicket');

    expect((float) $avgTicketMonth)->toBe(0.0);

    $avgTicketCustom = $this->actingAs($this->admin)
                            ->get(route('dashboard', [
                                'period' => 'custom',
                                'from'   => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                                'to'     => now()->subMonth()->endOfMonth()->format('Y-m-d'),
                            ]))
                            ->assertOk()
                            ->viewData('avgTicket');

    expect((float) $avgTicketCustom)->toBe(60.0);
});

// ─── Cobro partido ─────────────────────────────────────────────────────────────

it('split payment orders appear in split_count not cash or card count', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    $order = Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'split',
        'payment_status' => 'paid',
        'total'          => 40.00,
        'tip'            => 2.00,
        'updated_at'     => now(),
    ]);

    OrderPayment::factory()->create(['order_id' => $order->id, 'method' => 'card', 'amount' => 30.00, 'tip' => 2.00]);
    OrderPayment::factory()->create(['order_id' => $order->id, 'method' => 'cash', 'amount' => 10.00, 'tip' => 0]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->assertOk()
                    ->viewData('summary');

    expect((int) $summary->cash_count)->toBe(0);
    expect((int) $summary->card_count)->toBe(0);
    expect((int) $summary->split_count)->toBe(1);
    expect((int) $summary->total_count)->toBe(1);
});

it('split payment revenue and tips are broken down by method in summary', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    $order = Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'split',
        'payment_status' => 'paid',
        'total'          => 50.00,
        'tip'            => 5.00,
        'updated_at'     => now(),
    ]);

    OrderPayment::factory()->create(['order_id' => $order->id, 'method' => 'card', 'amount' => 35.00, 'tip' => 3.00]);
    OrderPayment::factory()->create(['order_id' => $order->id, 'method' => 'cash', 'amount' => 15.00, 'tip' => 2.00]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->assertOk()
                    ->viewData('summary');

    expect((float) $summary->split_card_revenue)->toBe(35.0);
    expect((float) $summary->split_cash_revenue)->toBe(15.0);
    expect((float) $summary->split_card_tip_revenue)->toBe(3.0);
    expect((float) $summary->split_cash_tip_revenue)->toBe(2.0);
});

it('split revenue is included in grand total', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 20.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);

    $splitOrder = Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'split',
        'payment_status' => 'paid',
        'total'          => 40.00,
        'tip'            => 2.00,
        'updated_at'     => now(),
    ]);

    OrderPayment::factory()->create(['order_id' => $splitOrder->id, 'method' => 'card', 'amount' => 30.00, 'tip' => 2.00]);
    OrderPayment::factory()->create(['order_id' => $splitOrder->id, 'method' => 'cash', 'amount' => 10.00, 'tip' => 1.00]);

    $grand = $this->actingAs($this->admin)
                  ->get(route('dashboard', ['period' => 'month']))
                  ->assertOk()
                  ->viewData('grand');

    // cash(20) + split_card(30) + split_cash(10) + split_card_tip(2) + split_cash_tip(1) = 63
    expect((float) $grand)->toBe(63.0);
});

it('split revenue from other restaurants is not counted', function () {
    $otherTable = Table::factory()->create(['user_id' => $this->other->id]);

    $otherOrder = Order::factory()->create([
        'table_id'       => $otherTable->id,
        'payment_method' => 'split',
        'payment_status' => 'paid',
        'total'          => 100.00,
        'updated_at'     => now(),
    ]);

    OrderPayment::factory()->create(['order_id' => $otherOrder->id, 'method' => 'card', 'amount' => 70.00]);
    OrderPayment::factory()->create(['order_id' => $otherOrder->id, 'method' => 'cash', 'amount' => 30.00]);

    $summary = $this->actingAs($this->admin)
                    ->get(route('dashboard', ['period' => 'month']))
                    ->assertOk()
                    ->viewData('summary');

    expect((float) $summary->split_cash_revenue)->toBe(0.0);
    expect((float) $summary->split_card_revenue)->toBe(0.0);
    expect((int) $summary->split_count)->toBe(0);
});

it('average ticket includes split orders', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total'          => 30.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);

    $splitOrder = Order::factory()->create([
        'table_id'       => $table->id,
        'payment_method' => 'split',
        'payment_status' => 'paid',
        'total'          => 50.00,
        'tip'            => 0,
        'updated_at'     => now(),
    ]);

    OrderPayment::factory()->create(['order_id' => $splitOrder->id, 'method' => 'card', 'amount' => 30.00, 'tip' => 0]);
    OrderPayment::factory()->create(['order_id' => $splitOrder->id, 'method' => 'cash', 'amount' => 20.00, 'tip' => 0]);

    $avgTicket = $this->actingAs($this->admin)
                      ->get(route('dashboard', ['period' => 'month']))
                      ->assertOk()
                      ->viewData('avgTicket');

    // (cash 30 + split_card 30 + split_cash 20) / 2 orders = 40
    expect((float) $avgTicket)->toBe(40.0);
});
