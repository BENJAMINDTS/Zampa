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
        Schema::create('daily_menu_order_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_order_id')
                ->constrained('daily_menu_orders')
                ->onDelete('cascade');
            $table->foreignId('daily_menu_section_id')
                ->constrained('daily_menu_sections')
                ->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->timestamps();

            $table->index('daily_menu_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_menu_order_selections');
    }
};
