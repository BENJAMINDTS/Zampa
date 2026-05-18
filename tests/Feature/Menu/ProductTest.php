<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    Storage::fake('public');
    $this->setupTenants();
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

// ─── Autenticación ───────────────────────────────────────────────────────────

it('redirects unauthenticated user away from products index', function () {
    $this->get(route('products.index'))
        ->assertRedirect(route('login'));
});

// ─── Index ───────────────────────────────────────────────────────────────────

it('shows products index to authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('products.index'))
        ->assertOk();
});

it('shows only the authenticated users products', function () {
    $otherCategory = Category::factory()->create(['user_id' => $this->other->id]);

    Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'name'        => 'Mi Hamburguesa',
    ]);
    Product::factory()->create([
        'user_id'     => $this->other->id,
        'category_id' => $otherCategory->id,
        'name'        => 'Ajena',
    ]);

    $this->actingAs($this->user)
        ->get(route('products.index'))
        ->assertSee('Mi Hamburguesa')
        ->assertDontSee('Ajena');
});

// ─── Create ──────────────────────────────────────────────────────────────────

it('shows product create form', function () {
    $this->actingAs($this->user)
        ->get(route('products.create'))
        ->assertOk();
});

it('creates a product without image', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Ensalada César',
            'description' => 'Clásica ensalada',
            'price'       => '8.50',
            'category_id' => $this->category->id,
        ])
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'name'  => 'Ensalada César',
        'image' => null,
    ]);
});

it('creates a product with image', function () {
    $image = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Burger Deluxe',
            'price'       => '12.00',
            'category_id' => $this->category->id,
            'image'       => $image,
        ])
        ->assertRedirect(route('products.index'));

    $product = Product::where('name', 'Burger Deluxe')->first();
    expect($product->image)->not->toBeNull();
});

it('saves image in storage products folder', function () {
    $image = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Producto Con Foto',
            'price'       => '10.00',
            'category_id' => $this->category->id,
            'image'       => $image,
        ]);

    $product = Product::where('name', 'Producto Con Foto')->first();
    Storage::disk('public')->assertExists($product->image);
    expect($product->image)->toStartWith('products/');
});

it('assigns user_id of authenticated user on create', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Plato Propio',
            'price'       => '9.00',
            'category_id' => $this->category->id,
        ]);

    $this->assertDatabaseHas('products', [
        'name'    => 'Plato Propio',
        'user_id' => $this->user->id,
    ]);
});

// ─── Validaciones de creación ─────────────────────────────────────────────────

it('fails to create a product without name', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'price'       => '10.00',
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasErrors('name');
});

it('fails to create a product without price', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Sin Precio',
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasErrors('price');
});

it('fails to create a product with negative price', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Precio Negativo',
            'price'       => '-5.00',
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasErrors('price');
});

it('fails to create a product with category belonging to another user', function () {
    $otherCategory = Category::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Intento de fraude',
            'price'       => '10.00',
            'category_id' => $otherCategory->id,
        ]);

    $this->assertDatabaseMissing('products', ['name' => 'Intento de fraude']);
});

it('fails to create a product with invalid image type', function () {
    $pdf = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Con PDF',
            'price'       => '10.00',
            'category_id' => $this->category->id,
            'image'       => $pdf,
        ])
        ->assertSessionHasErrors('image');
});

it('fails to create a product with image larger than 2MB', function () {
    $bigImage = UploadedFile::fake()->image('grande.jpg')->size(3000);

    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name'        => 'Imagen Grande',
            'price'       => '10.00',
            'category_id' => $this->category->id,
            'image'       => $bigImage,
        ])
        ->assertSessionHasErrors('image');
});

// ─── Edit / Update ───────────────────────────────────────────────────────────

it('shows product edit form to owner', function () {
    $product = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('products.edit', $product))
        ->assertOk();
});

it('returns 403 when user tries to edit another users product', function () {
    $otherCategory = Category::factory()->create(['user_id' => $this->other->id]);
    $product = Product::factory()->create([
        'user_id'     => $this->other->id,
        'category_id' => $otherCategory->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('products.edit', $product))
        ->assertStatus(403);
});

it('updates product name and price', function () {
    $product = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'name'        => 'Nombre Original',
        'price'       => '8.00',
    ]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => 'Nombre Actualizado',
            'price'       => '15.00',
            'category_id' => $this->category->id,
        ])
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'id'    => $product->id,
        'name'  => 'Nombre Actualizado',
        'price' => '15.00',
    ]);
});

it('replaces old image when updating with a new one', function () {
    $oldImage = UploadedFile::fake()->image('old.jpg');
    $oldPath  = $oldImage->store('products', 'public');

    $product = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'image'       => $oldPath,
    ]);

    $newImage = UploadedFile::fake()->image('new.jpg');

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => $product->name,
            'price'       => $product->price,
            'category_id' => $this->category->id,
            'image'       => $newImage,
        ]);

    Storage::disk('public')->assertMissing($oldPath);

    $updated = $product->fresh();
    Storage::disk('public')->assertExists($updated->image);
    expect($updated->image)->not->toBe($oldPath);
});

it('returns 403 when user tries to update another users product', function () {
    $otherCategory = Category::factory()->create(['user_id' => $this->other->id]);
    $product = Product::factory()->create([
        'user_id'     => $this->other->id,
        'category_id' => $otherCategory->id,
    ]);

    $this->actingAs($this->user)
        ->put(route('products.update', $product), [
            'name'        => 'Hack',
            'price'       => '1.00',
            'category_id' => $this->category->id,
        ])
        ->assertStatus(403);
});

// ─── Delete ──────────────────────────────────────────────────────────────────

it('deletes a product and removes its image from storage', function () {
    $image = UploadedFile::fake()->image('foto.jpg');
    $path  = $image->store('products', 'public');

    $product = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'image'       => $path,
    ]);

    $this->actingAs($this->user)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

it('returns 403 when user tries to delete another users product', function () {
    $otherCategory = Category::factory()->create(['user_id' => $this->other->id]);
    $product = Product::factory()->create([
        'user_id'     => $this->other->id,
        'category_id' => $otherCategory->id,
    ]);

    $this->actingAs($this->user)
        ->delete(route('products.destroy', $product))
        ->assertStatus(403);
});
