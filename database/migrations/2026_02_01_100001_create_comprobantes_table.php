<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurantes')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->string('pais', 2)->default('PE');
            $table->string('tipo', 20);                 // factura | boleta | nota_credito | nota_debito
            $table->string('serie', 8)->nullable();
            $table->unsignedBigInteger('correlativo')->nullable();
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 20)->default('pendiente')->index();
            $table->string('identificador_fiscal')->nullable(); // CDR/CUFE/CAE/UUID
            $table->string('hash')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('codigo_respuesta')->nullable();
            $table->text('mensaje')->nullable();
            $table->unsignedInteger('intentos')->default(0);
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();

            $table->unique(['restaurante_id', 'pais', 'serie', 'correlativo'], 'uq_comprobante_numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
