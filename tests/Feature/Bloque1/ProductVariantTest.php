<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user  = User::factory()->create();
    $this->other = User::factory()->create();
});

// ─── Gestión admin ────────────────────────────────────────────────────────────

it('gerente can add variants to a product', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0],
                ['name' => 'Media ración',  'price' => 7.50,  'sort_order' => 1],
            ],
        ])
        ->assertRedirect(route('products.index'));

    expect(ProductVariant::where('product_id', $product->id)->count())->toBe(2);
    expect($product->fresh()->price)->toBeNull();
});

it('gerente can edit an existing variant by resaving', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id, 'price' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Tapa', 'price' => 5.00, 'sort_order' => 0]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => 'Ración entera', 'price' => 14.00, 'sort_order' => 0],
            ],
        ])
        ->assertRedirect(route('products.index'));

    expect(ProductVariant::where('product_id', $product->id)->count())->toBe(1);
    expect(ProductVariant::where('product_id', $product->id)->first()->name)->toBe('Ración entera');
});

it('gerente can remove a variant from a product', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id, 'price' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Tapa', 'price' => 5.00, 'sort_order' => 0]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'price'       => 9.99,
            'category_id' => $cat->id,
        ])
        ->assertRedirect(route('products.index'));

    expect(ProductVariant::where('product_id', $product->id)->count())->toBe(0);
    expect((float) $product->fresh()->price)->toBe(9.99);
});

it('product with variants does not require direct price', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0],
            ],
        ])
        ->assertRedirect(route('products.index'));
});

it('product without variants requires direct price', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
        ])
        ->assertSessionHasErrors('price');
});

it('variant name is required', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => '', 'price' => 12.00, 'sort_order' => 0],
            ],
        ])
        ->assertSessionHasErrors('variants.0.name');
});

it('variant price must be positive', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => 'Ración', 'price' => 0, 'sort_order' => 0],
            ],
        ])
        ->assertRedirect(route('products.index'));
});

it('variant price cannot be negative', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'description' => $product->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => 'Ración', 'price' => -5.00, 'sort_order' => 0],
            ],
        ])
        ->assertSessionHasErrors('variants.0.price');
});

it('variants are deleted when product is deleted', function () {
    $cat     = Category::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat->id, 'price' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Tapa', 'price' => 5.00, 'sort_order' => 0]);

    expect(ProductVariant::where('product_id', $product->id)->count())->toBe(1);

    $this->actingAs($this->user)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    // Soft delete: product exists but variants cascade deleted
    expect(ProductVariant::where('product_id', $product->id)->count())->toBe(0);
});

it('gerente cannot add variants to another admins product', function () {
    $cat         = Category::factory()->create(['user_id' => $this->other->id]);
    $otherProduct = Product::factory()->create(['user_id' => $this->other->id, 'category_id' => $cat->id]);

    $this->actingAs($this->user)
        ->put(route('products.update', $otherProduct), [
            'name'        => $otherProduct->name,
            'description' => $otherProduct->description,
            'category_id' => $cat->id,
            'variants'    => [
                ['name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0],
            ],
        ])
        ->assertForbidden();

    expect(ProductVariant::where('product_id', $otherProduct->id)->count())->toBe(0);
});

it('variants are scoped to the product and restaurant', function () {
    $cat1 = Category::factory()->create(['user_id' => $this->user->id]);
    $cat2 = Category::factory()->create(['user_id' => $this->other->id]);
    $p1   = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $cat1->id, 'price' => null]);
    $p2   = Product::factory()->create(['user_id' => $this->other->id, 'category_id' => $cat2->id, 'price' => null]);
    ProductVariant::factory()->create(['product_id' => $p1->id, 'name' => 'Ración', 'price' => 10.00, 'sort_order' => 0]);
    ProductVariant::factory()->create(['product_id' => $p2->id, 'name' => 'Tapa', 'price' => 5.00, 'sort_order' => 0]);

    expect($p1->variants()->count())->toBe(1);
    expect($p2->variants()->count())->toBe(1);
    expect($p1->variants()->first()->name)->toBe('Ración');
});

// ─── Carta pública ────────────────────────────────────────────────────────────

it('product with variants shows variant options in menu', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Media ración',  'price' => 7.50,  'sort_order' => 1]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertStatus(200);
    $response->assertSee('Ración entera');
    $response->assertSee('Media ración');
});

it('product without variants shows direct price in menu', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => 9.99, 'is_active' => true]);

    $response = $this->get(route('menu.show', $table->unique_hash));

    $response->assertStatus(200);
    $response->assertSee('9,99');
});

it('customer can add product with variant to cart', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0]);

    $response = $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items' => [
            ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1],
        ],
    ]);

    $response->assertStatus(201)->assertJson(['success' => true]);
});

it('order item saves variant_id when variant is selected', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items' => [
            ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1],
        ],
    ]);

    $orderItem = OrderItem::latest()->first();
    expect($orderItem->variant_id)->toBe($variant->id);
});

it('order item saves variant name snapshot', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Media ración', 'price' => 7.50, 'sort_order' => 0]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items' => [
            ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1],
        ],
    ]);

    $orderItem = OrderItem::latest()->first();
    expect($orderItem->variant_name)->toBe('Media ración');
});

it('order item saves variant price as snapshot', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración entera', 'price' => 12.00, 'sort_order' => 0]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items' => [
            ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1],
        ],
    ]);

    $orderItem = OrderItem::latest()->first();
    expect((float) $orderItem->price)->toBe(12.00);
});

it('fails to create order item with variant from another product', function () {
    $table    = Table::factory()->create(['is_service_point' => true]);
    $cat      = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product1 = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    $product2 = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    ProductVariant::factory()->create(['product_id' => $product1->id, 'name' => 'Ración', 'price' => 10.00, 'sort_order' => 0]);
    $variantOfProduct2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'name' => 'Ración', 'price' => 10.00, 'sort_order' => 0]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items' => [
            ['product_id' => $product1->id, 'variant_id' => $variantOfProduct2->id, 'quantity' => 1],
        ],
    ])->assertStatus(422);
});

it('fails to create order item for product with variants without variant_id', function () {
    $table   = Table::factory()->create(['is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $table->user_id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $table->user_id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Ración', 'price' => 10.00, 'sort_order' => 0]);

    $this->postJson('/api/v1/orders', [
        'table_hash' => $table->unique_hash,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertStatus(422);
});

// ─── Panel de cocina ─────────────────────────────────────────────────────────

it('kitchen panel shows variant name next to product name', function () {
    $user    = $this->user;
    $user->update(['role' => 'kitchen']);
    $table   = Table::factory()->create(['user_id' => $user->id, 'is_service_point' => true]);
    $cat     = Category::factory()->create(['user_id' => $user->id, 'destination' => 'kitchen']);
    $product = Product::factory()->create(['user_id' => $user->id, 'category_id' => $cat->id, 'price' => null, 'is_active' => true]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Media racion', 'price' => 7.50, 'sort_order' => 0]);

    $order = Order::factory()->create(['table_id' => $table->id, 'status' => 'pending', 'total' => 7.50]);
    OrderItem::factory()->create([
        'order_id'     => $order->id,
        'product_id'   => $product->id,
        'variant_id'   => $variant->id,
        'variant_name' => 'Media racion',
        'price'        => 7.50,
        'quantity'     => 1,
        'status'       => 'queued',
        'destination'  => 'kitchen',
    ]);

    $response = $this->actingAs($user)->get(route('kitchen.index'));

    $response->assertStatus(200);
    $response->assertSee('Media racion');
});
