<?php

namespace App\Facturacion\Data;

use App\Facturacion\Enums\Pais;
use App\Facturacion\Enums\TipoComprobante;

/**
 * Representación neutra de un comprobante lista para ser emitida.
 * Es el "lenguaje ubicuo" que todos los adaptadores de país entienden.
 * No contiene ninguna regla específica de una autoridad fiscal.
 */
final class ComprobanteData
{
    /** @param ItemData[] $items */
    public function __construct(
        public readonly Pais $pais,
        public readonly TipoComprobante $tipo,
        public readonly EmisorData $emisor,
        public readonly ReceptorData $receptor,
        public readonly array $items,
        public readonly string $moneda,
        public readonly float $subtotal,
        public readonly float $impuestoTotal,
        public readonly float $total,
        public readonly \DateTimeImmutable $fechaEmision,
        public readonly ?string $serie = null,
        public readonly ?int $correlativo = null,
        public readonly array $referencia = [], // para notas de crédito/débito
        public readonly array $meta = [],        // datos extra por país sin ensuciar el core
    ) {}
}
