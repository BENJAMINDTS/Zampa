<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->superadmin()->create();
    $this->plan       = Plan::factory()->create();
    $this->admin      = User::factory()->admin()->withBusiness()->create([
        'plan_id' => $this->plan->id,
    ]);
});

// ──────────────────────────────────────────────
// ACCESO
// ──────────────────────────────────────────────

it('shows map page to superadmin', function () {
    $this->actingAs($this->superadmin)
         ->get(route('superadmin.map'))
         ->assertOk()
         ->assertViewIs('superadmin.map');
});

it('returns 403 for admin trying to access map', function () {
    $this->actingAs($this->admin)
         ->get(route('superadmin.map'))
         ->assertForbidden();
});

it('returns 403 for waiter trying to access map', function () {
    $waiter = User::factory()->waiter()->create();

    $this->actingAs($waiter)
         ->get(route('superadmin.map'))
         ->assertForbidden();
});

// ──────────────────────────────────────────────
// FILTRADO DE DATOS
// ──────────────────────────────────────────────

it('map controller returns only admins with coordinates', function () {
    $adminNoCoords = User::factory()->admin()->create([
        'lat' => null,
        'lng' => null,
    ]);

    $response    = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses  = $response->viewData('businesses');

    expect($businesses)->toHaveCount(1);
    $ids = $businesses->pluck('name')->toArray();
    expect(in_array($adminNoCoords->name, $ids))->toBeFalse();
});

it('map controller does not return staff with null admin_id', function () {
    // Staff huérfano: waiter con admin_id = null
    $orphan = User::factory()->waiter()->create(['admin_id' => null]);

    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    $names = $businesses->pluck('name')->toArray();
    expect(in_array($orphan->name, $names))->toBeFalse();
});

it('map controller does not return admins without lat', function () {
    $adminNoLat = User::factory()->admin()->create([
        'lat' => null,
        'lng' => 0.0,
    ]);

    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    expect($businesses)->toHaveCount(1)
        ->and($businesses->pluck('name')->toArray())
        ->not->toContain($adminNoLat->business_name ?? $adminNoLat->name);
});

it('map controller does not return admins without lng', function () {
    $adminNoLng = User::factory()->admin()->create([
        'lat' => 40.0,
        'lng' => null,
    ]);

    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    expect($businesses)->toHaveCount(1)
        ->and($businesses->pluck('name')->toArray())
        ->not->toContain($adminNoLng->business_name ?? $adminNoLng->name);
});

it('businesses without coordinates are counted in withoutCoords', function () {
    User::factory()->admin()->create(['lat' => null, 'lng' => null]);
    User::factory()->admin()->create(['lat' => 40.0, 'lng' => null]);

    $response      = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $withoutCoords = $response->viewData('withoutCoords');

    expect($withoutCoords)->toBe(2);
});

it('withoutCoords only counts admins not all users', function () {
    // 1 admin sin coords
    User::factory()->admin()->create(['lat' => null, 'lng' => null]);
    // staff huérfano sin coords — no debe inflar el contador
    User::factory()->waiter()->create(['lat' => null, 'lng' => null]);

    $response      = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $withoutCoords = $response->viewData('withoutCoords');

    expect($withoutCoords)->toBe(1);
});

// ──────────────────────────────────────────────
// FORMA DE LOS DATOS
// ──────────────────────────────────────────────

it('map data includes plan name', function () {
    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    expect($businesses->first()['plan'])->toBe($this->plan->name);
});

it('map data falls back to sin plan when admin has no plan', function () {
    $adminNoPlan = User::factory()->admin()->withBusiness()->create(['plan_id' => null]);

    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    $found = $businesses->first(fn ($b) => $b['name'] === ($adminNoPlan->business_name ?? $adminNoPlan->name));
    expect($found['plan'])->toBe('Sin plan');
});

it('map data includes active status as boolean', function () {
    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    expect($businesses->first()['active'])->toBeBool();
});

it('map data casts lat and lng to float', function () {
    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    $b = $businesses->first();
    expect($b['lat'])->toBeFloat()
        ->and($b['lng'])->toBeFloat();
});

it('map data uses business_name when available', function () {
    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    expect($businesses->first()['name'])->toBe($this->admin->business_name);
});

it('map data falls back to name when business_name is null', function () {
    $adminNoBusinessName = User::factory()->admin()->create([
        'business_name' => null,
        'lat'           => 41.0,
        'lng'           => 2.0,
    ]);

    $response   = $this->actingAs($this->superadmin)->get(route('superadmin.map'));
    $businesses = $response->viewData('businesses');

    $found = $businesses->first(fn ($b) => $b['lat'] === 41.0);
    expect($found['name'])->toBe($adminNoBusinessName->name);
});
