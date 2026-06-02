<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'served' to the order_items.status ENUM.
 *
 * The original migration only defined 'queued' and 'ready', which meant
 * BarPanelController::markItemServed() silently failed at the DB level
 * and orders got stuck in 'ready' / notification_ready=true forever.
 *
 * Also closes any pre-existing stuck orders (status='ready', all items 'ready')
 * so stale badge counts are cleared immediately after running this migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('queued','ready','served') NOT NULL DEFAULT 'queued'");

        // Clean up orders stuck in 'ready' because markItemServed could never
        // persist 'served' to the DB before this migration.
        DB::statement("
            UPDATE orders
            SET status = 'closed',
                notification_ready = 0
            WHERE status = 'ready'
              AND notification_ready = 1
              AND NOT EXISTS (
                  SELECT 1 FROM order_items
                  WHERE order_items.order_id = orders.id
                    AND order_items.status != 'ready'
              )
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('queued','ready') NOT NULL DEFAULT 'queued'");
    }
};
