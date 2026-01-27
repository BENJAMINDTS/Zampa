<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'cooking', 'ready', 'served', 'closed'])->default('pending');
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('tip', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card'])->nullable();
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
