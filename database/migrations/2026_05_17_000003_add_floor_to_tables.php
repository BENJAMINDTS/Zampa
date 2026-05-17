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
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedTinyInteger('floor')->default(1)->after('rotation');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('floor');
        });
    }
};
