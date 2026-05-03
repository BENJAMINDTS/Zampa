<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade campos de tapa extra y horario de cocina a tapa_configs.
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
        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->boolean('extra_tapa_enabled')->default(false)->after('tapa_price');
            $table->decimal('extra_tapa_price', 8, 2)->nullable()->after('extra_tapa_enabled');
            $table->time('kitchen_opens_at')->nullable()->after('extra_tapa_price');
            $table->time('kitchen_closes_at')->nullable()->after('kitchen_opens_at');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->dropColumn([
                'extra_tapa_enabled',
                'extra_tapa_price',
                'kitchen_opens_at',
                'kitchen_closes_at',
            ]);
        });
    }
};
