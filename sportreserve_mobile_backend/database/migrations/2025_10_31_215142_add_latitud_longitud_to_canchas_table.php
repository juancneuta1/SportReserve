<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            if (!Schema::hasColumn('canchas', 'latitud')) {
                $table->decimal('latitud', 10, 8)->default(0);
            }

            if (!Schema::hasColumn('canchas', 'longitud')) {
                $table->decimal('longitud', 11, 8)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            if (Schema::hasColumn('canchas', 'latitud')) {
                $table->dropColumn('latitud');
            }

            if (Schema::hasColumn('canchas', 'longitud')) {
                $table->dropColumn('longitud');
            }
        });
    }


};
