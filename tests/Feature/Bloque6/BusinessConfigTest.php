<?php

/**
 * @author BenjaminDTS
 */

use App\Models\Category;
use App\Models\Order;
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

// ─── Acceso ───────────────────────────────────────────────────────────────────

it('redirects unauthenticated user away from business config', function () {
    $this->get(route('negocio.config.edit'))
         ->assertRedirect(route('login'));
});

it('gerente can access business configuration page', function () {
    $this->actingAs($this->user)
         ->get(route('negocio.config.edit'))
         ->assertOk()
         ->assertViewIs('negocio.config');
});

it('creates default TapaConfig with ordering_cutoff_minutes on first visit', function () {
    $this->assertDatabaseMissing('tapa_configs', ['user_id' => $this->user->id]);

    $this->actingAs($this->user)->get(route('negocio.config.edit'));

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 0,
    ]);
});

// ─── Horarios del negocio ─────────────────────────────────────────────────────

it('saves a single business schedule slot correctly', function () {
    $this->actingAs($this->user)
         ->put(route('negocio.config.update'), [
             'max_tapa_variants'  => 3,
             'business_schedules' => [
                 ['opens_at' => '09:00', 'closes_at' => '14:00'],
             ],
         ])
         ->assertRedirect(route('negocio.config.edit'));

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->businessSchedules()->count())->toBe(1);
    expect($config->businessSchedules()->first()->opens_at)->toStartWith('09:00');
    expect($config->businessSchedules()->first()->type)->toBe('business');
});

it('saves multiple business schedule slots correctly', function () {
    $this->actingAs($this->user)
         ->put(route('negocio.config.update'), [
             'max_tapa_variants'  => 3,
             'business_schedules' => [
                 ['opens_at' => '07:00', 'closes_at' => '12:00'],
                 ['opens_at' => '13:00', 'closes_at' => '16:30'],
                 ['opens_at' => '19:00', 'closes_at' => '23:30'],
             ],
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->businessSchedules()->count())->toBe(3);
});

it('business supports multiple open slots in the same day', function () {
    Carbon::setTestNow('2026-05-21 14:30:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '07:00:00', 'closes_at' => '12:00:00']);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']);

    expect($config->isBusinessOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('business is closed when current time is outside all slots', function () {
    Carbon::setTestNow('2026-05-21 12:30:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '07:00:00', 'closes_at' => '12:00:00']);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']);

    expect($config->isBusinessOpen())->toBeFalse();

    Carbon::setTestNow();
});

it('business is open when no schedule is configured', function () {
    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    // Sin tramos → siempre abierto
    expect($config->isBusinessOpen())->toBeTrue();
});

it('maximum of 4 business slots per type is enforced', function () {
    $slots = array_fill(0, 5, ['opens_at' => '10:00', 'closes_at' => '11:00']);

    $this->actingAs($this->user)
         ->put(route('negocio.config.update'), [
             'max_tapa_variants'  => 3,
             'business_schedules' => $slots,
         ])
         ->assertSessionHasErrors('business_schedules');
});

it('menu shows closed message when business is closed', function () {
    Carbon::setTestNow('2026-05-21 12:30:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '07:00:00', 'closes_at' => '12:00:00']);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']);

    $table = Table::factory()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk();
    expect($response->viewData('businessOpen'))->toBeFalse();

    Carbon::setTestNow();
});

it('cannot place order when business is closed via API', function () {
    Carbon::setTestNow('2026-05-21 12:30:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '07:00:00', 'closes_at' => '12:00:00']);

    $table   = Table::factory()->create(['user_id' => $this->user->id]);
    $cat     = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertStatus(422)
      ->assertJsonFragment(['success' => false]);

    Carbon::setTestNow();
});

it('getBusinessNextOpeningTime returns the next opening slot', function () {
    Carbon::setTestNow('2026-05-21 12:30:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '07:00:00', 'closes_at' => '12:00:00']);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']);
    $config->load('businessSchedules');

    expect($config->getBusinessNextOpeningTime())->toBe('13:00');

    Carbon::setTestNow();
});

it('replaces existing business schedules on update', function () {
    $config = TapaConfig::factory()->create(['user_id' => $this->user->id]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '08:00', 'closes_at' => '14:00']);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '18:00', 'closes_at' => '22:00']);

    $this->actingAs($this->user)
         ->put(route('negocio.config.update'), [
             'max_tapa_variants'  => 3,
             'business_schedules' => [
                 ['opens_at' => '10:00', 'closes_at' => '15:00'],
             ],
         ]);

    $config->refresh();
    expect($config->businessSchedules()->count())->toBe(1);
    expect($config->businessSchedules()->first()->opens_at)->toStartWith('10:00');
});

it('business and kitchen schedules are stored independently', function () {
    $this->actingAs($this->user)
         ->put(route('negocio.config.update'), [
             'max_tapa_variants'  => 3,
             'business_schedules' => [
                 ['opens_at' => '09:00', 'closes_at' => '23:00'],
             ],
             'kitchen_schedules' => [
                 ['opens_at' => '13:00', 'closes_at' => '16:00'],
                 ['opens_at' => '20:00', 'closes_at' => '23:00'],
             ],
         ]);

    $config = TapaConfig::where('user_id', $this->user->id)->first();
    expect($config->businessSchedules()->count())->toBe(1);
    expect($config->kitchenSchedules()->count())->toBe(2);
});

// ─── Período de cierre de pedidos ─────────────────────────────────────────────

it('saves ordering_cutoff_minutes correctly', function () {
    $this->actingAs($this->user)
         ->put(route('negocio.config.update'), [
             'max_tapa_variants'       => 3,
             'ordering_cutoff_minutes' => 15,
         ]);

    $this->assertDatabaseHas('tapa_configs', [
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 15,
    ]);
});

it('isOrderingAllowed returns false when within closing period', function () {
    Carbon::setTestNow('2026-05-21 23:20:00');

    $config = TapaConfig::factory()->create([
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 15,
    ]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']);
    $config->load('businessSchedules');

    expect($config->isOrderingAllowed())->toBeFalse();

    Carbon::setTestNow();
});

it('isOrderingAllowed returns true outside closing period', function () {
    Carbon::setTestNow('2026-05-21 22:00:00');

    $config = TapaConfig::factory()->create([
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 15,
    ]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']);
    $config->load('businessSchedules');

    expect($config->isOrderingAllowed())->toBeTrue();

    Carbon::setTestNow();
});

it('closing period of 0 minutes means ordering allowed until close', function () {
    Carbon::setTestNow('2026-05-21 23:29:00');

    $config = TapaConfig::factory()->create([
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 0,
    ]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']);
    $config->load('businessSchedules');

    expect($config->isOrderingAllowed())->toBeTrue();

    Carbon::setTestNow();
});

it('cannot add to cart during closing period via API', function () {
    Carbon::setTestNow('2026-05-21 23:20:00');

    $config = TapaConfig::factory()->create([
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 15,
    ]);
    $config->businessSchedules()->create(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']);

    $table   = Table::factory()->create(['user_id' => $this->user->id]);
    $cat     = Category::factory()->create(['user_id' => $this->user->id, 'destination' => 'bar']);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items'      => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertStatus(422)
      ->assertJsonFragment(['success' => false]);

    Carbon::setTestNow();
});

it('ordering is allowed when business has no schedules configured', function () {
    $config = TapaConfig::factory()->create([
        'user_id'                 => $this->user->id,
        'ordering_cutoff_minutes' => 30,
    ]);
    // Sin tramos → siempre abierto → pedidos permitidos
    expect($config->isOrderingAllowed())->toBeTrue();
});

// ─── Cocina con múltiples tramos ──────────────────────────────────────────────

it('kitchen is open when current time is within a kitchen slot', function () {
    Carbon::setTestNow('2026-05-21 21:00:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id, 'tapas_enabled' => true]);
    $config->kitchenSchedules()->create(['type' => 'kitchen', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']);
    $config->kitchenSchedules()->create(['type' => 'kitchen', 'opens_at' => '20:00:00', 'closes_at' => '23:30:00']);
    $config->load('kitchenSchedules');

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('kitchen is closed when outside all kitchen slots', function () {
    Carbon::setTestNow('2026-05-21 17:00:00');

    $config = TapaConfig::factory()->create(['user_id' => $this->user->id, 'tapas_enabled' => true]);
    $config->kitchenSchedules()->create(['type' => 'kitchen', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']);
    $config->kitchenSchedules()->create(['type' => 'kitchen', 'opens_at' => '20:00:00', 'closes_at' => '23:30:00']);
    $config->load('kitchenSchedules');

    expect($config->isKitchenOpen())->toBeFalse();

    Carbon::setTestNow();
});

it('menu view receives businessOpen and orderingAllowed variables', function () {
    $table = Table::factory()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('menu.show', $table->unique_hash));
    $response->assertOk()
             ->assertViewHas('businessOpen')
             ->assertViewHas('orderingAllowed');
});
