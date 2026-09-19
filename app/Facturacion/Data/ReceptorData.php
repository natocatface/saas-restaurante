<?php

namespace App\Facturacion\Data;

/** Datos del receptor (cliente). Inmutable. */
final class ReceptorData
{
    public function __construct(
        public readonly string $tipoDocumento,   // p.ej. RUC, DNI, CE, sin doc.
        public readonly string $numeroDocumento,
        public readonly string $razonSocial,
        public readonly ?string $direccion = null,
        public readonly ?string $email = null,
    ) {}
}
