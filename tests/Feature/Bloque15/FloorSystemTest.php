<?php

use App\Models\Plan;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->plan  = Plan::factory()->create(['max_tables' => 20]);
    $this->admin = User::factory()->admin()->create([
        'plan_id'        => $this->plan->id,
        'floor_count'    => 1,
        'floors_enabled' => false,
    ]);
    $this->other = User::factory()->admin()->create([
        'plan_id'        => $this->plan->id,
        'floor_count'    => 1,
        'floors_enabled' => false,
    ]);
    $this->waiter = User::factory()->waiter()->create([
        'admin_id' => $this->admin->id,
    ]);
});

// -------------------------------------------------------------------------
// map() — datos pasados a la vista
// -------------------------------------------------------------------------

it('passes floorCount and floorsEnabled to the map view', function () {
    $this->admin->update(['floor_count' => 3, 'floors_enabled' => true]);

    $this->actingAs($this->admin)
        ->get(route('tables.map'))
        ->assertOk()
        ->assertViewHas('floorCount', 3)
        ->assertViewHas('floorsEnabled', true);
});

it('passes default floorCount 1 and floorsEnabled false when not set', function () {
    $this->actingAs($this->admin)
        ->get(route('tables.map'))
        ->assertOk()
        ->assertViewHas('floorCount', 1)
        ->assertViewHas('floorsEnabled', false);
});

it('waiter can view map but receives readonly true', function () {
    $this->actingAs($this->waiter)
        ->get(route('tables.map'))
        ->assertOk()
        ->assertViewHas('readonly', true);
});

it('redirects unauthenticated user away from map', function () {
    $this->get(route('tables.map'))
        ->assertRedirect(route('login'));
});

// -------------------------------------------------------------------------
// updateFloorSettings — PATCH /mesas/mapa/plantas/config
// -------------------------------------------------------------------------

it('admin can activate floors_enabled', function () {
    $this->actingAs($this->admin)
        ->patchJson(route('tables.floor-settings'), ['floors_enabled' => true])
        ->assertOk()
        ->assertJsonFragment(['floors_enabled' => true]);

    expect($this->admin->fresh()->floors_enabled)->toBeTrue();
});

it('admin can deactivate floors_enabled', function () {
    $this->admin->update(['floors_enabled' => true]);

    $this->actingAs($this->admin)
        ->patchJson(route('tables.floor-settings'), ['floors_enabled' => false])
        ->assertOk()
        ->assertJsonFragment(['floors_enabled' => false]);

    expect($this->admin->fresh()->floors_enabled)->toBeFalse();
});

it('admin can set floor_count between 1 and 5', function () {
    $this->actingAs($this->admin)
        ->patchJson(route('tables.floor-settings'), ['floor_count' => 4])
        ->assertOk()
        ->assertJsonFragment(['floor_count' => 4]);

    expect($this->admin->fresh()->floor_count)->toBe(4);
});

it('rejects floor_count greater than 5 with 422', function () {
    $this->actingAs($this->admin)
        ->patchJson(route('tables.floor-settings'), ['floor_count' => 6])
        ->assertUnprocessable();
});

it('rejects floor_count less than 1 with 422', function () {
    $this->actingAs($this->admin)
        ->patchJson(route('tables.floor-settings'), ['floor_count' => 0])
        ->assertUnprocessable();
});

it('can update both floors_enabled and floor_count at once', function () {
    $this->actingAs($this->admin)
        ->patchJson(route('tables.floor-settings'), [
            'floors_enabled' => true,
            'floor_count'    => 3,
        ])
        ->assertOk()
        ->assertJsonFragment(['floors_enabled' => true, 'floor_count' => 3]);
});

it('redirects unauthenticated user from updateFloorSettings', function () {
    $this->patchJson(route('tables.floor-settings'), ['floors_enabled' => true])
        ->assertUnauthorized();
});

it('waiter cannot call updateFloorSettings (403)', function () {
    $this->actingAs($this->waiter)
        ->patchJson(route('tables.floor-settings'), ['floors_enabled' => true])
        ->assertForbidden();
});

// -------------------------------------------------------------------------
// destroyFloor — DELETE /mesas/mapa/plantas/{floor}
// -------------------------------------------------------------------------

it('admin can delete the last floor and tables on it are removed', function () {
    $this->admin->update(['floor_count' => 2]);

    Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 2]);
    Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 2]);

    $this->actingAs($this->admin)
        ->deleteJson(route('tables.floor-destroy', ['floor' => 2]))
        ->assertOk()
        ->assertJsonFragment(['floor_count' => 1]);

    expect($this->admin->fresh()->floor_count)->toBe(1);
    expect(Table::where('user_id', $this->admin->id)->where('floor', 2)->count())->toBe(0);
});

it('only deletes tables belonging to the admin on that floor', function () {
    $this->admin->update(['floor_count' => 2]);
    $this->other->update(['floor_count' => 2]);

    Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 2]);
    $otherTable = Table::factory()->create(['user_id' => $this->other->id, 'floor' => 2]);

    $this->actingAs($this->admin)
        ->deleteJson(route('tables.floor-destroy', ['floor' => 2]))
        ->assertOk();

    expect(Table::find($otherTable->id))->not->toBeNull();
});

it('rejects deleting floor 1 with 422', function () {
    $this->actingAs($this->admin)
        ->deleteJson(route('tables.floor-destroy', ['floor' => 1]))
        ->assertUnprocessable();
});

it('rejects deleting a non-last floor with 422', function () {
    $this->admin->update(['floor_count' => 3]);

    $this->actingAs($this->admin)
        ->deleteJson(route('tables.floor-destroy', ['floor' => 2]))
        ->assertUnprocessable();
});

it('reduces floor_count by exactly 1 after deleting last floor', function () {
    $this->admin->update(['floor_count' => 3]);

    $this->actingAs($this->admin)
        ->deleteJson(route('tables.floor-destroy', ['floor' => 3]))
        ->assertOk();

    expect($this->admin->fresh()->floor_count)->toBe(2);
});

it('redirects unauthenticated user from destroyFloor', function () {
    $this->admin->update(['floor_count' => 2]);

    $this->deleteJson(route('tables.floor-destroy', ['floor' => 2]))
        ->assertUnauthorized();
});

it('waiter cannot delete a floor (403)', function () {
    $this->admin->update(['floor_count' => 2]);

    $this->actingAs($this->waiter)
        ->deleteJson(route('tables.floor-destroy', ['floor' => 2]))
        ->assertForbidden();
});

// -------------------------------------------------------------------------
// updateFloor — PATCH /mesas/{table}/planta
// -------------------------------------------------------------------------

it('admin can move a table to a valid floor', function () {
    $this->admin->update(['floor_count' => 3, 'floors_enabled' => true]);
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 1]);

    $this->actingAs($this->admin)
        ->patchJson(route('tables.update-floor', $table), ['floor' => 3])
        ->assertOk()
        ->assertJsonFragment(['floor' => 3]);

    expect($table->fresh()->floor)->toBe(3);
});

it('rejects moving a table to a floor beyond floor_count with 422', function () {
    $this->admin->update(['floor_count' => 2]);
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 1]);

    $this->actingAs($this->admin)
        ->patchJson(route('tables.update-floor', $table), ['floor' => 3])
        ->assertUnprocessable();
});

it('returns 403 when trying to move another users table to a floor', function () {
    $this->other->update(['floor_count' => 3]);
    $otherTable = Table::factory()->create(['user_id' => $this->other->id, 'floor' => 1]);

    $this->actingAs($this->admin)
        ->patchJson(route('tables.update-floor', $otherTable), ['floor' => 2])
        ->assertForbidden();
});

it('rejects floor value less than 1 with 422', function () {
    $this->admin->update(['floor_count' => 3]);
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 1]);

    $this->actingAs($this->admin)
        ->patchJson(route('tables.update-floor', $table), ['floor' => 0])
        ->assertUnprocessable();
});

it('rejects floor value greater than 5 with 422', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id, 'floor' => 1]);

    $this->actingAs($this->admin)
        ->patchJson(route('tables.update-floor', $table), ['floor' => 6])
        ->assertUnprocessable();
});

it('redirects unauthenticated user from updateFloor', function () {
    $table = Table::factory()->create(['user_id' => $this->admin->id]);

    $this->patchJson(route('tables.update-floor', $table), ['floor' => 2])
        ->assertUnauthorized();
});

// -------------------------------------------------------------------------
// store() con campo floor
// -------------------------------------------------------------------------

it('creates a table with the specified floor', function () {
    $this->admin->update(['floor_count' => 3, 'floors_enabled' => true]);

    $this->actingAs($this->admin)
        ->postJson(route('tables.store'), [
            'shape'      => 'square',
            'position_x' => 100,
            'position_y' => 100,
            'width'      => 80,
            'height'     => 80,
            'floor'      => 2,
        ])
        ->assertCreated()
        ->assertJsonFragment(['floor' => 2]);
});

it('defaults floor to 1 when not provided', function () {
    $this->actingAs($this->admin)
        ->postJson(route('tables.store'), [
            'shape'      => 'square',
            'position_x' => 0,
            'position_y' => 0,
            'width'      => 80,
            'height'     => 80,
        ])
        ->assertCreated()
        ->assertJsonFragment(['floor' => 1]);
});

it('rejects floor greater than 5 in store with 422', function () {
    $this->actingAs($this->admin)
        ->postJson(route('tables.store'), [
            'shape'      => 'square',
            'position_x' => 0,
            'position_y' => 0,
            'width'      => 80,
            'height'     => 80,
            'floor'      => 6,
        ])
        ->assertUnprocessable();
});

it('persists floor value in the database after store', function () {
    $this->admin->update(['floor_count' => 2]);

    $response = $this->actingAs($this->admin)
        ->postJson(route('tables.store'), [
            'shape'      => 'square',
            'position_x' => 0,
            'position_y' => 0,
            'width'      => 80,
            'height'     => 80,
            'floor'      => 2,
        ])
        ->assertCreated();

    $id = $response->json('data.id');
    expect(Table::find($id)->floor)->toBe(2);
});
