<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->enum('intervalo', ['mensual', 'anual'])->default('mensual');
            $table->integer('max_mesas')->nullable();      // null = ilimitado
            $table->integer('max_productos')->nullable();
            $table->integer('max_usuarios')->nullable();
            $table->json('features')->nullable();
            $table->boolean('destacado')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
