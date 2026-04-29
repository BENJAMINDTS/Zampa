<?php

/**
 * @author AyrtonAlania
 */

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin      = User::factory()->create(['role' => 'admin']);
    $this->otherAdmin = User::factory()->create(['role' => 'admin']);

    $this->waiter  = User::factory()->waiter()->staffOf($this->admin)->create();
    $this->kitchen = User::factory()->kitchen()->staffOf($this->admin)->create();

    // Recursos del admin
    $this->adminCategory   = Category::factory()->create(['user_id' => $this->admin->id, 'name' => 'Categoría del Admin']);
    $this->adminIngredient = Ingredient::factory()->create(['user_id' => $this->admin->id, 'name' => 'Ingrediente del Admin']);
    $this->adminProduct    = Product::factory()->create([
        'user_id'     => $this->admin->id,
        'category_id' => $this->adminCategory->id,
        'name'        => 'Producto del Admin',
    ]);

    // Recursos de otro admin (no deben ser visibles)
    $this->otherCategory = Category::factory()->create(['user_id' => $this->otherAdmin->id, 'name' => 'Categoría Ajena']);
    $this->otherProduct  = Product::factory()->create([
        'user_id'     => $this->otherAdmin->id,
        'category_id' => $this->otherCategory->id,
        'name'        => 'Producto Ajeno',
    ]);
});

// ─── CATEGORÍAS ───────────────────────────────────────────────────────────────

it('staff waiter can see their admins categories', function () {
    $this->actingAs($this->waiter)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Categoría del Admin');
});

it('staff kitchen can see their admins categories', function () {
    $this->actingAs($this->kitchen)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Categoría del Admin');
});

it('staff cannot see another admins categories', function () {
    $this->actingAs($this->waiter)
        ->get(route('categories.index'))
        ->assertDontSee('Categoría Ajena');
});

// ─── INGREDIENTES ─────────────────────────────────────────────────────────────

it('staff waiter can see their admins ingredients', function () {
    $this->actingAs($this->waiter)
        ->get(route('ingredients.index'))
        ->assertOk()
        ->assertSee('Ingrediente del Admin');
});

it('staff cannot see another admins ingredients', function () {
    $otherIngredient = Ingredient::factory()->create([
        'user_id' => $this->otherAdmin->id,
        'name'    => 'Ingrediente Ajeno',
    ]);

    $this->actingAs($this->waiter)
        ->get(route('ingredients.index'))
        ->assertDontSee('Ingrediente Ajeno');
});

// ─── PRODUCTOS ────────────────────────────────────────────────────────────────

it('staff kitchen can see their admins products', function () {
    $this->actingAs($this->kitchen)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('Producto del Admin');
});

it('staff cannot see another admins products', function () {
    $this->actingAs($this->waiter)
        ->get(route('products.index'))
        ->assertDontSee('Producto Ajeno');
});

// ─── ownerUserId EN ABORT_IF ──────────────────────────────────────────────────

it('staff waiter can edit their admins category', function () {
    $this->actingAs($this->waiter)
        ->get(route('categories.edit', $this->adminCategory))
        ->assertOk();
});

it('staff returns 403 editing a category from another admin', function () {
    $this->actingAs($this->waiter)
        ->get(route('categories.edit', $this->otherCategory))
        ->assertForbidden();
});
