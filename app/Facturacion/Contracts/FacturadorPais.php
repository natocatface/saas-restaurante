<?php

namespace App\Facturacion\Contracts;

use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Data\ResultadoFacturacion;
use App\Facturacion\Enums\Pais;

/**
 * Contrato ÚNICO que todo adaptador de país debe implementar.
 *
 * Es el corazón del patrón Strategy/Adapter: el ERP depende solo de esta
 * interfaz, nunca de una implementación concreta (SUNAT, DIAN, SII...).
 * Agregar un país = crear una nueva clase que implemente esta interfaz
 * y registrarla en el manager. Cero cambios en el código existente (OCP).
 */
interface FacturadorPais
{
    /** País que atiende esta estrategia. */
    public function pais(): Pais;

    /** Emite una factura o boleta. */
    public function emitir(ComprobanteData $comprobante): ResultadoFacturacion;

    /** Anula/da de baja un comprobante ya emitido. */
    public function anular(ComprobanteData $comprobante, string $motivo): ResultadoFacturacion;

    /** Emite una nota de crédito asociada a un comprobante. */
    public function emitirNotaCredito(ComprobanteData $notaCredito): ResultadoFacturacion;

    /** Consulta el estado de un comprobante ante la autoridad fiscal. */
    public function consultarEstado(ComprobanteData $comprobante): ResultadoFacturacion;
}
