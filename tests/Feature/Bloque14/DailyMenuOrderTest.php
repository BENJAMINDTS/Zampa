<?php

use App\Jobs\SendDailyMenuRound;
use App\Models\Category;
use App\Models\DailyMenu;
use App\Models\DailyMenuOrder;
use App\Models\DailyMenuOrderSelection;
use App\Models\DailyMenuSection;
use App\Models\DailyMenuTimingRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Services\DailyMenuOrderService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $plan        = Plan::factory()->create();
    $this->user  = User::factory()->admin()->create(['plan_id' => $plan->id]);

    $this->table = Table::factory()->create(['user_id' => $this->user->id]);

    $today = strtolower(now()->englishDayOfWeek);

    $this->menu = DailyMenu::factory()->create([
        'user_id'     => $this->user->id,
        'day_of_week' => $today,
        'is_active'   => true,
        'price'       => 12.00,
    ]);

    $this->category = Category::factory()->create([
        'user_id'     => $this->user->id,
        'destination' => 'kitchen',
    ]);

    $this->product = Product::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'price'       => 8.00,
    ]);

    $this->section = DailyMenuSection::factory()->create([
        'daily_menu_id' => $this->menu->id,
        'type'          => 'first_course',
        'is_required'   => true,
        'is_free'       => false,
    ]);

    $this->section->products()->attach($this->product->id);

    $this->timingRule = DailyMenuTimingRule::factory()->create([
        'daily_menu_id'          => $this->menu->id,
        'round_number'           => 1,
        'default_delay_minutes'  => 0,
        'estimated_prep_minutes' => 10,
        'section_types'          => ['first_course'],
    ]);

    $this->order = Order::factory()->create([
        'table_id' => $this->table->id,
        'status'   => 'pending',
        'total'    => $this->menu->price,
    ]);

    $this->dailyMenuOrder = DailyMenuOrder::factory()->create([
        'order_id'      => $this->order->id,
        'daily_menu_id' => $this->menu->id,
        'rounds_total'  => 1,
        'current_round' => 0,
        'status'        => 'pending',
    ]);

    DailyMenuOrderSelection::factory()->create([
        'daily_menu_order_id'   => $this->dailyMenuOrder->id,
        'daily_menu_section_id' => $this->section->id,
        'product_id'            => $this->product->id,
        'quantity'              => 1,
    ]);
});

// ─── DailyMenuOrderService::dispatchRounds ───────────────────────────────────

it('dispatches one job per timing rule', function () {
    Queue::fake();

    $rules = $this->menu->timingRules;
    DailyMenuOrderService::dispatchRounds($this->dailyMenuOrder, $rules, []);

    Queue::assertPushed(SendDailyMenuRound::class, 1);
});

it('dispatches a job with the correct round number', function () {
    Queue::fake();

    $rules = $this->menu->timingRules;
    DailyMenuOrderService::dispatchRounds($this->dailyMenuOrder, $rules, []);

    Queue::assertPushed(SendDailyMenuRound::class, function ($job) {
        return $job->round === 1;
    });
});

it('dispatches two jobs for two timing rules', function () {
    Queue::fake();

    DailyMenuTimingRule::factory()->create([
        'daily_menu_id'          => $this->menu->id,
        'round_number'           => 2,
        'default_delay_minutes'  => 30,
        'estimated_prep_minutes' => 10,
        'section_types'          => ['second_course'],
    ]);

    $rules = $this->menu->timingRules()->orderBy('round_number')->get();
    DailyMenuOrderService::dispatchRounds($this->dailyMenuOrder, $rules, []);

    Queue::assertPushed(SendDailyMenuRound::class, 2);
});

// ─── SendDailyMenuRound Job ──────────────────────────────────────────────────

it('creates order items when the job runs', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $this->assertDatabaseHas('order_items', [
        'order_id'      => $this->order->id,
        'product_id'    => $this->product->id,
        'is_daily_menu' => 1,
    ]);
});

it('sets is_daily_menu to true on the created order item', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $item = OrderItem::where('order_id', $this->order->id)->first();
    expect($item->is_daily_menu)->toBeTrue();
});

it('sets the destination from the product category', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $item = OrderItem::where('order_id', $this->order->id)->first();
    expect($item->destination)->toBe('kitchen');
});

it('sets price to product price when section is not free', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $item = OrderItem::where('order_id', $this->order->id)->first();
    expect((float) $item->price)->toBe(8.00);
});

it('sets price to 0 when section is free', function () {
    $this->section->update(['is_free' => true]);

    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $item = OrderItem::where('order_id', $this->order->id)->first();
    expect((float) $item->price)->toBe(0.0);
});

it('updates current_round after the job runs', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $this->dailyMenuOrder->refresh();
    expect($this->dailyMenuOrder->current_round)->toBe(1);
});

it('marks the daily menu order as completed when the last round runs', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $this->dailyMenuOrder->refresh();
    expect($this->dailyMenuOrder->status)->toBe('completed');
});

it('does not mark as completed when there are more rounds pending', function () {
    $this->dailyMenuOrder->update(['rounds_total' => 2]);

    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $this->dailyMenuOrder->refresh();
    expect($this->dailyMenuOrder->status)->toBe('pending');
});

it('skips processing when the daily menu order is cancelled', function () {
    $this->dailyMenuOrder->update(['status' => 'cancelled']);

    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $this->assertDatabaseMissing('order_items', ['order_id' => $this->order->id]);
});

it('does not create order items when the timing rule does not match', function () {
    // Round 2 does not exist in timing rules → job skips
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 99);
    $job->handle();

    $this->assertDatabaseMissing('order_items', ['order_id' => $this->order->id]);
});

it('creates order items with queued status', function () {
    $job = new SendDailyMenuRound($this->dailyMenuOrder, 1);
    $job->handle();

    $item = OrderItem::where('order_id', $this->order->id)->first();
    expect($item->status)->toBe('queued');
});

// ─── Timing calculation ──────────────────────────────────────────────────────

it('delay is zero when client delay equals estimated prep time', function () {
    $clientDelay   = 10;
    $estimatedPrep = 10;
    $delaySeconds  = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(0);
});

it('delay is zero when client delay is less than estimated prep time', function () {
    $clientDelay   = 5;
    $estimatedPrep = 10;
    $delaySeconds  = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(0);
});

it('delay is positive when client delay exceeds estimated prep time', function () {
    $clientDelay   = 30;
    $estimatedPrep = 10;
    $delaySeconds  = max(0, ($clientDelay - $estimatedPrep) * 60);

    expect($delaySeconds)->toBe(1200);
});
