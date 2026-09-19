<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();          // abrió
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->nullOnDelete();       // cerró
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('efectivo_esperado', 10, 2)->nullable();   // calculado al cerrar
            $table->decimal('monto_contado', 10, 2)->nullable();       // efectivo real contado
            $table->decimal('diferencia', 10, 2)->nullable();          // contado - esperado
            $table->text('notas_apertura')->nullable();
            $table->text('notas_cierre')->nullable();
            $table->timestamp('abierta_at')->nullable();
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
