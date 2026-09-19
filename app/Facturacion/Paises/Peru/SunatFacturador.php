<?php

namespace App\Facturacion\Paises\Peru;

use App\Facturacion\Contracts\FacturadorPais;
use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Data\ResultadoFacturacion;
use App\Facturacion\Enums\EstadoComprobante;
use App\Facturacion\Enums\Pais;
use Greenter\Model\Response\BillResult;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\See;
use Illuminate\Support\Facades\Storage;

/**
 * Adaptador de Perú (SUNAT) — implementación real sobre Greenter.
 *
 * Encapsula el 100% de SUNAT: generación UBL 2.1, firma XMLDSig y envío SOAP,
 * lectura del CDR y almacenamiento de XML/CDR. El resto del sistema solo ve
 * un FacturadorPais y consume ResultadoFacturacion.
 *
 * Requiere: composer require greenter/lite  + certificado .pem (SUNAT_CERT_PATH)
 * + credenciales Clave SOL (SUNAT_SOL_USER / SUNAT_SOL_PASS).
 */
class SunatFacturador implements FacturadorPais
{
    public function __construct(
        private readonly SunatConnectionFactory $conexion,
        private readonly SunatMapper $mapper,
        private readonly array $config = [],
    ) {}

    public function pais(): Pais
    {
        return Pais::PE;
    }

    public function emitir(ComprobanteData $c): ResultadoFacturacion
    {
        try {
            $see = $this->see($c);
            $invoice = $this->mapper->invoice($c);

            // Las boletas NO se envían individualmente: se firman y luego se
            // comunican por Resumen Diario. Las facturas sí van directo a SUNAT.
            if ($this->mapper->esBoleta($c)) {
                $xml = $see->getXmlSigned($invoice);
                $rutaXml = $this->guardar($c, $invoice->getName().'.xml', $xml);

                return new ResultadoFacturacion(
                    exito: true,
                    estado: EstadoComprobante::PENDIENTE, // pendiente de resumen diario
                    hash: $this->hashDesdeXml($xml),
                    xml: $rutaXml,
                    codigoRespuesta: null,
                    mensaje: 'Boleta firmada. Se comunicará a SUNAT por resumen diario.',
                );
            }

            $resultado = $see->send($invoice);
            $xmlFirmado = $see->getFactory()->getLastXml();
            $rutaXml = $this->guardar($c, $invoice->getName().'.xml', $xmlFirmado);

            return $this->desdeBillResult($c, $resultado, $invoice->getName(), $rutaXml, $xmlFirmado);
        } catch (\Throwable $e) {
            return ResultadoFacturacion::error($e->getMessage(), 'EXCEPTION');
        }
    }

    public function emitirNotaCredito(ComprobanteData $c): ResultadoFacturacion
    {
        try {
            $see = $this->see($c);
            $note = $this->mapper->note($c);

            $resultado = $see->send($note);
            $xmlFirmado = $see->getFactory()->getLastXml();
            $rutaXml = $this->guardar($c, $note->getName().'.xml', $xmlFirmado);

            return $this->desdeBillResult($c, $resultado, $note->getName(), $rutaXml, $xmlFirmado);
        } catch (\Throwable $e) {
            return ResultadoFacturacion::error($e->getMessage(), 'EXCEPTION');
        }
    }

    public function anular(ComprobanteData $c, string $motivo): ResultadoFacturacion
    {
        try {
            $see = $this->see($c);

            // Comunicación de Baja (facturas). Las boletas se anulan por
            // resumen diario con estado=3 (fuera del alcance de este método).
            $baja = (new Voided())
                ->setCorrelativo($c->meta['baja_correlativo'] ?? date('Ymd'))
                ->setFecGeneracion(\DateTime::createFromInterface($c->fechaEmision))
                ->setFecComunicacion(new \DateTime())
                ->setCompany($this->mapper->company($c))
                ->setDetails([
                    (new VoidedDetail())
                        ->setTipoDoc($this->mapper->tipoDoc($c))
                        ->setSerie($c->serie ?? 'F001')
                        ->setCorrelativo((string) ($c->correlativo ?? 1))
                        ->setDesMotivoBaja($motivo),
                ]);

            $resultado = $see->send($baja);
            $xmlFirmado = $see->getFactory()->getLastXml();
            $rutaXml = $this->guardar($c, $baja->getName().'.xml', $xmlFirmado);

            if (! $resultado->isSuccess()) {
                return ResultadoFacturacion::error(
                    $resultado->getError()?->getMessage() ?? 'Error al comunicar la baja',
                    $resultado->getError()?->getCode(),
                );
            }

            // La baja devuelve un ticket; se consulta el estado para el CDR.
            $ticket = $resultado->getTicket();
            $status = $see->getStatus($ticket);
            $rutaCdr = $this->guardarCdrDesdeStatus($c, $baja->getName(), $status);

            return new ResultadoFacturacion(
                exito: $status->isSuccess(),
                estado: $status->isSuccess() ? EstadoComprobante::ANULADO : EstadoComprobante::ERROR,
                identificadorFiscal: $ticket,
                xml: $rutaXml,
                pdf: $rutaCdr,
                mensaje: $status->getCdrResponse()?->getDescription() ?? "Baja enviada. Ticket: {$ticket}",
                crudo: ['ticket' => $ticket],
            );
        } catch (\Throwable $e) {
            return ResultadoFacturacion::error($e->getMessage(), 'EXCEPTION');
        }
    }

    public function consultarEstado(ComprobanteData $c): ResultadoFacturacion
    {
        try {
            $ticket = $c->meta['ticket'] ?? null;
            if (! $ticket) {
                return ResultadoFacturacion::error('No hay ticket para consultar el estado.', 'SIN_TICKET');
            }

            $status = $this->see($c)->getStatus($ticket);

            return new ResultadoFacturacion(
                exito: $status->isSuccess(),
                estado: $status->isSuccess() ? EstadoComprobante::ACEPTADO : EstadoComprobante::ERROR,
                identificadorFiscal: $ticket,
                mensaje: $status->getCdrResponse()?->getDescription()
                    ?? $status->getError()?->getMessage(),
            );
        } catch (\Throwable $e) {
            return ResultadoFacturacion::error($e->getMessage(), 'EXCEPTION');
        }
    }

    /* ------------------------------------------------------------------ */

    private function see(ComprobanteData $c): See
    {
        return $this->conexion->crear($c->emisor->documento);
    }

    /** Traduce el BillResult de Greenter (respuesta con CDR) a ResultadoFacturacion. */
    private function desdeBillResult(
        ComprobanteData $c,
        BillResult $resultado,
        string $nombre,
        string $rutaXml,
        string $xmlFirmado,
    ): ResultadoFacturacion {
        if (! $resultado->isSuccess()) {
            return new ResultadoFacturacion(
                exito: false,
                estado: EstadoComprobante::RECHAZADO,
                xml: $rutaXml,
                codigoRespuesta: $resultado->getError()?->getCode(),
                mensaje: $resultado->getError()?->getMessage() ?? 'Rechazado por SUNAT',
            );
        }

        $cdr = $resultado->getCdrResponse();
        $rutaCdr = $this->guardar($c, "R-{$nombre}.zip", $resultado->getCdrZip());

        // Catálogo 25: código 0 = aceptado; 2000-3999 = rechazado; 4000 = observado.
        $codigo = (int) ($cdr?->getCode() ?? 0);
        $estado = match (true) {
            $codigo === 0            => EstadoComprobante::ACEPTADO,
            $codigo >= 4000          => EstadoComprobante::OBSERVADO,
            default                  => EstadoComprobante::RECHAZADO,
        };

        return new ResultadoFacturacion(
            exito: $estado !== EstadoComprobante::RECHAZADO,
            estado: $estado,
            identificadorFiscal: $cdr?->getId(),
            hash: $this->hashDesdeXml($xmlFirmado),
            xml: $rutaXml,
            pdf: $rutaCdr,
            codigoRespuesta: (string) $codigo,
            mensaje: $cdr?->getDescription(),
            crudo: ['notes' => $cdr?->getNotes() ?? []],
        );
    }

    private function guardar(ComprobanteData $c, string $nombre, string $contenido): string
    {
        $ruta = "facturacion/PE/{$c->emisor->documento}/{$nombre}";
        Storage::disk($this->config['disk'] ?? 'local')->put($ruta, $contenido);

        return $ruta;
    }

    private function guardarCdrDesdeStatus(ComprobanteData $c, string $nombre, $status): ?string
    {
        if (! $status->isSuccess() || ! $status->getCdrZip()) {
            return null;
        }

        return $this->guardar($c, "R-{$nombre}.zip", $status->getCdrZip());
    }

    private function hashDesdeXml(string $xml): ?string
    {
        // El hash oficial se extrae de la firma (DigestValue). Aproximación útil
        // para trazabilidad interna mientras no se parsea el nodo de firma.
        if (preg_match('/<ds:DigestValue>(.*?)<\/ds:DigestValue>/s', $xml, $m)) {
            return trim($m[1]);
        }

        return sha1($xml);
    }
}
