<?php

namespace App\Facturacion\Paises\Peru;

use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Enums\TipoComprobante;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;

/**
 * Traduce el DTO neutro del dominio (ComprobanteData) a los modelos de Greenter.
 * Aquí —y solo aquí— viven los catálogos y códigos específicos de SUNAT.
 */
class SunatMapper
{
    /** Catálogo 01: tipo de comprobante. */
    private const TIPO_DOC = [
        'factura'      => '01',
        'boleta'       => '03',
        'nota_credito' => '07',
        'nota_debito'  => '08',
    ];

    public function __construct(private readonly NumberToLetters $letras) {}

    public function esBoleta(ComprobanteData $c): bool
    {
        return $c->tipo === TipoComprobante::BOLETA;
    }

    public function tipoDoc(ComprobanteData $c): string
    {
        return self::TIPO_DOC[$c->tipo->value] ?? '03';
    }

    /** Construye una Factura o Boleta de Greenter. */
    public function invoice(ComprobanteData $c): Invoice
    {
        [$gravadas, $igv, $total] = $this->totales($c);

        $invoice = (new Invoice())
            ->setUblVersion($c->meta['ubl_version'] ?? '2.1')
            ->setTipoOperacion($c->meta['tipo_operacion'] ?? '0101') // venta interna
            ->setTipoDoc($this->tipoDoc($c))
            ->setSerie($c->serie ?? ($this->esBoleta($c) ? 'B001' : 'F001'))
            ->setCorrelativo((string) ($c->correlativo ?? 1))
            ->setFechaEmision(\DateTime::createFromInterface($c->fechaEmision))
            ->setTipoMoneda($c->moneda)
            ->setCompany($this->company($c))
            ->setClient($this->client($c))
            ->setMtoOperGravadas($gravadas)
            ->setMtoIGV($igv)
            ->setTotalImpuestos($igv)
            ->setValorVenta($gravadas)
            ->setSubTotal($total)
            ->setMtoImpVenta($total)
            ->setDetails($this->detalles($c))
            ->setLegends([
                (new Legend())->setCode('1000')
                    ->setValue($this->letras->convertir($total, $this->nombreMoneda($c->moneda))),
            ]);

        return $invoice;
    }

    /** Construye una Nota de Crédito de Greenter. */
    public function note(ComprobanteData $c): Note
    {
        [$gravadas, $igv, $total] = $this->totales($c);
        $ref = $c->referencia;

        return (new Note())
            ->setUblVersion($c->meta['ubl_version'] ?? '2.1')
            ->setTipoDoc('07')
            ->setSerie($c->serie ?? 'FC01')
            ->setCorrelativo((string) ($c->correlativo ?? 1))
            ->setFechaEmision(\DateTime::createFromInterface($c->fechaEmision))
            ->setTipDocAfectado($ref['tipo_doc'] ?? '01')
            ->setNumDocfectado($ref['numero'] ?? '')
            ->setCodMotivo($ref['cod_motivo'] ?? '01') // 01: anulación de la operación
            ->setDesMotivo($ref['motivo'] ?? 'Anulación de la operación')
            ->setTipoMoneda($c->moneda)
            ->setCompany($this->company($c))
            ->setClient($this->client($c))
            ->setMtoOperGravadas($gravadas)
            ->setMtoIGV($igv)
            ->setTotalImpuestos($igv)
            ->setMtoImpVenta($total)
            ->setDetails($this->detalles($c))
            ->setLegends([
                (new Legend())->setCode('1000')
                    ->setValue($this->letras->convertir($total, $this->nombreMoneda($c->moneda))),
            ]);
    }

    public function company(ComprobanteData $c): Company
    {
        return (new Company())
            ->setRuc($c->emisor->documento)
            ->setRazonSocial($c->emisor->razonSocial)
            ->setNombreComercial($c->emisor->nombreComercial ?? $c->emisor->razonSocial)
            ->setAddress((new Address())
                ->setUbigueo($c->emisor->ubigeo ?? '150101')
                ->setDireccion($c->emisor->direccion ?? '-'));
    }

    public function client(ComprobanteData $c): Client
    {
        return (new Client())
            ->setTipoDoc($this->tipoDocReceptor($c->receptor->tipoDocumento))
            ->setNumDoc($c->receptor->numeroDocumento)
            ->setRznSocial($c->receptor->razonSocial);
    }

    /** @return SaleDetail[] */
    private function detalles(ComprobanteData $c): array
    {
        return array_map(function ($it) {
            $porc = round($it->tasaImpuesto * 100, 2);
            $precioConIgv = round($it->precioUnitario * (1 + $it->tasaImpuesto), 2);

            return (new SaleDetail())
                ->setCodProducto((string) ($it->codigo ?? ''))
                ->setUnidad($it->unidad)
                ->setDescripcion($it->descripcion)
                ->setCantidad($it->cantidad)
                ->setMtoValorUnitario($it->precioUnitario)
                ->setMtoValorVenta($it->valorVenta)
                ->setMtoBaseIgv($it->valorVenta)
                ->setPorcentajeIgv($porc)
                ->setIgv($it->impuesto)
                ->setTipAfeIgv('10')            // Gravado - Operación Onerosa
                ->setTotalImpuestos($it->impuesto)
                ->setMtoPrecioUnitario($precioConIgv);
        }, $c->items);
    }

    /** @return array{0: float, 1: float, 2: float} [gravadas, igv, total] */
    private function totales(ComprobanteData $c): array
    {
        $gravadas = 0.0;
        $igv = 0.0;
        foreach ($c->items as $it) {
            $gravadas += $it->valorVenta;
            $igv += $it->impuesto;
        }
        $gravadas = round($gravadas, 2);
        $igv = round($igv, 2);

        return [$gravadas, $igv, round($gravadas + $igv, 2)];
    }

    /** Catálogo 06: tipo de documento de identidad del receptor. */
    private function tipoDocReceptor(string $tipo): string
    {
        return match (strtoupper($tipo)) {
            'RUC'          => '6',
            'DNI'          => '1',
            'CE', 'CARNET' => '4',
            'PASAPORTE'    => '7',
            default        => '0', // Otros / sin documento
        };
    }

    private function nombreMoneda(string $moneda): string
    {
        return match ($moneda) {
            'PEN'   => 'SOLES',
            'USD'   => 'DOLARES AMERICANOS',
            'EUR'   => 'EUROS',
            default => $moneda,
        };
    }
}
