<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── Autenticación ────────────────────────────────────────────────────────────

it('redirects unauthenticated user away from product ingredients', function () {
    $product = Product::factory()
        ->for(Category::factory()->for($this->user))
        ->for($this->user)
        ->create();

    $this->get(route('products.ingredients.edit', $product))
        ->assertRedirect(route('login'));
});

// ─── Edit Ingredients ─────────────────────────────────────────────────────────

it('shows ingredient management page for product owner', function () {
    $product = Product::factory()
        ->for(Category::factory()->for($this->user))
        ->for($this->user)
        ->create();

    $this->actingAs($this->user)
        ->get(route('products.ingredients.edit', $product))
        ->assertOk();
});

it('returns 403 when user tries to view ingredients of another users product', function () {
    $product = Product::factory()
        ->for(Category::factory()->for($this->other))
        ->for($this->other)
        ->create();

    $this->actingAs($this->user)
        ->get(route('products.ingredients.edit', $product))
        ->assertForbidden();
});

// ─── Sync Ingredients ─────────────────────────────────────────────────────────

it('assigns ingredients to a product with correct pivot data', function () {
    $product    = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [
                $ingredient->id => [
                    'included'      => true,
                    'quantity_base' => 2,
                    'is_removable'  => true,
                    'is_extra'      => false,
                    'extra_price'   => 0,
                ],
            ],
        ])
        ->assertRedirect(route('products.edit', $product));

    $this->assertDatabaseHas('ingredient_product', [
        'product_id'    => $product->id,
        'ingredient_id' => $ingredient->id,
        'quantity_base' => 2,
    ]);
});

it('saves is_extra and extra_price in pivot table', function () {
    $product    = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [
                $ingredient->id => [
                    'included'    => true,
                    'quantity_base' => 1,
                    'is_extra'    => true,
                    'extra_price' => 1.50,
                ],
            ],
        ]);

    $this->assertDatabaseHas('ingredient_product', [
        'product_id'    => $product->id,
        'ingredient_id' => $ingredient->id,
        'is_extra'      => 1,
        'extra_price'   => 1.50,
    ]);
});

it('saves is_removable in pivot table', function () {
    $product    = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [
                $ingredient->id => [
                    'included'     => true,
                    'quantity_base' => 1,
                    'is_removable' => true,
                    'extra_price'  => 0,
                ],
            ],
        ]);

    $this->assertDatabaseHas('ingredient_product', [
        'product_id'    => $product->id,
        'ingredient_id' => $ingredient->id,
        'is_removable'  => 1,
    ]);
});

it('syncs with empty array removes all ingredients', function () {
    $product    = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $product->ingredients()->attach($ingredient->id, [
        'quantity_base' => 1,
        'is_removable'  => false,
        'is_extra'      => false,
        'extra_price'   => 0,
    ]);

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [],
        ]);

    $this->assertDatabaseMissing('ingredient_product', [
        'product_id'    => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);
});

it('replaces existing ingredients on sync no accumulation', function () {
    $product      = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();
    $ingredient1  = Ingredient::factory()->for($this->user)->create();
    $ingredient2  = Ingredient::factory()->for($this->user)->create();

    $product->ingredients()->attach($ingredient1->id, [
        'quantity_base' => 1,
        'is_removable'  => false,
        'is_extra'      => false,
        'extra_price'   => 0,
    ]);

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [
                $ingredient2->id => [
                    'included'      => true,
                    'quantity_base' => 1,
                    'extra_price'   => 0,
                ],
            ],
        ]);

    $this->assertDatabaseMissing('ingredient_product', [
        'product_id'    => $product->id,
        'ingredient_id' => $ingredient1->id,
    ]);

    $this->assertDatabaseHas('ingredient_product', [
        'product_id'    => $product->id,
        'ingredient_id' => $ingredient2->id,
    ]);
});

it('returns 403 when user tries to sync ingredients of another users product', function () {
    $product    = Product::factory()->for(Category::factory()->for($this->other))->for($this->other)->create();
    $ingredient = Ingredient::factory()->for($this->other)->create();

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [],
        ])
        ->assertForbidden();
});

it('shows success flash message after sync', function () {
    $product = Product::factory()->for(Category::factory()->for($this->user))->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('products.ingredients.sync', $product), [
            'ingredients' => [],
        ])
        ->assertRedirect(route('products.edit', $product))
        ->assertSessionHas('success');
});
