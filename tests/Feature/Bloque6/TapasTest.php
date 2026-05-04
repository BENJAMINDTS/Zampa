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

// ─── Horario de cocina ────────────────────────────────────────────────────────

it('saves kitchen schedule fields correctly', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'      => '1',
             'tapas_free'         => '1',
             'max_tapa_variants'  => 3,
             'kitchen_opens_at'   => '10:00',
             'kitchen_closes_at'  => '23:00',
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();

    expect($config->kitchen_opens_at)->toStartWith('10:00')
        ->and($config->kitchen_closes_at)->toStartWith('23:00');
});

it('allows null kitchen schedule meaning always open', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertRedirect(route('tapas.edit'));

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'          => $this->user->id,
        'kitchen_opens_at' => null,
        'kitchen_closes_at' => null,
    ]);
});

it('fails when only kitchen_opens_at is provided without kitchen_closes_at', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
             'kitchen_opens_at'  => '10:00',
             // kitchen_closes_at ausente
         ])
         ->assertSessionHasErrors('kitchen_closes_at');
});

it('fails when only kitchen_closes_at is provided without kitchen_opens_at', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'      => '1',
             'tapas_free'         => '1',
             'max_tapa_variants'  => 3,
             'kitchen_closes_at'  => '23:00',
             // kitchen_opens_at ausente
         ])
         ->assertSessionHasErrors('kitchen_opens_at');
});

it('menu hides kitchen categories when kitchen is closed', function () {
    Carbon::setTestNow('2026-05-03 15:00:00');

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'kitchen_opens_at'  => '17:00',
        'kitchen_closes_at' => '23:00',
    ]);

    $table           = Table::factory()->create(['user_id' => $this->user->id]);
    $kitchenCategory = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'kitchen']);
    $barCategory     = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);

    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $kitchenCategory->id, 'is_active' => true]);
    Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $barCategory->id, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('kitchenOpen'))->toBeFalse();

    $categories = $response->viewData('categories');
    $destinations = $categories->pluck('destination')->unique()->values()->toArray();
    expect($destinations)->not->toContain('kitchen');

    Carbon::setTestNow();
});

it('menu shows all categories when no schedule is configured', function () {
    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'kitchen_opens_at'  => null,
        'kitchen_closes_at' => null,
    ]);

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

// ─── Sugerencia de tapa ───────────────────────────────────────────────────────

it('shouldSuggest is true when conditions are met', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 3,
        'kitchen_opens_at'  => '10:00',
        'kitchen_closes_at' => '23:00',
    ]);

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

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 3,
        'kitchen_opens_at'  => '17:00',
        'kitchen_closes_at' => '23:00',
    ]);

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

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 2,
        'kitchen_opens_at'  => '10:00',
        'kitchen_closes_at' => '23:00',
    ]);

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

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 5,
        'kitchen_opens_at'  => '10:00',
        'kitchen_closes_at' => '23:00',
    ]);

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

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 5,
        'kitchen_opens_at'  => '10:00',
        'kitchen_closes_at' => '23:00',
    ]);

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

it('tapaVariantsUsed counts distinct tapa products in active orders', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    TapaConfig::factory()->create([
        'user_id'           => $this->user->id,
        'tapas_enabled'     => true,
        'max_tapa_variants' => 5,
        'kitchen_opens_at'  => '10:00',
        'kitchen_closes_at' => '23:00',
    ]);

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
