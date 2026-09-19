<?php

namespace App\Facturacion\Services;

use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Data\ResultadoFacturacion;
use App\Facturacion\Enums\EstadoComprobante;
use App\Facturacion\Models\Comprobante;
use App\Facturacion\Models\ComprobanteLog;
use App\Models\SerieComprobante;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de aplicación (use case). Coordina:
 *  1) reserva de correlativo (transacción + lock),
 *  2) persistencia del comprobante en estado 'enviando',
 *  3) delegación en la estrategia de país vía FacturacionManager,
 *  4) actualización de estado + bitácora de auditoría.
 *
 * Es lo que el ERP realmente invoca (directa o vía Job).
 */
class EmisorService
{
    public function __construct(private readonly FacturacionManager $manager) {}

    public function emitir(ComprobanteData $data): Comprobante
    {
        $comprobante = $this->crearRegistro($data, EstadoComprobante::ENVIANDO);

        $resultado = $this->manager->emitir($this->conNumero($data, $comprobante));
        $this->aplicarResultado($comprobante, 'emitir', $resultado);

        return $comprobante->refresh();
    }

    public function anular(Comprobante $comprobante, ComprobanteData $data, string $motivo): Comprobante
    {
        $resultado = $this->manager->anular($data, $motivo);
        $this->aplicarResultado($comprobante, 'anular', $resultado);

        return $comprobante->refresh();
    }

    /** Reserva de correlativo consecutivo con bloqueo pesimista. */
    private function crearRegistro(ComprobanteData $data, EstadoComprobante $estado): Comprobante
    {
        return DB::transaction(function () use ($data, $estado) {
            $serie = SerieComprobante::where('pais', $data->pais->value)
                ->where('tipo', $data->tipo->value)
                ->where('activa', true)
                ->lockForUpdate()
                ->first();

            $numeroSerie = $serie?->serie ?? ($data->tipo->value === 'factura' ? 'F001' : 'B001');
            $correlativo = $serie ? $serie->correlativo_actual + 1 : 1;

            if ($serie) {
                $serie->update(['correlativo_actual' => $correlativo]);
            }

            return Comprobante::create([
                'pedido_id'  => $data->meta['pedido_id'] ?? null,
                'pais'       => $data->pais->value,
                'tipo'       => $data->tipo->value,
                'serie'      => $numeroSerie,
                'correlativo' => $correlativo,
                'moneda'     => $data->moneda,
                'subtotal'   => $data->subtotal,
                'impuesto'   => $data->impuestoTotal,
                'total'      => $data->total,
                'estado'     => $estado,
            ]);
        });
    }

    private function conNumero(ComprobanteData $d, Comprobante $c): ComprobanteData
    {
        return new ComprobanteData(
            pais: $d->pais, tipo: $d->tipo, emisor: $d->emisor, receptor: $d->receptor,
            items: $d->items, moneda: $d->moneda, subtotal: $d->subtotal,
            impuestoTotal: $d->impuestoTotal, total: $d->total, fechaEmision: $d->fechaEmision,
            serie: $c->serie, correlativo: $c->correlativo, referencia: $d->referencia, meta: $d->meta,
        );
    }

    private function aplicarResultado(Comprobante $c, string $accion, ResultadoFacturacion $r): void
    {
        $c->update([
            'estado'               => $r->estado,
            'identificador_fiscal' => $r->identificadorFiscal,
            'hash'                 => $r->hash,
            'xml_path'             => $r->xml,
            'pdf_path'             => $r->pdf,
            'codigo_respuesta'     => $r->codigoRespuesta,
            'mensaje'              => $r->mensaje,
            'intentos'             => $c->intentos + 1,
            'enviado_at'           => now(),
        ]);

        ComprobanteLog::create([
            'comprobante_id' => $c->id,
            'accion'         => $accion,
            'estado'         => $r->estado->value,
            'codigo'         => $r->codigoRespuesta,
            'mensaje'        => $r->mensaje,
            'payload'        => $r->crudo,
        ]);
    }
}
