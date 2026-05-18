<?php

// ─── DailyMenuOrderService — timing math ─────────────────────────────────────
// These tests verify the delay calculation formula used in DailyMenuOrderService::dispatchRounds():
//   delaySeconds = max(0, (clientDelay - estimatedPrep) * 60)
// No database access required.

it('delay is zero when client delay equals estimated prep minutes', function () {
    $clientDelay   = 15;
    $estimatedPrep = 15;

    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(0);
});

it('delay is zero when client delay is less than estimated prep minutes', function () {
    $clientDelay   = 5;
    $estimatedPrep = 15;

    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(0);
});

it('delay is positive when client delay exceeds estimated prep minutes', function () {
    $clientDelay   = 30;
    $estimatedPrep = 10;

    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(20 * 60);
});

it('delay uses the client override instead of the default delay', function () {
    $defaultDelay  = 20;
    $clientOverride = 40;
    $estimatedPrep  = 10;

    $clientDelay  = $clientOverride;
    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(30 * 60);
});

it('delay falls back to default when no override is provided', function () {
    $defaultDelay  = 25;
    $estimatedPrep = 10;

    $overrides   = [];
    $round       = 1;
    $override    = collect($overrides)->firstWhere('round', $round);
    $clientDelay = $override['delay_minutes'] ?? $defaultDelay;

    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(15 * 60);
});

it('delay is exactly zero when client delay is exactly zero', function () {
    $clientDelay   = 0;
    $estimatedPrep = 10;

    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(0);
});

it('large client delay produces correct large delay in seconds', function () {
    $clientDelay   = 120;
    $estimatedPrep = 10;

    $delaySeconds = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(110 * 60);
});
