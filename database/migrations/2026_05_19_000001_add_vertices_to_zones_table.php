<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade soporte de vértices poligonales a las zonas del plano.
 * Solo las zonas (y la barra) pueden tener polígonos personalizados;
 * las mesas normales usan únicamente formas predefinidas.
 */
return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->json('vertices')->nullable()->after('floor');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('vertices');
        });
    }
};
