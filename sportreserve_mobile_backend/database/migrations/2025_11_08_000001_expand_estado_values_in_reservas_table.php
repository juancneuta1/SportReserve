<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE reservas DROP CONSTRAINT IF EXISTS reservas_estado_check;");
        DB::statement("ALTER TABLE reservas ADD CONSTRAINT reservas_estado_check CHECK (estado IN ('pendiente','pendiente_validacion','en_espera','confirmada','cancelada'));");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reservas DROP CONSTRAINT IF EXISTS reservas_estado_check;");
        DB::statement("ALTER TABLE reservas ADD CONSTRAINT reservas_estado_check CHECK (estado IN ('pendiente','confirmada','cancelada'));");
    }
};
