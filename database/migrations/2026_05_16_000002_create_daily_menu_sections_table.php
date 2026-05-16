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
        Schema::create('daily_menu_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'first_course', 'second_course', 'dessert',
                'coffee', 'drink', 'bread',
            ]);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_free')->default(false);
            $table->unsignedTinyInteger('max_quantity')->default(1);
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('daily_menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_menu_sections');
    }
};
