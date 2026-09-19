<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('promocion_id')->nullable()->after('cliente_id')->constrained('promociones')->nullOnDelete();
            $table->integer('puntos_usados')->default(0)->after('promocion_id');
            $table->integer('puntos_ganados')->default(0)->after('puntos_usados');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promocion_id');
            $table->dropColumn(['puntos_usados', 'puntos_ganados']);
        });
    }
};
