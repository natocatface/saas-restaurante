<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->cascadeOnDelete();
            $table->string('accion', 30);   // emitir | anular | nota_credito | consultar
            $table->string('estado', 20)->nullable();
            $table->string('codigo')->nullable();
            $table->text('mensaje')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobante_logs');
    }
};
