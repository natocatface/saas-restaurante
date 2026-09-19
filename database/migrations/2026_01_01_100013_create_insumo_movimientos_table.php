<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumo_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 3);          // siempre positivo
            $table->decimal('stock_anterior', 10, 2)->default(0);
            $table->decimal('stock_nuevo', 10, 2)->default(0);
            $table->string('motivo')->nullable();
            $table->string('referencia')->nullable();    // ej. código de pedido
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumo_movimientos');
    }
};
