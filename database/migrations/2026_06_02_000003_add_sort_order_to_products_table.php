<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade sort_order a products para permitir ordenación manual en la carta.
 * Productos existentes quedan con sort_order=0; el primer drag los ordena correctamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('image');
            $table->index(['user_id', 'sort_order'], 'products_user_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_user_sort_index');
            $table->dropColumn('sort_order');
        });
    }
};
