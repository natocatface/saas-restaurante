<?php

namespace App\Facturacion\Data;

/** Datos del emisor (el restaurante/tenant). Inmutable. */
final class EmisorData
{
    public function __construct(
        public readonly string $documento,      // RUC / NIT / RUT según país
        public readonly string $razonSocial,
        public readonly ?string $nombreComercial = null,
        public readonly ?string $direccion = null,
        public readonly ?string $ubigeo = null,  // código geográfico local
    ) {}
}
