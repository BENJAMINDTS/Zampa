<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar el enum payment_method para incluir 'mixed'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash','card','split','mixed') NULL");

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('mixed_cash_amount', 10, 2)->nullable()->after('tip');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('mixed_cash_amount');
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash','card','split') NULL");
    }
};
