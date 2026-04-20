<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Table;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── Relación allergens() en el modelo Product ────────────────────────────────

it('allergens relation returns only ingredients with is_allergen true', function () {
    $product     = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $allergen    = Ingredient::factory()->for($this->user)->create(['name' => 'Queso', 'is_allergen' => true, 'allergen_type' => 'lacteos']);
    $nonAllergen = Ingredient::factory()->for($this->user)->create(['name' => 'Lechuga', 'is_allergen' => false, 'allergen_type' => null]);

    $product->ingredients()->attach($allergen->id,    ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);
    $product->ingredients()->attach($nonAllergen->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);

    $allergens = $product->allergens()->get();

    expect($allergens)->toHaveCount(1);
    expect($allergens->first()->id)->toBe($allergen->id);
});

it('allergens relation returns empty when no allergens assigned', function () {
    $product     = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $nonAllergen = Ingredient::factory()->for($this->user)->create(['name' => 'Lechuga', 'is_allergen' => false, 'allergen_type' => null]);

    $product->ingredients()->attach($nonAllergen->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);

    expect($product->allergens()->get())->toBeEmpty();
});

it('allergens relation returns empty when no ingredients assigned', function () {
    $product = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();

    expect($product->allergens()->get())->toBeEmpty();
});

it('product with multiple allergens returns all of them', function () {
    $product   = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $allergen1 = Ingredient::factory()->for($this->user)->create(['name' => 'Queso', 'is_allergen' => true, 'allergen_type' => 'lacteos']);
    $allergen2 = Ingredient::factory()->for($this->user)->create(['name' => 'Huevo', 'is_allergen' => true, 'allergen_type' => 'huevos']);

    $product->ingredients()->attach($allergen1->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);
    $product->ingredients()->attach($allergen2->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);

    expect($product->allergens()->count())->toBe(2);
});

// ─── Carta pública y alérgenos ────────────────────────────────────────────────

it('public menu shows allergen names for products with allergens', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $product  = Product::factory()->for($category)->for($this->user)->create(['is_active' => true]);
    $allergen = Ingredient::factory()->for($this->user)->create(['name' => 'Queso', 'is_allergen' => true, 'allergen_type' => 'lacteos']);

    $product->ingredients()->attach($allergen->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);

    $this->get(route('menu.show', $table->unique_hash))
        ->assertOk()
        ->assertSee('Queso');
});

it('public menu view contains allergen data', function () {
    $table    = Table::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $product  = Product::factory()->for($category)->for($this->user)->create(['is_active' => true]);
    $allergen = Ingredient::factory()->for($this->user)->create(['name' => 'Bacon', 'is_allergen' => true, 'allergen_type' => 'gluten']);

    $product->ingredients()->attach($allergen->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertOk();
    $allergens = $response->viewData('allergens');
    expect($allergens)->not->toBeEmpty();
    expect($allergens->pluck('id')->contains($allergen->id))->toBeTrue();
});

it('allergen filter uses only active products', function () {
    $table           = Table::factory()->for($this->user)->create();
    $category        = Category::factory()->for($this->user)->create(['destination' => 'kitchen']);
    $activeProduct   = Product::factory()->for($category)->for($this->user)->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->for($category)->for($this->user)->create(['is_active' => false]);

    $allergen          = Ingredient::factory()->for($this->user)->create(['name' => 'Queso', 'is_allergen' => true, 'allergen_type' => 'lacteos']);
    $inactiveAllergen  = Ingredient::factory()->for($this->user)->create(['name' => 'Bacon', 'is_allergen' => true, 'allergen_type' => 'gluten']);

    $activeProduct->ingredients()->attach($allergen->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);
    $inactiveProduct->ingredients()->attach($inactiveAllergen->id, ['quantity_base' => 1, 'is_removable' => false, 'is_extra' => false, 'extra_price' => 0]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $allergens = $response->viewData('allergens');

    expect($allergens->pluck('id')->contains($allergen->id))->toBeTrue();
    expect($allergens->pluck('id')->contains($inactiveAllergen->id))->toBeFalse();
});
