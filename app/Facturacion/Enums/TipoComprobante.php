<?php

namespace App\Facturacion\Enums;

/**
 * Tipo de comprobante neutro respecto al país.
 * Cada adaptador traduce estos valores a su catálogo local
 * (p.ej. SUNAT: 01=Factura, 03=Boleta, 07=Nota de crédito).
 */
enum TipoComprobante: string
{
    case FACTURA        = 'factura';
    case BOLETA         = 'boleta';
    case NOTA_CREDITO   = 'nota_credito';
    case NOTA_DEBITO    = 'nota_debito';
}
