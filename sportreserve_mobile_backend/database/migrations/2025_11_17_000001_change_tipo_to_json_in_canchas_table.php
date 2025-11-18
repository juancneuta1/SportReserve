<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('canchas')) {
            return;
        }

        // Convertir la columna tipo (string) a jsonb para soportar múltiples deportes
        DB::statement("
            ALTER TABLE canchas
            ALTER COLUMN tipo DROP DEFAULT,
            ALTER COLUMN tipo TYPE jsonb
            USING (
                CASE
                    WHEN tipo IS NULL OR tipo = '' THEN '[]'::jsonb
                    ELSE to_jsonb(regexp_split_to_array(tipo, ',\\s*'))
                END
            ),
            ALTER COLUMN tipo SET DEFAULT '[]'::jsonb
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('canchas')) {
            return;
        }

        // Volver a texto plano (unir con coma)
        DB::statement("
            ALTER TABLE canchas
            ALTER COLUMN tipo DROP DEFAULT,
            ALTER COLUMN tipo TYPE varchar(255)
            USING (
                CASE
                    WHEN jsonb_typeof(tipo) = 'array' THEN array_to_string(ARRAY(SELECT jsonb_array_elements_text(tipo)), ', ')
                    ELSE tipo::text
                END
            )
        ");
    }
};
