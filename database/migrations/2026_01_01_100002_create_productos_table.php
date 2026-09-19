<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nombre');
            $table->string('sku')->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->decimal('costo', 10, 2)->default(0);
            $table->string('imagen')->nullable();
            $table->boolean('disponible')->default(true);
            $table->boolean('controla_stock')->default(false);
            $table->integer('stock')->default(0);
            $table->integer('vendidos')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
