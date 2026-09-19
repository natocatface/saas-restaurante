<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @php
        $titulos = ['01' => 'FACTURA ELECTRÓNICA', '03' => 'BOLETA DE VENTA ELECTRÓNICA', '07' => 'NOTA DE CRÉDITO ELECTRÓNICA', '08' => 'NOTA DE DÉBITO ELECTRÓNICA'];
        $titulo = $titulos[$tipoCode] ?? 'COMPROBANTE ELECTRÓNICO';
        $mon = $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda;
        $cli = $comprobante->pedido?->cliente;
        $items = $comprobante->pedido?->items ?? collect();
    @endphp
    <title>{{ $comprobante->serie }}-{{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; background: #e5e7eb; padding: 20px; font-size: 12px; }
        .page { width: 210mm; min-height: 148mm; margin: 0 auto; background: #fff; padding: 22px 26px; box-shadow: 0 6px 24px rgba(0,0,0,.12); }
        .top { display: flex; justify-content: space-between; gap: 20px; }
        .emisor h1 { font-size: 17px; color: #111827; margin-bottom: 4px; }
        .emisor p { color: #4b5563; line-height: 1.5; }
        .box { border: 2px solid #dc2626; border-radius: 10px; text-align: center; padding: 12px 18px; min-width: 220px; }
        .box .ruc { font-size: 13px; font-weight: bold; color: #111827; }
        .box .tit { font-size: 13px; font-weight: bold; color: #dc2626; margin: 6px 0; }
        .box .num { font-size: 16px; font-weight: bold; letter-spacing: .5px; }
        .flag { display:inline-block; width: 34px; height: 22px; vertical-align: middle; border:1px solid #e5e7eb; border-radius:3px; overflow:hidden; }
        .datos { margin-top: 18px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
        .datos .row { display: flex; gap: 8px; margin: 3px 0; }
        .datos .row b { min-width: 130px; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        thead th { background: #f97316; color: #fff; font-size: 11px; padding: 8px 8px; text-align: left; }
        thead th.r, tbody td.r { text-align: right; }
        tbody td { padding: 7px 8px; border-bottom: 1px solid #f1f5f9; }
        .tots { margin-top: 14px; display: flex; justify-content: flex-end; }
        .tots table { width: 300px; margin: 0; }
        .tots td { padding: 5px 8px; border: 0; }
        .tots .lbl { color: #6b7280; text-align: right; }
        .tots .val { text-align: right; font-weight: bold; }
        .tots .grand td { border-top: 2px solid #111827; font-size: 15px; }
        .foot { margin-top: 18px; display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; }
        .letras { flex: 1; font-style: italic; color: #374151; }
        .qr { text-align: center; }
        .qr img { width: 130px; height: 130px; }
        .qr .hash { font-size: 9px; color: #9ca3af; word-break: break-all; max-width: 160px; margin-top: 4px; }
        .estado { display:inline-block; padding: 2px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .e-aceptado { background:#d1fae5; color:#065f46; } .e-pendiente { background:#fef3c7; color:#92400e; }
        .e-rechazado, .e-error { background:#fee2e2; color:#991b1b; } .e-otro { background:#e5e7eb; color:#374151; }
        .btnbar { width: 210mm; margin: 14px auto 0; text-align: center; }
        .btn { display:inline-block; padding: 10px 20px; background:#ea580c; color:#fff; border:0; border-radius:8px; font-size: 13px; cursor:pointer; text-decoration:none; }
        .btn.sec { background:#475569; }
        @media print { body { background:#fff; padding:0; } .no-print { display:none !important; } .page { box-shadow:none; width:auto; margin:0; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="top">
            <div class="emisor">
                <span class="flag">
                    <svg viewBox="0 0 9 6" width="34" height="22"><rect width="3" height="6" x="0" fill="#D91023"/><rect width="3" height="6" x="3" fill="#fff"/><rect width="3" height="6" x="6" fill="#D91023"/></svg>
                </span>
                <h1>{{ $rest->nombre }}</h1>
                <p>
                    @if($rest->direccion){{ $rest->direccion }}<br>@endif
                    @if($rest->telefono)Tel: {{ $rest->telefono }} · @endif{{ $rest->email }}
                </p>
            </div>
            <div class="box">
                <div class="ruc">R.U.C. {{ $rest->ruc ?? '—' }}</div>
                <div class="tit">{{ $titulo }}</div>
                <div class="num">{{ $comprobante->serie }}-{{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        @php
            $ecls = match($comprobante->estado->value) {
                'aceptado' => 'e-aceptado', 'pendiente' => 'e-pendiente',
                'rechazado','error' => 'e-rechazado', default => 'e-otro',
            };
        @endphp

        <div class="datos">
            <div class="row"><b>Cliente:</b><span>{{ $cli?->nombre ?? 'Cliente varios' }}</span></div>
            <div class="row"><b>Documento:</b><span>{{ $cli?->documento ?? '—' }}</span></div>
            <div class="row"><b>Fecha de emisión:</b><span>{{ $comprobante->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="row"><b>Moneda:</b><span>{{ $comprobante->moneda }}</span></div>
            <div class="row"><b>Estado SUNAT:</b><span class="estado {{ $ecls }}">{{ $comprobante->estado->value }}</span></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:48px">Cant.</th>
                    <th>Descripción</th>
                    <th class="r" style="width:90px">P. Unit.</th>
                    <th class="r" style="width:90px">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $it)
                    <tr>
                        <td>{{ $it->cantidad }}</td>
                        <td>{{ $it->nombre ?? $it->producto?->nombre ?? 'Ítem' }}</td>
                        <td class="r">{{ $mon }} {{ number_format((float)$it->precio, 2) }}</td>
                        <td class="r">{{ $mon }} {{ number_format((float)$it->precio * $it->cantidad, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:14px">Sin detalle disponible.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="tots">
            <table>
                <tr><td class="lbl">Op. Gravada</td><td class="val">{{ $mon }} {{ number_format((float)$comprobante->subtotal, 2) }}</td></tr>
                <tr><td class="lbl">IGV</td><td class="val">{{ $mon }} {{ number_format((float)$comprobante->impuesto, 2) }}</td></tr>
                <tr class="grand"><td class="lbl">IMPORTE TOTAL</td><td class="val">{{ $mon }} {{ number_format((float)$comprobante->total, 2) }}</td></tr>
            </table>
        </div>

        <div class="foot">
            <div class="letras">
                <b>Son:</b> {{ $enLetras }}.
                <br><small style="color:#9ca3af">Representación impresa del comprobante electrónico. Consulte su validez en SUNAT.</small>
            </div>
            <div class="qr">
                @if($qr)
                    <img src="{{ $qr }}" alt="QR SUNAT">
                @else
                    <div style="font-size:9px;color:#9ca3af;max-width:150px">QR: instale <code>endroid/qr-code</code>.</div>
                @endif
                @if($comprobante->hash)<div class="hash">{{ $comprobante->hash }}</div>@endif
            </div>
        </div>
    </div>

    <div class="btnbar no-print">
        <a href="#" class="btn" onclick="window.print();return false;">🖨️ Imprimir / Guardar PDF</a>
        <a href="{{ route('facturacion.index') }}" class="btn sec">Volver</a>
    </div>
</body>
</html>
