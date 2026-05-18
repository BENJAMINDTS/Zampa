<?php

/**
 * @author BenjaminDTS
 */

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTenants;

uses(RefreshDatabase::class);
uses(CreatesTenants::class);

beforeEach(function () {
    $this->setupTenants();
});

// ──────────────────────────────────────────────
// RUTAS PROTEGIDAS — requieren autenticación
// ──────────────────────────────────────────────

it('all admin routes require authentication', function () {
    $routes = [
        ['GET', route('categories.index')],
        ['GET', route('products.index')],
        ['GET', route('ingredients.index')],
        ['GET', route('tables.index')],
        ['GET', route('dashboard')],
        ['GET', route('staff.index')],
        ['GET', route('manager.income')],
    ];

    foreach ($routes as [$method, $url]) {
        $response = $this->call($method, $url);
        expect($response->getStatusCode())
            ->toBeIn([302, 401], "Route {$url} should redirect unauthenticated users");

        if ($response->getStatusCode() === 302) {
            expect($response->headers->get('Location'))->toContain('login');
        }
    }
});

// ──────────────────────────────────────────────
// RUTAS SUPERADMIN — requieren rol superadmin
// ──────────────────────────────────────────────

it('all superadmin routes require superadmin role', function () {
    $admin = User::factory()->admin()->withBusiness()->create([
        'plan_id' => Plan::factory()->create()->id,
    ]);

    $routes = [
        ['GET', route('superadmin.businesses.index')],
        ['GET', route('superadmin.dashboard')],
        ['GET', route('superadmin.plans.index')],
    ];

    foreach ($routes as [$method, $url]) {
        $this->actingAs($admin)
             ->call($method, $url)
             ->assertForbidden();
    }
});

it('waiter cannot access admin-only routes', function () {
    $admin  = User::factory()->admin()->withBusiness()->create();
    $waiter = User::factory()->waiter()->staffOf($admin)->create();

    // Rutas protegidas por role:admin que un camarero no puede ver
    $this->actingAs($waiter)
         ->get(route('manager.income'))
         ->assertForbidden();

    $this->actingAs($waiter)
         ->get(route('staff.index'))
         ->assertForbidden();

    $this->actingAs($waiter)
         ->get(route('dashboard'))
         ->assertForbidden();
});

// ──────────────────────────────────────────────
// SOFT DELETES — visibilidad post-eliminación
// ──────────────────────────────────────────────

it('soft deleted categories are not visible in index', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    $category->delete();

    $response = $this->actingAs($this->user)
                     ->get(route('categories.index'));

    $categories = $response->viewData('categories');
    expect($categories->contains('id', $category->id))->toBeFalse();
});

it('soft deleted products do not appear in public menu', function () {
    $plan     = Plan::factory()->create();
    $owner    = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $table    = Table::factory()->create(['user_id' => $owner->id]);
    $category = Category::factory()->create(['user_id' => $owner->id, 'destination' => 'kitchen']);
    $product  = Product::factory()->create([
        'user_id'     => $owner->id,
        'category_id' => $category->id,
        'is_active'   => true,
    ]);

    $product->delete();

    $this->get(route('menu.show', $table->unique_hash))
         ->assertOk()
         ->assertDontSee($product->name);
});

it('soft deleted ingredients are not assignable to products', function () {
    $ingredient = Ingredient::factory()->create(['user_id' => $this->user->id]);
    $product    = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => Category::factory()->create(['user_id' => $this->user->id])->id,
    ]);

    $ingredient->delete();

    // El ingrediente eliminado no debe aparecer en el listado de ingredientes
    $remaining = Ingredient::where('user_id', $this->user->id)->get();
    expect($remaining->contains('id', $ingredient->id))->toBeFalse();

    // Intentar sincronizar con el ingrediente eliminado no debe incluirlo
    $this->actingAs($this->user)
         ->post(route('products.ingredients.sync', $product), [
             'ingredients' => [
                 $ingredient->id => ['included' => '1', 'quantity_base' => 1],
             ],
         ]);

    expect($product->fresh()->ingredients->contains('id', $ingredient->id))->toBeFalse();
});

it('soft deleted category is not accessible via edit route', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);
    $category->delete();

    $this->actingAs($this->user)
         ->get(route('categories.edit', $category->id))
         ->assertNotFound();
});

it('soft deleted product is not accessible via edit route', function () {
    $product = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => Category::factory()->create(['user_id' => $this->user->id])->id,
    ]);
    $product->delete();

    $this->actingAs($this->user)
         ->get(route('products.edit', $product->id))
         ->assertNotFound();
});

// ──────────────────────────────────────────────
// MULTITENANCY — protección cross-user
// ──────────────────────────────────────────────

it('user cannot access soft deleted resources of other users', function () {
    $categoryOther = Category::factory()->create(['user_id' => $this->other->id]);
    $categoryOther->delete();

    // El usuario propio no puede ver el recurso (soft-deleted + de otro usuario)
    $this->actingAs($this->user)
         ->get(route('categories.edit', $categoryOther->id))
         ->assertNotFound();
});

it('all destroy methods have multitenancy protection', function () {
    $category   = Category::factory()->create(['user_id' => $this->other->id]);
    $product    = Product::factory()->create([
        'user_id'     => $this->other->id,
        'category_id' => $category->id,
    ]);
    $ingredient = Ingredient::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
         ->delete(route('categories.destroy', $category))
         ->assertForbidden();

    $this->actingAs($this->user)
         ->delete(route('products.destroy', $product))
         ->assertForbidden();

    $this->actingAs($this->user)
         ->delete(route('ingredients.destroy', $ingredient))
         ->assertForbidden();

    // Recursos intactos en BD
    $this->assertDatabaseHas('categories',  ['id' => $category->id,   'deleted_at' => null]);
    $this->assertDatabaseHas('products',    ['id' => $product->id,    'deleted_at' => null]);
    $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'deleted_at' => null]);
});

it('table QR is accessible to restaurant staff via ownerUserId', function () {
    $admin  = User::factory()->admin()->alsoWaiter()->create();
    $table  = Table::factory()->create(['user_id' => $admin->id]);
    $waiter = User::factory()->waiter()->staffOf($admin)->create();

    $this->actingAs($waiter)
         ->get(route('tables.qr.show', $table))
         ->assertOk();
});

it('table QR is not accessible to staff of a different restaurant', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();
    $table  = Table::factory()->create(['user_id' => $admin1->id]);
    $waiter = User::factory()->waiter()->staffOf($admin2)->create();

    $this->actingAs($waiter)
         ->get(route('tables.qr.show', $table))
         ->assertForbidden();
});
