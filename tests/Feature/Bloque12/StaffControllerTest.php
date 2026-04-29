<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin       = User::factory()->create(['role' => 'admin']);
    $this->otherAdmin  = User::factory()->create(['role' => 'admin']);
    $this->waiterUser  = User::factory()->waiter()->staffOf($this->admin)->create();
    $this->kitchenUser = User::factory()->kitchen()->staffOf($this->admin)->create();
});

// ─── ACCESO ───────────────────────────────────────────────────────────────────

it('redirects unauthenticated user away from staff index', function () {
    $this->get(route('staff.index'))->assertRedirect(route('login'));
});

it('shows staff index to admin user', function () {
    $this->actingAs($this->admin)
        ->get(route('staff.index'))
        ->assertOk()
        ->assertSee($this->waiterUser->name)
        ->assertSee($this->kitchenUser->name);
});

it('returns 403 for waiter trying to access staff index', function () {
    $this->actingAs($this->waiterUser)
        ->get(route('staff.index'))
        ->assertForbidden();
});

it('returns 403 for kitchen trying to access staff index', function () {
    $this->actingAs($this->kitchenUser)
        ->get(route('staff.index'))
        ->assertForbidden();
});

// ─── SCOPE (solo ve su propio personal) ───────────────────────────────────────

it('shows only staff belonging to the authenticated admin', function () {
    $ownStaff = User::factory()->waiter()->staffOf($this->admin)->create(['name' => 'Mi Camarero']);

    $this->actingAs($this->admin)
        ->get(route('staff.index'))
        ->assertSee('Mi Camarero');
});

it('does not show staff from another admin', function () {
    $foreignStaff = User::factory()->waiter()->staffOf($this->otherAdmin)->create(['name' => 'Ajeno Camarero']);

    $this->actingAs($this->admin)
        ->get(route('staff.index'))
        ->assertDontSee('Ajeno Camarero');
});

// ─── CREACIÓN ─────────────────────────────────────────────────────────────────

it('admin can create a waiter', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Nuevo Camarero',
            'email'                 => 'camarero@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'waiter',
        ])
        ->assertRedirect(route('staff.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'camarero@test.com',
        'role'  => 'waiter',
    ]);
});

it('admin can create a kitchen user', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Nuevo Cocinero',
            'email'                 => 'cocinero@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'kitchen',
        ])
        ->assertRedirect(route('staff.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'cocinero@test.com',
        'role'  => 'kitchen',
    ]);
});

it('new staff has the admins id as admin_id', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Staff Test',
            'email'                 => 'staff@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'waiter',
        ]);

    $this->assertDatabaseHas('users', [
        'email'    => 'staff@test.com',
        'admin_id' => $this->admin->id,
    ]);
});

// ─── VALIDACIÓN ───────────────────────────────────────────────────────────────

it('fails to create staff without name', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => '',
            'email'                 => 'x@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'waiter',
        ])
        ->assertSessionHasErrors('name');
});

it('fails to create staff without email', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Sin Email',
            'email'                 => '',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'waiter',
        ])
        ->assertSessionHasErrors('email');
});

it('fails to create staff with duplicate email', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Duplicado',
            'email'                 => $this->waiterUser->email,
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'waiter',
        ])
        ->assertSessionHasErrors('email');
});

it('fails to create staff with invalid role', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Rol Inválido',
            'email'                 => 'rolmal@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'superadmin',
        ])
        ->assertSessionHasErrors('role');
});

it('fails to create staff with role admin', function () {
    $this->actingAs($this->admin)
        ->post(route('staff.store'), [
            'name'                  => 'Admin Falso',
            'email'                 => 'adminfalso@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'admin',
        ])
        ->assertSessionHasErrors('role');
});

// ─── ELIMINACIÓN ──────────────────────────────────────────────────────────────

it('admin can delete their own staff', function () {
    $this->actingAs($this->admin)
        ->delete(route('staff.destroy', $this->waiterUser))
        ->assertRedirect(route('staff.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $this->waiterUser->id]);
});

it('returns 403 when admin tries to delete staff from another admin', function () {
    $foreignStaff = User::factory()->waiter()->staffOf($this->otherAdmin)->create();

    $this->actingAs($this->admin)
        ->delete(route('staff.destroy', $foreignStaff))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $foreignStaff->id]);
});

it('deleted staff cannot login after deletion', function () {
    $email = $this->waiterUser->email;

    $this->actingAs($this->admin)
        ->delete(route('staff.destroy', $this->waiterUser));

    $this->assertDatabaseMissing('users', ['email' => $email]);

    // El usuario no existe en BD, por lo que cualquier intento de login falla
    $this->assertNull(User::where('email', $email)->first());
});
