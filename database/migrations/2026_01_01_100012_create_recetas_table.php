<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 3)->default(0); // insumo consumido por 1 unidad del producto
            $table->timestamps();

            $table->unique(['producto_id', 'insumo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
