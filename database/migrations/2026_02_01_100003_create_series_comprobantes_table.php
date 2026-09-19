<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Series y correlativos por tenant. Garantiza numeración consecutiva y sin
 * huecos por (restaurante, país, tipo, serie). La reserva del correlativo se
 * hace con bloqueo pesimista dentro de una transacción.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->string('pais', 2)->default('PE');
            $table->string('tipo', 20);
            $table->string('serie', 8);
            $table->unsignedBigInteger('correlativo_actual')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['restaurante_id', 'pais', 'tipo', 'serie'], 'uq_serie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_comprobantes');
    }
};
