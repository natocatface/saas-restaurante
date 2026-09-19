<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->string('nombre_cliente');
            $table->string('telefono')->nullable();
            $table->date('fecha');
            $table->time('hora');
            $table->integer('personas')->default(2);
            $table->enum('estado', ['pendiente', 'confirmada', 'cumplida', 'cancelada'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
