<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte la relación 1:N entre productos y categorías a una M:N.
 * Un producto puede pertenecer a varias categorías simultáneamente.
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
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->primary(['category_id', 'product_id']);
        });

        // Migrar datos existentes: copiar category_id de products al pivot
        DB::statement('
            INSERT INTO category_product (category_id, product_id)
            SELECT category_id, id FROM products
            WHERE category_id IS NOT NULL
        ');

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
        });

        // Restaurar la primera categoría del pivot a la columna
        DB::statement('
            UPDATE products p
            INNER JOIN (
                SELECT product_id, MIN(category_id) AS category_id
                FROM category_product
                GROUP BY product_id
            ) cp ON cp.product_id = p.id
            SET p.category_id = cp.category_id
        ');

        Schema::dropIfExists('category_product');
    }
};
