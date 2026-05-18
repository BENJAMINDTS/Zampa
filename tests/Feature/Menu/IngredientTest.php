<?php

use App\Models\Ingredient;
use Tests\Support\CreatesTenants;

uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ─── Autenticación ───────────────────────────────────────────────────────────

it('redirects unauthenticated user away from ingredients index', function () {
    $this->get(route('ingredients.index'))
        ->assertRedirect(route('login'));
});

it('redirects unauthenticated user away from ingredient create', function () {
    $this->get(route('ingredients.create'))
        ->assertRedirect(route('login'));
});

// ─── Index ───────────────────────────────────────────────────────────────────

it('shows ingredients index to authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('ingredients.index'))
        ->assertOk();
});

it('shows only the authenticated users ingredients', function () {
    $mine  = Ingredient::factory()->for($this->user)->create(['name' => 'Albahaca']);
    $other = Ingredient::factory()->for($this->other)->create(['name' => 'Orégano']);

    $this->actingAs($this->user)
        ->get(route('ingredients.index'))
        ->assertSee('Albahaca')
        ->assertDontSee('Orégano');
});

it('paginates ingredients at 15 per page', function () {
    Ingredient::factory()->for($this->user)->count(20)->create();

    $response = $this->actingAs($this->user)
        ->get(route('ingredients.index'));

    $response->assertOk();
    expect($response->viewData('ingredients')->count())->toBe(15);
});

// ─── Create ──────────────────────────────────────────────────────────────────

it('shows ingredient create form', function () {
    $this->actingAs($this->user)
        ->get(route('ingredients.create'))
        ->assertOk();
});

it('creates a non allergen ingredient', function () {
    $this->actingAs($this->user)
        ->post(route('ingredients.store'), [
            'name'        => 'Lechuga',
            'is_allergen' => false,
        ])
        ->assertRedirect(route('ingredients.index'));

    $this->assertDatabaseHas('ingredients', [
        'name'        => 'Lechuga',
        'is_allergen' => false,
        'user_id'     => $this->user->id,
    ]);
});

it('creates an allergen ingredient', function () {
    $this->actingAs($this->user)
        ->post(route('ingredients.store'), [
            'name'        => 'Queso',
            'is_allergen' => true,
        ])
        ->assertRedirect(route('ingredients.index'));

    $this->assertDatabaseHas('ingredients', [
        'name'        => 'Queso',
        'is_allergen' => true,
        'user_id'     => $this->user->id,
    ]);
});

it('assigns user_id of authenticated user on create', function () {
    $this->actingAs($this->user)
        ->post(route('ingredients.store'), [
            'name'        => 'Tomate',
            'is_allergen' => false,
        ]);

    $this->assertDatabaseHas('ingredients', [
        'name'    => 'Tomate',
        'user_id' => $this->user->id,
    ]);
});

it('fails to create an ingredient without name', function () {
    $this->actingAs($this->user)
        ->post(route('ingredients.store'), [
            'is_allergen' => false,
        ])
        ->assertSessionHasErrors('name');
});

it('fails to create an ingredient with name longer than 255 characters', function () {
    $this->actingAs($this->user)
        ->post(route('ingredients.store'), [
            'name'        => str_repeat('a', 256),
            'is_allergen' => false,
        ])
        ->assertSessionHasErrors('name');
});

// ─── Edit / Update ───────────────────────────────────────────────────────────

it('shows ingredient edit form to owner', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('ingredients.edit', $ingredient))
        ->assertOk();
});

it('returns 403 when user tries to edit another users ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->other)->create();

    $this->actingAs($this->user)
        ->get(route('ingredients.edit', $ingredient))
        ->assertStatus(403);
});

it('updates ingredient name and is_allergen correctly', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create([
        'name'        => 'Pan',
        'is_allergen' => false,
    ]);

    $this->actingAs($this->user)
        ->put(route('ingredients.update', $ingredient), [
            'name'        => 'Pan Integral',
            'is_allergen' => true,
        ])
        ->assertRedirect(route('ingredients.index'));

    $this->assertDatabaseHas('ingredients', [
        'id'          => $ingredient->id,
        'name'        => 'Pan Integral',
        'is_allergen' => true,
    ]);
});

it('returns 403 when user tries to update another users ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->other)->create();

    $this->actingAs($this->user)
        ->put(route('ingredients.update', $ingredient), [
            'name'        => 'Hack',
            'is_allergen' => false,
        ])
        ->assertStatus(403);
});

it('fails to update ingredient with empty name', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->put(route('ingredients.update', $ingredient), [
            'name'        => '',
            'is_allergen' => false,
        ])
        ->assertSessionHasErrors('name');
});

// ─── Delete ──────────────────────────────────────────────────────────────────

it('deletes an ingredient owned by the user', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->delete(route('ingredients.destroy', $ingredient))
        ->assertRedirect(route('ingredients.index'));

    $this->assertDatabaseMissing('ingredients', ['id' => $ingredient->id]);
});

it('returns 403 when user tries to delete another users ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->other)->create();

    $this->actingAs($this->user)
        ->delete(route('ingredients.destroy', $ingredient))
        ->assertStatus(403);
});

it('returns 404 when trying to delete a non existent ingredient', function () {
    $this->actingAs($this->user)
        ->delete(route('ingredients.destroy', 9999))
        ->assertStatus(404);
});

// ─── Flash messages ──────────────────────────────────────────────────────────

it('shows success flash message after create update and delete', function () {
    // Create
    $this->actingAs($this->user)
        ->post(route('ingredients.store'), [
            'name'        => 'Cilantro',
            'is_allergen' => false,
        ])
        ->assertSessionHas('success');

    $ingredient = Ingredient::factory()->for($this->user)->create();

    // Update
    $this->actingAs($this->user)
        ->put(route('ingredients.update', $ingredient), [
            'name'        => 'Actualizado',
            'is_allergen' => false,
        ])
        ->assertSessionHas('success');

    // Delete
    $ingredient2 = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->delete(route('ingredients.destroy', $ingredient2))
        ->assertSessionHas('success');
});
