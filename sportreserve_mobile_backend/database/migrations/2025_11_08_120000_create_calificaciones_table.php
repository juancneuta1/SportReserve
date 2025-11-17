<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancha_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'cancha_id']);
        });

        DB::statement("ALTER TABLE calificaciones ADD CONSTRAINT calificaciones_estrellas_check CHECK (estrellas BETWEEN 1 AND 5)");

        Schema::table('canchas', function (Blueprint $table) {
            if (! Schema::hasColumn('canchas', 'rating_promedio')) {
                $table->decimal('rating_promedio', 3, 2)->default(0)->after('precio_por_hora');
            }
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE IF EXISTS calificaciones DROP CONSTRAINT IF EXISTS calificaciones_estrellas_check");
        Schema::dropIfExists('calificaciones');

        Schema::table('canchas', function (Blueprint $table) {
            if (Schema::hasColumn('canchas', 'rating_promedio')) {
                $table->dropColumn('rating_promedio');
            }
        });
    }
};
