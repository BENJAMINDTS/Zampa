<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migra el campo allergen_type (string único) a allergen_types (JSON array).
 * Un ingrediente ahora puede pertenecer a varios alérgenos del Reglamento UE 1169/2011.
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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->json('allergen_types')->nullable()->after('is_allergen');
        });

        // Migra datos existentes: allergen_type string → allergen_types JSON array
        DB::statement("
            UPDATE ingredients
            SET allergen_types = JSON_ARRAY(allergen_type)
            WHERE allergen_type IS NOT NULL AND allergen_type != ''
        ");

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('allergen_type');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('allergen_type')->nullable()->after('is_allergen');
        });

        // Restaura el primer elemento del array como allergen_type
        DB::statement("
            UPDATE ingredients
            SET allergen_type = JSON_UNQUOTE(JSON_EXTRACT(allergen_types, '$[0]'))
            WHERE allergen_types IS NOT NULL AND JSON_LENGTH(allergen_types) > 0
        ");

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('allergen_types');
        });
    }
};
