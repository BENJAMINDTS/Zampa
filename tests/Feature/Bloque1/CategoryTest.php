<?php

use App\Models\Category;
use App\Models\User;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── Autenticación ───────────────────────────────────────────────────────────

it('redirects unauthenticated user away from categories index', function () {
    $this->get(route('categories.index'))
        ->assertRedirect(route('login'));
});

it('redirects unauthenticated user away from category create', function () {
    $this->get(route('categories.create'))
        ->assertRedirect(route('login'));
});

it('redirects unauthenticated user away from category edit', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $this->get(route('categories.edit', $category))
        ->assertRedirect(route('login'));
});

// ─── Index ───────────────────────────────────────────────────────────────────

it('shows categories index to authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('categories.index'))
        ->assertOk();
});

it('shows only the authenticated users categories', function () {
    Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Mis Tapas']);
    Category::factory()->create(['user_id' => $this->other->id, 'name' => 'Ajenas']);

    $this->actingAs($this->user)
        ->get(route('categories.index'))
        ->assertSee('Mis Tapas')
        ->assertDontSee('Ajenas');
});

it('paginates categories at 15 per page', function () {
    Category::factory()->count(20)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user)
        ->get(route('categories.index'));

    $response->assertOk();
    expect($response->viewData('categories')->count())->toBe(15);
});

// ─── Create ──────────────────────────────────────────────────────────────────

it('shows category create form', function () {
    $this->actingAs($this->user)
        ->get(route('categories.create'))
        ->assertOk();
});

it('creates a category with valid data', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name'        => 'Entrantes',
            'destination' => 'kitchen',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name'        => 'Entrantes',
        'destination' => 'kitchen',
    ]);
});

it('assigns user_id of authenticated user on create', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name'        => 'Bebidas',
            'destination' => 'bar',
        ]);

    $this->assertDatabaseHas('categories', [
        'name'    => 'Bebidas',
        'user_id' => $this->user->id,
    ]);
});

it('fails to create a category without name', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'destination' => 'kitchen',
        ])
        ->assertSessionHasErrors('name');
});

it('fails to create a category with invalid destination', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name'        => 'Postres',
            'destination' => 'oven',
        ])
        ->assertSessionHasErrors('destination');
});

it('fails to create a category without destination', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name' => 'Postres',
        ])
        ->assertSessionHasErrors('destination');
});

it('accepts kitchen as valid destination', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name'        => 'Cocina',
            'destination' => 'kitchen',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', ['destination' => 'kitchen']);
});

it('accepts bar as valid destination', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name'        => 'Barra',
            'destination' => 'bar',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', ['destination' => 'bar']);
});

// ─── Edit / Update ───────────────────────────────────────────────────────────

it('shows category edit form to owner', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('categories.edit', $category))
        ->assertOk();
});

it('returns 403 when user tries to edit another users category', function () {
    $category = Category::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->get(route('categories.edit', $category))
        ->assertStatus(403);
});

it('updates a category with valid data', function () {
    $category = Category::factory()->create([
        'user_id'     => $this->user->id,
        'name'        => 'Original',
        'destination' => 'kitchen',
    ]);

    $this->actingAs($this->user)
        ->put(route('categories.update', $category), [
            'name'        => 'Actualizado',
            'destination' => 'bar',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'id'          => $category->id,
        'name'        => 'Actualizado',
        'destination' => 'bar',
    ]);
});

it('returns 403 when user tries to update another users category', function () {
    $category = Category::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->put(route('categories.update', $category), [
            'name'        => 'Hack',
            'destination' => 'bar',
        ])
        ->assertStatus(403);
});

it('fails to update a category with empty name', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->put(route('categories.update', $category), [
            'name'        => '',
            'destination' => 'bar',
        ])
        ->assertSessionHasErrors('name');
});

// ─── Delete ──────────────────────────────────────────────────────────────────

it('deletes a category owned by the user', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

it('returns 403 when user tries to delete another users category', function () {
    $category = Category::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->delete(route('categories.destroy', $category))
        ->assertStatus(403);
});

it('returns 404 when trying to delete a non existent category', function () {
    $this->actingAs($this->user)
        ->delete(route('categories.destroy', 9999))
        ->assertStatus(404);
});

// ─── Flash messages ──────────────────────────────────────────────────────────

it('shows success flash message after create', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name'        => 'Ensaladas',
            'destination' => 'kitchen',
        ])
        ->assertSessionHas('success');
});

it('shows success flash message after update', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->put(route('categories.update', $category), [
            'name'        => 'Nuevo Nombre',
            'destination' => 'kitchen',
        ])
        ->assertSessionHas('success');
});

it('shows success flash message after delete', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('categories.destroy', $category))
        ->assertSessionHas('success');
});
