<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de facturación electrónica por empresa (tenant), usados por el
 * adaptador de SUNAT. La clave SOL se almacena cifrada (cast 'encrypted').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurantes', 'ubigeo')) {
                $table->string('ubigeo', 6)->nullable()->after('direccion');
            }
            if (! Schema::hasColumn('restaurantes', 'fe_activo')) {
                $table->boolean('fe_activo')->default(false)->after('igv');
            }
            if (! Schema::hasColumn('restaurantes', 'sunat_ambiente')) {
                $table->enum('sunat_ambiente', ['beta', 'produccion'])->default('beta')->after('fe_activo');
            }
            if (! Schema::hasColumn('restaurantes', 'sunat_sol_user')) {
                $table->string('sunat_sol_user')->nullable()->after('sunat_ambiente');
            }
            if (! Schema::hasColumn('restaurantes', 'sunat_sol_pass')) {
                $table->text('sunat_sol_pass')->nullable()->after('sunat_sol_user'); // cifrada
            }
            if (! Schema::hasColumn('restaurantes', 'sunat_cert_path')) {
                $table->string('sunat_cert_path')->nullable()->after('sunat_sol_pass');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            foreach (['ubigeo', 'fe_activo', 'sunat_ambiente', 'sunat_sol_user', 'sunat_sol_pass', 'sunat_cert_path'] as $col) {
                if (Schema::hasColumn('restaurantes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
