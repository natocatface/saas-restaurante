<?php

namespace App\Facturacion\Paises\Peru;

use App\Facturacion\Data\ResultadoFacturacion;
use App\Facturacion\Enums\EstadoComprobante;
use App\Facturacion\Models\Comprobante;
use App\Models\Restaurante;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Resumen Diario de Boletas (SUNAT) — específico de Perú.
 *
 * Las boletas no se envían individualmente: se firman al emitirse y se comunican
 * a SUNAT en lote mediante un Resumen Diario. Este servicio construye ese
 * documento a partir de las boletas pendientes de un tenant y un día, lo envía,
 * consulta el ticket y devuelve el resultado. NO forma parte del contrato neutro
 * FacturadorPais porque es una particularidad de SUNAT.
 */
class SunatResumenService
{
    public function __construct(private readonly SunatConnectionFactory $conexion) {}

    /**
     * @param  Collection<int, Comprobante>  $boletas  Boletas del mismo día a comunicar.
     */
    public function enviar(
        Restaurante $rest,
        Collection $boletas,
        \DateTimeInterface $fechaGeneracion,
        string $correlativo,
        string $estado = '1', // 1=Adicionar (emisión), 3=Anulación
    ): ResultadoFacturacion {
        if ($boletas->isEmpty()) {
            return ResultadoFacturacion::error('No hay boletas pendientes para el resumen.', 'SIN_BOLETAS');
        }

        try {
            $company = $this->company($rest);

            $detalles = $boletas->values()->map(function (Comprobante $b, int $i) use ($estado) {
                return $this->detalle($b, $i + 1, $estado);
            })->all();

            $resumen = (new Summary())
                ->setFecGeneracion(\DateTime::createFromInterface($fechaGeneracion))
                ->setFecResumen(new \DateTime())
                ->setCorrelativo($correlativo)
                ->setCompany($company)
                ->setDetails($detalles);

            $see = $this->conexion->crear($rest->ruc);
            $envio = $see->send($resumen);

            $xml = $see->getFactory()->getLastXml();
            $rutaXml = $this->guardar($rest->ruc, $resumen->getName().'.xml', $xml);

            if (! $envio->isSuccess()) {
                return new ResultadoFacturacion(
                    exito: false,
                    estado: EstadoComprobante::ERROR,
                    xml: $rutaXml,
                    codigoRespuesta: $envio->getError()?->getCode(),
                    mensaje: $envio->getError()?->getMessage() ?? 'Error al enviar el resumen',
                );
            }

            $ticket = $envio->getTicket();
            $status = $see->getStatus($ticket);
            $cdr = $status->getCdrResponse();

            $rutaCdr = ($status->isSuccess() && $status->getCdrZip())
                ? $this->guardar($rest->ruc, "R-{$resumen->getName()}.zip", $status->getCdrZip())
                : null;

            $codigo = (int) ($cdr?->getCode() ?? ($status->isSuccess() ? 0 : 9999));
            $aceptado = $status->isSuccess() && $codigo === 0;

            return new ResultadoFacturacion(
                exito: $aceptado,
                estado: $aceptado ? EstadoComprobante::ACEPTADO : EstadoComprobante::ERROR,
                identificadorFiscal: $ticket,
                xml: $rutaXml,
                pdf: $rutaCdr,
                codigoRespuesta: (string) $codigo,
                mensaje: $cdr?->getDescription() ?? $status->getError()?->getMessage() ?? "Ticket: {$ticket}",
                crudo: ['ticket' => $ticket, 'notes' => $cdr?->getNotes() ?? []],
            );
        } catch (\Throwable $e) {
            return ResultadoFacturacion::error($e->getMessage(), 'EXCEPTION');
        }
    }

    private function detalle(Comprobante $b, int $index, string $estado): SummaryDetail
    {
        $cliente = $b->pedido?->cliente;
        $docNro = $cliente?->documento ?: '00000000';
        // Catálogo 06 simplificado: 8 dígitos => DNI (1); 11 => RUC (6); resto => Otros (0)
        $tipoCliente = match (strlen($docNro)) {
            8       => '1',
            11      => '6',
            default => '0',
        };

        return (new SummaryDetail())
            ->setTipoDoc('03') // Boleta
            ->setSerieNro($b->serie.'-'.$b->correlativo)
            ->setEstado($estado)
            ->setClienteTipo($tipoCliente)
            ->setClienteNro($docNro)
            ->setTotal((float) $b->total)
            ->setMtoOperGravadas((float) $b->subtotal)
            ->setMtoIGV((float) $b->impuesto);
    }

    private function company(Restaurante $rest): Company
    {
        return (new Company())
            ->setRuc($rest->ruc)
            ->setRazonSocial($rest->nombre)
            ->setNombreComercial($rest->nombre)
            ->setAddress((new Address())
                ->setUbigueo($rest->ubigeo ?? '150101')
                ->setDireccion($rest->direccion ?? '-'));
    }

    private function guardar(string $ruc, string $nombre, string $contenido): string
    {
        $ruta = "facturacion/PE/{$ruc}/resumenes/{$nombre}";
        Storage::disk(config('facturacion.disk', 'local'))->put($ruta, $contenido);

        return $ruta;
    }
}
