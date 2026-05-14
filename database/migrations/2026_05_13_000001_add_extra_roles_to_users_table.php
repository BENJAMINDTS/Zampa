<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Añade las columnas booleanas is_waiter e is_kitchen a la tabla users.
 * Permite que un gerente (admin) asuma también el rol de camarero y/o cocinero
 * sin necesidad de crear cuentas de staff separadas.
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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_waiter')->default(false)->after('role');
            $table->boolean('is_kitchen')->default(false)->after('is_waiter');
        });

        // Backfill: los admins existentes conservan el comportamiento actual
        // (podían acceder a los paneles de barra y cocina antes del multirrol).
        DB::table('users')
            ->where('role', 'admin')
            ->update(['is_waiter' => true, 'is_kitchen' => true]);
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_waiter', 'is_kitchen']);
        });
    }
};
