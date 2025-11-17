<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (! Schema::hasColumn('reservas', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('estado');
            }

            if (! Schema::hasColumn('reservas', 'payment_link')) {
                $table->string('payment_link')->nullable()->after('payment_reference');
            }

            if (! Schema::hasColumn('reservas', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('payment_link');
            }

            if (! Schema::hasColumn('reservas', 'payment_status')) {
                $table->string('payment_status')->default('pendiente_pago')->after('payment_id');
            }

            if (! Schema::hasColumn('reservas', 'payment_detail')) {
                $table->json('payment_detail')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            foreach (['payment_detail', 'payment_status', 'payment_id', 'payment_link', 'payment_reference'] as $column) {
                if (Schema::hasColumn('reservas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
