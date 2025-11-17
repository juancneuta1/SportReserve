<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            // ✅ Campo para indicar si la cancha está disponible
            $table->boolean('disponibilidad')->default(true)->after('precio_por_hora');
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropColumn('disponibilidad');
        });
    }
};

