<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo')->nullable();                 // cupón opcional
            $table->enum('tipo', ['porcentaje', 'monto']);
            $table->decimal('valor', 10, 2)->default(0);
            $table->enum('alcance', ['total', 'producto'])->default('total');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->decimal('min_compra', 10, 2)->nullable();
            $table->date('inicia_at')->nullable();
            $table->date('termina_at')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
