<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // 🔹 Verificamos si no existe antes de agregarlo (evita errores en despliegues)
            if (!Schema::hasColumn('reservas', 'comprobante')) {
                $table->string('comprobante')->nullable()->after('estado')
                    ->comment('Ruta del comprobante de pago subido por el usuario');
            }

            // 🔹 Campo para estados más claros (opcional si no existe)
            if (!Schema::hasColumn('reservas', 'estado')) {
                $table->string('estado')->default('pendiente')
                    ->comment('Estado de la reserva: pendiente, pendiente_validacion, confirmada, cancelada');
            }
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (Schema::hasColumn('reservas', 'comprobante')) {
                $table->dropColumn('comprobante');
            }
        });
    }
};
