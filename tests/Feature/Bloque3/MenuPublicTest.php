<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    Cache::flush();
    $this->setupTenants();
});

// ─── Acceso público ───────────────────────────────────────────────────────────

it('shows public menu without authentication', function () {
    $table = Table::factory()->for($this->user)->create();

    $this->get(route('menu.show', $table->unique_hash))
        ->assertOk();
});

it('returns 404 with invalid hash', function () {
    $this->get(route('menu.show', 'hash-que-no-existe-jamás'))
        ->assertNotFound();
});

// ─── Productos activos / inactivos ────────────────────────────────────────────

it('shows only active products on public menu', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $active   = Product::factory()->for($category)->for($this->user)->create(['name' => 'Plato Activo', 'is_active' => true]);

    $this->get(route('menu.show', $table->unique_hash))
        ->assertOk()
        ->assertSee('Plato Activo');
});

it('does not show inactive products', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    Product::factory()->for($category)->for($this->user)->create(['name' => 'Plato Oculto', 'is_active' => false]);

    $this->get(route('menu.show', $table->unique_hash))
        ->assertOk()
        ->assertDontSee('Plato Oculto');
});

// ─── Categorías y productos ───────────────────────────────────────────────────

it('shows categories with their products', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['name' => 'Entrantes', 'destination' => 'kitchen']);
    Product::factory()->for($category)->for($this->user)->create(['name' => 'Patatas Bravas', 'is_active' => true]);

    $this->get(route('menu.show', $table->unique_hash))
        ->assertOk()
        ->assertSee('Entrantes')
        ->assertSee('Patatas Bravas');
});

it('only shows products of the restaurant that owns the table', function () {
    $myTable       = Table::factory()->for($this->user)->create();
    $myCategory    = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $myProduct     = Product::factory()->for($myCategory)->for($this->user)->create(['name' => 'Mi Producto', 'is_active' => true]);

    $otherCategory = Category::factory()->for($this->other)->create(['destination' => 'kitchen']);
    $otherProduct  = Product::factory()->for($otherCategory)->for($this->other)->create(['name' => 'Producto Ajeno', 'is_active' => true]);

    $this->get(route('menu.show', $myTable->unique_hash))
        ->assertOk()
        ->assertSee('Mi Producto')
        ->assertDontSee('Producto Ajeno');
});

// ─── Variables de vista ───────────────────────────────────────────────────────

it('passes table to view', function () {
    $table = Table::factory()->for($this->user)->create();

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('table')->id)->toBe($table->id);
});

it('passes categories to view', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    Product::factory()->for($category)->for($this->user)->create(['is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    expect($response->viewData('categories'))->not->toBeEmpty();
});

it('passes allergens to view for filters', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $product  = Product::factory()->for($category)->for($this->user)->create(['is_active' => true]);
    $allergen = Ingredient::factory()->for($this->user)->create(['name' => 'Queso', 'is_allergen' => true, 'allergen_types' => ['lacteos']]);

    $product->ingredients()->attach($allergen->id, [
        'quantity_base' => 1,
        'is_removable'  => false,
        'is_extra'      => false,
        'extra_price'   => 0,
    ]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    $allergens = $response->viewData('allergens');
    expect($allergens)->not->toBeEmpty();
});

// ─── Rate limiting ────────────────────────────────────────────────────────────

it('returns 429 after exceeding 60 requests per minute', function () {
    $table = Table::factory()->for($this->user)->create();

    for ($i = 0; $i < 60; $i++) {
        $this->get(route('menu.show', $table->unique_hash))->assertOk();
    }

    $this->get(route('menu.show', $table->unique_hash))->assertTooManyRequests();
});
