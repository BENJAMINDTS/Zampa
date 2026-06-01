<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Añade price_monthly, price_yearly, max_staff y max_floors a la tabla plans.
 * Convierte max_tables a nullable (null = ilimitado, plan Premium).
 *
 * @author BenjaminDTS
 */
return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('price_monthly', 8, 2)->default(0)->after('price');
            $table->decimal('price_yearly', 8, 2)->nullable()->after('price_monthly');
            $table->unsignedInteger('max_staff')->nullable()->after('max_tables');
            $table->unsignedTinyInteger('max_floors')->nullable()->after('max_staff');
        });

        // Backfill price_monthly desde price para filas existentes
        DB::table('plans')->update(['price_monthly' => DB::raw('price')]);

        // Convertir max_tables a nullable para soportar ilimitado (Premium)
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_tables')->nullable()->change();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['price_monthly', 'price_yearly', 'max_staff', 'max_floors']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->integer('max_tables')->nullable(false)->default(10)->change();
        });
    }
};
