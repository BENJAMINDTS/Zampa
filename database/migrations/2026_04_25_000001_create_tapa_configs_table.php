<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla tapa_configs para configuración de tapas por restaurante.
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
        Schema::create('tapa_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('tapas_enabled')->default(false);
            $table->boolean('tapas_free')->default(true);
            $table->unsignedInteger('max_tapa_variants')->default(3);
            $table->decimal('tapa_price', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tapa_configs');
    }
};
