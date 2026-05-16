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
        Schema::create('daily_menu_timing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('round_number');
            $table->unsignedSmallInteger('default_delay_minutes');
            $table->unsignedSmallInteger('estimated_prep_minutes');
            $table->json('section_types');
            $table->timestamps();

            $table->unique(['daily_menu_id', 'round_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_menu_timing_rules');
    }
};
