<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Amplía el sistema de horarios para soportar tramos del negocio
 * (además de cocina) y añade el período de cierre de pedidos.
 *
 * - kitchen_schedules.type: diferencia tramos de cocina vs. negocio.
 *   Todos los registros existentes quedan con type='kitchen'.
 * - tapa_configs.ordering_cutoff_minutes: minutos antes del cierre de
 *   cada tramo de negocio en los que se dejan de aceptar nuevos pedidos.
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
        Schema::table('kitchen_schedules', function (Blueprint $table) {
            $table->enum('type', ['kitchen', 'business'])
                  ->default('kitchen')
                  ->after('tapa_config_id');
        });

        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->unsignedSmallInteger('ordering_cutoff_minutes')
                  ->default(0)
                  ->after('extra_tapa_price');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('kitchen_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('tapa_configs', function (Blueprint $table) {
            $table->dropColumn('ordering_cutoff_minutes');
        });
    }
};
