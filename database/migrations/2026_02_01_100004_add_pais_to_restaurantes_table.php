<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurantes', 'pais')) {
                $table->string('pais', 2)->default('PE')->after('ruc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            if (Schema::hasColumn('restaurantes', 'pais')) {
                $table->dropColumn('pais');
            }
        });
    }
};
