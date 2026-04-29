<?php

/**
 * @author BenjaminDTS
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('name');
            $table->string('address')->nullable()->after('business_name');
            $table->decimal('lat', 10, 7)->nullable()->after('address');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });

        // SQLite (tests) treats enum as string and allows any value without ALTER.
        // Only run the ENUM modification on MySQL/MariaDB.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE users MODIFY COLUMN role ENUM('admin','waiter','kitchen','superadmin') DEFAULT 'admin'"
            );
        }
    }

    /**
     * @return void
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE users MODIFY COLUMN role ENUM('admin','waiter','kitchen') DEFAULT 'admin'"
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'address', 'lat', 'lng']);
        });
    }
};
