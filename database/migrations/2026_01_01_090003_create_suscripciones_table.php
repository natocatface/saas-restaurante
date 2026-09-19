<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->string('nombre_plan');
            $table->decimal('monto', 10, 2)->default(0);
            $table->enum('intervalo', ['mensual', 'anual'])->default('mensual');
            $table->enum('estado', ['pagado', 'pendiente', 'fallido'])->default('pagado');
            $table->string('metodo_pago')->nullable();
            $table->string('referencia')->nullable();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
