<?php

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->superadmin()->create();
    $this->plan       = Plan::factory()->create();
    $this->admin      = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);
});

// ──────────────────────────────────────────────
// INDEX
// ──────────────────────────────────────────────

it('shows businesses index to superadmin', function () {
    $this->actingAs($this->superadmin)
         ->get(route('superadmin.businesses.index'))
         ->assertOk()
         ->assertViewIs('superadmin.businesses.index');
});

it('returns 403 for admin trying to access business management', function () {
    $this->actingAs($this->admin)
         ->get(route('superadmin.businesses.index'))
         ->assertForbidden();
});

it('businesses index shows plan name and stats', function () {
    $response = $this->actingAs($this->superadmin)
                     ->get(route('superadmin.businesses.index'))
                     ->assertOk();

    $businesses = $response->viewData('businesses');
    $found      = $businesses->first(fn ($b) => $b->id === $this->admin->id);

    expect($found)->not->toBeNull()
        ->and($found->plan_id)->toBe($this->plan->id)
        ->and($found->tables_count)->toBeInt()
        ->and($found->orders_count)->toBeInt();
});

// ──────────────────────────────────────────────
// CREATE / STORE
// ──────────────────────────────────────────────

it('superadmin can create a new admin business', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Juan García',
             'email'                 => 'juan@rincon.es',
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => 'El Rincón',
             'address'               => 'Calle Mayor 1, Madrid',
             'lat'                   => 40.4168,
             'lng'                   => -3.7038,
             'plan_id'               => $this->plan->id,
         ])
         ->assertRedirect(route('superadmin.businesses.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'juan@rincon.es',
        'role'  => 'admin',
    ]);
});

it('new business has role admin and active true', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Test Gerente',
             'email'                 => 'gerente@test.es',
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => 'Negocio Test',
             'address'               => 'Calle Test 1',
             'plan_id'               => $this->plan->id,
         ]);

    $created = User::where('email', 'gerente@test.es')->first();

    expect($created->role)->toBe('admin')
        ->and($created->active)->toBeTrue()
        ->and($created->plan_id)->toBe($this->plan->id);
});

it('fails to create business without plan', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Juan García',
             'email'                 => 'juan2@rincon.es',
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => 'El Rincón',
             'address'               => 'Calle Mayor 1',
             'plan_id'               => '',
         ])
         ->assertSessionHasErrors('plan_id');
});

it('fails to create business with duplicate email', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Otro Gerente',
             'email'                 => $this->admin->email,
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => 'Negocio Duplicado',
             'address'               => 'Calle Test 2',
             'plan_id'               => $this->plan->id,
         ])
         ->assertSessionHasErrors('email');
});

// ──────────────────────────────────────────────
// TOGGLE
// ──────────────────────────────────────────────

it('superadmin can deactivate an active business', function () {
    expect($this->admin->active)->toBeTrue();

    $this->actingAs($this->superadmin)
         ->patch(route('superadmin.businesses.toggle', $this->admin))
         ->assertRedirect(route('superadmin.businesses.index'))
         ->assertSessionHas('success');

    expect($this->admin->fresh()->active)->toBeFalse();
});

it('superadmin can reactivate an inactive business', function () {
    $inactive = User::factory()->admin()->inactive()->withBusiness()->create([
        'plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->superadmin)
         ->patch(route('superadmin.businesses.toggle', $inactive))
         ->assertRedirect(route('superadmin.businesses.index'))
         ->assertSessionHas('success');

    expect($inactive->fresh()->active)->toBeTrue();
});

// ──────────────────────────────────────────────
// MIDDLEWARE — bloqueo de admins inactivos
// ──────────────────────────────────────────────

it('deactivated admin cannot access protected panel routes', function () {
    $inactive = User::factory()->admin()->inactive()->create();

    // categories.index está dentro del grupo middleware ['auth', 'business.active']
    $this->actingAs($inactive)
         ->get(route('categories.index'))
         ->assertRedirect(route('login'));
});

it('deactivated admin is logged out on panel access attempt', function () {
    $inactive = User::factory()->admin()->inactive()->create();

    $this->actingAs($inactive)
         ->get(route('categories.index'));

    $this->assertGuest();
});

// ──────────────────────────────────────────────
// DESTROY
// ──────────────────────────────────────────────

it('superadmin can delete a business', function () {
    $toDelete = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.businesses.destroy', $toDelete))
         ->assertRedirect(route('superadmin.businesses.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $toDelete->id]);
});

it('returns 403 when trying to toggle a non-admin user', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($this->superadmin)
         ->patch(route('superadmin.businesses.toggle', $waiter))
         ->assertForbidden();
});

it('returns 403 when trying to delete a non-admin user', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.businesses.destroy', $waiter))
         ->assertForbidden();
});
