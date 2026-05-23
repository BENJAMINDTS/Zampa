<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_item_payments', function (Blueprint $table) {
            $table->decimal('tip', 10, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_payments', function (Blueprint $table) {
            $table->dropColumn('tip');
        });
    }
};
