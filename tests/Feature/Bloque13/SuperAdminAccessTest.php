<?php

/**
 * @author AyrtonAlania
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin      = User::factory()->create(['role' => 'admin']);
    $this->waiter     = User::factory()->create(['role' => 'waiter']);
    $this->kitchen    = User::factory()->create(['role' => 'kitchen']);
});

it('redirects unauthenticated user from superadmin panel', function () {
    $this->get(route('superadmin.dashboard'))
         ->assertRedirect(route('login'));
});

it('returns 403 for admin role trying to access superadmin', function () {
    $this->actingAs($this->admin)
         ->get(route('superadmin.dashboard'))
         ->assertForbidden();
});

it('returns 403 for waiter role trying to access superadmin', function () {
    $this->actingAs($this->waiter)
         ->get(route('superadmin.dashboard'))
         ->assertForbidden();
});

it('returns 403 for kitchen role trying to access superadmin', function () {
    $this->actingAs($this->kitchen)
         ->get(route('superadmin.dashboard'))
         ->assertForbidden();
});

it('shows superadmin dashboard to superadmin role', function () {
    $this->actingAs($this->superadmin)
         ->get(route('superadmin.dashboard'))
         ->assertOk()
         ->assertViewIs('superadmin.dashboard');
});

it('superadmin cannot access routes protected by role:admin middleware', function () {
    $this->actingAs($this->superadmin)
         ->get(route('tapas.edit'))
         ->assertForbidden();
});
