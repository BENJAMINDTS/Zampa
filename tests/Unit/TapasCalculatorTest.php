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
    $config->setRelation('kitchenSchedules', collect());

    expect($config->isKitchenOpen())->toBeTrue();
});

it('isKitchenOpen returns true when current time is within schedule', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = new TapaConfig();
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isKitchenOpen returns false when current time is outside schedule', function () {
    Carbon::setTestNow('2026-05-03 09:00:00');

    $config = new TapaConfig();
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeFalse();

    Carbon::setTestNow();
});

it('isKitchenOpen supports ranges crossing midnight', function () {
    Carbon::setTestNow('2026-05-03 01:00:00');

    $config = new TapaConfig();
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '22:00:00', 'closes_at' => '02:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isKitchenOpen returns false at midday when range crosses midnight', function () {
    Carbon::setTestNow('2026-05-03 12:00:00');

    $config = new TapaConfig();
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '22:00:00', 'closes_at' => '02:00:00']),
    ]));

    expect($config->isKitchenOpen())->toBeFalse();

    Carbon::setTestNow();
});

it('isKitchenOpen returns true when time matches any of multiple schedules', function () {
    Carbon::setTestNow('2026-05-03 21:00:00');

    $config = new TapaConfig();
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '13:00:00', 'closes_at' => '16:30:00']),
        new KitchenSchedule(['opens_at' => '20:00:00', 'closes_at' => '23:30:00']),
    ]));

    expect($config->isKitchenOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isKitchenOpen returns false when time falls in none of multiple schedules', function () {
    Carbon::setTestNow('2026-05-03 18:00:00');

    $config = new TapaConfig();
    $config->setRelation('kitchenSchedules', collect([
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
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->shouldSuggestTapa(2, 0))->toBeFalse();

    Carbon::setTestNow();
});

it('shouldSuggestTapa returns false when kitchen closed even if tapas enabled', function () {
    Carbon::setTestNow('2026-05-03 09:00:00');

    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 3]);
    $config->setRelation('kitchenSchedules', collect([
        new KitchenSchedule(['opens_at' => '10:00:00', 'closes_at' => '23:00:00']),
    ]));

    expect($config->shouldSuggestTapa(2, 0))->toBeFalse();

    Carbon::setTestNow();
});

it('shouldSuggestTapa returns false when barItemsCount is zero', function () {
    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 3]);
    $config->setRelation('kitchenSchedules', collect());

    expect($config->shouldSuggestTapa(0, 0))->toBeFalse();
});

it('shouldSuggestTapa returns false when max variants already reached', function () {
    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 2]);
    $config->setRelation('kitchenSchedules', collect());

    expect($config->shouldSuggestTapa(3, 2))->toBeFalse();
});

it('shouldSuggestTapa returns true when all conditions are met', function () {
    $config = new TapaConfig(['tapas_enabled' => true, 'max_tapa_variants' => 3]);
    $config->setRelation('kitchenSchedules', collect());

    expect($config->shouldSuggestTapa(2, 1))->toBeTrue();
});

// ─── getPriceForProduct ───────────────────────────────────────────────────────

it('getPriceForProduct returns 0 when tapas are free regardless of product price', function () {
    $config = new TapaConfig([
        'tapas_enabled' => true,
        'tapas_free'    => true,
        'price_mode'    => 'fixed',
        'tapa_price'    => '3.00',
    ]);

    $product        = new \App\Models\Product();
    $product->price = 9.99;

    expect($config->getPriceForProduct($product))->toBe(0.0);
});

it('getPriceForProduct returns global fixed price when mode is fixed', function () {
    $config = new TapaConfig([
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'price_mode'    => 'fixed',
        'tapa_price'    => '1.50',
    ]);

    $product        = new \App\Models\Product();
    $product->price = 9.99;

    expect($config->getPriceForProduct($product))->toBe(1.50);
});

it('getPriceForProduct returns product price when mode is per_product', function () {
    $config = new TapaConfig([
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'price_mode'    => 'per_product',
        'tapa_price'    => null,
    ]);

    $product        = new \App\Models\Product();
    $product->price = 3.75;

    expect($config->getPriceForProduct($product))->toBe(3.75);
});

it('getPriceForProduct returns 0 when tapas are free even with per_product mode', function () {
    $config = new TapaConfig([
        'tapas_enabled' => true,
        'tapas_free'    => true,
        'price_mode'    => 'per_product',
        'tapa_price'    => null,
    ]);

    $product        = new \App\Models\Product();
    $product->price = 5.00;

    expect($config->getPriceForProduct($product))->toBe(0.0);
});

it('getPriceForProduct returns 0 when fixed mode and tapa_price is null', function () {
    $config = new TapaConfig([
        'tapas_enabled' => true,
        'tapas_free'    => false,
        'price_mode'    => 'fixed',
        'tapa_price'    => null,
    ]);

    $product        = new \App\Models\Product();
    $product->price = 4.00;

    expect($config->getPriceForProduct($product))->toBe(0.0);
});

// ─── isBusinessOpen ───────────────────────────────────────────────────────────

it('isBusinessOpen returns true with no schedule configured', function () {
    $config = new TapaConfig(['ordering_cutoff_minutes' => 0]);
    $config->setRelation('businessSchedules', collect());

    expect($config->isBusinessOpen())->toBeTrue();
});

it('isBusinessOpen returns true inside any active slot', function () {
    Carbon::setTestNow('2026-05-21 14:00:00');

    $config = new TapaConfig(['ordering_cutoff_minutes' => 0]);
    $config->setRelation('businessSchedules', collect([
        new KitchenSchedule(['type' => 'business', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']),
    ]));

    expect($config->isBusinessOpen())->toBeTrue();

    Carbon::setTestNow();
});

it('isBusinessOpen returns false outside all slots', function () {
    Carbon::setTestNow('2026-05-21 12:30:00');

    $config = new TapaConfig(['ordering_cutoff_minutes' => 0]);
    $config->setRelation('businessSchedules', collect([
        new KitchenSchedule(['type' => 'business', 'opens_at' => '07:00:00', 'closes_at' => '12:00:00']),
        new KitchenSchedule(['type' => 'business', 'opens_at' => '13:00:00', 'closes_at' => '16:30:00']),
    ]));

    expect($config->isBusinessOpen())->toBeFalse();

    Carbon::setTestNow();
});

// ─── isOrderingAllowed ────────────────────────────────────────────────────────

it('isOrderingAllowed returns false when within closing minutes', function () {
    Carbon::setTestNow('2026-05-21 23:20:00');

    $config = new TapaConfig(['ordering_cutoff_minutes' => 15]);
    $config->setRelation('businessSchedules', collect([
        new KitchenSchedule(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']),
    ]));

    expect($config->isOrderingAllowed())->toBeFalse();

    Carbon::setTestNow();
});

it('isOrderingAllowed returns true when not in closing period', function () {
    Carbon::setTestNow('2026-05-21 22:00:00');

    $config = new TapaConfig(['ordering_cutoff_minutes' => 15]);
    $config->setRelation('businessSchedules', collect([
        new KitchenSchedule(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']),
    ]));

    expect($config->isOrderingAllowed())->toBeTrue();

    Carbon::setTestNow();
});

it('isOrderingAllowed returns true when cutoff is 0', function () {
    Carbon::setTestNow('2026-05-21 23:29:00');

    $config = new TapaConfig(['ordering_cutoff_minutes' => 0]);
    $config->setRelation('businessSchedules', collect([
        new KitchenSchedule(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']),
    ]));

    expect($config->isOrderingAllowed())->toBeTrue();

    Carbon::setTestNow();
});

it('minutesUntilBusinessClose returns null when no business schedules', function () {
    $config = new TapaConfig(['ordering_cutoff_minutes' => 0]);
    $config->setRelation('businessSchedules', collect());

    expect($config->minutesUntilBusinessClose())->toBeNull();
});

it('minutesUntilBusinessClose returns positive integer when inside slot', function () {
    Carbon::setTestNow('2026-05-21 22:00:00');

    $config = new TapaConfig(['ordering_cutoff_minutes' => 15]);
    $config->setRelation('businessSchedules', collect([
        new KitchenSchedule(['type' => 'business', 'opens_at' => '19:00:00', 'closes_at' => '23:30:00']),
    ]));

    $minutes = $config->minutesUntilBusinessClose();
    expect($minutes)->toBeInt()->toBeGreaterThan(0);

    Carbon::setTestNow();
});
