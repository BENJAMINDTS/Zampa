<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('floor_count')->default(1)->after('floor_height');
            $table->boolean('floors_enabled')->default(false)->after('floor_count');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['floor_count', 'floors_enabled']);
        });
    }
};
