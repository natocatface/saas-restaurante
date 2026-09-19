<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('icono')->nullable();
            $table->string('color', 20)->default('#7257f0');
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
