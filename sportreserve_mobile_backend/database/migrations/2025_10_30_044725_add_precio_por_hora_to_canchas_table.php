<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            // ✅ Añadimos la columna precio_por_hora si no existe
            if (!Schema::hasColumn('canchas', 'precio_por_hora')) {
                $table->decimal('precio_por_hora', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropColumn('precio_por_hora');
        });
    }
};

