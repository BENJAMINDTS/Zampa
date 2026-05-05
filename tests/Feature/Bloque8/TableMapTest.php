<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Plan;
use App\Models\Table;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $plan         = Plan::factory()->create(['max_tables' => 5]);
    $this->admin  = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $this->other  = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $this->waiter = User::factory()->waiter()->create(['admin_id' => $this->admin->id]);
    $this->plan   = $plan;
});

// ─── Autenticación ────────────────────────────────────────────────────────────

it('redirects unauthenticated user away from map', function () {
    $this->get(route('tables.map'))
         ->assertRedirect(route('login'));
});

it('returns 401 for unauthenticated user on store', function () {
    $this->postJson(route('tables.store'), [])
         ->assertUnauthorized();
});

it('returns 401 for unauthenticated user on updatePosition', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->patchJson(route('tables.updatePosition', $table), [])
         ->assertUnauthorized();
});

it('returns 401 for unauthenticated user on destroy', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->deleteJson(route('tables.destroy', $table))
         ->assertUnauthorized();
});

// ─── Control de rol ───────────────────────────────────────────────────────────

it('returns 403 for non-admin on map page', function () {
    $this->actingAs($this->waiter)
         ->get(route('tables.map'))
         ->assertForbidden();
});

it('returns 403 for non-admin on store', function () {
    $this->actingAs($this->waiter)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa 1',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertForbidden();
});

it('returns 403 for non-admin on updatePosition', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->waiter)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertForbidden();
});

it('returns 403 for non-admin on destroy', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->waiter)
         ->deleteJson(route('tables.destroy', $table))
         ->assertForbidden();
});

// ─── Map (GET /mesas/mapa) ────────────────────────────────────────────────────

it('admin can access map view', function () {
    $this->actingAs($this->admin)
         ->get(route('tables.map'))
         ->assertOk()
         ->assertViewIs('tables.map');
});

it('map passes tables and maxTables to view', function () {
    Table::factory()->for($this->admin)->count(2)->create();

    $response = $this->actingAs($this->admin)
                     ->get(route('tables.map'));

    $response->assertViewHas('tables');
    $response->assertViewHas('maxTables');
    expect($response->viewData('tables'))->toHaveCount(2);
});

it('map only shows tables of the authenticated admin', function () {
    Table::factory()->for($this->admin)->count(3)->create();
    Table::factory()->for($this->other)->count(2)->create();

    $response = $this->actingAs($this->admin)
                     ->get(route('tables.map'));

    expect($response->viewData('tables'))->toHaveCount(3);
});

// ─── Store (POST /mesas) ──────────────────────────────────────────────────────

it('admin creates a table with valid data', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa 1',
             'shape'      => 'square',
             'position_x' => 10,
             'position_y' => 20,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertCreated()
         ->assertJsonPath('success', true)
         ->assertJsonPath('data.name', 'Mesa 1');
});

it('store saves table in database', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Terraza B',
             'shape'      => 'round',
             'position_x' => 50,
             'position_y' => 80,
             'width'      => 100,
             'height'     => 100,
         ]);

    $this->assertDatabaseHas('tables', [
        'name'    => 'Terraza B',
        'user_id' => $this->admin->id,
        'shape'   => 'round',
    ]);
});

it('store assigns user_id of the authenticated admin', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa Owner',
             'shape'      => 'rectangle',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 150,
             'height'     => 90,
         ]);

    $table = Table::where('name', 'Mesa Owner')->first();
    expect($table->user_id)->toBe($this->admin->id);
});

it('store generates unique_hash automatically', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa Hash',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ]);

    $table = Table::where('name', 'Mesa Hash')->first();
    expect($table->unique_hash)->not->toBeNull();
    expect(strlen($table->unique_hash))->toBeGreaterThan(10);
});

it('fails to create table without name', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable();
});

it('fails to create table without shape', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa X',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable();
});

it('fails to create table with invalid shape', function () {
    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa X',
             'shape'      => 'triangle',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable();
});

it('returns 422 with error message when plan limit is reached', function () {
    Table::factory()->for($this->admin)->count(5)->create();

    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa Extra',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable()
         ->assertJsonPath('success', false);
});

// ─── UpdatePosition (PATCH /mesas/{table}/posicion) ──────────────────────────

it('admin can update table position', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 200,
             'position_y' => 150,
             'width'      => 120,
             'height'     => 80,
         ])
         ->assertOk()
         ->assertJsonPath('success', true);

    $this->assertDatabaseHas('tables', [
        'id'         => $table->id,
        'position_x' => 200,
        'position_y' => 150,
        'width'      => 120,
        'height'     => 80,
    ]);
});

it('returns 403 when updating position of another admins table', function () {
    $table = Table::factory()->for($this->other)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertForbidden();
});

it('fails to update position with negative coordinates', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => -10,
             'position_y' => -20,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable();
});

// ─── Destroy (DELETE /mesas/{table}) ─────────────────────────────────────────

it('admin can delete own table', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->admin)
         ->deleteJson(route('tables.destroy', $table))
         ->assertOk()
         ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('tables', ['id' => $table->id]);
});

it('destroy response includes table name in message', function () {
    $table = Table::factory()->for($this->admin)->create(['name' => 'Mesa VIP']);

    $response = $this->actingAs($this->admin)
                     ->deleteJson(route('tables.destroy', $table));

    expect($response->json('message'))->toContain('Mesa VIP');
});

it('returns 403 when deleting another admins table', function () {
    $table = Table::factory()->for($this->other)->create();

    $this->actingAs($this->admin)
         ->deleteJson(route('tables.destroy', $table))
         ->assertForbidden();
});

it('returns 404 when deleting a non-existent table', function () {
    $this->actingAs($this->admin)
         ->deleteJson(route('tables.destroy', 99999))
         ->assertNotFound();
});

// ─── Unicidad de hashes ───────────────────────────────────────────────────────

it('each created table has a unique_hash', function () {
    $table = Table::factory()->for($this->admin)->create();
    expect($table->unique_hash)->not->toBeNull();
});

it('unique hashes are unique across all tables', function () {
    $tables = Table::factory()->for($this->admin)->count(5)->create();
    $hashes = $tables->pluck('unique_hash')->unique();

    expect($hashes)->toHaveCount(5);
});

it('public menu route returns 200 with valid table hash', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->get(route('menu.show', $table->unique_hash))
         ->assertOk();
});

it('public menu route returns 404 with invalid hash', function () {
    $this->get(route('menu.show', 'hash-invalido-xyz'))
         ->assertNotFound();
});

// ─── Límite del plan (Bloque 8.2) ─────────────────────────────────────────────

it('allows creation when one slot remains in plan', function () {
    Table::factory()->for($this->admin)->count(4)->create();

    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Última Mesa',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertCreated()
         ->assertJsonPath('success', true);
});

it('blocks creation at exactly the plan limit', function () {
    Table::factory()->for($this->admin)->count(5)->create();

    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa Extra',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable()
         ->assertJsonPath('success', false);

    expect(Table::where('user_id', $this->admin->id)->count())->toBe(5);
});

it('limit error message includes the plan max_tables value', function () {
    Table::factory()->for($this->admin)->count(5)->create();

    $response = $this->actingAs($this->admin)
                     ->postJson(route('tables.store'), [
                         'name'       => 'Mesa X',
                         'shape'      => 'square',
                         'position_x' => 0,
                         'position_y' => 0,
                         'width'      => 100,
                         'height'     => 100,
                     ]);

    expect($response->json('message'))->toContain('5');
});

it('counts only own tables towards the plan limit', function () {
    Table::factory()->for($this->other)->count(5)->create();

    $this->actingAs($this->admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa Propia',
             'shape'      => 'round',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertCreated()
         ->assertJsonPath('success', true);
});

it('user without plan defaults to limit of 10', function () {
    $admin = User::factory()->admin()->create(['plan_id' => null]);
    Table::factory()->for($admin)->count(10)->create();

    $this->actingAs($admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa Sin Plan',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertUnprocessable()
         ->assertJsonPath('success', false);
});

it('user without plan can still create up to 10 tables', function () {
    $admin = User::factory()->admin()->create(['plan_id' => null]);
    Table::factory()->for($admin)->count(9)->create();

    $this->actingAs($admin)
         ->postJson(route('tables.store'), [
             'name'       => 'Mesa 10',
             'shape'      => 'square',
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertCreated();
});

it('index view shows correct table count against plan limit', function () {
    Table::factory()->for($this->admin)->count(3)->create();

    $response = $this->actingAs($this->admin)
                     ->get(route('tables.index'));

    $response->assertViewHas('maxTables', 5);
    expect($response->viewData('tables')->count())->toBe(3);
});

// ─── Bloque 8.3 — Formas y rotación ──────────────────────────────────────────

it('updatePosition persists rotation when provided', function () {
    $table = Table::factory()->for($this->admin)->create(['rotation' => 0]);

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 100,
             'position_y' => 100,
             'width'      => 100,
             'height'     => 100,
             'rotation'   => 45,
         ])
         ->assertOk()
         ->assertJsonPath('success', true);

    expect($table->fresh()->rotation)->toBe(45);
});

it('updatePosition works without rotation field (backward compatibility)', function () {
    $table = Table::factory()->for($this->admin)->create(['rotation' => 30]);

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertOk();

    expect($table->fresh()->rotation)->toBe(30);
});

it('admin can change shape of own table', function () {
    $table = Table::factory()->for($this->admin)->create(['shape' => 'square']);

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updateShape', $table), ['shape' => 'round'])
         ->assertOk()
         ->assertJsonPath('success', true);

    expect($table->fresh()->shape)->toBe('round');
});

it('updateShape persists the new shape in the database', function () {
    $table = Table::factory()->for($this->admin)->create(['shape' => 'square']);

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updateShape', $table), ['shape' => 'rectangle']);

    expect($table->fresh()->shape)->toBe('rectangle');
});

it('returns 403 when changing shape of another admins table', function () {
    $table = Table::factory()->for($this->other)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updateShape', $table), ['shape' => 'round'])
         ->assertForbidden();
});

it('returns 401 for unauthenticated user on updateShape', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->patchJson(route('tables.updateShape', $table), ['shape' => 'round'])
         ->assertUnauthorized();
});

it('fails updateShape with invalid shape value', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updateShape', $table), ['shape' => 'triangle'])
         ->assertUnprocessable();
});

it('fails updateShape without shape field', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updateShape', $table), [])
         ->assertUnprocessable();
});

it('fails updatePosition with rotation out of bounds', function () {
    $table = Table::factory()->for($this->admin)->create();

    $this->actingAs($this->admin)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 0,
             'position_y' => 0,
             'width'      => 100,
             'height'     => 100,
             'rotation'   => 400,
         ])
         ->assertUnprocessable();
});

it('stores all three shapes correctly on creation', function () {
    foreach (['square', 'round', 'rectangle'] as $shape) {
        $this->actingAs($this->admin)
             ->postJson(route('tables.store'), [
                 'name'       => "Mesa {$shape}",
                 'shape'      => $shape,
                 'position_x' => 0,
                 'position_y' => 0,
                 'width'      => 100,
                 'height'     => 100,
             ])
             ->assertCreated()
             ->assertJsonPath('data.shape', $shape);
    }
});
