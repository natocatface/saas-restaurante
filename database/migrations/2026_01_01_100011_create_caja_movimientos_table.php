<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'yape', 'plin', 'transferencia'])->default('efectivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
