<?php

/**
 * @author AyrtonAlania
 */

use App\Models\TicketConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->waiter = User::factory()->create([
        'role'     => 'waiter',
        'admin_id' => $this->admin->id,
    ]);
    $this->other = User::factory()->create(['role' => 'admin']);
});

// ── Acceso ────────────────────────────────────────────────────────────────────

it('redirects unauthenticated user from ticket config', function () {
    $this->get(route('ticket-config.edit'))->assertRedirect(route('login'));
});

it('admin can access ticket config page', function () {
    $this->actingAs($this->admin)
        ->get(route('ticket-config.edit'))
        ->assertOk()
        ->assertViewIs('ticket-config.edit');
});

it('returns 403 for waiter trying to access ticket config', function () {
    $this->actingAs($this->waiter)
        ->get(route('ticket-config.edit'))
        ->assertForbidden();
});

it('preview endpoint is accessible to admin', function () {
    $this->actingAs($this->admin)
        ->get(route('ticket-config.preview'))
        ->assertOk();
});

// ── firstOrCreate ─────────────────────────────────────────────────────────────

it('ticket config is created with firstOrCreate on first visit', function () {
    $this->assertDatabaseMissing('ticket_configs', ['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)->get(route('ticket-config.edit'))->assertOk();

    $this->assertDatabaseHas('ticket_configs', ['user_id' => $this->admin->id]);
});

it('ticket config is created on first save', function () {
    $this->assertDatabaseMissing('ticket_configs', ['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'template' => 'classic',
    ])->assertRedirect(route('ticket-config.edit'));

    $this->assertDatabaseHas('ticket_configs', [
        'user_id'  => $this->admin->id,
        'template' => 'classic',
    ]);
});

it('ticket config is updated on subsequent saves', function () {
    TicketConfig::factory()->create(['user_id' => $this->admin->id, 'template' => 'classic']);

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'template'    => 'modern',
        'footer_text' => 'Gracias por su visita.',
    ])->assertRedirect(route('ticket-config.edit'));

    $this->assertDatabaseHas('ticket_configs', [
        'user_id'     => $this->admin->id,
        'template'    => 'modern',
        'footer_text' => 'Gracias por su visita.',
    ]);
    $this->assertDatabaseCount('ticket_configs', 1);
});

// ── Happy path ────────────────────────────────────────────────────────────────

it('admin can save ticket config with valid data', function () {
    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'tax_id'      => 'B12345678',
        'footer_text' => 'IVA incluido al 10%.',
        'template'    => 'minimal',
    ])->assertRedirect(route('ticket-config.edit'))
      ->assertSessionHas('success');

    $this->assertDatabaseHas('ticket_configs', [
        'user_id'     => $this->admin->id,
        'tax_id'      => 'B12345678',
        'footer_text' => 'IVA incluido al 10%.',
        'template'    => 'minimal',
    ]);
});

it('admin can save without logo', function () {
    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'template' => 'classic',
    ])->assertRedirect(route('ticket-config.edit'));

    $this->assertDatabaseHas('ticket_configs', [
        'user_id' => $this->admin->id,
        'logo'    => null,
    ]);
});

it('admin can upload logo image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('logo.png', 200, 200);

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'logo'     => $file,
        'template' => 'classic',
    ])->assertRedirect(route('ticket-config.edit'));

    $config = TicketConfig::where('user_id', $this->admin->id)->first();
    $this->assertNotNull($config->logo);
    Storage::disk('public')->assertExists($config->logo);
});

it('old logo is deleted when new one is uploaded', function () {
    Storage::fake('public');

    $oldFile = UploadedFile::fake()->image('old.png');
    $oldPath = $oldFile->store('tickets', 'public');

    TicketConfig::factory()->create([
        'user_id'  => $this->admin->id,
        'logo'     => $oldPath,
        'template' => 'classic',
    ]);

    $newFile = UploadedFile::fake()->image('new.png');

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'logo'     => $newFile,
        'template' => 'classic',
    ]);

    Storage::disk('public')->assertMissing($oldPath);

    $config = TicketConfig::where('user_id', $this->admin->id)->first();
    Storage::disk('public')->assertExists($config->logo);
});

// ── Validación ────────────────────────────────────────────────────────────────

it('fails with invalid template value', function () {
    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'template' => 'fancy',
    ])->assertSessionHasErrors('template');
});

it('fails when template is missing', function () {
    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'tax_id' => 'B12345678',
    ])->assertSessionHasErrors('template');
});

it('fails with tax_id longer than 20 characters', function () {
    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'tax_id'   => str_repeat('X', 21),
        'template' => 'classic',
    ])->assertSessionHasErrors('tax_id');
});

it('fails with footer_text longer than 500 characters', function () {
    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'footer_text' => str_repeat('a', 501),
        'template'    => 'classic',
    ])->assertSessionHasErrors('footer_text');
});

it('fails with non-image file as logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'logo'     => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        'template' => 'classic',
    ])->assertSessionHasErrors('logo');
});

it('fails with logo larger than 2MB', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'logo'     => UploadedFile::fake()->image('big.png')->size(3000),
        'template' => 'classic',
    ])->assertSessionHasErrors('logo');
});

// ── Multitenancy ──────────────────────────────────────────────────────────────

it('ticket config is scoped to the restaurant', function () {
    TicketConfig::factory()->create([
        'user_id'  => $this->other->id,
        'template' => 'modern',
        'tax_id'   => 'B99999999',
    ]);

    $this->actingAs($this->admin)->put(route('ticket-config.update'), [
        'template' => 'minimal',
        'tax_id'   => 'A11111111',
    ]);

    $this->assertDatabaseHas('ticket_configs', [
        'user_id' => $this->other->id,
        'tax_id'  => 'B99999999',
    ]);
    $this->assertDatabaseHas('ticket_configs', [
        'user_id' => $this->admin->id,
        'tax_id'  => 'A11111111',
    ]);
    $this->assertDatabaseCount('ticket_configs', 2);
});
