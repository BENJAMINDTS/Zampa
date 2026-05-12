<?php

/**
 * @author AyrtonAlania
 * @author SebastianBCF
 */

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

it('returns 403 for waiter trying to access business management', function () {
    $waiter = User::factory()->waiter()->create();

    $this->actingAs($waiter)
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

it('new business has null admin_id', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Test Admin',
             'email'                 => 'test@nulladmin.es',
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => 'Negocio Null',
             'address'               => 'Calle Nula 1',
             'plan_id'               => $this->plan->id,
         ]);

    $created = User::where('email', 'test@nulladmin.es')->first();
    expect($created->admin_id)->toBeNull();
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

it('fails to create business without email', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Juan García',
             'email'                 => '',
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => 'El Rincón',
             'address'               => 'Calle Mayor 1',
             'plan_id'               => $this->plan->id,
         ])
         ->assertSessionHasErrors('email');
});

it('fails to create business without business name', function () {
    $this->actingAs($this->superadmin)
         ->post(route('superadmin.businesses.store'), [
             'name'                  => 'Juan García',
             'email'                 => 'juan3@rincon.es',
             'password'              => 'secret123',
             'password_confirmation' => 'secret123',
             'business_name'         => '',
             'address'               => 'Calle Mayor 1',
             'plan_id'               => $this->plan->id,
         ])
         ->assertSessionHasErrors('business_name');
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

    // Soft delete: el registro sigue en BD pero con deleted_at
    $this->assertSoftDeleted('users', ['id' => $toDelete->id]);
});

it('staff is handled correctly when admin is deleted', function () {
    $toDelete = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);
    $staff    = User::factory()->waiter()->create(['admin_id' => $toDelete->id]);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.businesses.destroy', $toDelete));

    // Staff sigue existiendo con su admin_id intacto
    $this->assertDatabaseHas('users', ['id' => $staff->id]);
    expect($staff->fresh()->admin_id)->toBe($toDelete->id);
    // Staff queda desactivado para bloquear el acceso al panel
    expect($staff->fresh()->active)->toBeFalse();
});

it('staff cannot login after their admin is deleted', function () {
    $toDelete = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);
    $staff    = User::factory()->waiter()->create(['admin_id' => $toDelete->id]);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.businesses.destroy', $toDelete));

    // El middleware EnsureBusinessIsActive debe bloquear al staff
    $this->actingAs($staff->fresh())
         ->get(route('bar.index'))
         ->assertRedirect(route('login'));
});

it('admin data integrity is maintained after deletion', function () {
    $toDelete = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.businesses.destroy', $toDelete));

    // El admin sigue en BD (soft delete), datos intactos
    $trashed = User::withTrashed()->find($toDelete->id);
    expect($trashed)->not->toBeNull()
        ->and($trashed->deleted_at)->not->toBeNull()
        ->and($trashed->email)->toBe($toDelete->email);
});

it('deleted admin does not appear in businesses index', function () {
    $toDelete = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);

    $this->actingAs($this->superadmin)
         ->delete(route('superadmin.businesses.destroy', $toDelete));

    $response = $this->actingAs($this->superadmin)
                     ->get(route('superadmin.businesses.index'));

    $businesses = $response->viewData('businesses');
    expect($businesses->contains('id', $toDelete->id))->toBeFalse();
});

it('delete operation is atomic and rolls back on failure', function () {
    $toDelete = User::factory()->admin()->withBusiness()->create(['plan_id' => $this->plan->id]);
    $staff    = User::factory()->waiter()->create(['admin_id' => $toDelete->id]);

    $rolled = false;
    try {
        DB::transaction(function () use ($toDelete, $staff): void {
            User::where('admin_id', $toDelete->id)->update(['active' => false]);

            // Dentro de la transacción el staff ya aparece inactivo
            expect($staff->fresh()->active)->toBeFalse();

            // Forzar rollback
            throw new \RuntimeException('forced rollback');
        });
    } catch (\RuntimeException) {
        $rolled = true;
    }

    expect($rolled)->toBeTrue();
    // Tras el rollback el staff vuelve a estar activo
    expect($staff->fresh()->active)->toBeTrue();
    // El admin no fue soft-deleted
    expect(User::withTrashed()->find($toDelete->id)->deleted_at)->toBeNull();
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
