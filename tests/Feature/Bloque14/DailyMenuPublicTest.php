<?php

use App\Models\Category;
use App\Models\DailyMenu;
use App\Models\DailyMenuOrder;
use App\Models\DailyMenuSection;
use App\Models\DailyMenuTimingRule;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $plan        = Plan::factory()->create();
    $this->user  = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $this->other = User::factory()->admin()->create(['plan_id' => $plan->id]);

    $this->table = Table::factory()->create(['user_id' => $this->user->id]);
});

// ─── Helper ──────────────────────────────────────────────────────────────────

/**
 * Builds a fully configured daily menu for today with one section and one product.
 * Returns an array with menu, section, and product.
 */
function buildTodayMenu($userId): array
{
    $today = strtolower(now()->englishDayOfWeek);

    $menu = DailyMenu::factory()->create([
        'user_id'     => $userId,
        'day_of_week' => $today,
        'is_active'   => true,
        'price'       => 12.00,
    ]);

    $section = DailyMenuSection::factory()->create([
        'daily_menu_id' => $menu->id,
        'type'          => 'first_course',
        'is_required'   => true,
        'is_free'       => false,
    ]);

    $category = Category::factory()->create(['user_id' => $userId, 'destination' => 'kitchen']);
    $product  = Product::factory()->create(['user_id' => $userId, 'category_id' => $category->id]);

    $section->products()->attach($product->id);

    return compact('menu', 'section', 'product');
}

// ─── GET /api/v1/menu/{hash}/daily-menu ──────────────────────────────────────

it('returns 404 for an invalid table hash on show', function () {
    $this->getJson('/api/v1/menu/invalid-hash/daily-menu')
        ->assertStatus(404);
});

it('returns data null when no daily menu is active today', function () {
    $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu")
        ->assertOk()
        ->assertJson(['success' => true, 'data' => null]);
});

it('returns the active daily menu for today', function () {
    ['menu' => $menu] = buildTodayMenu($this->user->id);

    $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu")
        ->assertOk()
        ->assertJsonPath('data.menu.id', $menu->id)
        ->assertJsonPath('data.menu.title', $menu->title);
});

it('includes sections in the response', function () {
    ['section' => $section] = buildTodayMenu($this->user->id);

    $response = $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu");

    $response->assertOk();
    $sections = $response->json('data.sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['id'])->toBe($section->id);
});

it('embeds timing_rule inside each section', function () {
    ['menu' => $menu, 'section' => $section] = buildTodayMenu($this->user->id);

    DailyMenuTimingRule::factory()->create([
        'daily_menu_id'          => $menu->id,
        'round_number'           => 1,
        'default_delay_minutes'  => 0,
        'estimated_prep_minutes' => 10,
        'section_types'          => ['first_course'],
    ]);

    $response = $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu");

    $response->assertOk();
    $sections = $response->json('data.sections');
    expect($sections[0]['timing_rule'])->not->toBeNull();
    expect($sections[0]['timing_rule']['round_number'])->toBe(1);
});

it('returns timing_rule null for sections without a timing rule', function () {
    buildTodayMenu($this->user->id);

    $response = $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu");

    $response->assertOk();
    $sections = $response->json('data.sections');
    expect($sections[0]['timing_rule'])->toBeNull();
});

it('returns available count when max_per_day is set', function () {
    ['menu' => $menu] = buildTodayMenu($this->user->id);
    $menu->update(['max_per_day' => 5]);

    $order = Order::factory()->create(['table_id' => $this->table->id, 'status' => 'pending']);
    DailyMenuOrder::factory()->create([
        'daily_menu_id' => $menu->id,
        'order_id'      => $order->id,
        'status'        => 'pending',
    ]);

    $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu")
        ->assertOk()
        ->assertJsonPath('data.menu.available_count', 4);
});

it('returns available count null when max_per_day is not set', function () {
    buildTodayMenu($this->user->id);

    $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu")
        ->assertOk()
        ->assertJsonPath('data.menu.available_count', null);
});

it('does not return a daily menu from another restaurant', function () {
    buildTodayMenu($this->other->id);

    $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu")
        ->assertOk()
        ->assertJsonPath('data', null);
});

it('does not return an inactive daily menu', function () {
    $today = strtolower(now()->englishDayOfWeek);
    DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $today,
        'is_active'   => false,
    ]);

    $this->getJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu")
        ->assertOk()
        ->assertJsonPath('data', null);
});

// ─── POST /api/v1/menu/{hash}/daily-menu/order ───────────────────────────────

it('returns 404 when ordering with an invalid table hash', function () {
    $this->postJson('/api/v1/menu/invalid-hash/daily-menu/order', [
        'selections' => [['section_id' => 1, 'product_id' => 1, 'quantity' => 1]],
    ])->assertStatus(404);
});

it('returns 404 when no daily menu is available today', function () {
    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [['section_id' => 1, 'product_id' => 1, 'quantity' => 1]],
    ])->assertStatus(404);
});

it('returns 409 when the table has a la carte orders pending', function () {
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    $order = Order::factory()->create(['table_id' => $this->table->id, 'status' => 'pending']);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertStatus(409);
});

it('creates a daily menu order successfully', function () {
    Queue::fake();
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertCreated()
      ->assertJsonPath('success', true);

    $this->assertDatabaseHas('orders', [
        'table_id' => $this->table->id,
        'status'   => 'pending',
    ]);
});

it('creates a daily_menu_order record linked to the order', function () {
    Queue::fake();
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertCreated();

    $this->assertDatabaseHas('daily_menu_orders', [
        'daily_menu_id' => $menu->id,
        'status'        => 'pending',
    ]);
});

it('stores the customer selections in daily_menu_order_selections', function () {
    Queue::fake();
    ['section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertCreated();

    $this->assertDatabaseHas('daily_menu_order_selections', [
        'daily_menu_section_id' => $section->id,
        'product_id'            => $product->id,
        'quantity'              => 1,
    ]);
});

it('returns 422 when selections array is empty', function () {
    buildTodayMenu($this->user->id);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [],
    ])->assertStatus(422);
});

it('returns 422 when a section_id does not belong to the menu', function () {
    buildTodayMenu($this->user->id);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => 99999,
            'product_id' => 1,
            'quantity'   => 1,
        ]],
    ])->assertStatus(422);
});

it('returns 422 when a product is not available in the selected section', function () {
    ['menu' => $menu, 'section' => $section] = buildTodayMenu($this->user->id);

    $otherCat     = Category::factory()->create(['user_id' => $this->user->id]);
    $otherProduct = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $otherCat->id]);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $otherProduct->id,
            'quantity'   => 1,
        ]],
    ])->assertStatus(422);
});

it('returns 422 when a required section has no selection', function () {
    ['section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    // Add a second required section without providing its selection
    $menu = $section->dailyMenu;
    $requiredSection = DailyMenuSection::factory()->create([
        'daily_menu_id' => $menu->id,
        'type'          => 'second_course',
        'is_required'   => true,
    ]);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertStatus(422);
});

it('returns 422 when timing override is below the estimated prep time', function () {
    Queue::fake();
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    DailyMenuTimingRule::factory()->create([
        'daily_menu_id'          => $menu->id,
        'round_number'           => 1,
        'default_delay_minutes'  => 20,
        'estimated_prep_minutes' => 15,
        'section_types'          => ['first_course'],
    ]);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
        'timing_overrides' => [[
            'round'         => 1,
            'delay_minutes' => 5,
        ]],
    ])->assertStatus(422);
});

// Note: lockForUpdate() is a no-op on SQLite, so this test verifies the count
// threshold logic only. The race-condition guard is exercised only on MySQL/MariaDB.
it('returns 409 when the menu is sold out', function () {
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);
    $menu->update(['max_per_day' => 1]);

    $existingOrder = Order::factory()->create(['table_id' => $this->table->id, 'status' => 'pending']);
    DailyMenuOrder::factory()->create([
        'daily_menu_id' => $menu->id,
        'order_id'      => $existingOrder->id,
        'status'        => 'pending',
    ]);

    $otherTable = Table::factory()->create(['user_id' => $this->user->id]);

    $this->postJson("/api/v1/menu/{$otherTable->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertStatus(409);
});

it('dispatches a job for each timing rule when ordering', function () {
    Queue::fake();
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    DailyMenuTimingRule::factory()->create([
        'daily_menu_id'          => $menu->id,
        'round_number'           => 1,
        'default_delay_minutes'  => 0,
        'estimated_prep_minutes' => 10,
        'section_types'          => ['first_course'],
    ]);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
    ])->assertCreated();

    Queue::assertPushed(\App\Jobs\SendDailyMenuRound::class);
});

it('accepts timing_overrides that meet the minimum prep time', function () {
    Queue::fake();
    ['menu' => $menu, 'section' => $section, 'product' => $product] = buildTodayMenu($this->user->id);

    DailyMenuTimingRule::factory()->create([
        'daily_menu_id'          => $menu->id,
        'round_number'           => 1,
        'default_delay_minutes'  => 20,
        'estimated_prep_minutes' => 10,
        'section_types'          => ['first_course'],
    ]);

    $this->postJson("/api/v1/menu/{$this->table->unique_hash}/daily-menu/order", [
        'selections' => [[
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]],
        'timing_overrides' => [[
            'round'         => 1,
            'delay_minutes' => 15,
        ]],
    ])->assertCreated();
});
