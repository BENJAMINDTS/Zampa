<?php

/**
 * Tests de enforcement de límites del plan (staff, mesas, plantas).
 *
 * @author BenjaminDTS
 */

use App\Models\Plan;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────
// HELPERS
// ──────────────────────────────────────────────────────────

function makePlan(array $overrides = []): Plan
{
    return Plan::factory()->create(array_merge([
        'price'         => 9.99,
        'price_monthly' => 9.99,
        'max_tables'    => 5,
        'max_staff'     => 3,
        'max_floors'    => 1,
    ], $overrides));
}

function makeAdmin(Plan $plan): User
{
    return User::factory()->create([
        'role'    => 'admin',
        'plan_id' => $plan->id,
    ]);
}

function makeStaffFor(User $admin, int $count = 1): void
{
    User::factory()->count($count)->create([
        'role'     => 'waiter',
        'admin_id' => $admin->id,
    ]);
}

function makeTableFor(User $owner, int $count = 1): void
{
    for ($i = 0; $i < $count; $i++) {
        Table::factory()->create([
            'user_id'          => $owner->id,
            'is_service_point' => true,
            'shape'            => 'square',
        ]);
    }
}

// ──────────────────────────────────────────────────────────
// MODELO Plan — helpers
// ──────────────────────────────────────────────────────────

it('isLimitReached returns false when limit is null', function () {
    $plan = Plan::factory()->premium()->make();

    expect($plan->isLimitReached('tables', 9999))->toBeFalse()
        ->and($plan->isLimitReached('staff', 9999))->toBeFalse()
        ->and($plan->isLimitReached('floors', 9999))->toBeFalse();
});

it('isLimitReached returns false when current is below limit', function () {
    $plan = makePlan(['max_tables' => 20, 'max_staff' => 10, 'max_floors' => 3]);

    expect($plan->isLimitReached('tables', 19))->toBeFalse()
        ->and($plan->isLimitReached('staff', 9))->toBeFalse()
        ->and($plan->isLimitReached('floors', 2))->toBeFalse();
});

it('isLimitReached returns true when current equals limit', function () {
    $plan = makePlan(['max_tables' => 20, 'max_staff' => 10, 'max_floors' => 3]);

    expect($plan->isLimitReached('tables', 20))->toBeTrue()
        ->and($plan->isLimitReached('staff', 10))->toBeTrue()
        ->and($plan->isLimitReached('floors', 3))->toBeTrue();
});

it('isLimitReached returns true when current exceeds limit', function () {
    $plan = makePlan(['max_tables' => 5, 'max_staff' => 3, 'max_floors' => 1]);

    expect($plan->isLimitReached('tables', 6))->toBeTrue()
        ->and($plan->isLimitReached('staff', 4))->toBeTrue()
        ->and($plan->isLimitReached('floors', 2))->toBeTrue();
});

it('isLimitReached throws for unknown resource', function () {
    $plan = makePlan();

    expect(fn () => $plan->isLimitReached('unknown', 1))
        ->toThrow(InvalidArgumentException::class);
});

it('hasUnlimitedTables returns true when max_tables is null', function () {
    $plan = Plan::factory()->premium()->make();

    expect($plan->hasUnlimitedTables())->toBeTrue();
});

it('hasUnlimitedStaff returns true when max_staff is null', function () {
    $plan = Plan::factory()->premium()->make();

    expect($plan->hasUnlimitedStaff())->toBeTrue();
});

it('hasUnlimitedFloors returns true when max_floors is null', function () {
    $plan = Plan::factory()->premium()->make();

    expect($plan->hasUnlimitedFloors())->toBeTrue();
});

// ──────────────────────────────────────────────────────────
// SEEDER — tres planes correctos
// ──────────────────────────────────────────────────────────

it('seeder creates exactly three plans', function () {
    $this->artisan('db:seed');

    expect(Plan::count())->toBe(3);
});

it('basic plan has correct limits and prices', function () {
    $this->artisan('db:seed');

    $plan = Plan::where('name', 'Básico')->firstOrFail();

    expect((float) $plan->price_monthly)->toBe(29.99)
        ->and((float) $plan->price_yearly)->toBe(299.90)
        ->and($plan->max_tables)->toBe(20)
        ->and($plan->max_staff)->toBe(10)
        ->and($plan->max_floors)->toBe(1);
});

it('professional plan has correct limits and prices', function () {
    $this->artisan('db:seed');

    $plan = Plan::where('name', 'Profesional')->firstOrFail();

    expect((float) $plan->price_monthly)->toBe(74.99)
        ->and((float) $plan->price_yearly)->toBe(749.90)
        ->and($plan->max_tables)->toBe(50)
        ->and($plan->max_staff)->toBe(25)
        ->and($plan->max_floors)->toBe(3);
});

it('premium plan has null limits and correct prices', function () {
    $this->artisan('db:seed');

    $plan = Plan::where('name', 'Premium')->firstOrFail();

    expect((float) $plan->price_monthly)->toBe(119.99)
        ->and((float) $plan->price_yearly)->toBe(1199.90)
        ->and($plan->max_tables)->toBeNull()
        ->and($plan->max_staff)->toBeNull()
        ->and($plan->max_floors)->toBeNull();
});

// ──────────────────────────────────────────────────────────
// LÍMITE DE STAFF — StaffController
// ──────────────────────────────────────────────────────────

it('admin with plan can add staff up to max_staff limit', function () {
    $plan  = makePlan(['max_staff' => 2]);
    $admin = makeAdmin($plan);

    $this->actingAs($admin)
         ->post(route('staff.store'), [
             'name'                  => 'Camarero 1',
             'email'                 => 'staff1@test.com',
             'password'              => 'password',
             'password_confirmation' => 'password',
             'role'                  => 'waiter',
         ])
         ->assertRedirect(route('staff.index'));

    expect($admin->fresh()->staff()->count())->toBe(1);
});

it('admin cannot add staff beyond max_staff limit', function () {
    $plan  = makePlan(['max_staff' => 2]);
    $admin = makeAdmin($plan);
    makeStaffFor($admin, 2);

    $this->actingAs($admin)
         ->post(route('staff.store'), [
             'name'                  => 'Extra',
             'email'                 => 'extra@test.com',
             'password'              => 'password',
             'password_confirmation' => 'password',
             'role'                  => 'waiter',
         ])
         ->assertRedirect(route('staff.create'))
         ->assertSessionHas('error');

    expect($admin->fresh()->staff()->count())->toBe(2);
});

it('admin with premium plan has no staff limit', function () {
    $plan  = makePlan(['max_staff' => null]);
    $admin = makeAdmin($plan);
    makeStaffFor($admin, 50);

    $this->actingAs($admin)
         ->post(route('staff.store'), [
             'name'                  => 'Staff 51',
             'email'                 => 'staff51@test.com',
             'password'              => 'password',
             'password_confirmation' => 'password',
             'role'                  => 'waiter',
         ])
         ->assertRedirect(route('staff.index'));

    expect($admin->fresh()->staff()->count())->toBe(51);
});

it('staff limit error message includes plan name', function () {
    $plan  = makePlan(['max_staff' => 1]);
    $admin = makeAdmin($plan);
    makeStaffFor($admin, 1);

    $this->actingAs($admin)
         ->post(route('staff.store'), [
             'name'                  => 'Extra',
             'email'                 => 'extra@test.com',
             'password'              => 'password',
             'password_confirmation' => 'password',
             'role'                  => 'waiter',
         ])
         ->assertRedirect(route('staff.create'));

    $errorMsg = session('error');
    expect($errorMsg)->toContain($plan->name);
});

// ──────────────────────────────────────────────────────────
// LÍMITE DE MESAS — TableController
// ──────────────────────────────────────────────────────────

it('admin can add tables up to max_tables limit', function () {
    $plan  = makePlan(['max_tables' => 3]);
    $admin = makeAdmin($plan);
    makeTableFor($admin, 2);

    $this->actingAs($admin)
         ->postJson(route('tables.store'), [
             'shape'      => 'square',
             'position_x' => 10,
             'position_y' => 10,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertCreated();

    expect(Table::where('user_id', $admin->id)->servicePoints()->count())->toBe(3);
});

it('admin cannot add table beyond max_tables limit', function () {
    $plan  = makePlan(['max_tables' => 2]);
    $admin = makeAdmin($plan);
    makeTableFor($admin, 2);

    $this->actingAs($admin)
         ->postJson(route('tables.store'), [
             'shape'      => 'square',
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertStatus(422)
         ->assertJsonPath('success', false);

    expect(Table::where('user_id', $admin->id)->servicePoints()->count())->toBe(2);
});

it('admin with premium plan has no table limit', function () {
    $plan  = makePlan(['max_tables' => null]);
    $admin = makeAdmin($plan);
    makeTableFor($admin, 5);

    $this->actingAs($admin)
         ->postJson(route('tables.store'), [
             'shape'      => 'square',
             'position_x' => 50,
             'position_y' => 50,
             'width'      => 100,
             'height'     => 100,
         ])
         ->assertCreated();
});

it('decorative elements do not count toward table limit', function () {
    $plan  = makePlan(['max_tables' => 2]);
    $admin = makeAdmin($plan);
    makeTableFor($admin, 2);

    // Bar (decorative, is_service_point = false) should not be blocked
    $this->actingAs($admin)
         ->postJson(route('tables.store'), [
             'shape'      => 'bar',
             'position_x' => 10,
             'position_y' => 10,
             'width'      => 200,
             'height'     => 60,
         ])
         ->assertCreated();
});

// ──────────────────────────────────────────────────────────
// LÍMITE DE PLANTAS — TableController::updateFloorSettings
// ──────────────────────────────────────────────────────────

it('admin with basic plan cannot enable more than max_floors', function () {
    $plan  = makePlan(['max_floors' => 1]);
    $admin = makeAdmin($plan);

    // Validation rejects floor_count > max_floors before reaching isLimitReached
    $this->actingAs($admin)
         ->patchJson(route('tables.floor-settings'), [
             'floor_count' => 2,
         ])
         ->assertStatus(422);
});

it('admin can set floor_count exactly equal to max_floors', function () {
    $plan  = makePlan(['max_floors' => 3]);
    $admin = makeAdmin($plan);

    $this->actingAs($admin)
         ->patchJson(route('tables.floor-settings'), [
             'floor_count' => 3,
         ])
         ->assertOk()
         ->assertJsonPath('success', true);
});

it('admin with premium plan has no floor limit', function () {
    $plan  = makePlan(['max_floors' => null]);
    $admin = makeAdmin($plan);

    $this->actingAs($admin)
         ->patchJson(route('tables.floor-settings'), [
             'floor_count' => 5,
         ])
         ->assertOk()
         ->assertJsonPath('success', true);
});
