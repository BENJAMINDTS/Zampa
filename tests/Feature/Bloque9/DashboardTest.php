<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Order;
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

    expect((float) $summary->tip_revenue)->toBe(5.0);
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
    // cash(30) + card(40) + tip(5) = 75
    $grand = (float) $summary->cash_revenue
           + (float) $summary->card_revenue
           + (float) $summary->tip_revenue;
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
                     ->viewData('topTable');

    expect((float) $topTable->table_revenue)->toBe(50.0);
    expect((int) $topTable->table_order_count)->toBe(1);
});

it('returns null top table when no paid orders exist for the period', function () {
    $topTable = $this->actingAs($this->admin)
                     ->get(route('dashboard', ['period' => 'month']))
                     ->viewData('topTable');

    expect($topTable)->toBeNull();
});

it('top table changes correctly when period filter changes', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'name' => 'Mesa 1']);

    Order::factory()->create([
        'table_id' => $table->id, 'payment_method' => 'cash',
        'payment_status' => 'paid', 'total' => 60.00,
        'updated_at' => now()->subMonth()->midDay(),
    ]);

    $topTableMonth = $this->actingAs($this->admin)
                          ->get(route('dashboard', ['period' => 'month']))
                          ->viewData('topTable');

    expect($topTableMonth)->toBeNull();

    $topTableCustom = $this->actingAs($this->admin)
                           ->get(route('dashboard', [
                               'period' => 'custom',
                               'from'   => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                               'to'     => now()->subMonth()->endOfMonth()->format('Y-m-d'),
                           ]))
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
                     ->viewData('topTable');

    expect($topTable)->toBeNull();
});
