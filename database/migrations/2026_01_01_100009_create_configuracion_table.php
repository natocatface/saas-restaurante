<?php

use Illuminate\Database\Migrations\Migration;

/**
 * La configuración del negocio ahora vive en la tabla `restaurantes`
 * (multi-tenant). Esta migración se mantiene como no-op para no romper
 * el historial de migraciones en instalaciones existentes.
 */
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
