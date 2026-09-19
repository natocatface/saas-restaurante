<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->enum('tipo', ['ganado', 'canjeado']);
            $table->integer('puntos');                 // positivo
            $table->decimal('valor', 10, 2)->default(0); // equivalente en dinero (para canje)
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_puntos');
    }
};
