<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('canchas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('tipo');
            $table->string('ubicacion');
            $table->decimal('precio_por_hora', 10, 2)->default(0.00);
            $table->boolean('disponibilidad')->default(true);
            $table->string('imagen')->nullable();

            // 🔹 Campos nuevos correctamente tipados
            $table->decimal('latitud', 10, 8)->default(0.0);
            $table->decimal('longitud', 11, 8)->default(0.0);

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};
