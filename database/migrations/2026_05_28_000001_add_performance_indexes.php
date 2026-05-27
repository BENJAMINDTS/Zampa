<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade índices en las columnas usadas frecuentemente en WHERE, JOIN y ORDER BY.
 * Elimina full-table-scans en las rutas de polling y carta pública.
 */
return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('notification_ready');
            $table->index('bill_requested');
            $table->index(['table_id', 'status'], 'orders_table_status_idx');
            $table->index(['table_id', 'payment_status'], 'orders_table_payment_status_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('status');
            $table->index('destination');
            $table->index(['order_id', 'destination', 'status'], 'order_items_order_dest_status_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
            $table->index(['user_id', 'is_active'], 'products_user_active_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('destination');
            $table->index(['user_id', 'destination'], 'categories_user_dest_idx');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->index('status');
            $table->index(['table_id', 'status'], 'conversations_table_status_idx');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_table_status_idx');
            $table->dropIndex(['status']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_user_dest_idx');
            $table->dropIndex(['destination']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_user_active_idx');
            $table->dropIndex(['is_active']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_dest_status_idx');
            $table->dropIndex(['destination']);
            $table->dropIndex(['status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_table_payment_status_idx');
            $table->dropIndex('orders_table_status_idx');
            $table->dropIndex(['bill_requested']);
            $table->dropIndex(['notification_ready']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['status']);
        });
    }
};
