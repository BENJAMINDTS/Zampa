<?php

/**
 * @author AyrtonAlania
 */

use App\Models\User;
use App\Models\Plan;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $plan = Plan::factory()->create();

    $this->admin = User::factory()->create([
        'role'                   => 'admin',
        'plan_id'                => $plan->id,
        'split_payment_enabled'  => false,
        'split_payment_max_parts'=> null,
    ]);

    $this->other = User::factory()->create([
        'role'                   => 'admin',
        'plan_id'                => $plan->id,
        'split_payment_enabled'  => false,
        'split_payment_max_parts'=> null,
    ]);

    $this->waiter = User::factory()->create([
        'role'     => 'waiter',
        'admin_id' => $this->admin->id,
    ]);
});

it('redirects unauthenticated user from split payment config', function () {
    $this->get(route('split-payment.edit'))->assertRedirect(route('login'));
    $this->put(route('split-payment.update'))->assertRedirect(route('login'));
});

it('waiter cannot access split payment config', function () {
    $this->actingAs($this->waiter)
         ->get(route('split-payment.edit'))
         ->assertForbidden();
});

it('admin can view split payment config', function () {
    $this->actingAs($this->admin)
         ->get(route('split-payment.edit'))
         ->assertOk()
         ->assertViewIs('split-payment.edit')
         ->assertViewHas('user', $this->admin);
});

it('admin can enable split payment', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled' => '1',
         ])
         ->assertRedirect(route('split-payment.edit'));

    expect($this->admin->fresh()->split_payment_enabled)->toBeTrue();
});

it('admin can disable split payment', function () {
    $this->admin->update(['split_payment_enabled' => true, 'split_payment_max_parts' => 4]);

    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled' => '0',
         ])
         ->assertRedirect(route('split-payment.edit'));

    $fresh = $this->admin->fresh();
    expect($fresh->split_payment_enabled)->toBeFalse()
        ->and($fresh->split_payment_max_parts)->toBeNull();
});

it('admin can set max parts limit', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '1',
             'split_payment_max_parts' => '5',
         ])
         ->assertRedirect(route('split-payment.edit'));

    expect($this->admin->fresh()->split_payment_max_parts)->toBe(5);
});

it('max parts must be at least 2', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '1',
             'split_payment_max_parts' => '1',
         ])
         ->assertSessionHasErrors('split_payment_max_parts');
});

it('max parts cannot exceed 20', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '1',
             'split_payment_max_parts' => '21',
         ])
         ->assertSessionHasErrors('split_payment_max_parts');
});

it('max parts must be an integer', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '1',
             'split_payment_max_parts' => 'abc',
         ])
         ->assertSessionHasErrors('split_payment_max_parts');
});

it('max parts is cleared when split payment is disabled', function () {
    $this->admin->update(['split_payment_enabled' => true, 'split_payment_max_parts' => 6]);

    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '0',
             'split_payment_max_parts' => '6',
         ])
         ->assertRedirect(route('split-payment.edit'));

    expect($this->admin->fresh()->split_payment_max_parts)->toBeNull();
});

it('max parts can be left empty for no limit', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '1',
             'split_payment_max_parts' => '',
         ])
         ->assertRedirect(route('split-payment.edit'));

    expect($this->admin->fresh()->split_payment_max_parts)->toBeNull();
});

it('shows success flash after saving config', function () {
    $this->actingAs($this->admin)
         ->put(route('split-payment.update'), [
             'split_payment_enabled' => '1',
         ])
         ->assertSessionHas('success');
});

it('split payment config is scoped to the restaurant', function () {
    $this->admin->update(['split_payment_enabled' => true, 'split_payment_max_parts' => 3]);

    $this->actingAs($this->other)
         ->put(route('split-payment.update'), [
             'split_payment_enabled'   => '0',
             'split_payment_max_parts' => null,
         ]);

    expect($this->admin->fresh()->split_payment_enabled)->toBeTrue()
        ->and($this->admin->fresh()->split_payment_max_parts)->toBe(3);
});

it('other admin cannot change another admins split payment config', function () {
    $this->admin->update(['split_payment_enabled' => true]);

    $this->actingAs($this->other)
         ->put(route('split-payment.update'), ['split_payment_enabled' => '0']);

    expect($this->admin->fresh()->split_payment_enabled)->toBeTrue();
});
