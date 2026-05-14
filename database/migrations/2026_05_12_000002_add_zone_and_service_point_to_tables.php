<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('rotation')
                  ->constrained('zones')->nullOnDelete();
            $table->boolean('is_service_point')->default(true)->after('zone_id');
        });

        // Extend shape enum to include bar and stool (MySQL only; SQLite ignores enum constraints)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `tables` MODIFY `shape` ENUM('square','round','rectangle','bar','stool') NOT NULL DEFAULT 'square'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->integer('floor_width')->default(1200)->after('lng');
            $table->integer('floor_height')->default(800)->after('floor_width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn(['zone_id', 'is_service_point']);
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `tables` MODIFY `shape` ENUM('square','round','rectangle') NOT NULL DEFAULT 'square'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['floor_width', 'floor_height']);
        });
    }
};
