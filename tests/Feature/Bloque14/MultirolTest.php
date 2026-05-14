<?php

/**
 * @author AyrtonAlania
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Acceso a paneles con flag activado ──────────────────────────────────────

it('admin with waiter role can access bar panel', function () {
    $admin = User::factory()->admin()->alsoWaiter()->create();

    $this->actingAs($admin)
        ->get(route('bar.index'))
        ->assertOk();
});

it('admin with kitchen role can access kitchen panel', function () {
    $admin = User::factory()->admin()->alsoKitchen()->create();

    $this->actingAs($admin)
        ->get(route('kitchen.index'))
        ->assertOk();
});

// ─── Middleware no bloquea al admin sin flags (acceso directo permitido) ─────

it('admin without waiter flag can still access bar panel directly', function () {
    $admin = User::factory()->admin()->create(['is_waiter' => false]);

    $this->actingAs($admin)
        ->get(route('bar.index'))
        ->assertOk();
});

it('admin without kitchen flag can still access kitchen panel directly', function () {
    $admin = User::factory()->admin()->create(['is_kitchen' => false]);

    $this->actingAs($admin)
        ->get(route('kitchen.index'))
        ->assertOk();
});

// ─── Visibilidad en navegación según flags ───────────────────────────────────

it('admin with both roles sees both panels in navigation', function () {
    $admin = User::factory()->admin()->alsoWaiter()->alsoKitchen()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Barra')
        ->assertSee('Cocina');
});

it('admin without waiter flag does not see bar link in navigation', function () {
    $admin = User::factory()->admin()->create(['is_waiter' => false, 'is_kitchen' => false]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('bar.index'))
        ->assertDontSee(route('kitchen.index'));
});

// ─── Perfil: activar y desactivar roles ──────────────────────────────────────

it('admin can activate waiter role from profile', function () {
    $admin = User::factory()->admin()->create(['is_waiter' => false]);

    $this->actingAs($admin)
        ->patch(route('profile.update'), [
            'name'      => $admin->name,
            'email'     => $admin->email,
            'is_waiter' => '1',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($admin->fresh()->is_waiter)->toBeTrue();
});

it('admin can deactivate waiter role from profile', function () {
    $admin = User::factory()->admin()->alsoWaiter()->create();

    $this->actingAs($admin)
        ->patch(route('profile.update'), [
            'name'  => $admin->name,
            'email' => $admin->email,
            // is_waiter omitted (unchecked checkbox)
        ])
        ->assertRedirect(route('profile.edit'));

    expect($admin->fresh()->is_waiter)->toBeFalse();
});

// ─── Roles nativos sin cambios ────────────────────────────────────────────────

it('regular waiter role still works correctly', function () {
    $admin  = User::factory()->admin()->create();
    $waiter = User::factory()->waiter()->staffOf($admin)->create();

    $this->actingAs($waiter)
        ->get(route('bar.index'))
        ->assertOk();
});

it('regular kitchen role still works correctly', function () {
    $admin   = User::factory()->admin()->create();
    $kitchen = User::factory()->kitchen()->staffOf($admin)->create();

    $this->actingAs($kitchen)
        ->get(route('kitchen.index'))
        ->assertOk();
});
