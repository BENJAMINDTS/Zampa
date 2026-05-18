<?php

use App\Models\Table;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── unique_hash en creación ──────────────────────────────────────────────────

it('generates unique hash on table creation', function () {
    $table = Table::factory()->for($this->user)->create();

    expect($table->unique_hash)->not->toBeEmpty();
});

it('each table has a unique hash', function () {
    $tables = Table::factory()->for($this->user)->count(20)->create();

    $hashes = $tables->pluck('unique_hash');

    expect($hashes->unique()->count())->toBe(20);
});

it('two tables of different users never share the same hash', function () {
    $table1 = Table::factory()->for($this->user)->create();
    $table2 = Table::factory()->for($this->other)->create();

    expect($table1->unique_hash)->not->toBe($table2->unique_hash);
});

// ─── URLs de carta con hash ───────────────────────────────────────────────────

it('menu url with valid table hash returns 200', function () {
    $table = Table::factory()->for($this->user)->create();

    $this->get(route('menu.show', $table->unique_hash))
        ->assertOk();
});

it('menu url with wrong hash returns 404', function () {
    $this->get(route('menu.show', 'este-hash-no-existe'))
        ->assertNotFound();
});

// ─── Gestión de mesas — autenticación ────────────────────────────────────────

it('redirects unauthenticated user away from table management', function () {
    $this->get(route('tables.index'))
        ->assertRedirect(route('login'));
});

it('authenticated user can view their tables', function () {
    $this->actingAs($this->user)
        ->get(route('tables.index'))
        ->assertOk();
});

it('user only sees their own tables in management', function () {
    $myTable    = Table::factory()->for($this->user)->create(['name' => 'Mi Mesa Especial']);
    $otherTable = Table::factory()->for($this->other)->create(['name' => 'Mesa Ajena']);

    $response = $this->actingAs($this->user)
        ->get(route('tables.index'));

    $response->assertOk()
        ->assertSee('Mi Mesa Especial')
        ->assertDontSee('Mesa Ajena');
});
