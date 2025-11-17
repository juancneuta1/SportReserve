<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remitente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinatario_id')->constrained('users')->cascadeOnDelete();
            $table->string('asunto', 150);
            $table->text('contenido');
            $table->boolean('leido')->default(false)->index();
            $table->timestamps();

            $table->index('destinatario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
