<?php

/**
 * @author AyrtonAlania
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── isSuperAdmin() ───────────────────────────────────────────────────────────

it('isSuperAdmin returns true for superadmin role', function () {
    $user = User::factory()->superadmin()->create();
    expect($user->isSuperAdmin())->toBeTrue();
});

it('isSuperAdmin returns false for admin role', function () {
    $user = User::factory()->admin()->create();
    expect($user->isSuperAdmin())->toBeFalse();
});

it('isSuperAdmin returns false for waiter role', function () {
    $user = User::factory()->waiter()->create();
    expect($user->isSuperAdmin())->toBeFalse();
});

it('isSuperAdmin returns false for kitchen role', function () {
    $user = User::factory()->kitchen()->create();
    expect($user->isSuperAdmin())->toBeFalse();
});

// ─── isAdmin() ────────────────────────────────────────────────────────────────

it('isAdmin returns true for admin role', function () {
    $user = User::factory()->admin()->create();
    expect($user->isAdmin())->toBeTrue();
});

it('isAdmin returns false for superadmin role', function () {
    $user = User::factory()->superadmin()->create();
    expect($user->isAdmin())->toBeFalse();
});

it('isAdmin returns false for waiter role', function () {
    $user = User::factory()->waiter()->create();
    expect($user->isAdmin())->toBeFalse();
});

// ─── isStaff() ────────────────────────────────────────────────────────────────

it('isStaff returns true for waiter role', function () {
    $user = User::factory()->waiter()->create();
    expect($user->isStaff())->toBeTrue();
});

it('isStaff returns true for kitchen role', function () {
    $user = User::factory()->kitchen()->create();
    expect($user->isStaff())->toBeTrue();
});

it('isStaff returns false for admin role', function () {
    $user = User::factory()->admin()->create();
    expect($user->isStaff())->toBeFalse();
});

it('isStaff returns false for superadmin role', function () {
    $user = User::factory()->superadmin()->create();
    expect($user->isStaff())->toBeFalse();
});

// ─── isActive() ───────────────────────────────────────────────────────────────

it('isActive returns true when active is true', function () {
    $user = User::factory()->create(['active' => true]);
    expect($user->isActive())->toBeTrue();
});

it('isActive returns false when active is false', function () {
    $user = User::factory()->inactive()->create();
    expect($user->isActive())->toBeFalse();
});

// ─── ownerUserId() ────────────────────────────────────────────────────────────

it('ownerUserId returns own id when admin_id is null', function () {
    $admin = User::factory()->admin()->create(['admin_id' => null]);
    expect($admin->ownerUserId())->toBe($admin->id);
});

it('ownerUserId returns admin_id when admin_id is set', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->waiter()->staffOf($admin)->create();
    expect($staff->ownerUserId())->toBe($admin->id);
});
