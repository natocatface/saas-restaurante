<?php

namespace App\Facturacion\Services;

use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Data\EmisorData;
use App\Facturacion\Data\ItemData;
use App\Facturacion\Data\ReceptorData;
use App\Facturacion\Enums\Pais;
use App\Facturacion\Enums\TipoComprobante;
use App\Models\Pedido;
use App\Models\Restaurante;

/**
 * Capa anticorrupción (ACL): convierte un modelo del ERP (Pedido) en el DTO
 * neutro del dominio de facturación. Es el ÚNICO punto que conoce ambos mundos,
 * de modo que el core de facturación jamás depende de Eloquent ni del ERP.
 */
class ComprobanteBuilder
{
    /**
     * @param  string  $tipo  'factura' | 'boleta'
     */
    public function desdePedido(Pedido $pedido, string $tipo = 'boleta'): ComprobanteData
    {
        $rest = $pedido->restaurante ?? Restaurante::actual();
        $cliente = $pedido->cliente;

        $pais = Pais::from(strtoupper($rest->pais ?? 'PE'));
        $tasa = (float) ($rest->igv ?? 18) / 100;
        $monedaIso = in_array($rest->moneda, ['PEN', 'USD', 'EUR'], true) ? $rest->moneda : 'PEN';

        $items = $pedido->items->map(function ($it) use ($tasa) {
            $valorVenta = round((float) $it->precio * $it->cantidad / (1 + $tasa), 2);
            $impuesto   = round($valorVenta * $tasa, 2);

            return new ItemData(
                descripcion: $it->nombre ?? $it->producto?->nombre ?? 'Ítem',
                cantidad: (float) $it->cantidad,
                precioUnitario: round((float) $it->precio / (1 + $tasa), 2),
                valorVenta: $valorVenta,
                impuesto: $impuesto,
                codigo: (string) ($it->producto_id ?? ''),
                tasaImpuesto: $tasa,
            );
        })->all();

        return new ComprobanteData(
            pais: $pais,
            tipo: TipoComprobante::from($tipo),
            emisor: new EmisorData(
                documento: (string) $rest->ruc,
                razonSocial: $rest->nombre,
                nombreComercial: $rest->nombre,
                direccion: $rest->direccion,
                ubigeo: $rest->ubigeo,
            ),
            receptor: new ReceptorData(
                tipoDocumento: $cliente?->tipo_documento ?? 'DNI',
                numeroDocumento: $cliente?->documento ?? '00000000',
                razonSocial: $cliente?->nombre ?? 'Cliente varios',
                email: $cliente?->email,
            ),
            items: $items,
            moneda: $monedaIso,
            subtotal: (float) $pedido->subtotal,
            impuestoTotal: (float) $pedido->impuesto,
            total: (float) $pedido->total,
            fechaEmision: new \DateTimeImmutable(),
            meta: ['pedido_id' => $pedido->id, 'pedido_codigo' => $pedido->codigo],
        );
    }
}
