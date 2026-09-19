<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('ruc')->nullable();
            $table->string('direccion')->nullable();
            $table->string('logo')->nullable();
            $table->string('moneda', 10)->default('S/');
            $table->decimal('igv', 5, 2)->default(18);
            $table->decimal('meta_mensual', 12, 2)->default(50000);
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->enum('estado', ['trial', 'activo', 'suspendido', 'cancelado'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurantes');
    }
};
