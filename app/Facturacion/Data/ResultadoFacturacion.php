<?php

namespace App\Facturacion\Data;

use App\Facturacion\Enums\EstadoComprobante;

/**
 * Resultado uniforme devuelto por cualquier adaptador de país.
 * El ERP nunca inspecciona respuestas crudas de una autoridad fiscal:
 * solo consume este objeto.
 */
final class ResultadoFacturacion
{
    public function __construct(
        public readonly bool $exito,
        public readonly EstadoComprobante $estado,
        public readonly ?string $identificadorFiscal = null, // CUFE/CDR/CAE/UUID según país
        public readonly ?string $hash = null,
        public readonly ?string $xml = null,                 // ruta o contenido del XML
        public readonly ?string $pdf = null,                 // ruta del PDF/representación impresa
        public readonly ?string $codigoRespuesta = null,
        public readonly ?string $mensaje = null,
        public readonly array $crudo = [],                   // payload original para auditoría
    ) {}

    public static function error(string $mensaje, ?string $codigo = null, array $crudo = []): self
    {
        return new self(
            exito: false,
            estado: EstadoComprobante::ERROR,
            codigoRespuesta: $codigo,
            mensaje: $mensaje,
            crudo: $crudo,
        );
    }
}
