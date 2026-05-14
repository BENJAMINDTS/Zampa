<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reemplaza los campos de horario planos en tapa_configs por una tabla
 * kitchen_schedules que permite N tramos de apertura de cocina por restaurante.
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
        Schema::create('kitchen_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tapa_config_id')->constrained()->onDelete('cascade');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();
        });

        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->dropColumn(['kitchen_opens_at', 'kitchen_closes_at']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_schedules');

        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->time('kitchen_opens_at')->nullable();
            $table->time('kitchen_closes_at')->nullable();
        });
    }
};
