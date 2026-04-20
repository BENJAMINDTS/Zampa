<?php

namespace Tests\Support;

use App\Models\Plan;
use App\Models\User;

trait CreatesTenants
{
    protected User $user;
    protected User $other;

    protected function setupTenants(): void
    {
        $plan = Plan::factory()->create();

        $this->user  = User::factory()->create(['plan_id' => $plan->id]);
        $this->other = User::factory()->create(['plan_id' => $plan->id]);
    }
}
