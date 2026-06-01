<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin      = User::factory()->create(['role' => 'admin']);
});

// ──────────────────────────────────────────────
// INDEX
// ──────────────────────────────────────────────

it('shows plans index to superadmin', function () {
    Plan::factory()->count(3)->create();

    $this->actingAs($this->superadmin)
         ->get(route('superadmin.plans.index'))
         ->assertOk()
         ->assertViewIs('superadmin.plans.index');
});

it('returns 403 for admin trying to access plans management', function () {
    $this->actingAs($this->admin)
         ->get(route('superadmin.plans.index'))
         ->assertForbidden();
});

it('returns 403 for waiter trying to manage plans', function () {
    $waiter = User::factory()->waiter()->create();

    $this->actingAs($waiter)
         ->get(route('superadmin.plans.index'))
         ->assertForbidden();
});

it('plans index shows number of businesses per plan', function () {
    $plan = Plan::factory()->create();
    User::factory()->count(2)->create(['role' => 'admin', 'plan_id' => $plan->id]);

    $response = $this->actingAs($this->superadmin)
                     ->get(route('superadmin.plans.index'))
                     ->assertOk();

    $plans = $response->viewData('plans');
    $found = $plans->firstWhere('id', $plan->id);

    expect($found->users_count)->toBe(2);
});

// ──────────────────────────────────────────────
// CREATE
// ──────────────────────────────────────────────

it('superadmin can create a plan with valid data', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.plans.store'), [
             'name'          => 'Plan Premium',
             'price_monthly' => 29.99,
             'max_tables'    => 20,
             'max_staff'     => 10,
             'max_floors'    => 1,
         ])
         ->assertRedirect(route('superadmin.plans.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('plans', [
        'name'       => 'Plan Premium',
        'max_tables' => 20,
    ]);
});

it('superadmin can create a plan with null limits (ilimitado)', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.plans.store'), [
             'name'          => 'Plan Ilimitado',
             'price_monthly' => 119.99,
             'max_tables'    => null,
             'max_staff'     => null,
             'max_floors'    => null,
         ])
         ->assertRedirect(route('superadmin.plans.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('plans', ['name' => 'Plan Ilimitado', 'max_tables' => null]);
});

it('fails to create plan without name', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.plans.store'), [
             'name'          => '',
             'price_monthly' => 9.99,
             'max_tables'    => 5,
         ])
         ->assertSessionHasErrors('name');
});

it('fails to create plan with negative price', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.plans.store'), [
             'name'          => 'Plan X',
             'price_monthly' => -5,
             'max_tables'    => 5,
         ])
         ->assertSessionHasErrors('price_monthly');
});

it('fails to create plan with zero max_tables', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.plans.store'), [
             'name'          => 'Plan X',
             'price_monthly' => 9.99,
             'max_tables'    => 0,
         ])
         ->assertSessionHasErrors('max_tables');
});

it('fails to create plan with duplicate name', function () {
    Plan::factory()->create(['name' => 'Plan Básico']);

    $this->actingAs($this->superadmin)
         ->post(route('superadmin.plans.store'), [
             'name'          => 'Plan Básico',
             'price_monthly' => 19.99,
             'max_tables'    => 10,
         ])
         ->assertSessionHasErrors('name');
});

// ──────────────────────────────────────────────
// UPDATE
// ──────────────────────────────────────────────

it('superadmin can update a plan', function () {
    $plan = Plan::factory()->create(['name' => 'Plan Viejo', 'price' => 9.99, 'max_tables' => 5]);

    $this->actingAs($this->superadmin)
         ->put(route('superadmin.plans.update', $plan), [
             'name'          => 'Plan Nuevo',
             'price_monthly' => 19.99,
             'max_tables'    => 15,
             'max_staff'     => 8,
             'max_floors'    => 1,
         ])
         ->assertRedirect(route('superadmin.plans.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('plans', [
        'id'         => $plan->id,
        'name'       => 'Plan Nuevo',
        'max_tables' => 15,
    ]);
});

it('update allows keeping the same name on the same plan', function () {
    $plan = Plan::factory()->create(['name' => 'Plan Único']);

    $this->actingAs($this->superadmin)
         ->put(route('superadmin.plans.update', $plan), [
             'name'          => 'Plan Único',
             'price_monthly' => 49.99,
             'max_tables'    => 30,
         ])
         ->assertRedirect(route('superadmin.plans.index'))
         ->assertSessionHas('success');
});

// ──────────────────────────────────────────────
// DESTROY
// ──────────────────────────────────────────────

it('superadmin can delete a plan with no businesses', function () {
    $plan = Plan::factory()->create();

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.plans.destroy', $plan))
         ->assertRedirect(route('superadmin.plans.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
});

it('cannot delete a plan that has businesses assigned', function () {
    $plan = Plan::factory()->create();
    User::factory()->create(['role' => 'admin', 'plan_id' => $plan->id]);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.plans.destroy', $plan))
         ->assertRedirect(route('superadmin.plans.index'))
         ->assertSessionHas('error');

    $this->assertDatabaseHas('plans', ['id' => $plan->id]);
});
