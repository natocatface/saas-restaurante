<?php

namespace App\Facturacion\Data;

/** Línea de detalle del comprobante. Inmutable. */
final class ItemData
{
    public function __construct(
        public readonly string $descripcion,
        public readonly float $cantidad,
        public readonly float $precioUnitario,   // sin impuesto
        public readonly float $valorVenta,       // cantidad * precioUnitario
        public readonly float $impuesto,         // monto de impuesto de la línea
        public readonly string $unidad = 'NIU',  // unidad de medida (catálogo local)
        public readonly ?string $codigo = null,
        public readonly float $tasaImpuesto = 0.18,
    ) {}
}
