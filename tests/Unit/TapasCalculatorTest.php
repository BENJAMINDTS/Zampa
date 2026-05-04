<?php

/**
 * Unit tests para los helpers de TapaConfig:
 * isKitchenOpen() y shouldSuggestTapa().
 *
 * @author BenjaminDTS
 */

use App\Models\KitchenSchedule;
use App\Models\TapaConfig;
use Illuminate\Support\Carbon;

// ─── isKitchenOpen ────────────────────────────────────────────────────────────

it('isKitchenOpen returns true with no schedules configured', function () {
    $config = new TapaConfig();
    $config->setRelation('schedules', collect());

    expect($config->isKitchenOpen())->toBeTrue();
});

it('isKitchenOpen returns true when current time is within schedule', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = new TapaConfig();
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isKitchenOpen returns false when current time is outside schedule', function () {
    Carbon::setTestNow('2026-05-03 09:00:00');

    $config = new TapaConfig();
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeFalse();

    Carbon::setTestNow();
});

it('isKitchenOpen supports ranges crossing midnight', function () {
    Carbon::setTestNow('2026-05-03 01:00:00');

    $config = new TapaConfig();
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '22:00:00', 'closes_at' => '02:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isKitchenOpen returns false at midday when range crosses midnight', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = new TapaConfig();
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '22:00:00', 'closes_at' => '02:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeFalse();

    Carbon::setTestNow();
});

it('isKitchenOpen returns true when time matches any of multiple schedules', function () {
    Carbon::setTestNow('2026-05-03 21:00:00');

    $config = new TapaConfig();
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '13:00:00', 'closes_at' => '16:30:00']),
        new KitchenSchedule(['opens_at' => '20:00:00', 'closes_at' => '23:30:00']),
    ]));

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isKitchenOpen returns false when time falls in none of multiple schedules', function () {
    Carbon::setTestNow('2026-05-03 18:00:00');

    $config = new TapaConfig();
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '13:00:00', 'closes_at' => '16:30:00']),
        new KitchenSchedule(['opens_at' => '20:00:00', 'closes_at' => '23:30:00']),
    ]));

    expect($config->isKitchenOpen())->toBeFalse();

    Carbon::setTestNow();
});

// ─── shouldSuggestTapa ────────────────────────────────────────────────────────

it('shouldSuggestTapa returns false when tapas disabled even if kitchen is open', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = new TapaConfig(['tapas_enabled' => false, 'max_tapa_variants' => 3]);
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->shouldSuggestTapa(2, 0))->toBeFalse();

    Carbon::setTestNow();
});

it('shouldSuggestTapa returns false when kitchen closed even if tapas enabled', function () {
    Carbon::setTestNow('2026-05-03 09:00:00');

    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 3]);
    $config->setRelation('schedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->shouldSuggestTapa(2, 0))->toBeFalse();

    Carbon::setTestNow();
});

it('shouldSuggestTapa returns false when barItemsCount is zero', function () {
    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 3]);
    $config->setRelation('schedules', collect());

    expect($config->shouldSuggestTapa(0, 0))->toBeFalse();
});

it('shouldSuggestTapa returns false when max variants already reached', function () {
    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 2]);
    $config->setRelation('schedules', collect());

    expect($config->shouldSuggestTapa(3, 2))->toBeFalse();
});

it('shouldSuggestTapa returns true when all conditions are met', function () {
    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 3]);
    $config->setRelation('schedules', collect());

    expect($config->shouldSuggestTapa(2, 1))->toBeTrue();
});
