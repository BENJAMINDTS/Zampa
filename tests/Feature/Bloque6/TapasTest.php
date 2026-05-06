<?php

/**
 * @author AyrtonAlania
 * @author BenjaminDTS
 */

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Table;
use App\Models\TapaConfig;
use App\Models\User;
use Illuminate\Support\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $plan        = \App\Models\Plan::factory()->create();
    $this->user  = User::factory()->create(['plan_id' => $plan->id, 'role' => 'admin']);
    $this->other = User::factory()->create(['plan_id' => $plan->id, 'role' => 'admin']);
});

// ─── Acceso y autenticación ───────────────────────────────────────────────────

it('redirects unauthenticated user away from tapas config', function () {
    $this->get(route('tapas.edit'))
         ->assertRedirect(route('login'));
});

it('gerente can access tapas configuration page', function () {
    $this->actingAs($this->user)
         ->get(route('tapas.edit'))
         ->assertOk()
         ->assertViewIs('tapas.edit');
});

it('creates a default TapaConfig on first visit', function () {
    $this->assertDatabaseMissing('tapa_configs', ['user_id' => $this->user->id]);

    $this->actingAs($this->user)
         ->get(route('tapas.edit'));

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'        => $this->user->id,
        'tapas_enabled'  => 0,
        'tapas_free'     => 1,
        'max_tapa_variants' => 3,
    ]);
});

// ─── Activar / desactivar tapas ───────────────────────────────────────────────

it('gerente can enable tapas', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertRedirect(route('tapas.edit'));

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'       => $this->user->id,
        'tapas_enabled' => 1,
    ]);
});

it('gerente can disable tapas', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '0',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertRedirect(route('tapas.edit'));

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'       => $this->user->id,
        'tapas_enabled' => 0,
    ]);
});

// ─── Gratuitas / de pago ──────────────────────────────────────────────────────

it('gerente can set tapas as free', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 2,
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();

    expect($config->tapas_free)->toBeTrue()
        ->and($config->tapa_price)->toBeNull();
});

it('gerente can set tapas with price', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'max_tapa_variants' => 2,
             'tapa_price'        => '1.50',
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();

    expect($config->tapas_free)->toBeFalse()
        ->and((float) $config->tapa_price)->toBe(1.50);
});

it('tapa_price is cleared when tapas_free is switched to true', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'tapa_price'    => 2.00,
    ]);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ]);

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'    => $this->user->id,
        'tapa_price' => null,
    ]);
});

it('fails update when tapas are paid and tapa_price is missing', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'max_tapa_variants' => 3,
         ])
         ->assertSessionHasErrors('tapa_price');
});

it('fails update when tapas are paid and tapa_price is invalid', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'max_tapa_variants' => 3,
             'tapa_price'        => 'abc',
         ])
         ->assertSessionHasErrors('tapa_price');
});

// ─── Variantes máximas ────────────────────────────────────────────────────────

it('max tapa variants limit is respected in update', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 5,
         ]);

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'           => $this->user->id,
        'max_tapa_variants' => 5,
    ]);
});

it('fails update when max_tapa_variants exceeds 20', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 99,
         ])
         ->assertSessionHasErrors('max_tapa_variants');
});

it('fails update when max_tapa_variants is below 1', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 0,
         ])
         ->assertSessionHasErrors('max_tapa_variants');
});

// ─── Multitenancy ─────────────────────────────────────────────────────────────

it('each restaurant has its own independent tapa config', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 4,
         ]);

    $this->actingAs($this->other)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '0',
             'tapas_free'        => '0',
             'max_tapa_variants' => 2,
             'tapa_price'        => '2.00',
         ]);

    $userConfig  = TapaConfig::where('user_id', $this->user->id)->first();
    $otherConfig = TapaConfig::where('user_id', $this->other->id)->first();

    expect($userConfig->tapas_enabled)->toBeTrue()
        ->and($otherConfig->tapas_enabled)->toBeFalse();
});

// ─── Recuento de bebidas / tapas disponibles ──────────────────────────────────

it('tapas count equals total bar item quantity ordered at that table', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $table = Table::factory()->create(['user_id' => $this->user->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);

    OrderItem::factory()->create([
        'order_id'    => $order->id,
        'destination' => 'bar',
        'quantity'    => 3,
    ]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('barItemsCount'))->toBe(3);
});

it('no tapas shown when tapas are disabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => false,
    ]);

    $table = Table::factory()->create(['user_id' => $this->user->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 2]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('tapaConfig'))->toBeNull()
        ->and($response->viewData('barItemsCount'))->toBe(0);
});

it('no tapas shown when zero bar items ordered', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $table = Table::factory()->create(['user_id' => $this->user->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'kitchen', 'quantity' => 2]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('barItemsCount'))->toBe(0);
});

it('only counts bar items from active orders excluding closed ones', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $table  = Table::factory()->create(['user_id' => $this->user->id]);

    $active = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    $closed = Order::factory()->create(['table_id' => $table->id, 'status' => 'closed']);
    $served = Order::factory()->create(['table_id' => $table->id, 'status' => 'served']);

    OrderItem::factory()->create(['order_id' => $active->id, 'destination' => 'bar', 'quantity' => 2]);
    OrderItem::factory()->create(['order_id' => $closed->id, 'destination' => 'bar', 'quantity' => 5]);
    OrderItem::factory()->create(['order_id' => $served->id, 'destination' => 'bar', 'quantity' => 3]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    // served orders still count; only closed is excluded
    expect($response->viewData('barItemsCount'))->toBe(5);
});

it('waiter role cannot access tapas config', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($waiter)
         ->get(route('tapas.edit'))
         ->assertForbidden();
});

it('kitchen role cannot access tapas config', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);

    $this->actingAs($kitchen)
         ->get(route('tapas.edit'))
         ->assertForbidden();
});

it('flash success message shown after saving tapa config', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertSessionHas('success');
});

// ─── Categoría automática de Tapas ───────────────────────────────────────────

it('creates Tapas category when tapas are enabled for the first time', function () {
    $this->assertDatabaseMissing('categories', ['user_id' => $this->user->id, 'name' => 'Tapas']);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $this->user->id,
        'name'    => 'Tapas',
    ]);
});

it('creates Tapas category with destination kitchen', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ]);

    $this->assertDatabaseHas('categories', [
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);
});

it('does not create duplicate Tapas category if already exists', function () {
    Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ]);

    expect(Category::where('user_id', $this->user->id)->where('name', 'Tapas')->count())->toBe(1);
});

it('does not delete Tapas category when tapas are disabled', function () {
    Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);

    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '0',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $this->user->id,
        'name'    => 'Tapas',
    ]);
});

// ─── Tapa extra ───────────────────────────────────────────────────────────────

it('fails to save extra tapa enabled without extra tapa price', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'      => '1',
             'tapas_free'         => '1',
             'max_tapa_variants'  => 3,
             'extra_tapa_enabled' => '1',
             // extra_tapa_price ausente
         ])
         ->assertSessionHasErrors('extra_tapa_price');
});

it('allows saving extra tapa disabled without extra tapa price', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'      => '1',
             'tapas_free'         => '1',
             'max_tapa_variants'  => 3,
             'extra_tapa_enabled' => '0',
         ])
         ->assertSessionMissing('errors')
         ->assertRedirect(route('tapas.edit'));
});

it('saves extra tapa config correctly', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'      => '1',
             'tapas_free'         => '1',
             'max_tapa_variants'  => 3,
             'extra_tapa_enabled' => '1',
             'extra_tapa_price'   => '2.50',
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();

    expect($config->extra_tapa_enabled)->toBeTrue()
        ->and((float) $config->extra_tapa_price)->toBe(2.50);
});

it('clears extra tapa price when extra tapa is disabled', function () {
    TapaConfig::factory()->create([
        'user_id'            => $this->user->id,
        'tapas_enabled'      => true,
        'extra_tapa_enabled' => true,
        'extra_tapa_price'   => 2.00,
    ]);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'      => '1',
             'tapas_free'         => '1',
             'max_tapa_variants'  => 3,
             'extra_tapa_enabled' => '0',
         ]);

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'          => $this->user->id,
        'extra_tapa_price' => null,
    ]);
});

// ─── Horario de cocina (multi-tramo) ─────────────────────────────────────────

it('saves a single kitchen schedule slot correctly', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'schedules'         => [
                 ['opens_at' => '10:00', 'closes_at' => '16:00'],
             ],
         ])
         ->assertRedirect(route('tapas.edit'));

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->schedules()->count())->toBe(1);
    expect($config->schedules()->first()->opens_at)->toStartWith('10:00');
});

it('saves multiple kitchen schedule slots correctly', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'schedules'         => [
                 ['opens_at' => '13:00', 'closes_at' => '16:30'],
                 ['opens_at' => '20:00', 'closes_at' => '23:30'],
             ],
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->schedules()->count())->toBe(2);
});

it('replaces existing schedules on update', function () {
    $config = TapaConfig::factory()->create(['user_id' => $this->user->id, 'tapas_enabled' => true]);
    $config->schedules()->create(['opens_at' => '09:00', 'closes_at' => '14:00']);
    $config->schedules()->create(['opens_at' => '19:00', 'closes_at' => '23:00']);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'schedules'         => [
                 ['opens_at' => '12:00', 'closes_at' => '15:00'],
             ],
         ]);

    $config->refresh();
    expect($config->schedules()->count())->toBe(1);
    expect($config->schedules()->first()->opens_at)->toStartWith('12:00');
});

it('allows no schedules meaning kitchen always open', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertRedirect(route('tapas.edit'));

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->schedules()->count())->toBe(0);
});

it('fails when a schedule slot is missing closes_at', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'schedules'         => [
                 ['opens_at' => '10:00'],
             ],
         ])
         ->assertSessionHasErrors('schedules.0.closes_at');
});

it('fails when a schedule slot has invalid time format', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'schedules'         => [
                 ['opens_at' => 'not-a-time', 'closes_at' => '23:00'],
             ],
         ])
         ->assertSessionHasErrors('schedules.0.opens_at');
});

it('fails when schedules array exceeds 10 slots', function () {
    $slots = array_fill(0, 11, ['opens_at' => '10:00', 'closes_at' => '11:00']);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'schedules'         => $slots,
         ])
         ->assertSessionHasErrors('schedules');
});

it('menu hides kitchen categories when kitchen is closed', function () {
    Carbon::setTestNow('2026-05-03 15:00:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id, 'tapas_enabled' => true]);
    $config->schedules()->create(['opens_at' => '17:00:00', 'closes_at' => '23:00:00']);

    $table           = Table::factory()->create(['user_id' => $this->user->id]);
    $kitchenCategory = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'kitchen']);
    $barCategory     = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);

    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $kitchenCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $barCategory->id, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('kitchenOpen'))->toBeFalse();

    $categories   = $response->viewData('categories');
    $destinations = $categories->pluck('destination')->unique()->values()->toArray();
    expect($destinations)->not->toContain('kitchen');

    Carbon::setTestNow();
});

it('menu shows all categories when no schedule is configured', function () {
    TapaConfig::factory()->create(['user_id' => $this->user->id, 'tapas_enabled' => true]);
    // Sin schedules → cocina siempre abierta

    $table           = Table::factory()->create(['user_id' => $this->user->id]);
    $kitchenCategory = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'kitchen']);
    $barCategory     = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);

    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $kitchenCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $barCategory->id, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('kitchenOpen'))->toBeTrue();

    $categories   = $response->viewData('categories');
    $destinations = $categories->pluck('destination')->unique()->sort()->values()->toArray();
    expect($destinations)->toContain('kitchen')
        ->and($destinations)->toContain('bar');
});

it('menu is open when current time falls within any of multiple schedules', function () {
    Carbon::setTestNow('2026-05-03 21:00:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id, 'tapas_enabled' => true]);
    // Mediodía: ya cerrado
    $config->schedules()->create(['opens_at' => '13:00:00', 'closes_at' => '16:30:00']);
    // Noche: abierto ahora
    $config->schedules()->create(['opens_at' => '20:00:00', 'closes_at' => '23:30:00']);

    $table       = Table::factory()->create(['user_id' => $this->user->id]);
    $barCategory = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $barCategory->id, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('kitchenOpen'))->toBeTrue();

    Carbon::setTestNow();
});

// ─── Sugerencia de tapa ───────────────────────────────────────────────────────

it('shouldSuggest is true when conditions are met', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 3,
    ]);
    $config->schedules()->create(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 1]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('shouldSuggest'))->toBeTrue();

    Carbon::setTestNow();
});

it('shouldSuggest is false when tapas are disabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => false,
    ]);

    $table = Table::factory()->create(['user_id' => $this->user->id]);
    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 2]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('shouldSuggest'))->toBeFalse();
});

it('shouldSuggest is false when kitchen is closed', function () {
    Carbon::setTestNow('2026-05-03 15:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 3,
    ]);
    $config->schedules()->create(['opens_at' => '17:00:00', 'closes_at' => '23:00:00']);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 1]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('shouldSuggest'))->toBeFalse();

    Carbon::setTestNow();
});

it('shouldSuggest is false when max variants already reached', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 2,
    ]);
    $config->schedules()->create(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $product1     = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    $product2     = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 3]);
    // 2 variantes distintas de tapa ya pedidas (igual al max)
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'kitchen', 'product_id' => $product1->id, 'quantity' => 1]);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'kitchen', 'product_id' => $product2->id, 'quantity' => 1]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('shouldSuggest'))->toBeFalse();

    Carbon::setTestNow();
});

it('tapa products in view are limited to products from Tapas category', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 5,
    ]);
    $config->schedules()->create(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $otherCat     = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'kitchen']);

    $tapaProduct  = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $otherCat->id, 'is_active' => true]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 1]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();

    $tapaProducts = $response->viewData('tapaProducts');
    expect($tapaProducts)->toHaveCount(1)
        ->and($tapaProducts->first()->id)->toBe($tapaProduct->id);

    Carbon::setTestNow();
});

it('tapa products only include active products from Tapas category', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 5,
    ]);
    $config->schedules()->create(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);

    $active   = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => false]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 1]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();

    $tapaProducts = $response->viewData('tapaProducts');
    expect($tapaProducts)->toHaveCount(1)
        ->and($tapaProducts->first()->id)->toBe($active->id);

    Carbon::setTestNow();
});

// ─── Modalidad de precio (price_mode) ────────────────────────────────────────

it('saves price_mode fixed correctly', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'price_mode'        => 'fixed',
             'max_tapa_variants' => 3,
             'tapa_price'        => '1.00',
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->price_mode)->toBe('fixed');
});

it('saves price_mode per_product correctly', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'price_mode'        => 'per_product',
             'max_tapa_variants' => 3,
         ])
         ->assertRedirect(route('tapas.edit'));

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->price_mode)->toBe('per_product')
        ->and($config->tapa_price)->toBeNull();
});

it('fails to save paid tapas with fixed price mode and no global price', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'price_mode'        => 'fixed',
             'max_tapa_variants' => 3,
             // tapa_price ausente
         ])
         ->assertSessionHasErrors('tapa_price');
});

it('allows saving paid tapas with per_product mode and no global price', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'price_mode'        => 'per_product',
             'max_tapa_variants' => 3,
         ])
         ->assertSessionMissing('errors')
         ->assertRedirect(route('tapas.edit'));
});

it('allows saving free tapas without any price regardless of mode', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertSessionMissing('errors')
         ->assertRedirect(route('tapas.edit'));

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->tapa_price)->toBeNull();
});

it('clears tapa_price when mode changes to per_product', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_free'    => false,
        'price_mode'    => 'fixed',
        'tapa_price'    => 2.50,
    ]);

    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '0',
             'price_mode'        => 'per_product',
             'max_tapa_variants' => 3,
         ]);

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'    => $this->user->id,
        'tapa_price' => null,
        'price_mode' => 'per_product',
    ]);
});

it('tapa order item saves resolved price for fixed mode not product price', function () {
    $tapaConfig = TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'price_mode'    => 'fixed',
        'tapa_price'    => 1.50,
    ]);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $tapaProduct  = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $tapaCategory->id,
        'price'       => 9.99,
        'is_active'   => true,
    ]);

    $response = $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $tapaProduct->id, 'quantity' => 1],
        ],
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('order_items', [
        'product_id' => $tapaProduct->id,
        'price'      => 1.50,
    ]);
});

it('tapa order item saves product price for per_product mode', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'price_mode'    => 'per_product',
        'tapa_price'    => null,
    ]);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $tapaProduct  = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $tapaCategory->id,
        'price'       => 3.75,
        'is_active'   => true,
    ]);

    $response = $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $tapaProduct->id, 'quantity' => 1],
        ],
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('order_items', [
        'product_id' => $tapaProduct->id,
        'price'      => 3.75,
    ]);
});

it('tapa order item saves 0 when tapas are free', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
        'tapas_free'    => true,
    ]);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $tapaProduct  = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $tapaCategory->id,
        'price'       => 5.00,
        'is_active'   => true,
    ]);

    $response = $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $tapaProduct->id, 'quantity' => 1],
        ],
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('order_items', [
        'product_id' => $tapaProduct->id,
        'price'      => 0,
    ]);
});

it('non-tapa products always use product price regardless of tapa config', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'price_mode'    => 'fixed',
        'tapa_price'    => 1.00,
    ]);

    $table       = Table::factory()->create(['user_id' => $this->user->id]);
    $barCategory = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);
    $drink       = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $barCategory->id,
        'price'       => 2.80,
        'is_active'   => true,
    ]);

    $response = $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [
            ['product_id' => $drink->id, 'quantity' => 1],
        ],
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('order_items', [
        'product_id' => $drink->id,
        'price'      => 2.80,
    ]);
});

// ─── Protección de categoría Tapas ───────────────────────────────────────────

it('blocks renaming Tapas category when tapas_enabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $tapasCategory = Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);

    $this->actingAs($this->user)
         ->put(route('categories.update', $tapasCategory), [
             'name'        => 'Pinchos',
             'destination' => 'kitchen',
         ])
         ->assertSessionHasErrors('name');

    $this->assertDatabaseHas('categories', ['id' => $tapasCategory->id, 'name' => 'Tapas']);
});

it('allows renaming Tapas category when tapas_disabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => false,
    ]);

    $tapasCategory = Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);

    $this->actingAs($this->user)
         ->put(route('categories.update', $tapasCategory), [
             'name'        => 'Pinchos',
             'destination' => 'kitchen',
         ])
         ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', ['id' => $tapasCategory->id, 'name' => 'Pinchos']);
});

it('blocks deleting Tapas category when tapas_enabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $tapasCategory = Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);

    $this->actingAs($this->user)
         ->delete(route('categories.destroy', $tapasCategory))
         ->assertSessionHasErrors('general');

    $this->assertDatabaseHas('categories', ['id' => $tapasCategory->id]);
});

it('allows deleting Tapas category when tapas_disabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => false,
    ]);

    $tapasCategory = Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Tapas',
        'destination' => 'kitchen',
    ]);

    $this->actingAs($this->user)
         ->delete(route('categories.destroy', $tapasCategory))
         ->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $tapasCategory->id]);
});

it('tapas category products are not shown in main menu listing when tapas enabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => true,
    ]);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $barCategory  = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);

    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $barCategory->id, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    $categories = $response->viewData('categories');
    expect($categories->pluck('name')->toArray())->not->toContain('Tapas');
});

it('tapas category products appear in main listing when tapas disabled', function () {
    TapaConfig::factory()->create([
        'user_id'       => $this->user->id,
        'tapas_enabled' => false,
    ]);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();

    $categories = $response->viewData('categories');
    expect($categories->pluck('name')->toArray())->toContain('Tapas');
});

// ─── tapaVariantsUsed counts distinct tapa products ──────────────────────────

it('tapaVariantsUsed counts distinct tapa products in active orders', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 5,
    ]);
    $config->schedules()->create(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']);

    $table        = Table::factory()->create(['user_id' => $this->user->id]);
    $tapaCategory = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tapas', 'destination' => 'kitchen']);
    $product1     = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    $product2     = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $tapaCategory->id, 'is_active' => true]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending']);
    // Misma variante pedida 2 veces: solo cuenta como 1
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'kitchen', 'product_id' => $product1->id, 'quantity' => 2]);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'kitchen', 'product_id' => $product2->id, 'quantity' => 1]);
    OrderItem::factory()->create(['order_id' => $order->id, 'destination' => 'bar', 'quantity' => 3]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('tapaVariantsUsed'))->toBe(2);

    Carbon::setTestNow();
});
