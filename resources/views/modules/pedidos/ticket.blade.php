<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $pedido->codigo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; color: #111; background: #f3f4f6; padding: 16px; }
        .ticket { width: 300px; margin: 0 auto; background: #fff; padding: 18px; }
        .center { text-align: center; }
        .muted { color: #555; }
        h1 { font-size: 16px; }
        .sm { font-size: 11px; }
        .row { display: flex; justify-content: space-between; font-size: 12px; margin: 2px 0; }
        .hr { border-top: 1px dashed #999; margin: 8px 0; }
        table { width: 100%; font-size: 12px; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .tot { font-size: 14px; font-weight: bold; }
        .btn { display:block; width:300px; margin: 14px auto 0; padding: 10px; text-align:center; background:#ea580c; color:#fff; border:0; border-radius:8px; font-family: sans-serif; font-size: 13px; cursor:pointer; }
        @media print { body { background:#fff; padding:0; } .no-print { display:none; } .ticket { width: 80mm; } }
    </style>
</head>
<body onload="window.print()">
    @php $m = $config->moneda; @endphp
    <div class="ticket">
        <div class="center">
            <h1>{{ $config->nombre }}</h1>
            @if($config->ruc)<p class="sm muted">RUC: {{ $config->ruc }}</p>@endif
            @if($config->direccion)<p class="sm muted">{{ $config->direccion }}</p>@endif
            @if($config->telefono)<p class="sm muted">Tel: {{ $config->telefono }}</p>@endif
        </div>
        <div class="hr"></div>
        <div class="row"><span>Pedido:</span><span>{{ $pedido->codigo }}</span></div>
        <div class="row"><span>Fecha:</span><span>{{ $pedido->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Mesa:</span><span>{{ $pedido->mesa?->nombre ?? ucfirst($pedido->tipo) }}</span></div>
        <div class="row"><span>Cliente:</span><span>{{ $pedido->cliente?->nombre ?? 'Genérico' }}</span></div>
        <div class="row"><span>Atendió:</span><span>{{ $pedido->user?->name ?? '—' }}</span></div>
        <div class="hr"></div>
        <table>
            <tbody>
                @foreach($pedido->items as $it)
                    <tr>
                        <td>{{ $it->cantidad }}x {{ $it->nombre_producto }}</td>
                        <td style="text-align:right; white-space:nowrap;">{{ $m }} {{ number_format($it->subtotal,2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="hr"></div>
        <div class="row"><span>Subtotal</span><span>{{ $m }} {{ number_format($pedido->subtotal,2) }}</span></div>
        @if($pedido->descuento > 0)<div class="row"><span>Descuento</span><span>- {{ $m }} {{ number_format($pedido->descuento,2) }}</span></div>@endif
        <div class="row"><span>IGV ({{ $config->igv }}%)</span><span>{{ $m }} {{ number_format($pedido->impuesto,2) }}</span></div>
        <div class="row tot"><span>TOTAL</span><span>{{ $m }} {{ number_format($pedido->total,2) }}</span></div>
        <div class="hr"></div>
        <div class="row"><span>Pago:</span><span>{{ $pedido->metodo_pago ? ucfirst($pedido->metodo_pago) : 'Pendiente' }}</span></div>
        @if($pedido->puntos_ganados > 0)<div class="row"><span>Puntos ganados:</span><span>+{{ $pedido->puntos_ganados }}</span></div>@endif
        @if($pedido->puntos_usados > 0)<div class="row"><span>Puntos canjeados:</span><span>-{{ $pedido->puntos_usados }}</span></div>@endif
        <div class="hr"></div>
        <p class="center sm muted">¡Gracias por su preferencia!</p>
        <p class="center sm muted">{{ $config->nombre }}</p>
    </div>
    <button class="btn no-print" onclick="window.print()">Imprimir ticket</button>
</body>
</html>
