<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->string('codigo');
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['mesa', 'llevar', 'delivery'])->default('mesa');
            $table->enum('estado', ['pendiente', 'preparando', 'servido', 'pagado', 'cancelado'])->default('pendiente');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'yape', 'plin', 'transferencia'])->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('pagado_at')->nullable();
            $table->unique(['restaurante_id', 'codigo']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
