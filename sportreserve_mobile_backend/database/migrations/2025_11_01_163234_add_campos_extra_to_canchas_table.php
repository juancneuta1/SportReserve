<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            // 🟢 Campos opcionales nuevos
            if (!Schema::hasColumn('canchas', 'servicios')) {
                $table->string('servicios')->nullable();
            }
            if (!Schema::hasColumn('canchas', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
            if (!Schema::hasColumn('canchas', 'disponibilidad')) {
                $table->boolean('disponibilidad')->default(true);
            }
            if (!Schema::hasColumn('canchas', 'imagen')) {
                $table->string('imagen')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropColumn(['servicios', 'descripcion', 'disponibilidad', 'imagen']);
        });
    }
};
