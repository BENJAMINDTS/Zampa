<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Plan;
use App\Models\Table;
use App\Models\User;
use App\Models\Zone;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $plan        = Plan::factory()->create(['max_tables' => 3]);
    $this->user  = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $this->other = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $this->plan  = $plan;
});

// ─── Creación de zonas ────────────────────────────────────────────────────────

it('gerente can create a zone', function () {
    $this->actingAs($this->user)
         ->postJson(route('zones.store'), [
             'name'       => 'Terraza',
             'color'      => '#6366f1',
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 300,
             'height'     => 200,
         ])
         ->assertCreated()
         ->assertJson(['success' => true]);

    $this->assertDatabaseHas('zones', [
        'name'    => 'Terraza',
        'user_id' => $this->user->id,
        'color'   => '#6366f1',
    ]);
});

it('fails to create a zone without name', function () {
    $this->actingAs($this->user)
         ->postJson(route('zones.store'), [
             'color'      => '#6366f1',
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 300,
             'height'     => 200,
         ])
         ->assertUnprocessable();
});

it('fails to create a zone with invalid color', function () {
    $this->actingAs($this->user)
         ->postJson(route('zones.store'), [
             'name'       => 'VIP',
             'color'      => 'not-a-color',
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 300,
             'height'     => 200,
         ])
         ->assertUnprocessable();
});

// ─── Actualización de zonas ───────────────────────────────────────────────────

it('gerente can update a zone name and color', function () {
    $zone = Zone::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
         ->patchJson(route('zones.update', $zone), [
             'name'  => 'Comedor interior',
             'color' => '#10b981',
         ])
         ->assertOk()
         ->assertJson(['success' => true]);

    $this->assertDatabaseHas('zones', [
        'id'    => $zone->id,
        'name'  => 'Comedor interior',
        'color' => '#10b981',
    ]);
});

it('gerente can update zone position', function () {
    $zone = Zone::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
         ->patchJson(route('zones.update', $zone), [
             'position_x' => 150,
             'position_y' => 80,
             'width'      => 400,
             'height'     => 250,
         ])
         ->assertOk();

    $this->assertDatabaseHas('zones', [
        'id'         => $zone->id,
        'position_x' => 150,
        'position_y' => 80,
    ]);
});

// ─── Eliminación de zonas ─────────────────────────────────────────────────────

it('gerente can delete a zone', function () {
    $zone = Zone::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
         ->deleteJson(route('zones.destroy', $zone))
         ->assertOk()
         ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
});

it('deleting a zone does not delete its tables', function () {
    $zone  = Zone::factory()->create(['user_id' => $this->user->id]);
    $table = Table::factory()->create([
        'user_id' => $this->user->id,
        'zone_id' => $zone->id,
    ]);

    $this->actingAs($this->user)
         ->deleteJson(route('zones.destroy', $zone));

    $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
    $this->assertDatabaseHas('tables', ['id' => $table->id]);
    $this->assertDatabaseHas('tables', ['id' => $table->id, 'zone_id' => null]);
});

// ─── Multitenancy ─────────────────────────────────────────────────────────────

it('zone is scoped to the restaurant owner on update', function () {
    $zone = Zone::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->other)
         ->patchJson(route('zones.update', $zone), ['name' => 'Hack'])
         ->assertForbidden();
});

it('cannot delete another restaurants zone', function () {
    $zone = Zone::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
         ->deleteJson(route('zones.destroy', $zone))
         ->assertForbidden();
});

it('zone data is isolated per restaurant', function () {
    Zone::factory()->create(['user_id' => $this->other->id, 'name' => 'Zona secreta']);

    $response = $this->actingAs($this->user)
         ->get(route('tables.map'));

    $response->assertOk();
    $response->assertDontSee('Zona secreta');
});

// ─── Elementos especiales ─────────────────────────────────────────────────────

it('stool element does not appear in public menu', function () {
    $stool = Table::factory()->create([
        'user_id'          => $this->user->id,
        'shape'            => 'stool',
        'is_service_point' => false,
    ]);

    $this->get(route('menu.show', $stool->unique_hash))
         ->assertNotFound();
});

it('bar element does not appear in public menu', function () {
    $bar = Table::factory()->create([
        'user_id'          => $this->user->id,
        'shape'            => 'bar',
        'is_service_point' => false,
    ]);

    $this->get(route('menu.show', $bar->unique_hash))
         ->assertNotFound();
});

it('service point tables still appear in public menu', function () {
    $table = Table::factory()->create([
        'user_id'          => $this->user->id,
        'is_service_point' => true,
    ]);

    $this->get(route('menu.show', $table->unique_hash))
         ->assertOk();
});

it('creating a stool does not count against plan limit', function () {
    Table::factory()->create(['user_id' => $this->user->id, 'is_service_point' => true]);
    Table::factory()->create(['user_id' => $this->user->id, 'is_service_point' => true]);
    Table::factory()->create(['user_id' => $this->user->id, 'is_service_point' => true]);

    // Plan limit (3) reached for service points — stool should still be allowed
    $this->actingAs($this->user)
         ->postJson(route('tables.store'), [
             'name'             => 'Taburete 1',
             'shape'            => 'stool',
             'position_x'       => 10,
             'position_y'       => 10,
             'width'            => 50,
             'height'           => 50,
             'is_service_point' => false,
         ])
         ->assertCreated();
});

it('gerente can assign a table to a zone', function () {
    $zone  = Zone::factory()->create(['user_id' => $this->user->id]);
    $table = Table::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
         ->patchJson(route('tables.updatePosition', $table), [
             'position_x' => 100,
             'position_y' => 100,
             'width'      => 100,
             'height'     => 100,
             'rotation'   => 0,
         ])
         ->assertOk();
});
