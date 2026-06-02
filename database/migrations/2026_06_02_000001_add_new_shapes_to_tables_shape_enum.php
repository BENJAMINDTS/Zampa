<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Extends the tables.shape ENUM to support chair, fireplace, pillar and column
 * decorative elements on the visual map.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `tables` MODIFY `shape` ENUM('square','round','rectangle','bar','stool','chair','fireplace','pillar','column') NOT NULL DEFAULT 'square'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `tables` MODIFY `shape` ENUM('square','round','rectangle','bar','stool') NOT NULL DEFAULT 'square'");
        }
    }
};
