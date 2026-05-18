<?php

use App\Models\Category;
use App\Models\DailyMenu;
use App\Models\DailyMenuSection;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $plan        = Plan::factory()->create();
    $this->user  = User::factory()->admin()->create(['plan_id' => $plan->id]);
    $this->other = User::factory()->admin()->create(['plan_id' => $plan->id]);
});

// ─── Auth ────────────────────────────────────────────────────────────────────

it('redirects unauthenticated user away from daily menus index', function () {
    $this->get(route('daily-menus.index'))
        ->assertRedirect(route('login'));
});

it('redirects unauthenticated user away from daily menus create', function () {
    $this->get(route('daily-menus.create'))
        ->assertRedirect(route('login'));
});

it('redirects unauthenticated user away from daily menus edit', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->get(route('daily-menus.edit', $menu))
        ->assertRedirect(route('login'));
});

// ─── Index ───────────────────────────────────────────────────────────────────

it('shows daily menus index to authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('daily-menus.index'))
        ->assertOk();
});

it('shows only the authenticated users daily menus', function () {
    DailyMenu::factory()->create(['user_id' => $this->user->id, 'title' => 'Mi Menú']);
    DailyMenu::factory()->create(['user_id' => $this->other->id, 'title' => 'Menú Ajeno']);

    $this->actingAs($this->user)
        ->get(route('daily-menus.index'))
        ->assertSee('Mi Menú')
        ->assertDontSee('Menú Ajeno');
});

// ─── Store ───────────────────────────────────────────────────────────────────

it('creates a daily menu with a day of week', function () {
    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'       => 'Menú del Lunes',
            'price'       => 12.50,
            'day_of_week' => 'monday',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('daily_menus', [
        'user_id'     => $this->user->id,
        'title'       => 'Menú del Lunes',
        'day_of_week' => 'monday',
    ]);
});

it('creates a daily menu with a specific date', function () {
    $date = now()->addDays(3)->toDateString();

    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'         => 'Menú Especial',
            'price'         => 15.00,
            'specific_date' => $date,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('daily_menus', [
        'user_id' => $this->user->id,
        'title'   => 'Menú Especial',
    ]);
});

it('assigns the authenticated user id when creating a daily menu', function () {
    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'       => 'Menú',
            'price'       => 10.00,
            'day_of_week' => 'tuesday',
        ]);

    $this->assertDatabaseHas('daily_menus', ['user_id' => $this->user->id]);
});

it('fails to create a daily menu without title', function () {
    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'price'       => 10.00,
            'day_of_week' => 'monday',
        ])
        ->assertSessionHasErrors('title');
});

it('fails to create a daily menu without price', function () {
    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'       => 'Menú',
            'day_of_week' => 'monday',
        ])
        ->assertSessionHasErrors('price');
});

it('fails to create a daily menu without day or specific date', function () {
    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title' => 'Menú',
            'price' => 10.00,
        ])
        ->assertSessionHasErrors(['day_of_week', 'specific_date']);
});

it('fails to create a daily menu when active one already exists for that day', function () {
    DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => 'friday',
        'is_active'   => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'       => 'Otro Menú Viernes',
            'price'       => 11.00,
            'day_of_week' => 'friday',
        ])
        ->assertSessionHasErrors('day_of_week');
});

it('fails to create a daily menu when active one already exists for that specific date', function () {
    $date = now()->addDays(2)->toDateString();

    DailyMenu::factory()->create([
        'user_id'       => $this->user->id,
        'specific_date' => $date,
        'day_of_week'   => null,
        'is_active'     => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'         => 'Menú Duplicado',
            'price'         => 11.00,
            'specific_date' => $date,
        ])
        ->assertSessionHasErrors('specific_date');
});

it('flash success message after creating a daily menu', function () {
    $this->actingAs($this->user)
        ->post(route('daily-menus.store'), [
            'title'       => 'Menú Flash',
            'price'       => 10.00,
            'day_of_week' => 'saturday',
        ])
        ->assertSessionHas('success');
});

// ─── Edit ────────────────────────────────────────────────────────────────────

it('shows the edit form to the menu owner', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('daily-menus.edit', $menu))
        ->assertOk();
});

it('returns 403 when non-owner tries to edit a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->get(route('daily-menus.edit', $menu))
        ->assertForbidden();
});

// ─── Update ──────────────────────────────────────────────────────────────────

it('updates a daily menu with valid data', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id, 'day_of_week' => 'monday']);

    $this->actingAs($this->user)
        ->put(route('daily-menus.update', $menu), [
            'title'       => 'Menú Actualizado',
            'price'       => 14.00,
            'day_of_week' => 'monday',
        ])
        ->assertRedirect(route('daily-menus.index'));

    $this->assertDatabaseHas('daily_menus', [
        'id'    => $menu->id,
        'title' => 'Menú Actualizado',
    ]);
});

it('returns 403 when non-owner tries to update a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->other->id, 'day_of_week' => 'monday']);

    $this->actingAs($this->user)
        ->put(route('daily-menus.update', $menu), [
            'title'       => 'Hack',
            'price'       => 1.00,
            'day_of_week' => 'monday',
        ])
        ->assertForbidden();
});

it('flash success message after updating a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id, 'day_of_week' => 'monday']);

    $this->actingAs($this->user)
        ->put(route('daily-menus.update', $menu), [
            'title'       => 'Menú Actualizado',
            'price'       => 14.00,
            'day_of_week' => 'monday',
        ])
        ->assertSessionHas('success');
});

// ─── Destroy ─────────────────────────────────────────────────────────────────

it('deletes a daily menu owned by the user', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('daily-menus.destroy', $menu))
        ->assertRedirect(route('daily-menus.index'));

    $this->assertDatabaseMissing('daily_menus', ['id' => $menu->id]);
});

it('returns 403 when non-owner tries to delete a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->delete(route('daily-menus.destroy', $menu))
        ->assertForbidden();
});

it('flash success message after deleting a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('daily-menus.destroy', $menu))
        ->assertSessionHas('success');
});

// ─── Sections view ───────────────────────────────────────────────────────────

it('shows the sections configuration to the menu owner', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('daily-menus.sections', $menu))
        ->assertOk();
});

it('returns 403 when non-owner views sections of a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->get(route('daily-menus.sections', $menu))
        ->assertForbidden();
});

// ─── storeSection ────────────────────────────────────────────────────────────

it('adds a section to a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sections.store', $menu), [
            'section_type' => 'first_course',
        ])
        ->assertRedirect(route('daily-menus.sections', $menu));

    $this->assertDatabaseHas('daily_menu_sections', [
        'daily_menu_id' => $menu->id,
        'type'          => 'first_course',
    ]);
});

it('prevents adding a duplicate section type to a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    DailyMenuSection::factory()->create([
        'daily_menu_id' => $menu->id,
        'type'          => 'dessert',
    ]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sections.store', $menu), [
            'section_type' => 'dessert',
        ])
        ->assertSessionHas('error');
});

it('returns 403 when non-owner adds a section', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sections.store', $menu), [
            'section_type' => 'first_course',
        ])
        ->assertForbidden();
});

// ─── destroySection ──────────────────────────────────────────────────────────

it('removes a section from a daily menu', function () {
    $menu    = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $section = DailyMenuSection::factory()->create(['daily_menu_id' => $menu->id]);

    $this->actingAs($this->user)
        ->delete(route('daily-menus.sections.destroy', [$menu, $section]))
        ->assertRedirect(route('daily-menus.sections', $menu));

    $this->assertDatabaseMissing('daily_menu_sections', ['id' => $section->id]);
});

it('returns 403 when non-owner deletes a section', function () {
    $menu    = DailyMenu::factory()->create(['user_id' => $this->other->id]);
    $section = DailyMenuSection::factory()->create(['daily_menu_id' => $menu->id]);

    $this->actingAs($this->user)
        ->delete(route('daily-menus.sections.destroy', [$menu, $section]))
        ->assertForbidden();
});

it('returns 403 when section does not belong to the given daily menu', function () {
    $menu      = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $otherMenu = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $section   = DailyMenuSection::factory()->create(['daily_menu_id' => $otherMenu->id]);

    $this->actingAs($this->user)
        ->delete(route('daily-menus.sections.destroy', [$menu, $section]))
        ->assertForbidden();
});

// ─── syncProducts ────────────────────────────────────────────────────────────

it('syncs products to a section', function () {
    $menu     = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $section  = DailyMenuSection::factory()->create(['daily_menu_id' => $menu->id]);
    $category = Category::factory()->create(['user_id' => $this->user->id]);
    $product  = Product::factory()->create(['user_id' => $this->user->id, 'category_id' => $category->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sync-products', [$menu, $section]), [
            'product_ids' => [$product->id],
        ])
        ->assertRedirect(route('daily-menus.sections', $menu));

    $this->assertDatabaseHas('daily_menu_section_products', [
        'daily_menu_section_id' => $section->id,
        'product_id'            => $product->id,
    ]);
});

it('returns 403 when syncing products from another user', function () {
    $menu        = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $section     = DailyMenuSection::factory()->create(['daily_menu_id' => $menu->id]);
    $otherCat    = Category::factory()->create(['user_id' => $this->other->id]);
    $otherProduct = Product::factory()->create(['user_id' => $this->other->id, 'category_id' => $otherCat->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sync-products', [$menu, $section]), [
            'product_ids' => [$otherProduct->id],
        ])
        ->assertForbidden();
});

it('returns 403 when non-owner syncs products', function () {
    $menu    = DailyMenu::factory()->create(['user_id' => $this->other->id]);
    $section = DailyMenuSection::factory()->create(['daily_menu_id' => $menu->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sync-products', [$menu, $section]), [
            'product_ids' => [],
        ])
        ->assertForbidden();
});

it('flash success message after syncing products', function () {
    $menu    = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $section = DailyMenuSection::factory()->create(['daily_menu_id' => $menu->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.sync-products', [$menu, $section]), [
            'product_ids' => [],
        ])
        ->assertSessionHas('success');
});

// ─── storeTiming ─────────────────────────────────────────────────────────────

it('saves timing rules for a daily menu', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.timing', $menu), [
            'rounds' => [
                [
                    'round_number'           => 1,
                    'default_delay_minutes'  => 0,
                    'estimated_prep_minutes' => 10,
                    'section_types'          => ['first_course'],
                ],
                [
                    'round_number'           => 2,
                    'default_delay_minutes'  => 30,
                    'estimated_prep_minutes' => 10,
                    'section_types'          => ['second_course', 'dessert'],
                ],
            ],
        ])
        ->assertRedirect(route('daily-menus.sections', $menu));

    $this->assertDatabaseHas('daily_menu_timing_rules', [
        'daily_menu_id' => $menu->id,
        'round_number'  => 1,
    ]);
    $this->assertDatabaseHas('daily_menu_timing_rules', [
        'daily_menu_id' => $menu->id,
        'round_number'  => 2,
    ]);
});

it('replaces existing timing rules on save', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->user->id]);
    $menu->timingRules()->create([
        'round_number'           => 1,
        'default_delay_minutes'  => 5,
        'estimated_prep_minutes' => 5,
        'section_types'          => ['bread'],
    ]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.timing', $menu), [
            'rounds' => [
                [
                    'round_number'           => 1,
                    'default_delay_minutes'  => 20,
                    'estimated_prep_minutes' => 15,
                    'section_types'          => ['first_course'],
                ],
            ],
        ]);

    $this->assertEquals(1, $menu->timingRules()->count());
    $this->assertDatabaseHas('daily_menu_timing_rules', [
        'daily_menu_id'         => $menu->id,
        'default_delay_minutes' => 20,
    ]);
});

it('returns 403 when non-owner saves timing', function () {
    $menu = DailyMenu::factory()->create(['user_id' => $this->other->id]);

    $this->actingAs($this->user)
        ->post(route('daily-menus.timing', $menu), [
            'rounds' => [
                [
                    'round_number'           => 1,
                    'default_delay_minutes'  => 0,
                    'estimated_prep_minutes' => 10,
                    'section_types'          => ['first_course'],
                ],
            ],
        ])
        ->assertForbidden();
});
