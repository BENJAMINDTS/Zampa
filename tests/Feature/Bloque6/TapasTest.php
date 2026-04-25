<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\TapaConfig;
use App\Models\User;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);
uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->setupTenants();
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

it('flash success message shown after saving tapa config', function () {
    $this->actingAs($this->user)
         ->put(route('tapas.update'), [
             'tapas_enabled'     => '1',
             'tapas_free'        => '1',
             'max_tapa_variants' => 3,
         ])
         ->assertSessionHas('success');
});
