<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hasVariants returns true when product has variants', function () {
    $user    = User::factory()->create();
    $cat     = Category::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['user_id' => $user->id, 'category_id' => $cat->id, 'price' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración', 'price' => 10.00, 'sort_order' => 0]);

    expect($product->hasVariants())->toBeTrue();
});

it('hasVariants returns false when product has no variants', function () {
    $user    = User::factory()->create();
    $cat     = Category::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['user_id' => $user->id, 'category_id' => $cat->id]);

    expect($product->hasVariants())->toBeFalse();
});

it('getEffectivePrice returns direct price when no variants', function () {
    $user    = User::factory()->create();
    $cat     = Category::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['user_id' => $user->id, 'category_id' => $cat->id, 'price' => 9.99]);

    expect($product->getEffectivePrice())->toBe(9.99);
});

it('getEffectivePrice returns lowest variant price when variants exist', function () {
    $user    = User::factory()->create();
    $cat     = Category::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['user_id' => $user->id, 'category_id' => $cat->id, 'price' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Media ración',  'price' => 7.50,  'sort_order' => 1]);

    expect($product->getEffectivePrice())->toBe(7.50);
});
