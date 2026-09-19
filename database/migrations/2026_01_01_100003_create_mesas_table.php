<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->string('numero');
            $table->string('nombre')->nullable();
            $table->integer('capacidad')->default(4);
            $table->string('zona')->default('Salón principal');
            $table->enum('estado', ['libre', 'ocupada', 'reservada', 'cuenta'])->default('libre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
