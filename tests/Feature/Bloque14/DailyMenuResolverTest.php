<?php

use App\Models\DailyMenu;
use App\Models\Plan;
use App\Models\User;

beforeEach(function () {
    $plan        = Plan::factory()->create();
    $this->user  = User::factory()->admin()->create(['plan_id' => $plan->id]);
});

// ─── forToday() ──────────────────────────────────────────────────────────────

it('returns a menu matching todays day of week', function () {
    $today = strtolower(now()->englishDayOfWeek);

    $menu = DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $today,
        'is_active'   => true,
    ]);

    $result = DailyMenu::forToday($this->user->id);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($menu->id);
});

it('returns null when no menu matches today', function () {
    $notToday = collect(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'])
        ->reject(fn ($d) => $d === strtolower(now()->englishDayOfWeek))
        ->first();

    DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $notToday,
        'is_active'   => true,
    ]);

    expect(DailyMenu::forToday($this->user->id))->toBeNull();
});

it('returns null when the matching menu is inactive', function () {
    $today = strtolower(now()->englishDayOfWeek);

    DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $today,
        'is_active'   => false,
    ]);

    expect(DailyMenu::forToday($this->user->id))->toBeNull();
});

it('specific_date takes priority over day_of_week', function () {
    $today    = strtolower(now()->englishDayOfWeek);
    $todayDate = today()->toDateString();

    $byDay = DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $today,
        'is_active'   => true,
    ]);

    $byDate = DailyMenu::factory()->create([
        'user_id'       => $this->user->id,
        'specific_date' => $todayDate,
        'day_of_week'   => null,
        'is_active'     => true,
    ]);

    $result = DailyMenu::forToday($this->user->id);

    expect($result->id)->toBe($byDate->id);
});

it('falls back to day_of_week menu when specific_date menu is inactive', function () {
    $today    = strtolower(now()->englishDayOfWeek);
    $todayDate = today()->toDateString();

    DailyMenu::factory()->create([
        'user_id'       => $this->user->id,
        'specific_date' => $todayDate,
        'day_of_week'   => null,
        'is_active'     => false,
    ]);

    DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $today,
        'is_active'   => true,
    ]);

    // specific_date inactive → fallback to day_of_week
    $result = DailyMenu::forToday($this->user->id);

    expect($result)->not->toBeNull();
    expect($result->day_of_week)->toBe($today);
});

it('does not return menus from another user', function () {
    $plan       = Plan::factory()->create();
    $otherUser  = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $today      = strtolower(now()->englishDayOfWeek);

    DailyMenu::factory()->create([
        'user_id'     => $otherUser->id,
        'day_of_week' => $today,
        'is_active'   => true,
    ]);

    expect(DailyMenu::forToday($this->user->id))->toBeNull();
});
