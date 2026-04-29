<?php

/**
 * @author AyrtonAlania
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── ownerUserId() ────────────────────────────────────────────────────────────

it('returns own id when user is admin (admin_id is null)', function () {
    $admin = User::factory()->create(['role' => 'admin', 'admin_id' => null]);

    expect($admin->ownerUserId())->toBe($admin->id);
});

it('returns admin_id when user is staff (admin_id is set)', function () {
    $admin  = User::factory()->create(['role' => 'admin']);
    $waiter = User::factory()->waiter()->staffOf($admin)->create();

    expect($waiter->ownerUserId())->toBe($admin->id);
});

// ─── isAdmin() ────────────────────────────────────────────────────────────────

it('isAdmin returns true for admin role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    expect($admin->isAdmin())->toBeTrue();
});

it('isAdmin returns false for waiter role', function () {
    $waiter = User::factory()->waiter()->create();

    expect($waiter->isAdmin())->toBeFalse();
});

it('isAdmin returns false for kitchen role', function () {
    $kitchen = User::factory()->kitchen()->create();

    expect($kitchen->isAdmin())->toBeFalse();
});

// ─── isStaff() ────────────────────────────────────────────────────────────────

it('isStaff returns true for waiter role', function () {
    $waiter = User::factory()->waiter()->create();

    expect($waiter->isStaff())->toBeTrue();
});

it('isStaff returns true for kitchen role', function () {
    $kitchen = User::factory()->kitchen()->create();

    expect($kitchen->isStaff())->toBeTrue();
});

it('isStaff returns false for admin role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    expect($admin->isStaff())->toBeFalse();
});
