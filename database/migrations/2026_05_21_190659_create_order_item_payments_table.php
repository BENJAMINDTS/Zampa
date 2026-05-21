<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('stripe_payment_intent_id');
            $table->index('stripe_payment_intent_id');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('mode', ['items', 'equitative']);
            $table->unsignedSmallInteger('parts_total')->nullable();
            $table->unsignedSmallInteger('part_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_payments');
    }
};
