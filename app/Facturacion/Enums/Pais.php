<?php

namespace App\Facturacion\Enums;

/**
 * Países soportados por el motor de facturación electrónica.
 * El "value" es el código ISO-3166 alfa-2 usado como clave de estrategia.
 */
enum Pais: string
{
    case PE = 'PE'; // Perú  - SUNAT
    case CO = 'CO'; // Colombia - DIAN
    case CL = 'CL'; // Chile - SII
    case AR = 'AR'; // Argentina - ARCA (ex AFIP)
    case MX = 'MX'; // México - SAT

    public function autoridad(): string
    {
        return match ($this) {
            self::PE => 'SUNAT',
            self::CO => 'DIAN',
            self::CL => 'SII',
            self::AR => 'ARCA',
            self::MX => 'SAT',
        };
    }
}
