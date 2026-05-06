<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade la modalidad de precio al sistema de tapas.
 *
 * 'fixed'       → precio fijo global (tapa_price) para todas las tapas
 * 'per_product' → cada tapa usa el precio individual del producto
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->enum('price_mode', ['fixed', 'per_product'])
                  ->default('fixed')
                  ->after('tapas_free');
        });
    }

    public function down(): void
    {
        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->dropColumn('price_mode');
        });
    }
};
