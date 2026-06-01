<?php

/**
 * Unit tests para los helpers del modelo Plan.
 *
 * @author BenjaminDTS
 */

use App\Models\Plan;

// ──────────────────────────────────────────────────────────
// isLimitReached
// ──────────────────────────────────────────────────────────

it('isLimitReached returns false for null limit', function () {
    $plan = new Plan(['max_tables' => null, 'max_staff' => null, 'max_floors' => null]);

    expect($plan->isLimitReached('tables', 9999))->toBeFalse()
        ->and($plan->isLimitReached('staff', 9999))->toBeFalse()
        ->and($plan->isLimitReached('floors', 9999))->toBeFalse();
});

it('isLimitReached returns false below limit', function () {
    $plan = new Plan(['max_tables' => 20, 'max_staff' => 10, 'max_floors' => 3]);

    expect($plan->isLimitReached('tables', 19))->toBeFalse()
        ->and($plan->isLimitReached('staff', 9))->toBeFalse()
        ->and($plan->isLimitReached('floors', 2))->toBeFalse();
});

it('isLimitReached returns true at exact limit', function () {
    $plan = new Plan(['max_tables' => 20, 'max_staff' => 10, 'max_floors' => 3]);

    expect($plan->isLimitReached('tables', 20))->toBeTrue()
        ->and($plan->isLimitReached('staff', 10))->toBeTrue()
        ->and($plan->isLimitReached('floors', 3))->toBeTrue();
});

it('isLimitReached returns true above limit', function () {
    $plan = new Plan(['max_tables' => 5, 'max_staff' => 3, 'max_floors' => 1]);

    expect($plan->isLimitReached('tables', 6))->toBeTrue()
        ->and($plan->isLimitReached('staff', 4))->toBeTrue()
        ->and($plan->isLimitReached('floors', 2))->toBeTrue();
});

it('isLimitReached throws InvalidArgumentException for unknown resource', function () {
    $plan = new Plan(['max_tables' => 5]);

    expect(fn () => $plan->isLimitReached('orders', 1))
        ->toThrow(InvalidArgumentException::class);
});

// ──────────────────────────────────────────────────────────
// hasUnlimited* helpers
// ──────────────────────────────────────────────────────────

it('hasUnlimitedTables returns true when max_tables is null', function () {
    $plan = new Plan(['max_tables' => null]);

    expect($plan->hasUnlimitedTables())->toBeTrue();
});

it('hasUnlimitedTables returns false when max_tables is set', function () {
    $plan = new Plan(['max_tables' => 20]);

    expect($plan->hasUnlimitedTables())->toBeFalse();
});

it('hasUnlimitedStaff returns true when max_staff is null', function () {
    $plan = new Plan(['max_staff' => null]);

    expect($plan->hasUnlimitedStaff())->toBeTrue();
});

it('hasUnlimitedFloors returns true when max_floors is null', function () {
    $plan = new Plan(['max_floors' => null]);

    expect($plan->hasUnlimitedFloors())->toBeTrue();
});

// ──────────────────────────────────────────────────────────
// limitFor
// ──────────────────────────────────────────────────────────

it('limitFor returns the correct value for each resource', function () {
    $plan = new Plan(['max_tables' => 20, 'max_staff' => 10, 'max_floors' => 3]);

    expect($plan->limitFor('tables'))->toBe(20)
        ->and($plan->limitFor('staff'))->toBe(10)
        ->and($plan->limitFor('floors'))->toBe(3);
});

it('limitFor returns null for unlimited resources', function () {
    $plan = new Plan(['max_tables' => null, 'max_staff' => null, 'max_floors' => null]);

    expect($plan->limitFor('tables'))->toBeNull()
        ->and($plan->limitFor('staff'))->toBeNull()
        ->and($plan->limitFor('floors'))->toBeNull();
});
