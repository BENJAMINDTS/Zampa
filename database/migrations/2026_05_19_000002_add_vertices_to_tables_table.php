<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade soporte de vértices poligonales a los elementos de tipo barra en el plano.
 * Las mesas normales (square, round, rectangle) no usan este campo;
 * solo los elementos decorativos de tipo barra pueden tener polígonos personalizados.
 */
return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->json('vertices')->nullable()->after('floor');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('vertices');
        });
    }
};
